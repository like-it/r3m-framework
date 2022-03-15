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
        if($action === Secret::ACTION_GET){
            $attribute = $object->parameter($object, $action, 1);
            $data = $object->data_read($url);
            if($data){
                $get = $data->get($attribute);
                if(
                    $get &&
                    File::exist($url)
                ){
                    $string = File::read($key_url);
                    $key = Key::loadFromAsciiSafeString($string);
                    echo Crypto::decrypt($get, $key);
                }
            }
        }
        elseif($action === Secret::ACTION_SET){
            $attribute = $object->parameter($object, $action, 1);
            $value = $object->parameter($object, $action, 2);
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
                Core::exec($command);
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
            Core::exec($command);
            echo $attribute . PHP_EOL;
        }
        elseif($action === Secret::ACTION_HAS){
            $attribute = $object->parameter($object, $action, 1);
            $data = $object->data_read($url);
            if($data && $data->has($attribute)) {
                echo 'true' . PHP_EOL;
            } else {
                echo 'false' . PHP_EOL;
            }
        }
        elseif($action === Secret::ACTION_DELETE){
            $attribute = $object->parameter($object, $action, 1);
            $data = $object->data_read($url);
            if($data) {
                $data->delete($attribute);
                $data->write($url);
                $command = 'chown www-data:www-data ' . $url;
                Core::exec($command);
                echo 'Secret delete: ' . $attribute . PHP_EOL;
            }
        }
    }
}