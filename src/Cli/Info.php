<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace R3m\Io\Cli;

use Exception;

class Info {
    const DIR = __DIR__;
    
    public function run(){
//         $this->cli('create', 'Logo');
        $data = $this->request('data');
        //need host cli info files throught route
        echo Info::view($this);
    }
}