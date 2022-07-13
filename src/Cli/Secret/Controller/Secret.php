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
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

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
    const ACTION_STATUS = 'status';

    /**
     * @throws WrongKeyOrModifiedCiphertextException
     * @throws EnvironmentIsBrokenException
     * @throws BadFormatException
     * @throws FileWriteException
     * @throws ObjectException
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
                    if($data->has('secret.uuid')){
                        $uuid = Crypto::decrypt($data->get('secret.uuid'), $key);
                        if($data->has($uuid)){
                            $session = Crypto::decrypt((string) $data->get($uuid), $key);
                            if($session){
                                $session = Core::object($session, Core::OBJECT_ARRAY);
                                if(
                                    array_key_exists('unlock', $session) &&
                                    array_key_exists('since', $session['unlock']) &&
                                    !empty($session['unlock']['since'])
                                ){
                                    echo Crypto::decrypt($get, $key) . PHP_EOL;
                                    return;
                                }
                            }
                        }
                    }
                    if($data->has('secret.username')){
                        echo "Secret is locked, unlock first..." . PHP_EOL;
                        return;
                    } else {
                        echo "Secret is locked, unlock first..." . PHP_EOL;
                        return;
                    }
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
            $string = File::read($key_url);
            $key = Key::loadFromAsciiSafeString($string);
            $data = $object->data_read($url);
            if(!$data) {
                $data = new Data();
            }
            if($data){
                if($data->has('secret.uuid')) {
                    $uuid = Crypto::decrypt($data->get('secret.uuid'), $key);
                    if ($data->has($uuid)) {
                        $session = Crypto::decrypt($data->get($uuid), $key);
                        if ($session) {
                            $session = Core::object($session, Core::OBJECT_ARRAY);
                            if (
                                array_key_exists('unlock', $session) &&
                                array_key_exists('since', $session['unlock']) &&
                                !empty($session['unlock']['since'])
                            ) {
                                $value = Crypto::encrypt((string) $value, $key);
                                $data->set($attribute, $value);
                                $dir = Dir::name($url);
                                Dir::create($dir, Dir::CHMOD);
                                $data->write($url);
                                $command = 'chown www-data:www-data ' . $url;
                                Core::execute($command);
                                echo $attribute . PHP_EOL;
                                return;
                            }
                        }
                    }
                }
                if($data->has('secret.username')){
                    echo "Secret locked, unlock first..." . PHP_EOL;
                } else {
                    $value = Crypto::encrypt((string) $value, $key);
                    $data->set($attribute, $value);
                    $dir = Dir::name($url);
                    Dir::create($dir, Dir::CHMOD);
                    $data->write($url);
                    $command = 'chown www-data:www-data ' . $url;
                    Core::execute($command);
                    echo $attribute . PHP_EOL;
                }
            }


        }
        elseif($action === Secret::ACTION_HAS){
            $attribute = $object->parameter($object, $action, 1);
            if(empty($attribute)){
                $attribute = Cli::read('input', 'key: ');
            }
            $data = $object->data_read($url);
            if(
                $data->has('secret.username') &&
                $data->has('secret.password') &&
                !$data->has('secret.uuid')
            ){
                echo "Secret is locked, unlock first..." . PHP_EOL;
                return;
            }
            if($data->has('secret.uuid')) {
                $string = File::read($key_url);
                $key = Key::loadFromAsciiSafeString($string);
                $uuid = Crypto::decrypt($data->get('secret.uuid'), $key);
                if ($data->has($uuid)) {
                    $session = Crypto::decrypt($data->get($uuid), $key);
                    if ($session) {
                        $session = Core::object($session, Core::OBJECT_ARRAY);
                        if (
                            array_key_exists('unlock', $session) &&
                            array_key_exists('since', $session['unlock']) &&
                            !empty($session['unlock']['since'])
                        ) {
                            if ($data && $data->has($attribute)) {
                                echo 'true' . PHP_EOL;
                            } else {
                                echo 'false' . PHP_EOL;
                            }
                        }
                    }
                }
            }
        }
        elseif($action === Secret::ACTION_DELETE) {
            $attribute = $object->parameter($object, $action, 1);
            if (empty($attribute)) {
                $attribute = Cli::read('input', 'key: ');
            }
            $data = $object->data_read($url);
            if ($data) {
                if(
                    $data->has('secret.username') &&
                    $data->has('secret.password') &&
                    !$data->has('secret.uuid')
                ){
                    echo "Secret is locked, unlock first..." . PHP_EOL;
                    return;
                }
                if($data->has('secret.uuid')){
                    $string = File::read($key_url);
                    $key = Key::loadFromAsciiSafeString($string);
                    $uuid = Crypto::decrypt($data->get('secret.uuid'), $key);
                    if($data->has($uuid)){
                        $session = Crypto::decrypt($data->get($uuid), $key);
                        if($session){
                            $session = Core::object($session, Core::OBJECT_ARRAY);
                            if(
                                array_key_exists('unlock', $session) &&
                                array_key_exists('since', $session['unlock']) &&
                                !empty($session['unlock']['since'])
                            ){
                                $data->delete($attribute);
                                $data->write($url);
                                $command = 'chown www-data:www-data ' . $url;
                                Core::execute($command);
                                echo 'Secret delete: ' . $attribute . PHP_EOL;
                                return;
                            }
                        }
                    }
                }
                echo 'Secret is locked...' . PHP_EOL;
                return;
            }
        }
        elseif($action === Secret::ACTION_LOCK) {
            $username = $object->parameter($object, $action, 1);
            $password = $object->parameter($object, $action, 2);
            $cost = $object->parameter($object, $action, 3);
            $data = $object->data_read($url);
            if(!$data) {
                $data = new Data();
            }
            if ($data) {
                if($data->has('secret.uuid')){
                    if(
                        !empty($username) &&
                        !empty($password)
                    ){
                        $attribute = 'secret.username';
                        $get = $data->get($attribute);
                        $string = File::read($key_url);
                        $key = Key::loadFromAsciiSafeString($string);
                        $username = Crypto::encrypt((string) $username, $key);
                        $data->set($attribute, $username);
                        if (empty($cost)) {
                            $attribute = 'secret.cost';
                            if($data->has($attribute)){
                                $cost = Crypto::decrypt($data->get($attribute), $key);
                            }
                            if (empty($cost)) {
                                $cost = 13;
                            }
                        }
                        $value = Crypto::encrypt((string) $cost, $key);
                        $data->set($attribute, $value);
                        $attribute = 'secret.password';
                        $hash = password_hash(
                            $password,
                            PASSWORD_BCRYPT,
                            [
                                'cost' => (int) $cost
                            ]
                        );
                        $password = Crypto::encrypt((string) $hash, $key);
                        $data->set($attribute, $password);
                        if($data->has('secret.uuid')){
                            $uuid = Crypto::decrypt($data->get('secret.uuid'), $key);
                            $data->delete('secret.uuid');
                            $data->delete($uuid);
                        }
                        $dir = Dir::name($url);
                        Dir::create($dir, Dir::CHMOD);
                        $write = $data->write($url);
                        $command = 'chown www-data:www-data ' . $url;
                        Core::execute($command);
                        echo "Successfully locked with new username & password..." . PHP_EOL;
                        return;
                    }
                    $string = File::read($key_url);
                    $key = Key::loadFromAsciiSafeString($string);
                    $uuid = Crypto::decrypt($data->get('secret.uuid'), $key);
                    $data->delete('secret.uuid');
                    $data->delete($uuid);
                    $dir = Dir::name($url);
                    Dir::create($dir, Dir::CHMOD);
                    $write = $data->write($url);
                    $command = 'chown www-data:www-data ' . $url;
                    Core::execute($command);
                    echo "Successfully locked..." . PHP_EOL;
                    return;
                } else {
                    if(
                        !empty($username) &&
                        !empty($password)
                    ){
                        $attribute = 'secret.username';
                        $get = $data->get($attribute);
                        if(empty($get)) {
                            $string = File::read($key_url);
                            $key = Key::loadFromAsciiSafeString($string);
                            $username = Crypto::encrypt((string)$username, $key);
                            $data->set($attribute, $username);
                            if (empty($cost)) {
                                $attribute = 'secret.cost';
                                if ($data->has($attribute)) {
                                    $cost = Crypto::decrypt($data->get($attribute), $key);
                                }
                                if (empty($cost)) {
                                    $cost = 13;
                                }
                            }
                            dd($data);
                            $value = Crypto::encrypt((string)$cost, $key);
                            $data->set($attribute, $value);
                            $attribute = 'secret.password';
                            dd($data);
                            $hash = password_hash(
                                $password,
                                PASSWORD_BCRYPT,
                                [
                                    'cost' => (int)$cost
                                ]
                            );
                            $password = Crypto::encrypt((string)$hash, $key);
                            $data->set($attribute, $password);
                            $dir = Dir::name($url);
                            Dir::create($dir, Dir::CHMOD);
                            $write = $data->write($url);
                            $command = 'chown www-data:www-data ' . $url;
                            Core::execute($command);
                            echo "Successfully locked..." . PHP_EOL;
                            return;
                        }
                    }
                }
                if (empty($username)) {
                    $username = Cli::read('input', 'username: ');
                }
                if (empty($password)) {
                    $password = Cli::read('input-hidden', 'password: ');
                }
                $attribute = 'secret.username';
                $get = $data->get($attribute);
                if(empty($get)){
                    $string = File::read($key_url);
                    $key = Key::loadFromAsciiSafeString($string);
                    $username = Crypto::encrypt((string) $username, $key);
                    $data->set($attribute, $username);
                    if (empty($cost)) {
                        $attribute = 'secret.cost';
                        if($data->has($attribute)){
                            $cost = Crypto::decrypt($data->get($attribute), $key);
                        }
                        if (empty($cost)) {
                            $cost = 13;
                        }
                    }
                    $value = Crypto::encrypt((string) $cost, $key);
                    $data->set($attribute, $value);
                    $attribute = 'secret.password';
                    $hash = password_hash(
                        $password,
                        PASSWORD_BCRYPT,
                        [
                            'cost' => (int) $cost
                        ]
                    );
                    $password = Crypto::encrypt((string) $hash, $key);
                    $data->set($attribute, $password);
                    $dir = Dir::name($url);
                    Dir::create($dir, Dir::CHMOD);
                    $write = $data->write($url);
                    $command = 'chown www-data:www-data ' . $url;
                    Core::execute($command);
                    echo "Successfully locked..." . PHP_EOL;
                } else if (
                    $get &&
                    File::exist($url)
                ) {
                    $string = File::read($key_url);
                    $key = Key::loadFromAsciiSafeString($string);
                    if(
                        $data->has('secret.username') &&
                        $data->has('secret.password') &&
                        !$data->has('secret.uuid')
                    ){
                        echo "Secret is locked, unlock first..." . PHP_EOL;
                        return;
                    }
                    if($data->has('secret.uuid')){
                        $uuid = Crypto::decrypt($data->get('secret.uuid'), $key);
                        $data->delete('secret.uuid');
                        $data->delete($uuid);
                    }
                    $username = Crypto::encrypt((string) $username, $key);
                    $data->set($attribute, $username);
                    if (empty($cost)) {
                        $attribute = 'secret.cost';
                        if($data->has($attribute)){
                            $cost = Crypto::decrypt($data->get($attribute), $key);
                        }
                        if (empty($cost)) {
                            $cost = 13;
                        }
                    }
                    $value = Crypto::encrypt((string) $cost, $key);
                    $data->set($attribute, $value);
                    $attribute = 'secret.password';
                    $hash = password_hash(
                        $password,
                        PASSWORD_BCRYPT,
                        [
                            'cost' => (int) $cost //move to encrypted old value
                        ]
                    );
                    $password = Crypto::encrypt((string) $hash, $key);
                    $data->set($attribute, $password);
                    $dir = Dir::name($url);
                    Dir::create($dir, Dir::CHMOD);
                    $write = $data->write($url);
                    echo $url . PHP_EOL;
                    echo $write . PHP_EOL;
                    $command = 'chown www-data:www-data ' . $url;
                    Core::execute($command);
                    echo "Successfully locked..." . PHP_EOL;
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
                $password = Cli::read('input-hidden', 'password: ');
            }
            $data = $object->data_read($url);
            $verify = false;
            if ($data) {
                if($data->get('secret.uuid')){
                    echo "Already unlocked..." . PHP_EOL;
                    return;
                }
                $attribute = 'secret.username';
                $get = $data->get($attribute);
                if (
                    $get &&
                    File::exist($url)
                ) {
                    $string = File::read($key_url);
                    $key = Key::loadFromAsciiSafeString($string);
                    $get = Crypto::decrypt((string) $get, $key);
                    if ($get === $username) {
                        $attribute = 'secret.password';
                        $get = $data->get($attribute);
                        $hash = Crypto::decrypt((string) $get, $key);
                        $verify = password_verify($password, $hash);
                        if ($verify) {
                            $attribute = 'secret.uuid';
                            $uuid = Core::uuid();
                            $value = Crypto::encrypt((string) $uuid, $key);
                            $data->set($attribute, $value);
                            $json = [];
                            $json['unlock'] = [];
                            $json['unlock']['since'] = microtime(true);
                            $value = Core::object($json, Core::OBJECT_JSON);
                            $value = Crypto::encrypt((string) $value, $key);
                            $data->set($uuid, $value);
                            $dir = Dir::name($url);
                            Dir::create($dir, Dir::CHMOD);
                            $data->write($url);
                            $command = 'chown www-data:www-data ' . $url;
                            Core::execute($command);
                            echo "Successfully unlocked..." . PHP_EOL;
                            return;
                        }
                    }
                }
                sleep(2);
                echo "Invalid username and / or password..." . PHP_EOL;
            }
        }
        elseif($action === Secret::ACTION_STATUS) {
            $string = File::read($key_url);
            $key = Key::loadFromAsciiSafeString($string);
            $data = $object->data_read($url);
            if($data){
                if($data->has('secret.uuid')){
                    $uuid = Crypto::decrypt($data->get('secret.uuid'), $key);
                    if($data->has($uuid)){
                        $session = Crypto::decrypt($data->get($uuid), $key);
                        if($session) {
                            $session = Core::object($session, Core::OBJECT_ARRAY);
                            if (
                                array_key_exists('unlock', $session) &&
                                array_key_exists('since', $session['unlock']) &&
                                !empty($session['unlock']['since'])
                            ) {
                                echo 'Session unlocked since: ' . date('Y-m-d H:i:s', $session['unlock']['since']) . '+00:00' . PHP_EOL;
                                return;
                            }
                        }
                    }
                } else {
                    if($data->get('secret.username')){
                        echo 'Session locked...' . PHP_EOL;
                    } else {
                        echo 'Session unlocked...' . PHP_EOL;
                    }
                }
            }
        }
    }
}