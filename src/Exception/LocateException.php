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

use Exception;
use Throwable;

class LocateException extends Exception {

    protected $location;

    public function __construct($message = "", $location=[], $code = 0, Throwable $previous = null) {
        $this->setLocation($location);
        parent::__construct($message, $code, $previous);
    }


    public function getLocation(){
        return $this->location;
    }

    public function setLocation($location=[]){
        $this->location = $location;
    }

    public function toArray($default=[]){
        $default['location'] = $this->getLocation();
        return $default;
    }
}
