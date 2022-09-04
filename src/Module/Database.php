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

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PDO;

use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use Doctrine\DBAL\Logging;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;

use Doctrine\ORM\ORMSetup;

use R3m\Io\App;
use R3m\Io\Config;

use Exception;
use PDOException;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\Exception\ORMException as ORMException2;

class Database {
    const NAMESPACE = __NAMESPACE__;
    const NAME = 'Database';
    const FETCH = PDO::FETCH_OBJ;

    const LOGGER_DOCTRINE = 'Doctrine';

    /**
     * @throws Exception
     */
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

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws ORMException
     * @throws ORMException2
     * @throws Exception
     */
    public static function entityManager(App $object, $options=[]){
        $entityManager = $object->get(Database::NAME . '.entityManager');
        if(!empty($entityManager)){
            return $entityManager;
        }
        $environment = $object->config('framework.environment');
        $url = $object->config('project.dir.data') . 'Config.json';
        $config  = $object->parse_read($url, sha1($url));
        if($config){
            if(empty($environment)){
                $environment = Config::MODE_DEVELOPMENT;
            }
            if(array_key_exists('environment', $options)){
                $environment = $options['environment'];
            }
            $connection = (array) $config->get('doctrine.' . $environment);
            $paths = $config->get('doctrine.paths');
            $proxyDir = $config->get('doctrine.proxy.dir');
            $cache = null;
            $config = ORMSetup::createAnnotationMetadataConfiguration($paths, false, $proxyDir, $cache);

            $logger = new Logger(Database::LOGGER_DOCTRINE);
            $logger->pushHandler(new StreamHandler($object->config('project.dir.log') . 'sql.log', Logger::DEBUG));
            $logger->pushProcessor(new PsrLogMessageProcessor(null, true));

            $object->logger($logger->getName(), $logger);
            $logger->info('Logger initialized...');

            $config->setMiddlewares([new Logging\Middleware($logger)]);
            $connection = DriverManager::getConnection($connection, $config, new EventManager());
            $logging_connection = new Logging\Connection($connection, $logger);
            $em = EntityManager::create($connection, $config);
            $debug_stack = new DebugStack();
            $em->getConnection()->getConfiguration()->setSQLLogger($debug_stack);


            $object->set(Database::NAME .'.entityManager', $em);
            return $em;
        }
    }
}