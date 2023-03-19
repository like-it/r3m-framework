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


use R3m\Io\Exception\LocateException;
use stdClass;

use R3m\Io\App;
use R3m\Io\Config;

use Exception;

use R3m\Io\Exception\ObjectException;

class Autoload {
    const DIR = __DIR__;
    const FILE = 'Autoload.json';
    const TEMP = 'Temp';
    const NAME = 'Autoload';
    const EXT_PHP = 'php';
    const EXT_TPL = 'tpl';
    const EXT_JSON = 'json';
    const EXT_CLASS_PHP = 'class.php';
    const EXT_TRAIT_PHP = 'trait.php';

    protected $expose;
    protected $read;
    protected $fileList;
    protected $cache_dir;

    protected $object;

    public $prefixList = array();
    public $environment = 'production';

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function configure(App $object){
        $autoload = new Autoload();
        $autoload->object($object);
        $prefix = $object->config('autoload.prefix');
        if(
            !empty($prefix) &&
            is_array($prefix)
        ){
            foreach($prefix as $record){
                $parameters = Core::object($record, 'array');
                $parameters = Config::parameters($object, $parameters);
                if(
                    array_key_exists('prefix', $parameters) &&
                    array_key_exists('directory', $parameters) &&
                    array_key_exists('extension', $parameters)
                ){
                    $autoload->addPrefix($parameters['prefix'],  $parameters['directory'], $parameters['extension']);
                }
                elseif(
                    array_key_exists('prefix', $parameters) &&
                    array_key_exists('directory', $parameters)
                ){
                    $autoload->addPrefix($parameters['prefix'],  $parameters['directory']);
                }
            }
        } else {
            $autoload->addPrefix('Host',  $object->config(Config::DATA_PROJECT_DIR_HOST));
            $autoload->addPrefix('Source',  $object->config(Config::DATA_PROJECT_DIR_SOURCE));
        }
        $cache_dir = $object->config('autoload.cache.ramdisk');
        if($cache_dir){
            $class_dir = $object->config('ramdisk.url') . 'Class' . $object->config('ds');
            $object->config('autoload.cache.class', $class_dir);

            if(!is_dir($object->config('ramdisk.url'))){
                mkdir($object->config('ramdisk.url'), 0750, true);
                if(empty($id)){
                    exec('chown www-data:www-data ' . $object->config('ramdisk.url'));
                }
            }
            if(!is_dir($class_dir)){
                mkdir($class_dir,0750, true);
                $id = posix_geteuid();
                if(empty($id)){
                    exec('chown www-data:www-data ' . $class_dir);
                }
            }
        }
        if(empty($cache_dir)){
            $cache_dir = $object->config('autoload.cache.directory');
        }
        if(empty($cache_dir)){
            $cache_dir =
                $object->config(Config::DATA_FRAMEWORK_DIR_CACHE) .
                Autoload::NAME .
                $object->config(Config::DS)
            ;
        }
        $parameters = [];
        $parameters['cache'] = $cache_dir;
        $parameters = Config::parameters($object, $parameters);
        $cache_dir = $parameters['cache'];
        $autoload->cache_dir($cache_dir);
        $autoload->register();
        $autoload->environment($object->config('framework.environment'));
        $object->data(App::AUTOLOAD_R3M, $autoload);        
    }

    public function register($method='load', $prepend=false){
        $functions = spl_autoload_functions();
        if(is_array($functions)){
            foreach($functions as $function){
                $object = reset($function);
                if(is_object($object) && get_class($object) == get_class($this)){
                    return true; //register once...
                }
            }
        }
        return spl_autoload_register(array($this, $method), true, $prepend);
    }

    public function unregister($method='load'){
        return spl_autoload_unregister(array($this, $method));
    }

    public function priority(){
        $functions = spl_autoload_functions();
        foreach($functions as $nr => $function){
            $object = reset($function);
            if(is_object($object) && get_class($object) == get_class($this) && $nr > 0){
                spl_autoload_unregister($function);
                spl_autoload_register($function, false, true); //prepend (prioritize)
            }
        }
    }

    public function object(App $object=null){
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }

    private function setObject(App $object){
        $this->object = $object;
    }

    private function getObject(){
        return $this->object;
    }

    private function setEnvironment($environment='production'){
        $this->environment = $environment;
    }

    private function getEnvironment(){
        return $this->environment;
    }

    public function environment($environment=null){
        if($environment !== null){
            $this->setEnvironment($environment);
        }
        return $this->getEnvironment();
    }

    public function addPrefix($prefix='', $directory='', $extension=''){
        $prefix = trim($prefix, '\\\/'); //.'\\';
        $directory = str_replace('\\\/', DIRECTORY_SEPARATOR, rtrim($directory,'\\\/')) . DIRECTORY_SEPARATOR; //see File::dir()
        $list = $this->getPrefixList();
        if(empty($list)){
            $list = [];
        }
        if(empty($extension)){
            $found = false;
            foreach($list as $record){
                if(
                    $record['prefix'] == $prefix &&
                    $record['directory'] == $directory
                ){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                $list[]  = array(
                    'prefix' => $prefix,
                    'directory' => $directory
                );
            }
        } else {
            $found = false;
            foreach($list as $record){
                if(
                    $record['prefix'] == $prefix &&
                    $record['directory'] == $directory &&
                    !empty($record['extension']) &&
                    $record['extension'] == $extension
                ){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                $list[]  = array(
                    'prefix' => $prefix,
                    'directory' => $directory,
                    'extension' => $extension
                );
            }
        }
        $this->setPrefixList($list);
    }

    public function prependPrefix($prefix='', $directory='', $extension=''){
        $prefix = trim($prefix, '\\\/'); //.'\\';
        $directory = str_replace('\\\/', DIRECTORY_SEPARATOR, rtrim($directory,'\\\/')) . DIRECTORY_SEPARATOR; //see File::dir()
        $list = $this->getPrefixList();
        $prepend = [];
        if(empty($list)){
            $list = [];
        }
        if(empty($extension)){
            $found = false;
            foreach($list as $record){
                if(
                    $record['prefix'] == $prefix &&
                    $record['directory'] == $directory
                ){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                $prepend[]  = array(
                    'prefix' => $prefix,
                    'directory' => $directory
                );
            }
        } else {
            $found = false;
            foreach($list as $record){
                if(
                    $record['prefix'] == $prefix &&
                    $record['directory'] == $directory &&
                    !empty($record['extension']) &&
                    $record['extension'] == $extension
                ){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                $prepend[]  = array(
                    'prefix' => $prefix,
                    'directory' => $directory,
                    'extension' => $extension
                );
            }
        }
        foreach($list as $record){
            $prepend[] = $record;
        }
        $this->setPrefixList($prepend);
    }

    private function setPrefixList($list = array()){
        $this->prefixList = $list;
    }

    public function getPrefixList(){
        return $this->prefixList;
    }

    /**
     * @throws Exception
     */
    public function load($load): bool
    {
        $file = $this->locate($load);
        if (!empty($file)) {
            require_once $file;
            return true;
        }
        return false;
    }

    private static function name_reducer(App $object, $name='', $length=100, $separator='_', $pop_or_shift='pop'){
        $name_length = strlen($name);
        if($name_length >= $length){
            $explode = explode($separator, $name);
            $explode = array_unique($explode);
            $tmp = implode('_', $explode);
            if(strlen($tmp) < $length){
                $name = $tmp;
            } else {
                while(strlen($tmp) >= $length){
                    $count = count($explode);
                    if($count === 1){
                        break;
                    }
                    switch($pop_or_shift){
                        case 'pop':
                            array_pop($explode);
                        break;
                        case 'shift':
                            array_shift($explode);
                        break;
                    }
                    $tmp = implode('_', $explode);
                }
                $name = $tmp;
            }
        }
        return str_replace($separator, '_', $name);
    }

    public function fileList($item=array(), $url=''): array
    {
        if(empty($item)){
            return [];
        }
        if(empty($this->read)){
            $this->read = $this->read($url);
        }
        $data = [];
        $caller = get_called_class();
        $object = $this->object();
        if(
            $object &&
            $object->config('autoload.cache.class')
        ){
            $load = $item['directory'] . $item['file'];
            $load_directory = dirname($load);
            $load = basename($load) . '.' . Autoload::EXT_PHP;
            $load = Autoload::name_reducer($object, $load, $object->config('autoload.cache.file.max_length_file'),'_', 'shift');
            $load_directory = Autoload::name_reducer($object, $load_directory, $object->config('autoload.cache.file.max_length_directory'), $object->config('ds'), 'pop');
            $load_url = $object->config('autoload.cache.class') . $load_directory . '_' . $load;
            $data[] = $load_url;
            d($load_url);
            $object->config('autoload.cache.file.name', $load_url);
        }
        if(
            property_exists($this->read, 'autoload') &&
            property_exists($this->read->autoload, $caller) &&
            property_exists($this->read->autoload->{$caller}, $item['load'])
        ){
            $data[] = $this->read->autoload->{$caller}->{$item['load']};
        }
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        if(empty($item['dirName'])){
            $data[] = $item['directory'] . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
            $data[] = $item['directory'] . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
            $data[] = $item['directory'] . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
            $data[] = $item['directory'] . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
            $data[] =  '[---]';
        } else {
            $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
            $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
            $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
            $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
            $data[] =  '[---]';
        }
        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $this->fileList[$item['baseName']][] = $data;
        $result = array();
        foreach($data as $nr => $file){
            if($file === '[---]'){
                $file = $file . $nr;
            }
            $result[$file] = $file;
        }
        return $result;
    }

    /**
     * @throws LocateException
     * @throws Exception
     */
    public function locate($load=null, $is_data=false){
        d($load);
        $dir = $this->cache_dir();
        $url = $dir . Autoload::FILE;
        $load = ltrim($load, '\\');
        $prefixList = $this->getPrefixList();
        $fileList = [];
        $object = $this->object();
        if(!empty($prefixList)){
            foreach($prefixList as $nr => $item){
                if(empty($item['prefix'])){
                    continue;
                }
                if(empty($item['directory'])){
                    continue;
                }
                $item['file'] = false;
                if (strpos($load, $item['prefix']) === 0) {
                    $item['file'] =
                    trim(substr($load, strlen($item['prefix'])),'\\');
                    $item['file'] =
                    str_replace('\\', DIRECTORY_SEPARATOR, $item['file']);
                } elseif($is_data === false) {
                    $tmp = explode('.', $load);
                    if(count($tmp) >= 2){
                        array_pop($tmp);
                    }
                    $item['file'] = implode('.',$tmp);
                } else {
                    continue;
                }
                if(empty($item['file'])){
                    $item['file'] = $load;
                }
                if(!empty($item['file'])){
                    $item['load'] = $load;
                    $item['file'] = str_replace('\\', DIRECTORY_SEPARATOR, $item['file']);
                    $item['file'] = str_replace('.'  . DIRECTORY_SEPARATOR , DIRECTORY_SEPARATOR, $item['file']);
                    $item['baseName'] = basename(
                        $this->removeExtension($item['file'],
                            array(
                                Autoload::EXT_PHP,
                                Autoload::EXT_TPL
                            )
                    ));
                    $item['baseName'] = explode(DIRECTORY_SEPARATOR, $item['baseName'], 2);
                    $item['baseName'] = end($item['baseName']);
                    $item['dirName'] = dirname($item['file']);
                    if($item['dirName'] == '.'){
                        unset($item['dirName']);
                    }
                    $fileList[$nr] = $this->fileList($item, $url);
                    if(is_array($fileList[$nr]) && empty($this->expose())){
                        foreach($fileList[$nr] as $file){
                            if(substr($file, 0, 5) == '[---]'){
                                continue;
                            }
                            if(file_exists($file)){
                                if($object->config('autoload.cache.file.name')){
                                    $config_dir = $object->config('ramdisk.url') .
                                        Autoload::NAME .
                                        $object->config('ds')
                                    ;
                                    $config_url = $config_dir .
                                        'File.Mtime' .
                                        $object->config('extension.json')
                                    ;
                                    $mtime = $object->get(sha1($config_url));
                                    if(empty($mtime)){
                                        $mtime = [];
                                        if(file_exists($config_url)){
                                            $mtime = file_get_contents($config_url);
                                            if($mtime){
                                                $mtime = json_decode($mtime, true);
                                            }
                                        }
                                    }
                                    if(
                                        $mtime &&
                                        $file === $object->config('autoload.cache.file.name')
                                    ){
                                        if(
                                            array_key_exists(sha1($file), $mtime) &&
                                            filemtime($file) === filemtime($mtime[sha1($file)])
                                        ){
                                            //from ramdisk
                                            $this->cache($file, $load);
                                            return $file;
                                        } else {
                                            continue;
                                        }
                                    } else {
                                        if(Autoload::ramdisk_exclude_load($object, $load)){
                                            //controllers cannot be cached
                                            d($file);
                                        } else {
                                            //from disk
                                            //copy to ramdisk
                                            $id = posix_geteuid();
                                            $dirname = dirname($object->config('autoload.cache.file.name'));
                                            if(!is_dir($dirname)){
                                                mkdir($dirname, 0750, true);
                                                if(empty($id)){
                                                    exec('chown www-data:www-data ' . $dirname);
                                                }
                                            }
                                            $read = file_get_contents($file);
                                            if(Autoload::ramdisk_exclude_content($object, $read)){
                                                d($file);
                                                //files with content __DIR__, __FILE__ cannot be cached
                                            } else {
                                                file_put_contents($object->config('autoload.cache.file.name'), $read);
                                                touch($object->config('autoload.cache.file.name'), filemtime($file));
                                                //save file reference for filemtime comparison
                                                $mtime[sha1($object->config('autoload.cache.file.name'))] = $file;
                                                if(!is_dir($config_dir)){
                                                    mkdir($config_dir, 0750, true);
                                                }
                                                file_put_contents($config_url, json_encode($mtime, JSON_PRETTY_PRINT));
                                                $object->set(sha1($config_url), $mtime);
                                                $id = posix_geteuid();
                                                if(empty($id)){
                                                    exec('chown www-data:www-data ' . $object->config('autoload.cache.file.name'));
                                                    exec('chown www-data:www-data ' . $config_dir);
                                                    exec('chown www-data:www-data ' . $config_url);
                                                }
                                            }
                                        }
                                    }
                                }
                                $this->cache($file, $load);
                                return $file;
                            }
                        }
                    }
                }
            }
        }
        if($is_data === true){
            if($this->environment() == 'development'){
                throw new LocateException('Could not find data file (' . $load . ')', Autoload::exception_filelist($fileList));
            } else {
                throw new LocateException('Could not find data file (' . $load . ')');
            }

        }
        if($this->environment() == 'development' || !empty($this->expose())){
            if(empty($this->expose())){
                Logger::debug('Autoload prefixList: ', [ $prefixList ]);
                Logger::debug('Autoload error: ', [ $fileList ]);
                throw new LocateException('Autoload error, cannot load (' . $load .') class.', Autoload::exception_filelist($fileList));
            }
            $object = new stdClass();
            $object->load = $load;
            $debug = debug_backtrace(true);
            $output = [];
            for($i=0; $i < 5; $i++){
                if(!isset($debug[$i])){
                    continue;
                }
                $output[$i] = $debug[$i];
            }
            $attribute = 'R3m\Io\Exception\LocateException';
            if(!empty($this->expose())){
                $attribute = $load;
            }
            if(
                isset($item) &&
                isset($item['baseName']) &&
                isset($this->fileList[$item['baseName']])
            ){
                $object->{$attribute} = $this->fileList[$item['baseName']];
            }
            $object->debug = $output;
            if(ob_get_level() !== 0){
                ob_flush();
            }
            if(empty($this->expose())){
                echo '<pre>';
                echo json_encode($object, JSON_PRETTY_PRINT);
                echo '</pre>';
                die;

            } else {
                echo json_encode($object, JSON_PRETTY_PRINT);
            }
        }
        return false;
    }

    public function __destruct(){
        if(!empty($this->read)){
            $dir = $this->cache_dir();
            if($dir){
                $url = $dir . Autoload::FILE;
                $this->write($url, $this->read);
                $id = posix_getuid();
                if(empty($id)){
                    if(file_exists($dir)){
                        exec('chown www-data:www-data ' . $dir);
                    }
                    if(file_exists($url)){
                        exec('chown www-data:www-data ' . $url);
                    }
                }
            }
        }
    }

    public function cache_dir($directory=null){
        if($directory !== null){
            $this->cache_dir = $directory;
        }
        return $this->cache_dir;
    }

    private function cache($file='', $class=''){
        if(empty($this->read)){
            $dir = $this->cache_dir();
            $url = $dir . Autoload::FILE;
            $this->read = $this->read($url);
        }
        if(empty($this->read->autoload)){
            $this->read->autoload = new stdClass();
        }
        $caller = get_called_class();
        if(empty($this->read->autoload->{$caller})){
            $this->read->autoload->{$caller}= new stdClass();
        }
        $this->read->autoload->{$caller}->{$class} = (string) $file;
    }

    protected function write($url='', $data=''){
        if(posix_geteuid() === 0){
            return false;
        }
        $data = (string) json_encode($data, JSON_PRETTY_PRINT);
        if(empty($data)){
            return false;
        }
        $fwrite = 0;
        $dir = dirname($url);
        if(is_dir($dir) === false){
            try {
                @mkdir($dir, 0750, true);
            } catch(Exception $exception){
                return false;
            }
        }
        if(is_dir($dir) === false){
            return false;
        }
        $resource = fopen($url, 'w');
        if($resource === false){
            return $resource;
        }
        flock($resource, LOCK_EX);
        fseek($resource, 0);
        for ($written = 0; $written < strlen($data); $written += $fwrite) {
            $fwrite = fwrite($resource, substr($data, $written));
            if ($fwrite === false) {
                break;
            }
        }
        flock($resource, LOCK_UN);
        fclose($resource);
        if($written != strlen($data)){
            return false;
        } else {
            return $fwrite;
        }
    }

    private function read($url=''){
        if(file_exists($url) === false){
            $this->read = new stdClass();
            return $this->read;
        }
        $this->read =  json_decode(implode('', file($url)));
        if(empty($this->read)){
            $this->read = new stdClass();
        }
        return $this->read;
    }

    private function removeExtension($filename='', $extension=array()){
        foreach($extension as $ext){
            $ext = '.' . ltrim($ext, '.');
            $filename = explode($ext, $filename, 2);
            if(count($filename) > 1 && empty(end($filename))){
                array_pop($filename);
            }
            $filename = implode($ext, $filename);
        }
        return $filename;
    }

    public function expose($expose=null)
    {
        if(!empty($expose) || $expose === false){
            $this->expose = (bool) $expose;
        }
        return $this->expose;
    }

    private static function exception_filelist($filelist=[]): array
    {
        $result = [];
        foreach($filelist as  $list){
            foreach($list as $record){
                if(substr($record, 0, 5) === '[---]'){
                    $result[] = '[---]';
                } else {
                    $result[] = $record;
                }
            }
        }
        return $result;
    }

    public static function ramdisk_exclude_load(App $object, $load=''): bool
    {
        $is_exclude = false;
        $exclude_load = $object->config('ramdisk.autoload.exclude.load');
        if(
            !empty($exclude_load) &&
            is_array($exclude_load)
        ){
            foreach($exclude_load as $needle){
                if(stristr($load, $needle) !== false){
                    $is_exclude = true;
                    break;
                }
            }
        }
        return $is_exclude;
    }

    public static function ramdisk_exclude_content(App $object, $content=''): bool
    {
        $exclude_content = $object->config('ramdisk.autoload.exclude.content');
        $is_exclude = false;
        if(
            !empty($exclude_content) &&
            is_array($exclude_content)
        ){
            foreach ($exclude_content as $needle){
                if(stristr($content, $needle) !== false){
                    $is_exclude = true;
                    break;
                }
            }
        }
        return $is_exclude;
    }

    public static function ramdisk_configure(App $object){
        $function ='ramdisk_load';
        spl_autoload_register(array($object, $function), true, true);
    }
}