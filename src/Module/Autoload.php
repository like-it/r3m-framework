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
        if(
            empty($object->config('ramdisk.is.disabled')) &&
            $object->config('ramdisk.url')
        ){
            $cache_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                Autoload::NAME .
                $object->config('ds')
            ;
            if($cache_dir){
                $class_dir = $object->config('ramdisk.url') .
                    $object->config(Config::POSIX_ID) .
                    $object->config('ds') .
                    'Class' .
                    $object->config('ds')
                ;
                $object->config('autoload.cache.class', $class_dir);
                $compile_dir = $object->config('ramdisk.url') .
                    $object->config(Config::POSIX_ID) .
                    $object->config('ds') .
                    'Compile' .
                    $object->config('ds')
                ;
                $object->config('autoload.cache.compile', $compile_dir);
                if(!is_dir($object->config('ramdisk.url'))){
                    mkdir($object->config('ramdisk.url'), 0750, true);
                    if(empty($object->config(Config::POSIX_ID))){
                        exec('chown www-data:www-data ' . $object->config('ramdisk.url'));
                    }
                }
                if(!is_dir($class_dir)){
                    mkdir($class_dir,0750, true);
                }
            }
        }
        if(empty($cache_dir)){
            $cache_dir = $object->config('autoload.cache.directory');
            if($cache_dir){
                $parameters = [];
                $parameters['cache'] = $cache_dir;
                $parameters = Config::parameters($object, $parameters);
                $cache_dir = $parameters['cache'];
            }
        }
        if(empty($cache_dir)){
            $cache_dir =
                $object->config(Config::DATA_FRAMEWORK_DIR_TEMP) .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                Autoload::NAME .
                $object->config(Config::DS)
            ;
        }
        $autoload->cache_dir($cache_dir);
        $autoload->register();
        $autoload->environment($object->config('framework.environment'));
        $object->data(App::AUTOLOAD_R3M, $autoload);        
    }

    public function register($method='load', $prepend=false){
        $object = $this->object();
        $object->logger($object->config('project.log.name'))->info('Registering autoloader', [$method, $prepend]);
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
        Logger::debug('Autoload loader: ', [ $load ]);
        $file = $this->locate($load);
        if (!empty($file)) {
            require_once $file;
            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function name_reducer(App $object, $name='', $length=100, $separator='_', $pop_or_shift='pop'){
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
                        default:
                            throw new Exception('cannot reduce name with: ' . $pop_or_shift);
                    }
                    $tmp = implode('_', $explode);
                }
                $name = $tmp;
            }
        }
        return str_replace($separator, '_', $name);
    }

    /**
     * @throws Exception
     */
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
            empty($object->config('ramdisk.is.disabled')) &&
            $object->config('autoload.cache.class') &&
            $object->config('cache.autoload.url.name_length') &&
            $object->config('cache.autoload.url.name_separator') &&
            $object->config('cache.autoload.url.name_pop_or_shift') &&
            $object->config('cache.autoload.url.directory_length') &&
            $object->config('cache.autoload.url.directory_separator') &&
            $object->config('cache.autoload.url.directory_pop_or_shift')
        ){
            $load = $item['directory'] . $item['file'];
            $load_directory = dirname($load);
            $load = basename($load) . '.' . Autoload::EXT_PHP;
            $load_compile = Autoload::name_reducer(
                $object,
                $load,
                $object->config('cache.parse.url.name_length'),
                $object->config('cache.parse.url.name_separator'),
                $object->config('cache.parse.url.name_pop_or_shift')
            );
            $data[] = $object->config('autoload.cache.compile') . $load_compile;
            $load = Autoload::name_reducer(
                $object,
                $load,
                $object->config('cache.autoload.url.name_length'),
                $object->config('cache.autoload.url.name_separator'),
                $object->config('cache.autoload.url.name_pop_or_shift')
            );
            $load_directory = Autoload::name_reducer(
                $object,
                $load_directory,
                $object->config('cache.autoload.url.directory_length'),
                $object->config('cache.autoload.url.directory_separator'),
                $object->config('cache.autoload.url.directory_pop_or_shift')
            );
            $load_url = $object->config('autoload.cache.class') . $load_directory . '_' . $load;
            $data[] = $load_url;
            $object->config('autoload.cache.file.name', $load_url);
        }
        if(
            property_exists($this->read, 'autoload') &&
            property_exists($this->read->autoload, $caller) &&
            property_exists($this->read->autoload->{$caller}, $item['load'])
        ){
            $data[] = $this->read->autoload->{$caller}->{$item['load']};
        }
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $this->fileList[$item['baseName']][] = $data;
        $result = array();
        foreach($data as $nr => $file){
            if($file === '[---]'){
                $file = $file . $nr;
            }
            $result[$file] = $file;
        }
        /*
        $data = json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
        $url = '/Application/Log/Autoload.log';
        $resource = @fopen($url, 'a');
        if($resource === false){
            return $result;
        }
        flock($resource, LOCK_EX);
        for ($written = 0; $written < strlen($data); $written += $fwrite) {
            $fwrite = fwrite($resource, substr($data, $written));
            if ($fwrite === false) {
                break;
            }
        }
        flock($resource, LOCK_UN);
        fclose($resource);
        */
        return $result;
    }

    /**
     * @throws LocateException
     * @throws Exception
     */
    public function locate($load=null, $is_data=false){

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
                                if(
                                    empty($object->config('ramdisk.is.disabled')) &&
                                    $object->config('autoload.cache.file.name')
                                ){
                                    $config_dir = $object->config('ramdisk.url') .
                                        $object->config(Config::POSIX_ID) .
                                        $object->config('ds') .
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
                                            $content = file_get_contents($config_url);
                                            if($content){
                                                $mtime = json_decode($content, true);
                                            }
                                        }
                                    }
                                    if(
                                        $mtime &&
                                        $file === $object->config('autoload.cache.file.name') &&
                                        array_key_exists(sha1($file), $mtime) &&
                                        file_exists($mtime[sha1($file)]) &&
                                        filemtime($file) === filemtime($mtime[sha1($file)])
                                    ){
                                        //from ramdisk
                                        $this->cache($file, $load);
                                        return $file;
                                    } else {
                                        if(Autoload::ramdisk_exclude_load($object, $load)){
                                            //controllers cannot be cached
                                        } else {
                                            //from disk
                                            //copy to ramdisk
                                            $dirname = dirname($object->config('autoload.cache.file.name'));
                                            if(!is_dir($dirname)){
                                                mkdir($dirname, 0750, true);
                                            }
                                            $read = file_get_contents($file);
                                            if(Autoload::ramdisk_exclude_content($object, $read, $file)){
                                                //save tp file
                                                //files with content __DIR__, __FILE__ cannot be cached
                                            } else {
                                                file_put_contents($object->config('autoload.cache.file.name'), $read);
                                                touch($object->config('autoload.cache.file.name'), filemtime($file));

                                                //save file reference for filemtime comparison
                                                $mtime[sha1($object->config('autoload.cache.file.name'))] = $file;
                                                if(!is_dir($config_dir)){
                                                    mkdir($config_dir, 0750, true);
                                                }
                                                $write = json_encode($mtime, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
                                                file_put_contents($config_url, $write);
                                                $object->set(sha1($config_url), $mtime);
                                                exec('chmod 640 ' . $object->config('autoload.cache.file.name'));
                                                exec('chmod 640 ' . $config_url);
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
                if(file_exists($url)) {
                    exec('chmod 640 ' . $url);
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

    /**
     * @throws Exception
     */
    public static function ramdisk_exclude_content(App $object, $content='', $file=''): bool
    {
        $exclude_content = $object->config('ramdisk.autoload.exclude.content');
        $is_exclude = false;
        $exclude = [];
        $exclude_dir = false;
        $exclude_url = false;
        if($object->config('ramdisk.url')){
            $exclude_dir = $object->config('ramdisk.url') .
                $object->config(Config::POSIX_ID) .
                $object->config('ds') .
                Autoload::NAME .
                $object->config('ds')
            ;
            $exclude_url = $exclude_dir .
                'Exclude' .
                $object->config('extension.json')
            ;
            if(file_exists($exclude_url)){
                $read = file_get_contents($exclude_url);
                if($read){
                    $exclude = json_decode($read, true);
                    if(
                        array_key_exists(sha1($file), $exclude) &&
                        file_exists($file) &&
                        filemtime($file) === $exclude[sha1($file)]
                    ){
                        return true;
                    }
                }
            }
        }
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
        if(
            $is_exclude &&
            $exclude_dir &&
            $exclude_url &&
            file_exists($file)
        ){
            $exclude[sha1($file)] = filemtime($file);
            $write = json_encode($exclude, JSON_PRETTY_PRINT);
            if(!file_exists($exclude_dir)){
                mkdir($exclude_dir, 0750, true);
            }
            file_put_contents($exclude_url, $write);
            exec('chmod 640 ' . $exclude_url);
        }
        return $is_exclude;
    }

    public static function ramdisk_configure(App $object){
        $function ='ramdisk_load';
        spl_autoload_register(array($object, $function), true, true);
    }
}