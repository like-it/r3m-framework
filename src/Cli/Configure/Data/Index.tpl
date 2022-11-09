<?php
{if(!is.empty($subdomain))}namespace Host\{$subdomain}\{$domain}\{$extension}\Controller;{else}namespace Host\{$domain}\{$extension}\Controller;{/if}{literal}

use R3m\Io\App;
use R3m\Io\Module\View;

use Exception;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\UrlEmptyException;
use R3m\Io\Exception\UrlNotExistException;

class Index extends View {
    const DIR = __DIR__ . DIRECTORY_SEPARATOR;    

    public static function overview(App $object){
        $name = Index::name(__FUNCTION__);
        try {
            $url = Index::locate($object, $name);
            return Index::response($object, $url);
        } catch (Exception | LocateException | UrlEmptyException | UrlNotExistException $exception){
            return $exception;
        }
    }
}
{/literal}