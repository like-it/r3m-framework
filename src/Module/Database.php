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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use stdClass;
use R3m\Io\App;
use R3m\Io\Config;
use Exception;
use PDO;
use PDOException;

class Database {
    const NAMESPACE = __NAMESPACE__;
    const NAME = 'Database';
    const FETCH = PDO::FETCH_OBJ;

    public static function connect($object, $environment=null){
        $config = $object->data(App::CONFIG);
        if(empty($environment)){
            $environment = $config->data(Config::DATA_FRAMEWORK_ENVIRONMENT);
        }
        $data = $config->data(Config::DATA_PDO . '.' . $environment);
        if(empty($data)){
            throw new Exception('Config data missing for environment (' . $environment .')');
        }
        $username = $config->data(Config::DATA_PDO . '.' . $environment . '.' . 'user');
        $password = $config->data(Config::DATA_PDO . '.' . $environment . '.' . 'password');
        $options = $config->data(Config::DATA_PDO . '.' . $environment . '.' . 'options');
        $driver = $config->data(Config::DATA_PDO . '.' . $environment . '.' . 'driver');
        $dbname = $config->data(Config::DATA_PDO . '.' . $environment . '.' . 'dbname');
        $host = $config->data(Config::DATA_PDO . '.' . $environment . '.' . 'host');
        switch($driver){
            case 'pdo_mysql' :
                    $dsn = 'mysql:dbname=' . $dbname . ';host=' . $host;
            break;
            default:
                throw new Exception('driver undefined');
        }
        $pdo = null;
        if(empty($username) && empty($password) && empty($options)){
            //sqlite
            try {
                $pdo = new PDO($dsn);
            } catch (PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
            }
        }
        elseif(empty($options)){
            try {
                $pdo = new PDO($dsn, $username, $password);
            } catch (PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
                exit;
            }
        } else {
            try {
                $pdo = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
                exit;
            }
        }
        // fix LIMIT 0, 1000
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
        return $pdo;
    }

    public static function entityManager(App $object, $options=[]){
        $url = $object->config('project.dir.data') . 'Config.json';
        $config  = $object->parse_read($url, sha1($url));
        if($config){
            $environment = $config->get('framework.environment');
            if(empty($environment)){
                $environment = Config::MODE_DEVELOPMENT;
            }
            $connection = (array) $config->get('doctrine.' . $environment);
            $is_development = false;
            if($environment == Config::MODE_DEVELOPMENT){
                $is_development = true;
            }
            $paths = $config->get('doctrine.paths');
            $proxyDir = null;
            $cache = null;
            $useSimpleAnnotationReader = false;
            $config = Setup::createAnnotationMetadataConfiguration($paths, $is_development, $proxyDir, $cache, $useSimpleAnnotationReader);
//        $config = Setup::createXMLMetadataConfiguration(array(__DIR__."/config/xml"), $is_development);
            return EntityManager::create($connection, $config);
        }
    }
}