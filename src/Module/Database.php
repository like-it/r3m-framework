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
     * @throws ORMException2
     * @throws ORMException
     * @throws \Doctrine\DBAL\Exception
     * @throws FileWriteException
     * @throws Exception
     */
    public static function entityManager(App $object, $options=[]): ?EntityManager
    {
        $environment = $object->config('framework.environment');
        if(empty($environment)){
            $environment = Config::MODE_DEVELOPMENT;
        }
        if(array_key_exists('environment', $options)){
            $environment = $options['environment'];
        }
        $name = $object->config('framework.api');
        if(array_key_exists('name', $options)){
            $name = $options['name'];
        }
        $entityManager = $object->get(Database::NAME . '.entityManager.' . $name . '.' . $environment);
        if(!empty($entityManager)){
            return $entityManager;
        }
        $connection = $object->config('doctrine.' . $name . '.' . $environment);
        if(!empty($connection)){
            $connection = (array) $connection;
            if(empty($connection)){
                $logger = new Logger(Database::LOGGER_DOCTRINE);
                $logger->pushHandler(new StreamHandler($object->config('project.dir.log') . 'sql.log', Logger::DEBUG));
                $logger->pushProcessor(new PsrLogMessageProcessor(null, true));
                $object->logger($logger->getName(), $logger);
                $logger->error('Error: No connection string...');
                return null;
            }
            $paths = $object->config('doctrine.paths');
            $paths = Config::parameters($object, $paths);
            $parameters = [];
            $parameters[] = $object->config('doctrine.proxy.dir');
            $parameters = Config::parameters($object, $parameters);
            if(array_key_exists(0, $parameters)){
                $proxyDir = $parameters[0];
            }
            $cache = null;
            $config = ORMSetup::createAnnotationMetadataConfiguration($paths, false, $proxyDir, $cache);

            if(!empty($connection['logging'])){
                $logger = new Logger(Database::LOGGER_DOCTRINE);
                $logger->pushHandler(new StreamHandler($object->config('project.dir.log') . 'sql.log', Logger::DEBUG));
                $logger->pushProcessor(new PsrLogMessageProcessor(null, true));
                $object->logger($logger->getName(), $logger);
                $logger->info('Logger initialised.');
                $config->setMiddlewares([new Logging\Middleware($logger)]);
            }
            $connection = DriverManager::getConnection($connection, $config, new EventManager());
            $em = EntityManager::create($connection, $config);
            $object->set(Database::NAME .'.entityManager.' . $name . '.' . $environment, $em);
            return $em;
        }
        return null;
    }
}