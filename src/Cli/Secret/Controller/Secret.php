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
namespace R3m\Io\Cli\Secret\Controller;

use Exception;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use R3m\Io\App;
use R3m\Io\Module\Cli;
use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\View;
use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Secret extends View {
    const DIR = __DIR__;
    const NAME = 'Secret';

    const DEFAULT_NAME = 'info';

    const INFO = '{{binary()}} secret                         | get or set a secret by key';

    const ACTION_GET = 'get';
    const ACTION_SET = 'set';
    const ACTION_HAS = 'has';
    const ACTION_DELETE = 'delete';
    const ACTION_LOCK = 'lock';
    const ACTION_UNLOCK = 'unlock';

    /**
     * @throws WrongKeyOrModifiedCiphertextException
     * @throws EnvironmentIsBrokenException
     * @throws BadFormatException
     * @throws \R3m\Io\Exception\FileWriteException
     */
    public static function run(App $object){
        $action = $object->parameter($object, Secret::NAME, 1);
        if(empty($action)){
            try {
                $name = Secret::name(Secret::DEFAULT_NAME, Secret::NAME);
                $url = Secret::locate($object, $name);
                return Secret::response($object, $url);
            } catch(Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
                return 'Command undefined.' . PHP_EOL;
            }
        }
        $url =
            $object->config('project.dir.data') .
            'Secret' .
            $object->config('extension.json')
        ;
        $key_url =
            $object->config('project.dir.data') .
            'Defuse'.
            $object->config('ds') .
            'Secret.key'
        ;
        dd($action);
        if($action === Secret::ACTION_GET){
            $attribute = $object->parameter($object, $action, 1);
            if(empty($attribute)){
                $attribute = Cli::read('input', 'key: ');
            }
            $data = $object->data_read($url);
            if($data){
                $get = $data->get($attribute);
                if(
                    $get &&
                    File::exist($url)
                ){
                    $string = File::read($key_url);
                    $key = Key::loadFromAsciiSafeString($string);

                    $uuid = Crypto::decrypt($data->get('secret.uuid'));
                    $session = Crypto::decrypt($data->get($uuid));

                    dd($session);

                    echo Crypto::decrypt($get, $key);
                }
            }
        }
        elseif($action === Secret::ACTION_SET){
            $attribute = $object->parameter($object, $action, 1);
            if(empty($attribute)){
                $attribute = Cli::read('input', 'key: ');
            }
            $value = $object->parameter($object, $action, 2);
            if(empty($value)){
                $value = Cli::read('input', 'value:' . PHP_EOL);
            }
            if(File::exist($key_url)){
                $string = File::read($key_url);
                $key = Key::loadFromAsciiSafeString($string);
            } else {
                $key = Key::createNewRandomKey();
                $string = $key->saveToAsciiSafeString();
                $dir = Dir::name($key_url);
                Dir::create($dir, Dir::CHMOD);
                File::write($key_url, $string);
                $command = 'chown www-data:www-data ' . $dir . ' -R';
                Core::execute($command);
            }
            $value = Crypto::encrypt($value, $key);
            $data = $object->data_read($url);
            if(!$data) {
                $data = new Data();
            }
            $data->set($attribute, $value);
            $dir = Dir::name($url);
            Dir::create($dir, Dir::CHMOD);
            $data->write($url);
            $command = 'chown www-data:www-data ' . $url;
            Core::execute($command);
            echo $attribute . PHP_EOL;
        }
        elseif($action === Secret::ACTION_HAS){
            $attribute = $object->parameter($object, $action, 1);
            if(empty($attribute)){
                $attribute = Cli::read('input', 'key: ');
            }
            $data = $object->data_read($url);
            if($data && $data->has($attribute)) {
                echo 'true' . PHP_EOL;
            } else {
                echo 'false' . PHP_EOL;
            }
        }
        elseif($action === Secret::ACTION_DELETE){
            $attribute = $object->parameter($object, $action, 1);
            if(empty($attribute)){
                $attribute = Cli::read('input', 'key: ');
            }
            $data = $object->data_read($url);
            if($data) {
                $data->delete($attribute);
                $data->write($url);
                $command = 'chown www-data:www-data ' . $url;
                Core::execute($command);
                echo 'Secret delete: ' . $attribute . PHP_EOL;
            }

            elseif($action === Secret::ACTION_LOCK) {
                $username = $object->parameter($object, $action, 1);
                $password = $object->parameter($object, $action, 2);
                $cost = $object->parameter($object, $action, 3);
                if (empty($username)) {
                    $username = Cli::read('input', 'username: ');
                }
                if (empty($password)) {
                    $password = Cli::read('input', 'password: ');
                }

                $data = $object->data_read($url);
                if ($data) {
                    $attribute = 'secret.username';
                    $get = $data->get($attribute);
                    if (
                        $get &&
                        File::exist($url)
                    ) {
                        $string = File::read($key_url);
                        $key = Key::loadFromAsciiSafeString($string);
                        $username = Crypto::encrypt($username, $key);
                        $data->set($attribute, $username);
                        if (empty($cost)) {
                            $attribute = 'secret.cost';
                            $cost = Crypto::decrypt($attribute, $key);
                            if (empty($cost)) {
                                $cost = 13;
                            }
                        }
                        $cost = Crypto::encrypt($cost, $key);
                        $data->set($attribute, $cost);
                        $attribute = 'secret.password';
                        $hash = password_hash($password, PASSWORD_BCRYPT, [
                            'cost' => $cost //move to encrypted old value
                        ]);
                        $password = Crypto::encrypt($hash, $key);
                        $data->set($attribute, $password);
                        $dir = Dir::name($url);
                        Dir::create($dir, Dir::CHMOD);
                        $data->write($url);
                        $command = 'chown www-data:www-data ' . $url;
                        Core::execute($command);
                    }
                }
            }
            elseif($action === Secret::ACTION_UNLOCK) {
                $username = $object->parameter($object, $action, 1);
                $password = $object->parameter($object, $action, 2);
                if (empty($username)) {
                    $username = Cli::read('input', 'username: ');
                }
                if (empty($password)) {
                    $password = Cli::read('input', 'password: ');
                }
                $data = $object->data_read($url);
                $verify = false;
                if ($data) {
                    $attribute = 'secret.username';
                    $get = $data->get($attribute);
                    if (
                        $get &&
                        File::exist($url)
                    ) {
                        $string = File::read($key_url);
                        $key = Key::loadFromAsciiSafeString($string);
                        $get = Crypto::decrypt($get, $key);
                        if ($get === $username) {
                            $attribute = 'secret.password';
                            $get = $data->get($attribute);
                            $hash = Crypto::decrypt($get, $key);
                            $verify = password_verify($password, $hash);
                            if ($verify) {
                                $attribute = 'secret.uuid';
                                $uuid = Core::uuid();
                                $value = Crypto::encrypt($uuid, $key);
                                $data->set($attribute, $value);
                                $value = [];
                                $value['unlock'] = [];
                                $value['unlock']['since'] = microtime(true);
                                $data->set($uuid, $value);
                                $dir = Dir::name($url);
                                Dir::create($dir, Dir::CHMOD);
                                $data->write($url);
                                $command = 'chown www-data:www-data ' . $url;
                                Core::execute($command);
                            }
                        }
                    }
                    sleep(2);
                    echo "Invalid username and / or password..." . PHP_EOL;
                }
            }
        }
    }
}