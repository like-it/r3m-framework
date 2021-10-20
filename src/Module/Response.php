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

use R3m\Io\App;
use stdClass;
use Exception;

class Response {
    const TYPE_JSON = 'json';
    const TYPE_HTML = 'html';
    const TYPE_OBJECT = 'object';
    const TYPE_FILE = 'file';

    private $data;
    private $type;
    private $status;
    private $header;

    public function __construct($data='', $type='', $status=200, $headers=[]){
        $this->data($data);
        $this->type($type);
        $this->status($status);
        $this->header($headers);
    }

    public static function output(App $object, Response $response){
        $status = $response->status();
        if(!Handler::header('has', 'Status')){
            Handler::header('Status: ' . $status, $status, true);
        }
        $type = $response->type();
        if($type === null &&  $object->data(App::CONTENT_TYPE) === App::CONTENT_TYPE_JSON){
            $type = Response::TYPE_OBJECT;
        }
        elseif($type === null){
            $type = Response::TYPE_FILE;
        }
        if(!Handler::header('has', 'Content-Type')){
            switch($type){
                case Response::TYPE_OBJECT :
                case Response::TYPE_JSON :
                    Handler::header('Content-Type: application/json', null, true);
                break;
                case Response::TYPE_HTML :
                    Handler::header('Content-Type: text/html', null, true);
                break;
                case Response::TYPE_FILE :
                break;
                default:
                    $type = Response::TYPE_HTML;
                    Handler::header('Content-Type: text/html', null, true);
                break;
            }
        }
        $header = $response->header();
        if(is_array($header)){
            foreach($header as $value){
                Handler::header($value,null, true);
            }
        }
        switch($type){
            case Response::TYPE_JSON :
                if(is_string($response->data())){
                    return trim($response->data(), " \t\r\n");
                } else {
                    try {
                        return Core::object($response->data(), Core::OBJECT_JSON);
                    }
                    catch (Exception $exception){
                        return $exception;
                    }
                }
            case Response::TYPE_OBJECT :
                $json = new stdClass();
                $json->html = $response->data();
                if($object->data('method')){
                    $json->method = $object->data('method');
                } else {
                    $json->method = $object->request('method');
                }
                if($object->data('target')){
                    $json->target = $object->data('target');
                } else {
                    $json->target = $object->request('target');
                }
                $append_to = $object->data('append-to');
                if(empty($append_to)){
                    $append_to = $object->data('append.to');
                }
                if(empty($append_to)){
                    $append_to = $object->request('append-to');
                }
                if(empty($append_to)){
                    $append_to = $object->request('append.to');
                }
                if($append_to){
                    if(empty($json->append)){
                        $json->append = new stdClass();
                    }
                    $json->append->to = $append_to;
                }
                $json->script = $object->data(App::SCRIPT);
                $json->link = $object->data(App::LINK);
                return Core::object($json, Core::OBJECT_JSON);
            case Response::TYPE_FILE :
            case Response::TYPE_HTML :
                return $response->data();
        }
    }

    public function data($data=null){
        if($data !== null){
            $this->setData($data);
        }
        return $this->getData();
    }

    private function setData($data=null){
        $this->data = $data;
    }

    private function getData(){
        return $this->data;
    }

    public function type($type=null){
        if($type !== null){
            $this->setType($type);
        }
        return $this->getType();
    }

    private function setType($type=null){
        $this->type = $type;
    }

    private function getType(){
        return $this->type;
    }

    public function status($status=null){
        if($status !== null){
            $this->setStatus($status);
        }
        return $this->getStatus();
    }

    private function setStatus($status=null){
        $this->status = $status;
    }

    private function getStatus(){
        return $this->status;
    }

    public function header($header=null){
        if($header !== null){
            $this->setHeader($header);
        }
        return $this->getHeader();
    }

    private function setHeader($header=null){
        $this->header = $header;
    }

    private function getHeader(){
        return $this->header;
    }
}