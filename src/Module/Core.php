<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use stdClass;
use ReflectionObject;
use ReflectionProperty;

use Defuse\Crypto\Key;
use R3m\Io\App;

use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;

use Exception;
use ReflectionException;

use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

class Core
{

    const EXCEPTION_MERGE_ARRAY_OBJECT = 'Cannot merge an array with an object.';
    const EXCEPTION_OBJECT_OUTPUT = 'Unknown output in object.';

    const ATTRIBUTE_EXPLODE = [
        '.'
    ];

    const OBJECT_ARRAY = 'array';
    const OBJECT_OBJECT = 'object';
    const OBJECT_JSON = 'json';
    const OBJECT_JSON_DATA = 'json-data';
    const OBJECT_JSON_LINE = 'json-line';

    const OBJECT_TYPE_ROOT = 'root';
    const OBJECT_TYPE_CHILD = 'child';

    const SHELL_DETACHED = 'detached';
    const SHELL_NORMAL = 'normal';
    const SHELL_PROCESS = 'process';

    const OUTPUT_MODE_IMPLICIT = 'implicit';
    const OUTPUT_MODE_EXPLICIT = 'explicit';
    const OUTPUT_MODE_DEFAULT = Core::OUTPUT_MODE_EXPLICIT;

    const LOCAL = 'local';

    const OUTPUT_MODE = [
        Core::OUTPUT_MODE_IMPLICIT,
        Core::OUTPUT_MODE_EXPLICIT,
    ];

    const MODE_INTERACTIVE = Core::OUTPUT_MODE_IMPLICIT;
    const MODE_PASSIVE = Core::OUTPUT_MODE_EXPLICIT;

    const STREAM = 'stream';
    const FILE = 'file';
    const PROMPT = 'prompt';

    public static function binary(): string|null
    {
        if (array_key_exists('_', $_SERVER)) {
            $dirname = Dir::name($_SERVER['_']);
            return str_replace($dirname, '', $_SERVER['_']);
        }
        return null;
    }

    /**
     * @throws ObjectException
     */
    public static function detach(App $object, $command): bool|array|int|null
    {
        $output = [];
        $error = [];
        return Core::execute($object, $command, $output, $error, Core::SHELL_DETACHED);
    }

    /**
     * @throws ObjectException
     */
    public static function async(App $object, $command): bool|array|int|null
    {
        if (stristr($command, '&') === false) {
            $command .= ' &';
        }
        $output = [];
        $error = [];
        return Core::execute($object, $command, $output, $error, Core::SHELL_PROCESS);
    }

    /**
     * @throws ObjectException
     */
    public static function execute(App $object, $command, &$output = '', &$notification = '', $type = null)
    {
        if ($output === null) {
            $output = '';
        }
        $result = [
            'pid' => getmypid()
        ];
        if (
            in_array(
                $type,
                [
                    Core::SHELL_DETACHED,
                    Core::SHELL_PROCESS
                ]
            )
        ) {
            $pid = pcntl_fork();
            switch ($pid) {
                // fork errror
                case -1 :
                    return false;
                case 0 :
                    //in child process
                    //create a separate process to execute another process (async);
                    $descriptorspec = [
                        0 => ["pipe", "r"],  // stdin
                        1 => ["pipe", "w"],  // stdout
                        2 => ["pipe", "w"],  // stderr
                    ];
                    $data = $object->config('core.execute.data');
                    $object->config('delete', 'core.execute.data');
                    if(empty($data)){
                        $data = $object->config('core.execute.stream.data');
                        $object->config('delete', 'core.execute.stream.data');
                    }
                    if($object->config('core.execute.stream.is.default')){
                        $from = clone $object;
                        if(!$from->has('request')){
                            $from->set('request', $object->request());
                        }
                        $delete = $object->config('core.execute.stream.delete');
                        if(
                            $delete &&
                            is_array($delete)
                        ){
                            foreach($delete as $attribute){
                                $from->delete($attribute);
                            }
                        }
                        $data = Core::object(
                            $from->data(),
                            'json-line'
                        );
                    }
                    $process = proc_open($command, $descriptorspec, $pipes, Dir::current(), null);
                    if($data){
                        fwrite($pipes[0], $data . PHP_EOL);
                    }
                    fclose($pipes[0]);
                    $output = stream_get_contents($pipes[1]);
                    $notification = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    fclose($pipes[1]);
                    proc_close($process);
                    exit(0);
                default :
                    if ($type == Core::SHELL_PROCESS) {
                        pcntl_waitpid(0, $status, WNOHANG);
                        $status = pcntl_wexitstatus($status);
                        $child = [
                            'status' => $status,
                            'pid' => $pid
                        ];
                        $result['child'] = $child;
                        return $result;
                    }
                    //main process (parent)
                    while (pcntl_waitpid(0, $status) != -1) {
                        //add max execution time here / time outs etc..
                        $status = pcntl_wexitstatus($status);
                        $child = [
                            'status' => $status,
                            'pid' => $pid
                        ];
                        $result['child'] = $child;
                    }
            }
            return $result;
        } else {
            $option = $object->config('core.execute.mode');
            if($object->config('core.execute.stream.init')){
                $option = Core::STREAM;
                $object->config('core.execute.stream.is.default', true);
            }
            //get option from $command
            switch($option){
                case Core::FILE:
                    $descriptorspec = [
                        0 => ['file', 'php://stdin' , 'r'],  // stdin
                        1 => ['file', 'php://stdout', 'w'],  // stdout
                        2 => ['pipe', 'w'],  // stderr
                    ];
                    $process = proc_open($command, $descriptorspec, $pipes, Dir::current(), null);
                    $notification = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    return proc_close($process);
                case Core::STREAM :
                    $descriptorspec = [
                        0 => ["pipe", "r"],  // stdin
                        1 => ["pipe", "w"],  // stdout
                        2 => ["pipe", "w"],  // stderr
                    ];
                    $data = $object->config('core.execute.data');
                    $object->config('delete', 'core.execute.data');
                    if(empty($data)){
                        $data = $object->config('core.execute.stream.data');
                        $object->config('delete', 'core.execute.stream.data');
                    }
                    if($object->config('core.execute.stream.is.default')){
                        $from = clone $object;
                        if(!$from->has('request')){
                            $from->set('request', $object->request());
                        }
                        $delete = $object->config('core.execute.stream.delete');
                        if(
                            $delete &&
                            is_array($delete)
                        ){
                            foreach($delete as $attribute){
                                $from->delete($attribute);
                            }
                        }
                        $data = Core::object(
                            $from->data(),
                            'json-line'
                        );
                    }
                    $process = proc_open($command, $descriptorspec, $pipes, Dir::current(), null);
//                      stream_set_blocking($pipes[1], 0);
//                      stream_set_blocking($pipes[2], 0);
//                      stream_set_blocking(STDIN, 0);
                    fwrite($pipes[0], $data . PHP_EOL);
                    fclose($pipes[0]);
                    $output = stream_get_contents($pipes[1]);
                    $notification = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    fclose($pipes[1]);
                    return proc_close($process);
                case Core::PROMPT :
                default :
                    $descriptorspec = array(
                        0 => STDIN,  // stdin
                        1 => STDOUT,  // stdout
                        2 => ["pipe", "w"],  // stderr
                    );
                    $process = proc_open($command, $descriptorspec, $pipes, Dir::current(), null);
                    $notification = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    return proc_close($process);
            }
        }
    }

    public static function output_mode($mode = null): void
    {
        if (!in_array($mode, Core::OUTPUT_MODE)) {
            $mode = Core::OUTPUT_MODE_DEFAULT;
        }
        switch ($mode) {
            case  Core::MODE_INTERACTIVE :
                ob_implicit_flush(true);
                try {
                    @ob_end_flush();
                } catch (\Exception $e) {
                    //do nothing
                }
                break;
            default :
                ob_implicit_flush(false);
                try {
                    @ob_end_flush();
                } catch (\Exception $e) {
                    //do nothing
                }
        }
    }

    public static function interactive(): void
    {
        Core::output_mode(Core::MODE_INTERACTIVE);
    }

    public static function passive(): void
    {
        Core::output_mode(Core::MODE_PASSIVE);
    }

    /**
     * @throws UrlEmptyException
     */
    public static function redirect($url = ''): void
    {
        if (empty($url)) {
            throw new UrlEmptyException('url is empty...');
        }
        header('Location: ' . $url);
        exit;
    }

    public static function is_array_nested($array = []): bool
    {
        $array = (array)$array;
        foreach ($array as $value) {
            if (is_array($value)) {
                return true;
            }
        }
        return false;
    }

    public static function array_bestmatch_list($array=[], $search='', $with_score=false): bool|array
    {
        if(empty($array)){
            return false;
        }
        $bestmatch = [];
        $search = substr($search, 0, 255);
        foreach($array as $nr => $record){
            $match = substr($record, 0, 255);
            $levensthein = levenshtein($search, $match);
            $length = strlen($match);
            $score = $length - $levensthein / $length;
            $bestmatch[$score][$nr] = $match;
        }
        krsort($bestmatch, SORT_NATURAL);
        $array = [];
        foreach($bestmatch as $score => $list){
            foreach($list as $key => $match){
                if($with_score){
                    $array[$key] = [
                        'string' => $match,
                        'score' => $score
                    ];
                } else {
                    $array[$key] = $match;
                }
            }
        }
        return $array;
    }

    public static function array_bestmatch_key($array=[], $search=''): bool|int|string|null
    {
        if(empty($array)){
            return false;
        }
        $array = Core::array_bestmatch_list($array, $search, false);
        reset($array);
        return key($array);
    }

    public static function array_bestmatch($array=[], $search='', $with_score=false){
        if(empty($array)){
            return false;
        }
        $array = Core::array_bestmatch_list($array, $search, $with_score);
        return reset($array);
    }

    public static function array_object($array = []): stdClass
    {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $object->{$key} = Core::array_object($value);
            } else {
                $object->{$key} = $value;
            }
        }
        return $object;
    }

    /**
     * @throws ReflectionException
     */
    public static function object_array($object = null): array
    {
        $list = [];
        if ($object === null) {
            return $list;
        }
        $reflection = new ReflectionObject($object);
        do {
            foreach ($reflection->getProperties() as $property) {
                $property->setAccessible(true);
                if (!array_key_exists($property->name, $list)) {
                    $list[$property->name] = $property->getValue($object);
                }
            }
        } while ($reflection = $reflection->getParentClass());
        return $list;
    }

    public static function explode_multi($delimiter = [], $string = '', $limit = []): array
    {
        $result = array();
        if (!is_array($limit)) {
            $limit = explode(',', $limit);
            $value = reset($limit);
            if (count($delimiter) > count($limit)) {
                for ($i = count($limit); $i < count($delimiter); $i++) {
                    $limit[$i] = $value;
                }
            }
        }
        foreach ($delimiter as $nr => $delim) {
            if (isset($limit[$nr])) {
                $tmp = explode($delim, $string, $limit[$nr]);
            } else {
                $tmp = explode($delim, $string);
            }
            if (count($tmp) == 1) {
                continue;
            }
            foreach ($tmp as $tmp_value) {
                $result[] = $tmp_value;
            }
        }
        if (empty($result)) {
            $result[] = $string;
        }
        return $result;
    }

    /**
     * @throws ObjectException
     */
    public static function object($input = '', $output = null, $type = null)
    {
        if ($output === null) {
            $output = Core::OBJECT_OBJECT;
        }
        if ($type === null) {
            $type = Core::OBJECT_TYPE_ROOT;
        }
        if (is_bool($input)) {
            if ($output == Core::OBJECT_OBJECT || $output == Core::OBJECT_JSON) {
                $data = new stdClass();
                if (empty($input)) {
                    $data->false = false;
                } else {
                    $data->true = true;
                }
                if ($output == Core::OBJECT_JSON) {
                    $data = json_encode($data);
                }
                return $data;
            } elseif ($output == Core::OBJECT_ARRAY) {
                return array($input);
            } else {
                throw new ObjectException(Core::EXCEPTION_OBJECT_OUTPUT);
            }
        } elseif (is_null($input)) {
            if ($output == Core::OBJECT_OBJECT) {
                return new stdClass();
            } elseif ($output == Core::OBJECT_ARRAY) {
                return array();
            } elseif ($output == Core::OBJECT_JSON) {
                return '{}';
            }
        } elseif (is_object($input) && $output === Core::OBJECT_JSON) {
            $json = json_encode($input, JSON_PRETTY_PRINT);
            if (json_last_error()) {
                $debug = debug_backtrace(true);
                d($debug[0]['file'] . ':' . $debug[0]['line'] . ':' . $debug[0]['function']);
                d($debug[1]['file'] . ':' . $debug[1]['line'] . ':' . $debug[1]['function']);
                d($debug[2]['file'] . ':' . $debug[2]['line'] . ':' . $debug[2]['function']);
                throw new ObjectException(json_last_error_msg());
            }
            return $json;
        } elseif (is_array($input) && $output === Core::OBJECT_OBJECT) {
            return Core::array_object($input);
        } elseif (is_array($input) && $output === Core::OBJECT_JSON) {
            $json = json_encode($input, JSON_PRETTY_PRINT);
            if (json_last_error()) {
                $debug = debug_backtrace(true);
                d($debug[0]['file'] . ':' . $debug[0]['line'] . ':' . $debug[0]['function']);
                d($debug[1]['file'] . ':' . $debug[1]['line'] . ':' . $debug[1]['function']);
                d($debug[2]['file'] . ':' . $debug[2]['line'] . ':' . $debug[2]['function']);
                throw new ObjectException(json_last_error_msg());
            }
            return $json;
        } elseif (is_string($input)) {
            $input = trim($input);
            if ($output == Core::OBJECT_OBJECT) {
                if (substr($input, 0, 1) == '{' && substr($input, -1, 1) == '}') {
                    $json = json_decode($input);
                    if (json_last_error()) {
                        $debug = debug_backtrace(true);
                        d($debug[0]['file'] . ':' . $debug[0]['line'] . ':' . $debug[0]['function']);
                        d($debug[1]['file'] . ':' . $debug[1]['line'] . ':' . $debug[1]['function']);
                        d($debug[2]['file'] . ':' . $debug[2]['line'] . ':' . $debug[2]['function']);
                        throw new ObjectException(json_last_error_msg());
                    }
                    return $json;
                } elseif (substr($input, 0, 1) == '[' && substr($input, -1, 1) == ']') {
                    $json = json_decode($input);
                    if (json_last_error()) {
                        $debug = debug_backtrace(true);
                        d($debug[0]['file'] . ':' . $debug[0]['line'] . ':' . $debug[0]['function']);
                        d($debug[1]['file'] . ':' . $debug[1]['line'] . ':' . $debug[1]['function']);
                        d($debug[2]['file'] . ':' . $debug[2]['line'] . ':' . $debug[2]['function']);
                        throw new ObjectException(json_last_error_msg());
                    }
                    return $json;
                }
            } elseif (stristr($output, Core::OBJECT_JSON) !== false) {
                if (substr($input, 0, 1) == '{' && substr($input, -1, 1) == '}') {
                    $input = json_decode($input);
                }
            } elseif ($output == Core::OBJECT_ARRAY) {
                if (substr($input, 0, 1) == '{' && substr($input, -1, 1) == '}') {
                    return json_decode($input, true);
                } elseif (substr($input, 0, 1) == '[' && substr($input, -1, 1) == ']') {
                    return json_decode($input, true);
                }
            }
        }
        if (stristr($output, Core::OBJECT_JSON) !== false && stristr($output, 'data') !== false) {
            $data = str_replace('"', '&quot;', json_encode($input));
        } elseif (stristr($output, Core::OBJECT_JSON) !== false && stristr($output, 'line') !== false) {
            $data = json_encode($input);
        } else {
            $data = json_encode($input, JSON_PRETTY_PRINT);
        }
        if ($output == Core::OBJECT_OBJECT) {
            return json_decode($data);
        } elseif (stristr($output, Core::OBJECT_JSON) !== false) {
            if ($type == Core::OBJECT_TYPE_CHILD) {
                return substr($data, 1, -1);
            } else {
                return $data;
            }
        } elseif ($output == Core::OBJECT_ARRAY) {
            return json_decode($data, true);
        } else {
            throw new ObjectException(Core::EXCEPTION_OBJECT_OUTPUT);
        }
    }

    public static function object_delete($attributeList = [], $object = ''): bool
    {
        if(is_string($attributeList) || is_numeric($attributeList)) {
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, (string) $attributeList);
        }
        if(is_array($object)){
            $properties = [];
            if(
                count($attributeList) === 1 &&
                strpos($attributeList[0], '.') === false
            ){
                if(array_key_exists($attributeList[0], $object)){
                    unset($object[$attributeList[0]]);
                    return true;
                } else {
                    return false;
                }
            }
            $parent = false;
            $attribute = false;
            while(!empty($attributeList)){
                $properties[] = implode('.', $attributeList);
                array_pop($attributeList);
                if(empty($attributeList)){
                    break;
                }
            }
            $need_next_change = false;
            $ready = false;
            while(!empty($properties)){
                foreach($properties as $nr => $property){
                    if(strpos($property, '.') !== false){
                        if(array_key_exists($property, $object)){
                            $parent = $object;
                            $object = $object[$property];
                            if($need_next_change){
                                $need_next_change = false;
                            }
                            unset($properties[$nr]);
                            if(empty($properties)){
                                unset($object[$property]);
                                $ready = true;
                            }
                        }
                    }
                }
                if(
                    count($properties) === 1 &&
                    strpos($property, '.') === false
                ){
                    if(
                        array_key_exists($property, $object) &&
                        $ready
                    ){
                        unset($object[$property]);
                        return true;
                    } else {
                        return false;
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return true;
                    }
                    return false;
                }
                foreach($properties as $nr => $property){
                    $attributeList = explode('.', $property);
                    if(array_key_exists(1, $attributeList)){
                        $shift = array_shift($attributeList);
                        if(
                            is_array($object) &&
                            array_key_exists($shift, $object)
                        ){
                            $parent = $object;
                            $object = $object[$shift];
                            $ready = false;
                            $attribute = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $parent = $object;
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $parent = $object;
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                    break;
                                }
                                elseif($need_next_change === true){
                                    return false;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    if($attribute){
                                        if(is_array($parent)){
                                            unset($parent[$attribute]);
                                        }
                                        elseif(is_object($parent)){
                                            unset($parent->{$attribute});
                                        }
                                    } else {
                                        if(is_array($parent)){
                                            unset($parent[$shift]);
                                        }
                                        elseif(is_object($parent)){
                                            unset($parent->{$shift});
                                        }
                                    }
                                    return true;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                        elseif(
                            is_object($object) &&
                            property_exists($object, $shift)
                        ){
                            $parent = $object;
                            $object = $object->{$shift};
                            $ready = false;
                            $attribute = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $parent = $object;
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $parent = $object;
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                    break;
                                }
                                elseif($need_next_change === true){
                                    return false;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    if($attribute){
                                        if(is_array($parent)){
                                            unset($parent[$attribute]);
                                        }
                                        elseif(is_object($parent)){
                                            unset($parent->{$attribute});
                                        }
                                    } else {
                                        if(is_array($parent)){
                                            unset($parent[$shift]);
                                        }
                                        elseif(is_object($parent)){
                                            unset($parent->{$shift});
                                        }
                                    }
                                    return true;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        } else {
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                    } else {
                        unset($properties[$nr]);
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return true;
                    }
                    return false;
                }
            }
            return false;
        }
        elseif(is_object($object)){
            $properties = [];
            if(
                count($attributeList) === 1 &&
                strpos($attributeList[0], '.') === false
            ){
                if(property_exists($object, $attributeList[0])){
                    unset($object->{$attributeList[0]});
                    return true;
                } else {
                    return false;
                }
            }
            $parent = false;
            $attribute = false;
            while(!empty($attributeList)){
                $properties[] = implode('.', $attributeList);
                array_pop($attributeList);
                if(empty($attributeList)){
                    break;
                }
            }
            $need_next_change = false;
            $ready = false;
            while(!empty($properties)){
                foreach($properties as $nr => $property){
                    if(strpos($property, '.') !== false){
                        if(property_exists($object, $property)){
                            $object = $object->{$property};
                            if($need_next_change){
                                $need_next_change = false;
                            }
                            unset($properties[$nr]);
                            if(empty($properties)){
                                $ready = true;
                            }
                        }
                    }
                }
                if(
                    count($properties) === 1 &&
                    strpos($property, '.') === false
                ){
                    if(
                        property_exists($object, $property) &&
                        $ready
                    ){
                        unset($object->{$property});
                        return true;
                    } else {
                        return false;
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return true;
                    }
                    return false;
                }
                foreach($properties as $nr => $property){
                    $attributeList = explode('.', $property);
                    if(array_key_exists(1, $attributeList)){
                        $shift = array_shift($attributeList);
                        if(
                            is_array($object) &&
                            array_key_exists($shift, $object)
                        ){
                            $parent = $object;
                            $object = $object[$shift];
                            $ready = false;
                            $attribute = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $parent = $object;
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $parent = $object;
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                }
                                elseif($need_next_change === true){
                                    return false;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    if($attribute){
                                        if(is_array($parent)){
                                            unset($parent[$attribute]);
                                        }
                                        elseif(is_object($parent)){
                                            unset($parent->{$attribute});
                                        }
                                    } else {
                                        if(is_array($parent)){
                                            unset($parent[$shift]);
                                        }
                                        elseif(is_object($parent)){
                                            unset($parent->{$shift});
                                        }
                                    }
                                    return true;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                        elseif(
                            is_object($object) &&
                            property_exists($object, $shift)
                        ){
                            $parent = $object;
                            $object = $object->{$shift};
                            $ready = false;
                            $attribute = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $parent = $object;
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $parent = $object;
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                }
                                elseif($need_next_change === true){
                                    return false;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    if($attribute){
                                        if(is_array($parent)){
                                            unset($parent[$attribute]);
                                        }
                                        elseif(is_object($parent)){
                                            unset($parent->{$attribute});
                                        }
                                    } else {
                                        if(is_array($parent)){
                                            unset($parent[$shift]);
                                        }
                                        elseif(is_object($parent)){
                                            unset($parent->{$shift});
                                        }
                                    }
                                    return true;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        } else {
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                    } else {
                        unset($properties[$nr]);
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return true;
                    }
                    return false;
                }
            }
            return false;
        }
        return false;
    }

    /*
    public static function object_delete($attributeList = [], $object = '', $parent = '', $key = null): bool
    {
        if (is_scalar($attributeList)) {
            $explode = explode('.', $attributeList, 3);
            if(is_object($object)){
                if(
                    property_exists($object, $attributeList)
                ) {
                    unset($object->{$attributeList});
                    return true;
                }
                elseif(
                    array_key_exists(2, $explode) &&
                    isset($object->{$explode[0]})
                ){
                    return Core::object_delete($explode[1] . '.' . $explode[2], $object->{$explode[0]}, $object, $explode[0]);
                }
                elseif(
                    array_key_exists(1, $explode) &&
                    isset($object->{$explode[0]})
                ){
                    return Core::object_delete($explode[1], $object->{$explode[0]}, $object, $explode[0]);
                }
            }
            elseif(is_array($object)) {
                if(
                    array_key_exists($attributeList, $object)
                ){
                    unset($object[$attributeList]);
                    return true;
                }
                elseif(
                    array_key_exists(2, $explode) &&
                    isset($object[$explode[0]])
                ){
                    return Core::object_delete($explode[1] . '.' . $explode[2], $object[$explode[0]], $object, $explode[0]);
                }
                elseif(
                    array_key_exists(1, $explode) &&
                    isset($object[$explode[0]])
                ){
                    return Core::object_delete($explode[1], $object[$explode[0]], $object, $explode[0]);
                }
            }
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, (string)$attributeList);
        }
        if (is_array($attributeList)) {
            $attributeList = Core::object_horizontal($attributeList);
        }
        if (!empty($attributeList) && is_object($attributeList)) {
            foreach ($attributeList as $key => $attribute) {
                if(is_object($object)){
                    if (isset($object->{$key})) {
                        return Core::object_delete($attribute, $object->{$key}, $object, $key);
                    } else {
                        unset($object->{$key}); //to delete nulls
                        return false;
                    }
                } elseif(is_array($object)) {
                    if (isset($object[$key])) {
                        return Core::object_delete($attribute, $object[$key], $object, $key);
                    } else {
                        unset($object[$key]); //to delete nulls
                        return false;
                    }
                }
            }
        } else {
            if(!empty($parent)){
                if(is_object($parent)){
                    unset($parent->{$key});    //unset $object won't delete it from the first object (parent) given
                }
                elseif(is_array($parent)){
                    unset($parent[$key]);
                }
            }
            return true;
        }
        return false;
    }
    */

    public static function object_has_property($attributeList = [], $object = ''): bool
    {
        if(is_string($attributeList) || is_numeric($attributeList)) {
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, (string) $attributeList);
        }
        if(is_array($object)){
            $properties = [];
            if(
                count($attributeList) === 1 &&
                strpos($attributeList[0], '.') === false
            ){
                if(array_key_exists($attributeList[0], $object)){
                    return true;
                } else {
                    return false;
                }
            }
            while(!empty($attributeList)){
                $properties[] = implode('.', $attributeList);
                array_pop($attributeList);
                if(empty($attributeList)){
                    break;
                }
            }
            $need_next_change = false;
            $ready = false;
            while(!empty($properties)){
                foreach($properties as $nr => $property){
                    if(strpos($property, '.') !== false){
                        if(array_key_exists($property, $object)){
                            $object = $object[$property];
                            if($need_next_change){
                                $need_next_change = false;
                            }
                            unset($properties[$nr]);
                            if(empty($properties)){
                                $ready = true;
                            }
                        }
                    }
                }
                if(
                    count($properties) === 1 &&
                    strpos($property, '.') === false
                ){
                    if(
                        array_key_exists($property, $object) &&
                        $ready
                    ){
                        return true;
                    } else {
                        return false;
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return true;
                    }
                    return false;
                }
                foreach($properties as $nr => $property){
                    $attributeList = explode('.', $property);
                    if(array_key_exists(1, $attributeList)){
                        $shift = array_shift($attributeList);
                        if(
                            is_array($object) &&
                            array_key_exists($shift, $object)
                        ){
                            $object = $object[$shift];
                            $ready = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                    break;
                                }
                                elseif($need_next_change === true){
                                    return false;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    return true;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                        elseif(
                            is_object($object) &&
                            property_exists($object, $shift)
                        ){
                            $object = $object->{$shift};
                            $ready = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                    break;
                                }
                                elseif($need_next_change === true){
                                    return false;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    return true;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        } else {
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                    } else {
                        unset($properties[$nr]);
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return true;
                    }
                    return false;
                }
            }
            return false;
        }
        elseif(is_object($object)){
            $properties = [];
            if(
                count($attributeList) === 1 &&
                strpos($attributeList[0], '.') === false
            ){
                if(property_exists($object, $attributeList[0])){
                    return true;
                } else {
                    return false;
                }
            }
            while($attributeList !== null){
                $properties[] = implode('.', $attributeList);
                array_pop($attributeList);
                if(empty($attributeList)){
                    break;
                }
            }
            $need_next_change = false;
            $ready = false;
            while(!empty($properties)){
                foreach($properties as $nr => $property){
                    if(strpos($property, '.') !== false){
                        if(property_exists($object, $property)){
                            $object = $object->{$property};
                            if($need_next_change){
                                $need_next_change = false;
                            }
                            unset($properties[$nr]);
                            if(empty($properties)){
                                $ready = true;
                            }
                        }
                    }
                }
                if(
                    count($properties) === 1 &&
                    strpos($property, '.') === false
                ){
                    if(property_exists($object, $property) && $ready){
                        return true;
                    } else {
                        return false;
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return true;
                    }
                    return false;
                }
                foreach($properties as $nr => $property){
                    $attributeList = explode('.', $property);
                    if(array_key_exists(1, $attributeList)){
                        $shift = array_shift($attributeList);
                        if(
                            is_array($object) &&
                            array_key_exists($shift, $object)
                        ){
                            $object = $object[$shift];
                            $ready = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                }
                                elseif($need_next_change === true){
                                    return false;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    return true;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                        elseif(
                            is_object($object) &&
                            property_exists($object, $shift)
                        ){
                            $object = $object->{$shift};
                            $ready = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                }
                                elseif($need_next_change === true){
                                    return false;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    return true;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        } else {
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                    } else {
                        unset($properties[$nr]);
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return true;
                    }
                    return false;
                }
            }
            return false;
        }
        return false;
    }

    public static function object_has($attributeList = [], $object = ''): bool
    {
        if (
            is_object($object) &&
            Core::object_is_empty($object)
        ) {
            if (empty($attributeList)) {
                return true;
            }
            return false;
        }
        elseif(
            is_array($object) &&
            empty($object)
        ){
            if (empty($attributeList)) {
                return true;
            }
            return false;
        }
        $get = Core::object_get($attributeList, $object);
        if($get){
            return true;
        }
        return false;
    }

    public static function object_get($attributeList = [], $object = '', $is_debug=false){
        if(is_string($attributeList) || is_numeric($attributeList)) {
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, (string) $attributeList);
        }
        if(is_array($object)){
            $properties = [];
            if(
                count($attributeList) === 1 &&
                strpos($attributeList[0], '.') === false
            ){
                if(array_key_exists($attributeList[0], $object)){
                    return $object[$attributeList[0]];
                } else {
                    return null;
                }
            }
            while($attributeList !== null){
                $properties[] = implode('.', $attributeList);
                array_pop($attributeList);
                if(empty($attributeList)){
                    break;
                }
            }
            $need_next_change = false;
            $ready = false;
            while(!empty($properties)){
                foreach($properties as $nr => $property){
                    if(strpos($property, '.') !== false){
                        if(array_key_exists($property, $object)){
                            $object = $object[$property];
                            if($need_next_change){
                                $need_next_change = false;
                            }
                            unset($properties[$nr]);
                            if(empty($properties)){
                                $ready = true;
                            }
                        }
                    }
                }
                if(
                    count($properties) === 1 &&
                    strpos($property, '.') === false
                ){
                    if(
                        array_key_exists($property, $object) &&
                        $ready
                    ){
                        return $object[$property];
                    } else {
                        return null;
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return $object;
                    }
                    return null;
                }
                foreach($properties as $nr => $property){
                    $attributeList = explode('.', $property);
                    if(array_key_exists(1, $attributeList)){
                        $shift = array_shift($attributeList);
                        if(
                            is_array($object) &&
                            array_key_exists($shift, $object)
                        ){
                            $object = $object[$shift];
                            $ready = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                    break;
                                }
                                elseif($need_next_change === true){
                                    return null;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    return $object;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                        elseif(
                            is_object($object) &&
                            property_exists($object, $shift)
                        ){
                            $object = $object->{$shift};
                            $ready = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                    break;
                                }
                                elseif($need_next_change === true){
                                    return null;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    return $object;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        } else {
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                    } else {
                        unset($properties[$nr]);
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return $object;
                    }
                    return null;
                }
            }
            return null;
        }
        elseif(is_object($object)){
            $properties = [];
            if(
                count($attributeList) === 1 &&
                strpos($attributeList[0], '.') === false
            ){
                if(property_exists($object, $attributeList[0])){
                    return $object->{$attributeList[0]};
                } else {
                    return null;
                }
            }
            while($attributeList !== null){
                $properties[] = implode('.', $attributeList);
                array_pop($attributeList);
                if(empty($attributeList)){
                    break;
                }
            }
            $need_next_change = false;
            $ready = false;
            while(!empty($properties)){
                foreach($properties as $nr => $property){
                    if(strpos($property, '.') !== false){
                        if(property_exists($object, $property)){
                            $object = $object->{$property};
                            if($need_next_change){
                                $need_next_change = false;
                            }
                            unset($properties[$nr]);
                            if(empty($properties)){
                                $ready = true;
                            }
                        }
                    }
                }
                if(
                    count($properties) === 1 &&
                    strpos($property, '.') === false
                ){
                    if(property_exists($object, $property) && $ready){
                        return $object->{$property};
                    } else {
                        return null;
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return $object;
                    }
                    return null;
                }
                foreach($properties as $nr => $property){
                    $attributeList = explode('.', $property);
                    if(array_key_exists(1, $attributeList)){
                        $shift = array_shift($attributeList);
                        if(
                            is_array($object) &&
                            array_key_exists($shift, $object)
                        ){
                            $object = $object[$shift];
                            $ready = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                }
                                elseif($need_next_change === true){
                                    return null;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    return $object;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                        elseif(
                            is_object($object) &&
                            property_exists($object, $shift)
                        ){
                            $object = $object->{$shift};
                            $ready = false;
                            foreach($attributeList as $attributeList_nr => $attribute){
                                if(
                                    is_array($object) &&
                                    array_key_exists($attribute, $object)
                                ){
                                    $object = $object[$attribute];
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif(
                                    is_object($object) &&
                                    property_exists($object, $attribute)
                                ){
                                    $object = $object->{$attribute};
                                    unset($attributeList[$attributeList_nr]);
                                    $need_next_change = false;
                                    $ready = true;
                                }
                                elseif($need_next_change === false){
                                    $need_next_change = true;
                                    $ready = false;
                                }
                                elseif($need_next_change === true){
                                    return null;
                                }
                            }
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                                if($ready){
                                    return $object;
                                }
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        } else {
                            if(empty($attributeList)){
                                unset($properties[$nr]);
                            } else {
                                $properties[$nr] = implode('.', $attributeList);
                            }
                        }
                    } else {
                        unset($properties[$nr]);
                    }
                }
                if(empty($properties)){
                    if($ready){
                        return $object;
                    }
                    return null;
                }
            }
            return null;
        }
        return null;
    }


    /*
    public static function object_get($attributeList = [], $object = '', $is_debug=false)
    {
        if($is_debug){
            $debug = debug_backtrace(true);
            var_dump($debug[0]['file'] . ' on line ' . $debug[0]['line']);
            var_dump($debug[1]['file'] . ' on line ' . $debug[1]['line']);
            var_dump($is_debug);
            var_dump($attributeList);
            var_dump(gettype($object));
        }

        if(
            is_array($object) &&
            $attributeList !== null
        ){
            if(is_array($attributeList)){
                foreach($attributeList as $key => $attribute){
                    if ($key === null || $key === '') {
                        continue;
                    }
                    if (array_key_exists($key, $object)) {
                        return Core::object_get($attributeList->{$key}, $object[$key], $is_debug);
                    }
                }
            }
            elseif(is_string($attributeList) || is_numeric($attributeList)){
                if (array_key_exists($attributeList, $object)) {
                    return $object[$attributeList];
                }
            }
        }
        elseif (Core::object_is_empty($object)) {
            if (empty($attributeList) && !is_scalar($attributeList)) {
                return $object;
            }
            if (is_array($object)) {
                if (is_array($attributeList)) {
                    foreach ($attributeList as $key => $attribute) {
                        if ($key === null || $key === '') {
                            continue;
                        }
                        if (array_key_exists($key, $object)) {
                            return Core::object_get($attributeList[$key], $object[$key], $is_debug);
                        }
                    }
                }
            }
            return null;
        }
        if (is_scalar($attributeList)) {
            if(
                (
                    $attributeList === '0' &&
                    isset($object->{$attributeList})
                ) ||
                isset($object->{$attributeList})
            ){
                return $object->{$attributeList};
            } else {
                $explode = explode('.', $attributeList);
                $shift = array_shift($explode);
                if(isset($object->{$shift})){
                    return Core::object_get(implode('.', $explode), $object->{$shift}, $is_debug);
                }
                $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, (string) $attributeList);
                foreach ($attributeList as $nr => $attribute) {
                    if ($attribute === null || $attribute === '') {
                        unset($attributeList[$nr]);
                    }
                }
            }
        }
        if (is_array($attributeList)) {
            $attributeList = Core::object_horizontal($attributeList);
            if($is_debug){
                echo '1 ';
                var_dump($attributeList);
            }
        }
        if (empty($attributeList)) {
            echo '2 ';
            var_dump($object);
            return $object;
        }

        while(true){
            foreach($attributeList as $key => $attribute){
                $attributeList = $attribute;
            }
        }

        while($attributeList !== null){
            foreach ($attributeList as $key => $attribute) {
                if ($key === null || $key === '') {
                    continue;
                }
                if (isset($object->{$key})) {
                    echo '3 ';
                    var_dump($key);
                    var_dump($object->{$key});
                    echo '3.1 ';
                    var_dump($attributeList);
                    $debug = debug_backtrace(true);
                    var_dump($debug[0]['file'] . ' on line ' . $debug[0]['line']);
                    var_dump($debug[1]['file'] . ' on line ' . $debug[1]['line']);
                    if(null === $attributeList->{$key}){
                        return $object->{$key};
                    }
                    return Core::object_get($attributeList->{$key}, $object->{$key}, $is_debug);
                }
                elseif(is_array($object)){
                    if(array_key_exists($key, $object)){
                        if(null === $attributeList->{$key}){
                            return $object[$key];
                        }
                        return Core::object_get($attributeList->{$key}, $object[$key], $is_debug);
                    } else {
                        if($is_debug){
                            echo '4 ';
                            var_dump($key);
//                        var_dump($object);
                            var_dump($attributeList->{$key});
                        }
                        return Core::object_get_nested($attributeList->{$key}, $object, $key, $is_debug);
                    }
                } else {
                    echo '5 ';
                    var_dump($key);
//                var_dump($object);
                    var_dump($attributeList->{$key});
                    return Core::object_get_nested($attributeList->{$key}, $object, $key, $is_debug);
                }
            }
            if(property_exists($attributeList, $key)){
                $attributeList = $attribute->{$key};
            } else {

            }

        }
        return null;
    }
    */

    private static function object_get_nested($attributeList, $object, $key='', $is_debug=false){
        $is_collect = [];
        $is_collect[] = $key;
        if(empty($attributeList)){
            return null;
        }
        $keys = [];
        echo '14 ';
        var_dump($attributeList);
        while($attributeList !== null){
            foreach($attributeList as $key_attribute => $value_attribute) {
                $is_collect[] = $key_attribute;
                $key_collect = implode('.', $is_collect);
                $keys[] = $key_collect;
                $keys_attribute_list[] = $attributeList->{$key_attribute};
                $attributeList = $value_attribute;
            }
        }
        krsort($keys, SORT_NATURAL);
        krsort($keys_attribute_list, SORT_NATURAL);
        echo '11 ';
        var_dump($keys);
        var_dump($keys_attribute_list);
        foreach($keys as $nr => $key_collect){
            if (isset($object->{$key_collect})) {
                if(null === $keys_attribute_list[$nr]){
                    return $object->{$key_collect};
                } else {
                    return Core::object_get($keys_attribute_list[$nr], $object->{$key_collect}, $is_debug);
                }
            }
            elseif(is_array($object)){
                echo '6 ';
                var_dump($key_attribute);
                var_dump($key_collect);
//                var_dump($object);
                if(array_key_exists($key_collect, $object)){
                    echo 'has';
                    if(null === $keys_attribute_list[$nr]){
                        return $object[$key_collect];
                    } else {
                        return Core::object_get($keys_attribute_list[$nr], $object[$key_collect], $is_debug);
                    }
                } else {
                    echo 'has not';
                    echo '13 ';
                    var_dump($key_collect);
                    if(null === $keys_attribute_list[$nr]){
                        continue;
                    } else {
                        return Core::object_get_nested($keys_attribute_list[$nr], $object, $key_collect, $is_debug);
                    }
                }
            }
            else {
                echo '7 ';
                var_dump($key_attribute);
                var_dump($key_collect);
                return Core::object_get_nested($keys_attribute_list[$nr], $object, $key_collect, $is_debug);
            }
        }
        return null;
    }

    /**
     * @throws ObjectException
     */
    public static function object_merge()
    {
        $objects = func_get_args();
        $main = array_shift($objects);
        if (empty($main) && !is_array($main)) {
            $main = new stdClass();
        }
        foreach ($objects as $nr => $object) {
            if (is_array($object)) {
                foreach ($object as $key => $value) {
                    if (is_object($main)) {
                        throw new ObjectException(Core::EXCEPTION_MERGE_ARRAY_OBJECT);
                    }
                    if (!isset($main[$key])) {
                        $main[$key] = $value;
                    } else {
                        if (is_array($value) && is_array($main[$key])) {
                            $main[$key] = Core::object_merge($main[$key], $value);
                        } else {
                            $main[$key] = $value;
                        }
                    }
                }
            } elseif (is_object($object)) {
                foreach ($object as $key => $value) {
                    if ((!isset($main->{$key}))) {
                        $main->{$key} = $value;
                    } else {
                        if (is_object($value) && is_object($main->{$key})) {
                            $main->{$key} = Core::object_merge(clone $main->{$key}, clone $value);
                        } else {
                            $main->{$key} = $value;
                        }
                    }
                }
            }
        }
        return $main;
    }

    public static function object_set($attributeList = [], $value = null, $object = '', $return = 'child', $is_debug=false)
    {
        if (
            !is_object($object) &&
            !is_array($object)
        ) {
            return null;
        }
        if (is_string($return) && $return !== 'child') {
            if ($return === 'root') {
                $return = $object;
            } else {
                $return = Core::object_get($return, $object);
            }
        }
        if (is_scalar($attributeList)) {
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, (string)$attributeList);
        }
        if (is_array($attributeList)) {
            $attributeList = Core::object_horizontal($attributeList);
        }
        if (!empty($attributeList)) {
            foreach ($attributeList as $key => $attribute) {
                if (isset($object->{$key}) && is_object($object->{$key})) {
                    if (empty($attribute) && $attribute !== '0' && is_object($value)) {
                        foreach ($value as $value_key => $value_value) {
                            /*
                            if(isset($object->$key->$value_key)){
                                // unset($object->$key->$value_key);   //so sort will happen, @bug request will take forever and apache2 crashes needs reboot apache2
                            }
                            */
                            $object->{$key}->{$value_key} = $value_value;
                        }
                        return $object->{$key};
                    }
                    return Core::object_set($attribute, $value, $object->{$key}, $return);
                }
                elseif (is_object($attribute)) {
                    if (
                        property_exists($object, $key) &&
                        is_array($object->{$key})
                    ) {
                        foreach ($attribute as $index => $unused) {
                            if(is_object($unused)){
                                $child = new stdClass();
                                $child = Core::object_set($unused, $value, $child, 'root', true);
                                $object->{$key}[$index] = $child;
                            } else {
                                $object->{$key}[$index] = $value;
                            }

                        }
                        return $object->{$key};
                    }
                    else {
                        $object->{$key} = new stdClass();
                        return Core::object_set($attribute, $value, $object->{$key}, $return);
                    }

                } else {
                    if(is_array($object)){
                        $debug = debug_backtrace(true);
                        d($debug[0]['file'].':'. $debug[0]['function'] . ':' . $debug[0]['line']);
                        d($debug[1]['file'].':'. $debug[1]['function'] . ':' . $debug[1]['line']);
                        d($debug[2]['file'].':'. $debug[2]['function'] . ':' . $debug[2]['line']);
                        ddd($object);
                    }
                    $object->{$key} = $value;
                }
            }
        }
        if ($return === 'child') {
            return $value;
        }
        return $return;
    }

    public static function object_is_empty($object = null): bool
    {
        if (!is_object($object)) {
            return true;
        }
        $is_empty = true;
        foreach ($object as $value) {
            $is_empty = false;
            break;
        }
        return $is_empty;
    }

    public static function is_cli()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $domain = $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $domain = $_SERVER['SERVER_NAME'];
        } else {
            $domain = '';
        }
        if (empty($domain)) {
            if (!defined('IS_CLI')) {
                define('IS_CLI', true);
                return true;
            }
        }
        return false;
    }

    public static function object_horizontal($verticalArray = [], $value = null, $return = 'object')
    {
        if (empty($verticalArray)) {
            return false;
        }
        $object = new stdClass();
        if (is_object($verticalArray)) {
            $attributeList = get_object_vars($verticalArray);
            $list = array_keys($attributeList);
            $last = array_pop($list);
            if ($value === null) {
                $value = $verticalArray->$last;
            }
            $verticalArray = $list;
        } else {
            $last = array_pop($verticalArray);
        }
        if ($last === null || $last === '') {
            return false;
        }
        foreach ($verticalArray as $attribute) {
            if (empty($attribute) && $attribute !== '0') {
                continue;
            }
            if (!isset($deep)) {
                $object->{$attribute} = new stdClass();
                $deep = $object->{$attribute};
            } else {
                $deep->{$attribute} = new stdClass();
                $deep = $deep->{$attribute};
            }
        }
        if(strlen(trim($last)) === 0){
            $last = '\\' . $last;
        }
        if (!isset($deep)) {
            $object->$last = $value;
        } else {
            $deep->$last = $value;
        }
        if ($return == 'array') {
            $json = json_encode($object);
            return json_decode($json, true);
        } else {
            return $object;
        }
    }

    /**
     * @throws FileWriteException
     * @throws BadFormatException
     * @throws EnvironmentIsBrokenException
     */
    public static function key($url): Key
    {
        if (File::exist($url)) {
            $string = File::read($url);
            $key = Key::loadFromAsciiSafeString($string);
        } else {
            $key = Key::createNewRandomKey();
            $string = $key->saveToAsciiSafeString();
            $dir = Dir::name($url);
            Dir::create($dir, Dir::CHMOD);
            File::write($url, $string);
            if (posix_geteuid() === 0) {
                File::chown($dir, 'www-data', 'www-data', true);
            }
        }
        return $key;
    }

    public static function uuid(): string
    {
        $data = openssl_random_pseudo_bytes(16);
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function uuid_variable(): string
    {
        $uuid = Core::uuid();
        $search = [];
        $search[] = 0;
        $search[] = 1;
        $search[] = 2;
        $search[] = 3;
        $search[] = 4;
        $search[] = 5;
        $search[] = 6;
        $search[] = 7;
        $search[] = 8;
        $search[] = 9;
        $search[] = '-';
        $replace = [];
        $replace[] = 'g';
        $replace[] = 'h';
        $replace[] = 'i';
        $replace[] = 'j';
        $replace[] = 'k';
        $replace[] = 'l';
        $replace[] = 'm';
        $replace[] = 'n';
        $replace[] = 'o';
        $replace[] = 'p';
        $replace[] = '_';
        $variable = '$' . str_replace($search, $replace, $uuid);
        return $variable;
    }

    public static function ucfirst_sentence($string = '', $delimiter = '.'): string
    {
        $explode = explode($delimiter, $string);
        foreach ($explode as $nr => $part) {
            $explode[$nr] = ucfirst(trim($part));
        }
        return implode($delimiter, $explode);
    }

    public static function deep_clone($object)
    {
        if (is_array($object)) {
            foreach ($object as $key => $value) {
                if (is_object($value)) {
                    $object[$key] = Core::deep_clone($value);
                }
            }
            return $object;
        }
        $clone = clone $object;
        foreach ($object as $key => $value) {
            if (is_object($value)) {
                $clone->$key = Core::deep_clone($value);
            }
        }
        return $clone;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
    public static function object_select(Parse $parse, Data $data, $url='', $select=null, $compile=false, $scope='scope:object')
    {
        if(
            $compile === true &&
            in_array(
                $scope, [
                    'object',
                    'scope:object'
                ]
            )
        ){
            $read = Core::object_select(
                $parse,
                $data,
                $url,
                $select,
                false
            );
            if(empty($read)){
                throw new ObjectException('Could not compile item: ' . $select . PHP_EOL);
            }
            if(is_array($read)){
                $explode = explode('.', $select);
                $key = array_pop($explode);
                foreach($read as $nr => $record){
                    if(is_object($record)){
                        $record->{$parse->object()->config('parse.read.object.this.key')} = $key;
                    }
                }
                return $parse->compile($read, $data->data(), $parse->storage());
            } else {
                $explode = explode('.', $select);
                $key = array_pop($explode);
                $read->{$parse->object()->config('parse.read.object.this.key')} = $key;
                return $parse->compile($read, $data->data(), $parse->storage());
            }

        } else {
            //document
            //scope:document
            if (File::exist($url)) {
                $read = File::read($url);
                $read = Core::object($read);
                if(empty($read)){
                    throw new ObjectException('Could not read item: ' . $select . PHP_EOL);
                }
                if ($compile) {
                    $read = $parse->compile($read, $data->data(), $parse->storage());
                }
                $json = new Data();
                $json->data($read);
                return $json->get($select);
            }
            return null;
        }

    }
}
