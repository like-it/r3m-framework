<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-13
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_terminal_color(Parse $parse, Data $data, $color, $background=null){
    $result = '';
    $reset = false;
    if(
        !empty($color) &&
        (
            is_array($color) ||
            is_object($color)
        )
    ){
        if(
            is_array($color) &&
            array_key_exists('r', $color) &&
            array_key_exists('g', $color) &&
            array_key_exists('b', $color)
        ){
            return chr(27) . '[38;2;' . $color['r'] . ';' . $color['g'] . ';' . $color['b'] . 'm'; //rgb foreground color
        } elseif(
            is_object($color) &&
            property_exists($color, 'r') &&
            property_exists($color, 'g') &&
            property_exists($color, 'b')
        ) {
            return chr(27) . '[38;2;' . $color->r . ';' . $color->g . ';' . $color->b . 'm'; //rgb foreground color
        }
    } else {
        switch($color){
            case 'white' :
                $color = 15; //15
                break;
            case 'black' :
                $color = 0; //0
                break;
            case 'green' :
                $color = 2; //2
                break;
            case 'red' :
                $color = 1; //1
                break;
            case 'yellow' :
                $color = 3; //3
                break;
            case 'lightgrey' :
            case 'light-grey' :
                $color = 7; //7
                break;
            case 'grey' :
                $color = 8; //7
                break;
            case 'blue' :
                $color = 4; //4
                break;
            case 'green-blue' :
            case 'greenblue' :
                $color = 6; //6
                break;
            case 'light-green-blue' :
            case 'lightgreenblue' :
                $color = 14; //14
                break;
            case 'light-blue' :
            case 'lightblue' :
                $color = 12; //12
                break;
            case 'light-green' :
            case 'lightgreen' :
                $color = 10; //10
                break;
            case 'light-red' :
            case 'lightred' :
                $color = 9; //9
                break;
            case 'light-yellow' :
            case 'lightyellow' :
                $color = 11; //11
                break;
            case 'purple' :
                $color = 5;  //5
                break;
            case 'light-purple' :
            case 'lightpurple' :
                $color = 13;  //5
                break;
            case 'reset' :
                $reset = true;
                break;
        }
        $argument = [];
        if($reset === true){
            $command = 'reset';
        } else {
            $command = 'color';
            $argument[] = $color;
        }
        $result .= \R3m\Io\Module\Cli::tput($command, $argument);
        $reset = false;
    }

    if($background !== null){
        if(
            !empty($background) &&
            (
                is_array($background) ||
                is_object($background)
            )
        ){
            if(
                is_array($color) &&
                array_key_exists('r', $color) &&
                array_key_exists('g', $color) &&
                array_key_exists('b', $color)
            ){
                return chr(27) . '[48;2;' . $color['r'] . ';' . $color['g'] . ';' . $color['b'] . 'm'; //rgb background color
            } elseif(
                is_object($color) &&
                property_exists($color, 'r') &&
                property_exists($color, 'g') &&
                property_exists($color, 'b')
            ) {
                return chr(27) . '[48;2;' . $color->r . ';' . $color->g . ';' . $color->b . 'm'; //rgb background color
            }
        } else {
            switch($background){
                case 'white' :
                    $color = 15; //15
                    break;
                case 'black' :
                    $color = 0; //0
                    break;
                case 'green' :
                    $color = 2; //2
                    break;
                case 'red' :
                    $color = 1; //1
                    break;
                case 'yellow' :
                    $color = 3; //3
                    break;
                case 'lightgrey' :
                case 'light-grey' :
                    $color = 7; //7
                    break;
                case 'grey' :
                    $color = 8; //7
                    break;
                case 'blue' :
                    $color = 4; //4
                    break;
                case 'green-blue' :
                case 'greenblue' :
                    $color = 6; //6
                    break;
                case 'light-green-blue' :
                case 'lightgreenblue' :
                    $color = 14; //14
                    break;
                case 'light-blue' :
                case 'lightblue' :
                    $color = 12; //12
                    break;
                case 'light-green' :
                case 'lightgreen' :
                    $color = 10; //10
                    break;
                case 'light-red' :
                case 'lightred' :
                    $color = 9; //9
                    break;
                case 'light-yellow' :
                case 'lightyellow' :
                    $color = 11; //11
                    break;
                case 'purple' :
                    $color = 5;  //5
                    break;
                case 'light-purple' :
                case 'lightpurple' :
                    $color = 13;  //5
                    break;
                case 'reset' :
                    $reset = true;
                    break;
            }
            $argument = [];
            if($reset === true){
                $command = 'reset';
            } else {
                $command = 'background';
                $argument[] = $color;
            }
            $result .= \R3m\Io\Module\Cli::tput($command, $argument);
        }
    }
    return $result;
}
