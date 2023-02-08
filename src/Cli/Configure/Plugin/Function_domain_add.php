<?php

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;
use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

/**
 * @throws ObjectException
 * @throws FileWriteException
 * @throws Exception
 */
function function_domain_add(Parse $parse, Data $data, $domain=''){
    $id = posix_geteuid();
    if(
        !in_array(
            $id,
            [
                0,
                33
            ]
        )
    ){
        throw new Exception('Only root & www-data can configure domain add...');
    }
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
        Dir::create($host_dir_view . 'Index');
        Dir::create($host_dir_view . 'Index/Public/Css');
        Dir::create($host_dir_view . 'Index/Public/Image');
        Dir::create($host_dir_view . 'Main');

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
            return $exception;
        }
        $url = $object->config('controller.dir.data') . 'Controller/Index.tpl';
        $controller_read = File::read($url);
        $controller_data = new Data();
        $controller_data->data('module', 'Index');
        $controller_data->data('domain', ucfirst($domain));
        $controller_data->data('extension', ucfirst($extension));
        $controller_data->data('controller', $data->get('controller'));
        $controller_parse = new Parse($object);
        $write = $controller_parse->compile($controller_read, $controller_data->data());
        try {
            $url = $host_dir_controller . 'Index' . $object->config('extension.php');
            if(!File::exist($url)){
                File::write($url, $write);
            }
        } catch (Exception | FileWriteException $exception){
            return $exception;
        }
        $source = $object->config('controller.dir.data') . 'View/Index/Overview.tpl';
        $destination = $host_dir_view . 'Index/Overview.tpl';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $source = $object->config('controller.dir.data') . 'View/Main/Main.json';
        $destination = $host_dir_data . 'Main.json';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $source = $object->config('controller.dir.data') . 'View/Main/Main.tpl';
        $destination = $host_dir_view . 'Main/Main.tpl';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Css/Main.css';
        $destination = $host_dir_view . 'Index/Public/Css/Main.css';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Image/Details-close.png';
        $destination = $host_dir_view . 'Index/Public/Image/Details-close.png';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Image/Details-open.png';
        $destination = $host_dir_view . 'Index/Public/Image/Details-open.png';
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
        $resource = '{{$project.dir.host}}' .
            ucfirst($domain) .
            $object->config('ds') .
            ucfirst($extension) .
            $object->config('ds') .
            $object->config('dictionary.data') .
            $object->config('ds') .
            'Route' .
            $object->config('extension.json');
        if($route){
            $is_found = false;
            foreach($route->get() as $record){
                if(
                    property_exists($record, 'resource') &&
                    stristr($record->resource, $resource) !== false
                ){
                    $is_found = true;
                }
            }
            if(!$is_found) {
                $route->data(Core::uuid() . '.resource', $resource);
                try {
                    File::write($url, Core::object($route->data(), Core::OBJECT_JSON));
                } catch (Exception|FileWriteException|ObjectException $exception) {
                    return $exception;
                }
            }
            $id = posix_geteuid();
            if ($id === 0) {
                File::chmod($url, 0666);
                Core::execute('chown www-data:www-data -R ' . $object->config('project.dir.host'));
                Core::execute('chmod 777 -R ' . $object->config('project.dir.host'));
                Core::execute('chown www-data:www-data -R ' . $project_dir_data);
                if (File::exist($project_dir_data . 'Cache/0/')) {
                    Core::execute('chown root:root -R ' . $project_dir_data . 'Cache/0/');
                }
                if (File::exist($project_dir_data . 'Compile/0/')) {
                    Core::execute('chown root:root -R ' . $project_dir_data . 'Compile/0/');
                }
                if (File::exist($project_dir_data . 'Cache/1000/')) {
                    Core::execute('chown 1000:1000 -R ' . $project_dir_data . 'Cache/1000/');
                }
                if (File::exist($project_dir_data . 'Compile/1000/')) {
                    Core::execute('chown 1000:1000 -R ' . $project_dir_data . 'Compile/1000/');
                }
            }
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
        Dir::create($host_dir_view . 'Index');
        Dir::create($host_dir_view . 'Index/Public/Css');
        Dir::create($host_dir_view . 'Index/Public/Image');
        Dir::create($host_dir_view . 'Main');

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
            return $exception;
        }
        $url = $object->config('controller.dir.data') . 'Controller/Index.tpl';
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
            return $exception;
        }
        $source = $object->config('controller.dir.data') . 'View/Main/Main.json';
        $destination = $host_dir_data . 'Main.json';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $source = $object->config('controller.dir.data') . 'View/Main/Main.tpl';
        $destination = $host_dir_view . 'Main/Main.tpl';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $source = $object->config('controller.dir.data') . 'View/Index/Overview.tpl';
        $destination = $host_dir_view . 'Index/Overview.tpl';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Css/Main.css';
        $destination = $host_dir_view . 'Index/Public/Css/Main.css';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Image/Details-close.png';
        $destination = $host_dir_view . 'Index/Public/Image/Details-close.png';
        if(!File::exist($destination)){
            File::copy($source, $destination);
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Image/Details-open.png';
        $destination = $host_dir_view . 'Index/Public/Image/Details-open.png';
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
        if($route){
            $resource = '{{$project.dir.host}}' .
                ucfirst($domain) .
                $object->config('ds') .
                ucfirst($extension) .
                $object->config('ds') .
                $object->config('dictionary.data') .
                $object->config('ds') .
                'Route' .
                $object->config('extension.json');
            $is_found = false;
            foreach($route->get() as $record) {
                if (
                    property_exists($record, 'resource') &&
                    stristr($record->resource, $resource) !== false
                ) {
                    $is_found = true;
                    break;
                }
            }
            if(!$is_found){
                $route->data(Core::uuid() . '.resource', $resource);
                try {
                    File::write($url, Core::object($route->data(), Core::OBJECT_JSON));
                } catch (Exception|FileWriteException|ObjectException $exception) {
                    return $exception;
                }
            }
            $id = posix_geteuid();
            if($id === 0){
                File::chmod($url, 0666);
                Core::execute('chown www-data:www-data -R ' . $object->config('project.dir.host'));
                Core::execute('chmod 777 -R ' . $object->config('project.dir.host'));
                Core::execute('chown www-data:www-data -R ' . $project_dir_data);
                if(File::exist($project_dir_data . 'Cache/0/')){
                    Core::execute('chown root:root -R ' . $project_dir_data . 'Cache/0/');
                }
                if(File::exist($project_dir_data . 'Compile/0/')){
                    Core::execute('chown root:root -R ' . $project_dir_data . 'Compile/0/');
                }
                if(File::exist($project_dir_data . 'Cache/1000/')){
                    Core::execute('chown 1000:1000 -R ' . $project_dir_data . 'Cache/1000/');
                }
                if(File::exist($project_dir_data . 'Compile/1000/')){
                    Core::execute('chown 1000:1000 -R ' . $project_dir_data . 'Compile/1000/');
                }
            }
        }
    }
}

