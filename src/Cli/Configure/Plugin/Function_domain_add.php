<?php

use R3m\Io\Config;

use R3m\Io\Module\Core;
use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
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
    $object = $parse->object();
    $id = $object->config(Config::POSIX_ID);
    if(
        !in_array(
            $id,
            [
                0,
                33
            ],
            true
        )
    ){
        $exception = new Exception('Only root & www-data can configure domain add...');
        Event::trigger($object, 'cli.configure.domain.add', [
            'domain' => $domain,
            'exception' => $exception
        ]);
        throw $exception;
    }
    $domain_add = strtolower($domain);
    $explode = explode('.', $domain_add);
    $domain = false;
    $subdomain = false;
    $extension = false;
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
            $exception = new Exception('Invalid domain');
            Event::trigger($object, 'cli.configure.domain.add', [
                'subdomain' => $subdomain,
                'domain' => $domain,
                'extension' => $extension,
                'input_domain' => $domain_add,
                'exception' => $exception
            ]);
            throw $exception;
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
        Dir::create($host_dir_data, Dir::CHMOD);
        Dir::create($host_dir_controller, Dir::CHMOD);
        Dir::create($host_dir_view, Dir::CHMOD);
        Dir::create($host_dir_view . 'Index', Dir::CHMOD);
        Dir::create($host_dir_view . 'Index/Public/Css', Dir::CHMOD);
        Dir::create($host_dir_view . 'Index/Public/Image', Dir::CHMOD);
        Dir::create($host_dir_view . 'Main', Dir::CHMOD);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 777 ' . $host_dir_data);
            exec('chmod 777 ' . $host_dir_controller);
            exec('chmod 777 ' . $host_dir_view);
            exec('chmod 777 ' . $host_dir_view . 'Index');
            exec('chmod 777 ' . $host_dir_view . 'Index/Public');
            exec('chmod 777 ' . $host_dir_view . 'Index/Public/Css');
            exec('chmod 777 ' . $host_dir_view . 'Index/Public/Image');
            exec('chmod 777 ' . $host_dir_view . 'Main');
        }
        $dir = $object->config('project.dir.host') .
            ucfirst($domain) .
            $object->config('ds');
        $cwd = Dir::change($dir);
        $exec = 'ln -s ' . ucfirst($extension) . ' Local';
        $output = [];
        Core::execute($object, $exec, $output);
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
                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                    exec('chmod 666 ' . $url);
                } else {
                    exec('chmod 640 ' . $url);
                }
            }
        } catch (Exception | FileWriteException | ObjectException $exception){
            Event::trigger($object, 'cli.configure.domain.add', [
                'subdomain' => $subdomain,
                'domain' => $domain,
                'extension' => $extension,
                'input_domain' => $domain_add,
                'exception' => $exception
            ]);
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
                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                    exec('chmod 666 ' . $url);
                } else {
                    exec('chmod 640 ' . $url);
                }
            }
        } catch (Exception | FileWriteException $exception){
            Event::trigger($object, 'cli.configure.domain.add', [
                'subdomain' => $subdomain,
                'domain' => $domain,
                'extension' => $extension,
                'input_domain' => $domain_add,
                'exception' => $exception
            ]);
            return $exception;
        }
        $source = $object->config('controller.dir.data') . 'View/Index/Overview.tpl';
        $destination = $host_dir_view . 'Index/Overview.tpl';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $source = $object->config('controller.dir.data') . 'View/Main/Main.json';
        $destination = $host_dir_data . 'Main.json';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $source = $object->config('controller.dir.data') . 'View/Main/Main.tpl';
        $destination = $host_dir_view . 'Main/Main.tpl';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Css/Main.css';
        $destination = $host_dir_view . 'Index/Public/Css/Main.css';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Image/Details-close.png';
        $destination = $host_dir_view . 'Index/Public/Image/Details-close.png';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Image/Details-open.png';
        $destination = $host_dir_view . 'Index/Public/Image/Details-open.png';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $project_dir_data = $object->config('project.dir.data');
        if(!File::exist($project_dir_data)){
            Dir::create($project_dir_data, Dir::CHMOD);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 777 ' . $project_dir_data);
            }
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
                    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                        exec('chmod 666 ' . $url);
                    } else {
                        exec('chmod 640 ' . $url);
                    }
                } catch (Exception|FileWriteException|ObjectException $exception) {
                    Event::trigger($object, 'cli.configure.domain.add', [
                        'subdomain' => $subdomain,
                        'domain' => $domain,
                        'extension' => $extension,
                        'exception' => $exception
                    ]);
                    return $exception;
                }
            }
            if ($id === 0) {
                Core::execute($object, 'chown www-data:www-data -R ' . $object->config('project.dir.host'));
                Core::execute($object, 'chown www-data:www-data -R ' . $project_dir_data);
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

        Dir::create($host_dir_data, Dir::CHMOD);
        Dir::create($host_dir_controller, Dir::CHMOD);
        Dir::create($host_dir_view, Dir::CHMOD);
        Dir::create($host_dir_view . 'Index', Dir::CHMOD);
        Dir::create($host_dir_view . 'Index/Public/Css', Dir::CHMOD);
        Dir::create($host_dir_view . 'Index/Public/Image', Dir::CHMOD);
        Dir::create($host_dir_view . 'Main', Dir::CHMOD);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 777 ' . $host_dir_data);
            exec('chmod 777 ' . $host_dir_controller);
            exec('chmod 777 ' . $host_dir_view);
            exec('chmod 777 ' . $host_dir_view . 'Index');
            exec('chmod 777 ' . $host_dir_view . 'Index/Public/');
            exec('chmod 777 ' . $host_dir_view . 'Index/Public/Css');
            exec('chmod 777 ' . $host_dir_view . 'Index/Public/Image');
            exec('chmod 777 ' . $host_dir_view . 'Main');
        }
        $dir = $object->config('project.dir.host') .
            ucfirst($subdomain) .
            $object->config('ds') .
            ucfirst($domain) .
            $object->config('ds');
        $cwd = Dir::change($dir);
        $exec = 'ln -s ' . ucfirst($extension) . ' Local';
        $output = [];
        Core::execute($object, $exec, $output);
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
                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                    exec('chmod 666 ' . $url);
                } else {
                    exec('chmod 640 ' . $url);
                }
            }
        } catch (Exception | FileWriteException | ObjectException $exception){
            Event::trigger($object, 'cli.configure.domain.add', [
                'subdomain' => $subdomain,
                'domain' => $domain,
                'extension' => $extension,
                'exception' => $exception
            ]);
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
                if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                    exec('chmod 666 ' . $url);
                } else {
                    exec('chmod 640 ' . $url);
                }
            }
        } catch (Exception | FileWriteException $exception){
            Event::trigger($object, 'cli.configure.domain.add', [
                'subdomain' => $subdomain,
                'domain' => $domain,
                'extension' => $extension,
                'exception' => $exception
            ]);
            return $exception;
        }
        $source = $object->config('controller.dir.data') . 'View/Main/Main.json';
        $destination = $host_dir_data . 'Main.json';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $source = $object->config('controller.dir.data') . 'View/Main/Main.tpl';
        $destination = $host_dir_view . 'Main/Main.tpl';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $source = $object->config('controller.dir.data') . 'View/Index/Overview.tpl';
        $destination = $host_dir_view . 'Index/Overview.tpl';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Css/Main.css';
        $destination = $host_dir_view . 'Index/Public/Css/Main.css';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Image/Details-close.png';
        $destination = $host_dir_view . 'Index/Public/Image/Details-close.png';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $source = $object->config('controller.dir.data') . 'View/Public/Image/Details-open.png';
        $destination = $host_dir_view . 'Index/Public/Image/Details-open.png';
        if(!File::exist($destination)){
            File::copy($source, $destination);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 666 ' . $destination);
            } else {
                exec('chmod 640 ' . $destination);
            }
        }
        $project_dir_data = $object->config('project.dir.data');
        if(!File::exist($project_dir_data)){
            Dir::create($project_dir_data, Dir::CHMOD);
            if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                exec('chmod 777 ' . $project_dir_data);
            }
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
                    if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
                        exec('chmod 666 ' . $url);
                    } else {
                        exec('chmod 640 ' . $url);
                    }
                } catch (Exception|FileWriteException|ObjectException $exception) {
                    Event::trigger($object, 'cli.configure.domain.add', [
                        'subdomain' => $subdomain,
                        'domain' => $domain,
                        'extension' => $extension,
                        'exception' => $exception
                    ]);
                    return $exception;
                }
            }
            if(empty($id)){
                Core::execute($object, 'chown www-data:www-data -R ' . $object->config('project.dir.host'));
                Core::execute($object, 'chown www-data:www-data -R ' . $project_dir_data);
            }
        }
    }
    $dir = $object->config('project.dir.data');
    $url = $dir .
        'Hosts' .
        $object->config('extension.json')
    ;
    $read = $object->data_read($url);
    if(!$read){
        $read = new Data();
        Dir::create($dir, Dir::CHMOD);
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 777 ' . $dir);
        }
        if(empty($id)){
            Core::execute($object, 'chown www-data:www-data ' . $dir);
        }
    }
    if($read){
        if($subdomain){
            $read->set('host.' . $subdomain . '-' . $domain . '-' . $extension . '.subdomain', $subdomain);
            $read->set('host.' . $subdomain . '-' . $domain . '-' . $extension . '.domain', $domain);
            $read->set('host.' . $subdomain . '-' . $domain . '-' . $extension . '.extension', $extension);
        } else {
            $read->set('host.' . $domain . '-' . $extension . '.subdomain', false);
            $read->set('host.' . $domain . '-' . $extension . '.domain', $domain);
            $read->set('host.' . $domain . '-' . $extension . '.extension', $extension);
        }
        $read->write($url);
        if(empty($id)){
            Core::execute($object, 'chown www-data:www-data ' . $url);
        }
        if($object->config('framework.environment') === Config::MODE_DEVELOPMENT){
            exec('chmod 666 ' . $url);
        } else {
            exec('chmod 640 ' . $url);
        }
    }
    Event::trigger($object, 'cli.configure.domain.add', [
        'subdomain' => $subdomain,
        'domain' => $domain,
        'extension' => $extension
    ]);
}

