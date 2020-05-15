<?php
/**
 * @author          Remco van der Velde
 * @since           2020-01-04
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

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
        $dsn = $config->data(Config::DATA_PDO . '.' . $environment . '.' . 'dsn');
        $username = $config->data(Config::DATA_PDO . '.' . $environment . '.' . 'username');
        $password = $config->data(Config::DATA_PDO . '.' . $environment . '.' . 'password');
        $options = $config->data(Config::DATA_PDO . '.' . $environment . '.' . 'options');
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
}