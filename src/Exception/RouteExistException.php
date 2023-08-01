<?php
/**
 * @author          Remco van der Velde
 * @since           10-02-2021
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Exception;

use R3m\Io\App;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Throwable;

use Exception;

class RouteExistException extends Exception {

    const MESSAGE = 'Route resource already exists...';

}
