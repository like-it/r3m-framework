<?php

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

function function_domain_add(Parse $parse, Data $data, $domain=''){
    $object = $parse->object();
    $domain = strtolower($domain);
    $explode = explode('.', $domain);
    switch(count($explode)){
        case 3:
            $subdomain = $explode[0];
            $domain = $explode[1];
            $extension = $explode[2];
            break;
        case 2:
            $subdomain = '';
            $domain = $explode[0];
            $extension = $explode[1];
            break;
        default:
            throw new Exception('Invalid domain');
    }
    if(empty($subdomain)){
        $host_dir_root = $object->config('project.dir.host') .
            ucfirst($domain) .
            $object->config('ds') .
            ucfirst($extension) .
            $object->config('ds');
        $host_dir_data = $host_dir_root .
            $object->config('dictionary.data') .
            $object->config('ds');
        $host_dir_controller = $host_dir_root .
            $object->config('dictionary.controller') .
            $object->config('ds');
        $host_dir_view = $host_dir_root .
            $object->config('dictionary.view') .
            $object->config('ds');
        Dir::create($host_dir_data);
        Dir::create($host_dir_controller);
        Dir::create($host_dir_view);

        $dir = $object->config('project.dir.host') .
            ucfirst($domain) .
            $object->config('ds');
        $cwd = Dir::change($dir);
        $exec = 'ln -s ' . ucfirst($extension) . ' Local';
        $output = [];
        Core::execute($exec, $output);
        $url = $dir . '.gitignore';
        $write = 'Local/' . PHP_EOL;
        File::write($url, $write);
        Dir::change($cwd);

        $route = new Data();
        $route->data($domain . '-' . $extension . '-index.path', '/');
        $route->data($domain . '-' . $extension . '-index.host', [ $domain . '.' .  $extension]);
        $route->data($domain . '-' . $extension . '-index.controller',
            'Host.' .
            ucfirst($domain) . '.' .
            ucfirst($extension) . '.' .
            'Controller' . '.' .
            'Index' . '.' .
            'overview'
        );
        $route->data($domain . '-' . $extension . '-index.method', [ 'GET' , 'POST']);

        try {
            $url = $host_dir_data . 'Route' . $object->config('extension.json');
            if(!File::exist($url)){
                File::write($url, Core::object($route->data(), Core::OBJECT_JSON));
            }
        } catch (Exception | FileWriteException | ObjectException $exception){
            return $exception->getMessage() . "\n";
        }
        $url = $object->config('controller.dir.data') . 'Index.tpl';
        $controller_read = File::read($url);
        $controller_data = new Data();
        $controller_data->data('domain', ucfirst($domain));
        $controller_data->data('extension', ucfirst($extension));
        $controller_parse = new Parse($object);
        $write = $controller_parse->compile($controller_read, $controller_data->data());

        try {
            $url = $host_dir_controller . 'Index' . $object->config('extension.php');
            if(!File::exist($url)){
                File::write($url, $write);
            }
        } catch (Exception | FileWriteException $exception){
            return $exception->getMessage() . PHP_EOL;
        }
        $source = $object->config('controller.dir.data') . 'Overview.tpl';
        $destination = $host_dir_view . 'Overview.tpl';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $project_dir_data = $object->config('project.dir.data');
        if(!File::exist($project_dir_data)){
            Dir::create($project_dir_data);
        }
        $url = $project_dir_data . 'Route' . $object->config('extension.json');
        if(!File::exist($url)){
            $route = new Data();
        } else {
            $route = $object->data_read($url);
        }
        $route->data(Core::uuid() . '.resource',
            '{$project.dir.host}' .
            ucfirst($domain) .
            $object->config('ds') .
            ucfirst($extension) .
            $object->config('ds') .
            $object->config('dictionary.data') .
            $object->config('ds') .
            'Route' .
            $object->config('extension.json')
        );
        try {
            File::write($url, Core::object($route->data(), Core::OBJECT_JSON));
            File::chmod($url, 0666);
        } catch (Exception | FileWriteException | ObjectException $exception){
            return $exception->getMessage() . PHP_EOL;
        }
    } else {
        $host_dir_root = $object->config('project.dir.host') .
            ucfirst($subdomain) .
            $object->config('ds') .
            ucfirst($domain) .
            $object->config('ds') .
            ucfirst($extension) .
            $object->config('ds');
        $host_dir_data = $host_dir_root .
            $object->config('dictionary.data') .
            $object->config('ds');
        $host_dir_controller = $host_dir_root .
            $object->config('dictionary.controller') .
            $object->config('ds');
        $host_dir_view = $host_dir_root .
            $object->config('dictionary.view') .
            $object->config('ds');
        Dir::create($host_dir_data);
        Dir::create($host_dir_controller);
        Dir::create($host_dir_view);

        $dir = $object->config('project.dir.host') .
            ucfirst($subdomain) .
            $object->config('ds') .
            ucfirst($domain) .
            $object->config('ds');
        $cwd = Dir::change($dir);
        $exec = 'ln -s ' . ucfirst($extension) . ' Local';
        $output = [];
        Core::execute($exec, $output);
        $dir = $object->config('project.dir.host') .
            ucfirst($subdomain) .
            $object->config('ds') .
            ucfirst($domain) .
            $object->config('ds') .
            ucfirst($extension) .
            $object->config('ds');
        Dir::change($dir);
        //$exec = 'rm ' . ucfirst($extension);
        //$output = [];
        //Core::execute($exec, $output);
        Dir::change($cwd);

        $route = new Data();
        $route->data($subdomain . '-' . $domain . '-' . $extension . '-index.path', '/');
        $route->data($subdomain . '-' . $domain . '-' . $extension . '-index.host', [ $subdomain . '.' . $domain . '.' .  $extension]);
        $route->data($subdomain . '-' . $domain . '-' . $extension . '-index.controller',
            'Host.' .
            ucfirst($subdomain) . '.' .
            ucfirst($domain) . '.' .
            ucfirst($extension) . '.' .
            'Controller' . '.' .
            'Index' . '.' .
            'overview'
        );
        $route->data($subdomain . '-' . $domain . '-' . $extension . '-index.method', [ 'GET' , 'POST']);

        try {
            $url = $host_dir_data . 'Route' . $object->config('extension.json');
            if(!File::exist($url)){
                File::write($url, Core::object($route->data(), Core::OBJECT_JSON));
            }
        } catch (Exception | FileWriteException | ObjectException $exception){
            return $exception->getMessage() . PHP_EOL;
        }
        $url = $object->config('controller.dir.data') . 'Index.tpl';
        $controller_read = File::read($url);
        $controller_data = new Data();
        $controller_data->data('subdomain', ucfirst($subdomain));
        $controller_data->data('domain', ucfirst($domain));
        $controller_data->data('extension', ucfirst($extension));
        $controller_parse = new Parse($object);
        $write = $controller_parse->compile($controller_read, $controller_data->data());

        try {
            $url = $host_dir_controller . 'Index' . $object->config('extension.php');
            if(!File::exist($url)){
                File::write($url, $write);
            }
        } catch (Exception | FileWriteException $exception){
            return $exception->getMessage() . PHP_EOL;
        }
        $source = $object->config('controller.dir.data') . 'Overview.tpl';
        $destination = $host_dir_view . 'Overview.tpl';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $project_dir_data = $object->config('project.dir.data');
        if(!File::exist($project_dir_data)){
            Dir::create($project_dir_data);
        }
        $url = $project_dir_data . 'Route' . $object->config('extension.json');
        if(!File::exist($url)){
            $route = new Data();
        } else {
            $route = $object->data_read($url);
        }
        $route->data(Core::uuid() . '.resource',
            '{$project.dir.host}' .
            ucfirst($subdomain) .
            $object->config('ds') .
            ucfirst($domain) .
            $object->config('ds') .
            ucfirst($extension) .
            $object->config('ds') .
            $object->config('dictionary.data') .
            $object->config('ds') .
            'Route' .
            $object->config('extension.json')
        );
        try {
            File::write($url, Core::object($route->data(), Core::OBJECT_JSON));
            File::chmod($url, 0666);
        } catch (Exception | FileWriteException | ObjectException $exception){
            return $exception->getMessage() . "\n";
        }
    }
    Core::execute('chmod 777 -R ' . $host_dir_root);
}

