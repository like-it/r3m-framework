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

use Throwable;

use Exception;

class ParseException extends Exception {

    protected $object;
    protected $url;

    public function __construct($message = "", $url='', $code = 0, Throwable $previous = null) {
        $this->setUrl($url);
        parent::__construct($message, $code, $previous);
    }

    public function object($object=null){
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }

    private function setObject(App $object){
        $this->object = $object;
    }

    private function getObject(){
        return $this->object;
    }

    public function getUrl(){
        return $this->url;
    }

    public function setUrl($url=''){
        $this->url = $url;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public function __toString()
    {
        $result = [];
        $explode = explode('on line', $this->getMessage());
        if(array_key_exists(1, $explode)) {
            $line_nr = trim($explode[1]);
            if ($this->getUrl()) {
                $read = File::read($this->getUrl());
            }
            if ($read) {
                $explode = explode(PHP_EOL, $read);
                for ($i = $line_nr - 5; $i <= $line_nr + 5; $i++) {
                    if (array_key_exists($i, $explode)) {
                        $result[] = $explode[$i];
                    }
                }
            }
        }
        $string = parent::__toString();
        $string .= PHP_EOL . 'Code: ' . PHP_EOL;
        $string .= implode(PHP_EOL, $result);

        if(App::is_cli()){
            $output = [];
            $output[] = $string;
        } else {
            $output = [];
            $output[] = '<pre>';
            $output[] = $string;
            $output[] = '</pre>';
        }
        return implode(PHP_EOL, $output);
    }

}
