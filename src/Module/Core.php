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

    public static function binary()
    {
        if (array_key_exists('_', $_SERVER)) {
            $dirname = Dir::name($_SERVER['_']);
            return str_replace($dirname, '', $_SERVER['_']);
        }
    }

    public static function detach(App $object, $command)
    {
        $output = [];
        $error = [];
        return Core::execute($object, $command, $output, $error, Core::SHELL_DETACHED);
    }

    public static function async(App $object, $command)
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
    public static function execute(App $object, $command, &$output = '', &$error = '', $type = null)
    {
        if ($output === null) {
            $output = [];
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
                    $descriptorspec = array(
                        0 => STDIN,  // stdin
                        1 => STDOUT,  // stdout
                        2 => array("pipe", "w"),  // stderr
                    );

                    $process = proc_open($command, [], $pipes, Dir::current(), null);

                    $output = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    /*
                    $error = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    */
                    proc_close($process);
                    exit();
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

            $option = 'default';
            switch($option){
                case 'file' :
                    $descriptorspec = [
                        0 => ['file', 'php://stdin' , 'r'],  // stdin
                        1 => ['file', 'php://stdout', 'w'],  // stdout
                        2 => ['pipe', 'w'],  // stderr
                    ];
                    $process = proc_open($command, $descriptorspec, $pipes, Dir::current(), null);
                    $error = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    return proc_close($process);
                case 'read' :
                    $descriptorspec = array(
                        0 => STDIN,  // stdin
                        1 => STDOUT,  // stdout
                        2 => ["pipe", "w"],  // stderr
                    );
                    $process = proc_open($command, $descriptorspec, $pipes, Dir::current(), null);
                    $error = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    return proc_close($process);
                default :
                    $descriptorspec = [
                        0 => ["pipe", "r"],  // stdin
                        1 => ["pipe", "w"],  // stdout
                        2 => ["pipe", "w"],  // stderr
                    ];
                    $data = Core::object(
                        Core::object_merge(
                            $object->data(),
                            $object->config(),
                            $object->route()
                        ),
                        'json-line'
                    );
                    $process = proc_open($command, $descriptorspec, $pipes, Dir::current(), null);
                    fwrite($pipes[0], $data .PHP_EOL);
                    fclose($pipes[0]);
                    $output = stream_get_contents($pipes[1]);
                    $error = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    fclose($pipes[1]);
                    return proc_close($process);
                }
//            stream_set_blocking($pipes[1], 0);
//            stream_set_blocking($pipes[2], 0);
//            stream_set_blocking(STDIN, 0);
        }
    }

    public static function output_mode($mode = null)
    {
        if (!in_array($mode, Core::OUTPUT_MODE)) {
            $mode = Core::OUTPUT_MODE_DEFAULT;
        }
        switch ($mode) {
            case  Core::MODE_INTERACTIVE :
                ob_implicit_flush(true);
                @ob_end_flush();
                break;
            default :
                ob_implicit_flush(false);
                @ob_end_flush();
        }
    }

    public static function interactive()
    {
        Core::output_mode(Core::MODE_INTERACTIVE);
    }

    public static function passive()
    {
        Core::output_mode(Core::MODE_PASSIVE);
    }

    /**
     * @throws UrlEmptyException
     */
    public static function redirect($url = '')
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
                throw new ObjectException(json_last_error_msg());
            }
            return $json;
        } elseif (is_array($input) && $output === Core::OBJECT_OBJECT) {
            return Core::array_object($input);
        } elseif (is_array($input) && $output === Core::OBJECT_JSON) {
            $json = json_encode($input, JSON_PRETTY_PRINT);
            if (json_last_error()) {
                throw new ObjectException(json_last_error_msg());
            }
            return $json;
        } elseif (is_string($input)) {
            $input = trim($input);
            if ($output == Core::OBJECT_OBJECT) {
                if (substr($input, 0, 1) == '{' && substr($input, -1, 1) == '}') {
                    $json = json_decode($input);
                    if (json_last_error()) {
                        throw new ObjectException(json_last_error_msg());
                    }
                    return $json;
                } elseif (substr($input, 0, 1) == '[' && substr($input, -1, 1) == ']') {
                    $json = json_decode($input);
                    if (json_last_error()) {
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

    public static function object_delete($attributeList = [], $object = '', $parent = '', $key = null)
    {
        if (is_scalar($attributeList)) {
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, (string)$attributeList);
        }
        if (is_array($attributeList)) {
            $attributeList = Core::object_horizontal($attributeList);
        }
        if (!empty($attributeList) && is_object($attributeList)) {
            foreach ($attributeList as $key => $attribute) {
                if (isset($object->{$key})) {
                    return Core::object_delete($attribute, $object->{$key}, $object, $key);
                } else {
                    unset($object->{$key}); //to delete nulls
                    return false;
                }
            }
        } else {
            unset($parent->{$key});    //unset $object won't delete it from the first object (parent) given
            return true;
        }
    }

    public static function object_has($attributeList = [], $object = ''): bool
    {
        if (Core::object_is_empty($object)) {
            if (empty($attributeList)) {
                return true;
            }
            return false;
        }
        if (is_scalar($attributeList)) {
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, (string)$attributeList);
            foreach ($attributeList as $nr => $attribute) {
                if (empty($attribute)) {
                    unset($attributeList[$nr]);
                }
            }
        }
        if (is_array($attributeList)) {
            $attributeList = Core::object_horizontal($attributeList);
        }
        if (empty($attributeList)) {
            return true;
        }
        foreach ($attributeList as $key => $attribute) {
            if (empty($key)) {
                continue;
            }
            if (property_exists($object, $key)) {
                $get = Core::object_has($attributeList->{$key}, $object->{$key});
                if ($get === false) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    public static function object_get($attributeList = [], $object = '')
    {
        if (Core::object_is_empty($object)) {
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
                            return Core::object_get($attributeList->{$key}, $object[$key]);
                        }
                    }
                } elseif (is_scalar($attributeList)) {
                    var_dump($attributeList);
                    die;
                }
            }
            return null;
        }
        if (is_scalar($attributeList)) {
            $attributeList = Core::explode_multi(Core::ATTRIBUTE_EXPLODE, (string)$attributeList);
            foreach ($attributeList as $nr => $attribute) {
                if ($attribute === null || $attribute === '') {
                    unset($attributeList[$nr]);
                }
            }
        }
        if (is_array($attributeList)) {
            $attributeList = Core::object_horizontal($attributeList);
        }
        if (empty($attributeList)) {
            return $object;
        }
        foreach ($attributeList as $key => $attribute) {
            if ($key === null || $key === '') {
                continue;
            }
            if (isset($object->{$key})) {
                return Core::object_get($attributeList->{$key}, $object->{$key});
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
        if (!is_object($object)) {
            return;
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
                } elseif (is_object($attribute)) {
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
                    } else {
                        $object->{$key} = new stdClass();
                    }
                    return Core::object_set($attribute, $value, $object->{$key}, $return);
                } else {
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
        } else {
            return false;
        }
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

    /**
     * @throws Exception
     */
    /*
    public static function cors_is_allowed(App $object, $origin=''): bool
    {
        $origin = rtrim($origin, '/');
        $origin = explode('://', $origin);
        if(array_key_exists(1, $origin)){
            $origin = $origin[1];
            $explode = explode('/', $origin);    //bugfix samsung browser ?
            $origin = $explode[0];
        } else {
            return false;
        }
        $host_list = $object->config('server.cors');
        if(is_array($host_list)){
            foreach($host_list as $host){
                $explode = explode('.', $host);
                $local = $explode;
                $count_explode = count($explode);
                if($count_explode === 3){
                    $local[2] = Core::LOCAL;
                    if($explode[0] === '*'){
                        $temp = explode('.', $origin);
                        if(count($temp) === 3){
                            $explode[0] = '';
                            $temp[0] = '';
                            $host = implode('.', $explode);
                            $match = implode('.', $temp);
                            if($host === $match){
                                return true;
                            }
                            $local[0] = '';
                            $host = implode('.', $local);
                            if($host === $match){
                                return true;
                            }
                        }
                    } else {
                        if($host === $origin){
                            return true;
                        }
                        $host = implode('.', $local);
                        if($host === $origin){
                            return true;
                        }
                    }
                }
                elseif($count_explode === 2){
                    $local[1] = Core::LOCAL;
                    if($host === $origin){
                        return true;
                    }
                    $host = implode('.', $local);
                    if($host === $origin){
                        return true;
                    }
                }
                elseif($count_explode === 1){
                    if($host === '*'){
                        return true;
                    }
                }
            }
        }
        $object->logger('App')->debug('Cors rejected...');
        return false;
    }
    */

    /**
     * @throws Exception
     */
    /*
    public static function cors(App $object){
        header("HTTP/1.1 200 OK");
        if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
            $origin = $_SERVER['HTTP_ORIGIN'];
            $object->logger('App')->debug('HTTP_ORIGIN: ', [ $origin]);
            if(Core::cors_is_allowed($object, $origin)){
                header('Access-Control-Allow-Credentials: true');
                header("Access-Control-Allow-Origin: {$origin}");
//                header("Access-Control-Allow-Origin: * ");
                $object->logger('App')->debug('Make Access');
            }
        }
        if (
            array_key_exists('REQUEST_METHOD', $_SERVER) &&
            $_SERVER['REQUEST_METHOD'] == 'OPTIONS'
        ) {
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
            //header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization');
            header('Access-Control-Allow-Headers: Origin, Cache-Control, Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
            $object->logger('App')->debug('REQUEST_METHOD: ', [ $_SERVER['REQUEST_METHOD'] ]);
            $object->logger('App')->debug('REQUEST: ', [ Core::object($object->request(), Core::OBJECT_ARRAY) ]);
            exit(0);
        }
        if(array_key_exists('REQUEST_METHOD', $_SERVER)){
            $object->logger('App')->debug('REQUEST_METHOD: ', [ $_SERVER['REQUEST_METHOD'] ]);
        }
        $object->logger('App')->debug('REQUEST: ', [ Core::object($object->request(), Core::OBJECT_ARRAY) ]);
    }
    */

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
    public static function object_select(Parse $parse, Data $data, $url = '', $select = null, $compile = false, $scope='scope:object')
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
            $explode = explode('.', $select);
            $key = array_pop($explode);
            $read->{$parse->object()->config('parse.read.object.this.key')} = $key;
            return $parse->compile($read, $data->data(), $parse->storage());
        } else {
            //document
            //scope:document
            if (File::exist($url)) {
                $read = File::read($url);
                $read = Core::object($read);
                if ($compile) {
                    $read = $parse->compile($read, $data->data(), $parse->storage());
                }
                $json = new Data();
                $json->data($read);
                return $json->get($select);
            }
            return '';
        }

    }
}
