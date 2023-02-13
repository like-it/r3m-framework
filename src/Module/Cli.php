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

use R3m\Io\Config;
use R3m\Io\Exception\ObjectException;
use stdClass;
use Exception;

class Cli {
    const COLOR_BLACK = 0;
    const COLOR_RED = 1;
    const COLOR_GREEN = 2;
    const COLOR_YELLOW = 3;
    const COLOR_BLUE = 4;
    const COLOR_PURPLE = 5;
    const COLOR_LIGHTBLUE = 6;
    const COLOR_LIGHTGREY = 7;

    /**
     * @throws ObjectException
     */
    public static function read($url='', $text='')
    {
        $is_flush = false;
        if(ob_get_level() > 0){
            $is_flush =true;
        }
        if($is_flush){
            ob_flush();
        }
        if(empty($url)){
            $url = 'input';
        }
        $input = null;
        switch($url){
            case 'input':
                fwrite(STDOUT, $text);
//            echo $text;
                if($is_flush){
                    ob_flush();
                }
                fflush(STDOUT);
                $input = trim(fgets(STDIN));
            break;
            case 'input-hidden':
                fwrite(STDOUT, $text);
                if($is_flush){
                    ob_flush();
                }
                fflush(STDOUT);
                system('stty -echo');
                $input = trim(fgets(STDIN));
                system('stty echo');
            break;
            case 'stream' :
                $input = trim(fgets(STDIN));
                $input = Core::object($input);
                ddd($input);
                $input->{'R3m\Io'}->Config = new Config($input->Config);
                $input->Route = new Route($input->Route);
            break;
        }
        return $input;
    }

    public static function default(){
        echo chr(27) . "[0m";
    }

    public static function tput($tput='', $arguments=[]): string
    {
        if(!is_array($arguments)){
            $arguments = (array) $arguments;
        }
        switch(strtolower($tput)){
            case 'screen.save' :
            case 'screen.write' :
            case ' smcup' :
                $tput = 'smcup';
                break;
            case 'screen.restore' :
            case 'rmcup' :
                $tput = 'rmcup';
                break;
            case 'home' :
            case 'cursor.home':
                $tput = 'home';
                break;
            case 'cursor.invisible' :
            case 'civis' :
                $tput = 'civis';
                break;
            case 'cursor.normal' :
            case 'cnorm' :
                $tput = 'cnorm';
                break;
            case 'cursor.save' :
            case 'cursor.write' :
            case 'sc' :
                $tput = 'sc';
                break;
            case 'cursor.restore' :
            case 'rc' :
                $tput = 'rc';
                break;
            case 'color' :
            case 'setaf' :
                $color = isset($arguments[0]) ? (int) $arguments[0] : 9; //9 = default
                $tput = 'setaf ' . $color;
                break;
            case 'background' :
            case 'setab' :
                $color = isset($arguments[0]) ? (int) $arguments[0] : 0; //0 = default
                $tput = 'setab ' . $color;
                break;
            case 'cursor.up' :
            case 'up' :
            case 'cuu' :
                $amount = isset($arguments[0]) ? (int) $arguments[0] : 1;
                $tput = 'cuu' . $amount;
                break;
            case 'cursor.down' :
            case 'down' :
            case 'cud' :
                $amount = isset($arguments[0]) ? (int) $arguments[0] : 1;
                $tput = 'cud' . $amount;
                break;
            case 'cursor.position' :
            case 'position' :
            case 'cup' :
                $cols = isset($arguments[0]) ? (int) $arguments[0] : 0; //x
                $rows = isset($arguments[1]) ? (int) $arguments[1] : 0; //y
                $tput = 'cup ' . $rows . ' ' . $cols;
                break;
            case 'rows':
            case 'row':
            case 'height':
            case 'lines' :
                $tput = 'lines';
                break;
            case 'width':
            case 'columns':
            case 'column' :
            case 'cols' :
                $tput = 'cols';
                break;
            case 'default':
            case 'reset':
            case 'sgr0':
                $tput  = 'sgr0';
                break;
            case 'init':
                $tput = 'init';
                break;
        }
        ob_start();
        $result = system('tput ' . $tput);
        ob_end_clean();
        return $result;
    }
}