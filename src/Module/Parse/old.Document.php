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
use R3m\Io\App;
use R3m\Io\Config;
use R3m\Io\Module\File;
use R3m\Io\Module\Dir;

class old.Document {    
         
    public static function write($token=[], Data $storage, $object){
        $config = $object->data(App::NAMESPACE . '.' . Config::NAME);
        
        $target = $config->data('host.dir.data') . 'Parse' . $config->data('ds');
        
        
        $document[] = '<?php';
        $document[] = 'namespace R3m\Io\Module\Parse;';
        $document[] = '';
        $document[] = '';
        $document[] = '/**';
        $document[] = ' * @copyright            (c) 2019 r3m.io';
        $document[] = ' * @note                 Auto generated, do not modify...';
        $document[] = ' */';
        $document[] = '';
        $document[] = 'use R3m\Io\Module\Data;';
        $document[] = 'use R3m\Io\App;';
        $document[] = 'use R3m\Io\Config;';        
        $document[] = '';
        $document[] = 'class Template_' . $object->data('parse.compile.key') . ' {';
        $document[] = '';        
        $document[] = "\t" . 'public function run(Data $storage=null){';
        
        $in_script = null;
        $is_method = null;
        $depth = null;
        $content = '';
        
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_CURLY_OPEN){
                $in_script = $nr;
                continue;
            }
            elseif(
                $in_script === null &&
                $record['type'] == Token::TYPE_WHITESPACE
            ){
                dd('found writable whitespace');
            }
            if($in_script !== null){
                if($record['type'] == Token::TYPE_WHITESPACE){
                    continue;
                }
                elseif($record['type'] == Token::TYPE_METHOD){
                    $content .= 'function_' . str_replace('.', '_', $record['method']['name']);
                    $depth = $record['depth'];
                    continue;
                }                               
                elseif($record['type'] == Token::TYPE_PARENTHESE_CLOSE){
                    $content .= $record['value'];
                    if($record['depth'] == $depth + 1){
                        $document[] = $content;
                        $content = '';
                        $depth = null;
                    }
                    continue;
                } else {
                    $content .= $record['value'];
                    continue;
                }
                d($content);
                dd($record);    
            }
             
                
                
                
                                
            
            
//             d($record);
        }
        if(!empty($method)){
            $document[] = $method;
        }
        $method = '';
        
        $document[] = "\t" . '}';
        $document[] = '';
        $document[] = '}';
        $document[] = '';
        
        Dir::create($target);
        
        $url = $target . $object->data('parse.compile.key') . $config->data('extension.php');
        $data = implode("\n", $document);
        
        
        File::write($url, $data);
        d($url);
        dd('check file');
        
    }
    
}