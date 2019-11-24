<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace R3m\Io\Module\Parse;

use R3m\Io\Module\Data;

class Method {        
    
    public static function create($token=[], Data $storage){ 
        
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_METHOD){
                $record = Method::attribute($record, $storage);
            }
            
        }
        
        
        dd($token);
        
        
        return $token;
    }
    
    private static function attribute($method=[], Data $storage){
        if(array_key_exists('method', $method)){
            $attribute = $method['method']['attribute'];
            
            $tag = Tag::create($attribute);
            dd($tag);
            
            
            
        }
    }
    
}