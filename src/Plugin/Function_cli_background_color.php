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

use R3m\Io\Module\Cli;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;

function function_cli_background_color(Parse $parse, Data $data, $attribute=null){
    switch(strtolower($attribute)) {
        case 'black' :
            echo Cli::tput('background', Cli::COLOR_BLACK);
            break;
        case 'red' :
            echo Cli::tput('background', Cli::COLOR_RED);
            break;
        case 'green' :
            echo Cli::tput('background', Cli::COLOR_GREEN);
            break;
        case 'yellow' :
            echo Cli::tput('background', Cli::COLOR_YELLOW);
            break;
        case 'blue' :
            echo Cli::tput('background', Cli::COLOR_BLUE);
            break;
        case 'purple' :
            echo Cli::tput('background', Cli::COLOR_PURPLE);
            break;
        case 'lightblue' :
        case 'light-blue' :
            echo Cli::tput('background', Cli::COLOR_LIGHTBLUE);
            break;
        case 'lightgrey' :
        case 'light-grey' :
            echo Cli::tput('background', Cli::COLOR_LIGHTGREY);
            break;
        case 'set' :
            echo Cli::tput('background', Cli::COLOR_SET);
            break;
    }
}
