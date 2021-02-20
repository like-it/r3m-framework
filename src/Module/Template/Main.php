<?php
/**
 * @author          Remco van der Velde
 * @author          R3m\Io\Module\Parse\Build
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 * @note            Auto generated file, do not modify!
 */
namespace R3m\Io\Module\Template;

use stdClass;
use Exception;
use R3m\Io\Module\Data;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Parse\Token;

class Main {
	private $parse;
	private $storage;

	public function __construct(Parse $parse, Data $storage){
	    set_time_limit(600);
		$this->parse($parse);
		$this->storage($storage);
	}

	public function parse($parse=null){
	    if($parse !== null){
	        $this->setParse($parse);
	    }
	    return $this->getParse();
	}

	private function setParse($parse=null){
	    $this->parse = $parse;
	}

	private function getParse(){
	    return $this->parse;
	}

	public function storage($storage=null){
	    if($storage !== null){
	        $this->setStorage($storage);
	    }
	    return $this->getStorage();
	}

	private function setStorage($storage=null){
	    $this->storage = $storage;
	}

	private function getStorage(){
	    return $this->storage;
	}

	protected function assign_min_equal($variable1=null, $variable2=null){
	    $variable1 += 0;
	    $variable2 += 0;
	    return $variable1 - $variable2;
	}

	protected function assign_plus_equal($variable1=null, $variable2=null){
       $variable1 += 0;
       $variable2 += 0;
       return $variable1 + $variable2;
	}

	protected function assign_dot_equal($variable1=null, $variable2=null){
        $variable1 = (string) $variable1;
        $variable2 = (string) $variable2;
        return $variable1 . $variable2;
	}

	protected function assign_plus_plus($variable=0){
	    $variable += 0;
	    $variable++;
	    return $variable;
	}

	protected function assign_min_min($variable=0){
	    $variable += 0;
	    $variable--;
	    return $variable;
	}

	protected function value_plus_plus($variable=0){
	    $variable += 0;
	    $variable++;
	    return $variable;
	}

	protected function value_min_min($variable=0){
	    $variable += 0;
	    $variable--;
	    return $variable;
	}

	protected function plus_plus_assign($variable=0){
	    $variable += 0;
	    ++$variable;
	    return $variable;
	}

	protected function min_min_assign($variable=0){
	    $variable += 0;
	    --$variable;
	    return $variable;
	}

	protected function plus_plus_value($variable=0){
	    $variable += 0;
	    ++$variable;
	    return $variable;
	}

	protected function min_min_value($variable=0){
	    $variable += 0;
	    --$variable;
	    return $variable;
	}

	protected function value_plus($variable1=null, $variable2=null){
        $type1 = getType($variable1);
        $type2 = getType($variable2);
        if(
            $type1 == Token::TYPE_STRING ||
            $type2 == Token::TYPE_STRING
        ){
            return (string) $variable1 . (string) $variable2;
        } else {
            $variable1 += 0;
            $variable2 += 0;
            return $variable1 + $variable2;
        }
	}

	protected function value_minus($variable1=null, $variable2=null){		
		$variable1 += 0;
		$variable2 += 0;
		return $variable1 - $variable2;        
	}

	protected function value_multiply($variable1=null, $variable2=null){
        $variable1 += 0;
        $variable2 += 0;
        return $variable1 * $variable2;
	}

	protected function value_divide($variable1=null, $variable2=null){
	    $variable1 += 0;
	    $variable2 += 0;
	    if($variable2 != 0){
	        return $variable1 / $variable2;
	    } else {
	        return INF;
	    }
	}

	protected function value_modulo($variable1=null, $variable2=null){
	    return $variable1 % $variable2;
	}

	protected function value_smaller($variable1=null, $variable2=null){
	    return $variable1 < $variable2;
	}

	protected function value_smaller_equal($variable1=null, $variable2=null){
	    return $variable1 <= $variable2;
	}

	protected function value_smaller_smaller($variable1=null, $variable2=null){
	    return $variable1 << $variable2;
	}

	protected function value_greater($variable1=null, $variable2=null){
	    return $variable1 > $variable2;
	}

	protected function value_greater_equal($variable1=null, $variable2=null){
	    return $variable1 >= $variable2;
	}

	protected function value_greater_greater($variable1=null, $variable2=null){
	    return $variable1 >> $variable2;
	}

	protected function value_not_equal($variable1=null, $variable2=null){
	    return $variable1 != $variable2;
	}

	protected function value_not_identical($variable1=null, $variable2=null){
	    return $variable1 !== $variable2;
	}

	protected function value_equal($variable1=null, $variable2=null){
	    return $variable1 == $variable2;
	}

	protected function value_identical($variable1=null, $variable2=null){
	    return $variable1 === $variable2;
	}

	protected function cache_write($url){
	    if(opcache_is_script_cached($url) === false){
	        opcache_compile_file($url);
	    }
	}

	protected function cache_invalidate($url){
	    if(opcache_is_script_cached($url) === true){
	        opcache_invalidate($url, true);
	    }
	}
}
