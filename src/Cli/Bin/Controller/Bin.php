<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace R3m\Io\Cli\Bin\Controller;

use Exception;
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\File;
use R3m\Io\Module\Dir;
use R3m\Io\Module\View;

class Bin extends View {
    const DIR = __DIR__;
    const NAME = 'Bin';

    const DEFAULT_NAME = 'r3m.io';
    const TARGET = '/usr/bin/';
    const EXE = 'R3m.php';

    public static function run($object){
        $name = $object->parameter($object, Bin::NAME, 1);
        if(empty($name)){
            $name = Bin::DEFAULT_NAME;
        }
        $object->data('name', $name);
        Bin::create($object);
        $url = Bin::locate($object, 'Info');
        return Bin::view($object, $url);
    }

    private static function create($object){
        $config = $object->data(App::DATA_CONFIG);
        $execute = $config->data(Config::DATA_PROJECT_DIR_BINARY) . Bin::EXE;
        Dir::create($config->data(Config::DATA_PROJECT_DIR_BINARY), Dir::CHMOD);
        $dir = Dir::name(Bin::DIR) . $config->data(Config::DICTIONARY . '.' . Config::DATA) . $config->data('ds');
        $source = $dir . Bin::EXE;
        if(File::exist($execute)){
            File::delete($execute);
        }
        File::copy($source, $execute);
        $name = $object->data('name');
        $url = Bin::TARGET . $name;
        $content = [];
        $content[] = '#!/bin/sh';
        $content[] = 'php ' . $execute . ' "$@"';
        $content = implode(PHP_EOL, $content);
        File::write($url, $content);
        shell_exec('chmod +x ' . $url);
    }
}