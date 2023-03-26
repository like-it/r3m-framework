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

use R3m\Io\Module\Cli;
use R3m\Io\Module\File;

use Throwable;

use Exception;

class ParseException extends Exception {

    protected $object;
    protected $options;

    public function __construct($message = "", $options=[], $code = 0, Throwable $previous = null) {
        if(!empty($options)){
            $this->setOptions($options);
        }
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

    public function getOptions(){
        return $this->options;
    }

    public function setOptions($options=''){
        $this->options = $options;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public function __toString()
    {
        $options = $this->getOptions();
        $result = [];
        $explode = explode('on line', $this->getMessage());
        if(array_key_exists(1, $explode)) {
            $tmp = explode(PHP_EOL, $explode[1]);
            $line_nr = (int) trim($tmp[0]);
            if (!empty($options['url'])) {
                $read = File::read($options['url']);
            }
            if ($read) {
                $explode = explode(PHP_EOL, $read);
                for ($i = $line_nr - 5; $i <= $line_nr + 5; $i++) {
                    if (array_key_exists($i, $explode)) {
                        if($i === $line_nr - 1){
                            if(!App::is_cli()){
                                $explode[$i] = '<span style="color: rgba(200, 0, 0, 1);">' . $explode[$i] . '</span>';
                            } else {
                                $explode[$i] = Cli::color(['r'=> 200, 'g' => 0, 'b' => 0]) . $explode[$i] . Cli::tput('init');
                            }
                        }
                        $result[] = $explode[$i];
                    }
                }
            }
        }
        $source = '';
        if(!empty($options['source'])){
            $source = File::read($options['source']);
        }
        $string = parent::__toString();
        $string .= PHP_EOL .
            PHP_EOL
        ;
        if(App::is_cli()){
            $title = 'Code: ';
            $width = Cli::width();
            $title_length = strlen($title);
            $width = $width - $title_length;
            $title .= str_repeat(' ', $width);
            $string .= Cli::color(null, ['r'=> 200, 'g' => 0, 'b' => 0]) . $title . Cli::tput('init') . PHP_EOL;
        } else {
            $string .= 'Code: ' . PHP_EOL;
        }
        $string .= implode(PHP_EOL, $result);
        if($source){
            $string .= PHP_EOL .
                PHP_EOL
            ;
            if(App::is_cli()){
                $title = 'Source: ';
                $width = Cli::width();
                $title_length = strlen($title);
                $width = $width - $title_length;
                $title .= str_repeat(' ', $width);
                $string .= Cli::color(null, ['r'=> 200, 'g' => 0, 'b' => 0]) . $title . Cli::tput('init') . PHP_EOL;
            } else {
                $string .= 'Source: ' . PHP_EOL;
            }
            $string .= $source;
        }
        if(App::is_cli()){
            $output = [];
            $output[] = $string;
            $output[] = '';
        } else {
            $output = [];
            $output[] = '<pre>';
            $output[] = $string;
            $output[] = '</pre>';
        }
        return implode(PHP_EOL, $output);
    }

}
