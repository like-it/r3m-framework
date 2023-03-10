<?php
/**
 * @author          Remco van der Velde
 * @since           19-01-2023
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module\Stream;


use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse\Token;

use Exception;

use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

class Notification {

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public static function is_new(App $object, $action='', $options=[], &$config=false, &$tokens=[]): bool
    {
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
            throw new Exception('Only root & www-data can check is_new...');
        }
        $url = $object->config('project.dir.data') .
            'Stream' .
            $object->config('ds') .
            'Stream' .
            $object->config('extension.json');
        $config = $object->data_read($url);
        $tree = Token::tree('{' . $options['notification'] . '}', [
            'with_whitespace' => true
        ]);
        $notifications = [];
        foreach($tree as $notification){
            $notifications[] = $notification;
        }
        $tokens = $notifications;
        $dir_stream =
            $object->config('project.dir.data') .
            'Stream' .
            $object->config('ds')
        ;
        $dir_require =
            $dir_stream .
            'Document' .
            $object->config('ds')
        ;
        $is_new = true;
        if($config && $config->has('stream.notification')) {
            foreach ($config->data('stream.notification') as $stream) {
                if (
                    property_exists($stream, 'action') &&
                    $stream->action === $action &&
                    property_exists($stream, 'create') &&
                    property_exists($stream->create, 'filter') &&
                    !empty($stream->create->filter) &&
                    is_array($stream->create->filter)
                ) {
                    $filters = $stream->create->filter;
                    if ($filters) {
                        foreach ($filters as $filter) {
                            if (
                                property_exists($filter, 'document') &&
                                !empty($filter->document) &&
                                is_array($filter->document)
                            ) {
                                foreach ($filter->document as $uuid) {
                                    $require = $dir_require . $uuid . '.stream';
                                    if (File::exist($require)) {
                                        $read = File::read($require);
                                        $tree = Token::tree('{' . $read . '}', [
                                            'with_whitespace' => true
                                        ]);
                                        $require_tokens = [];
                                        foreach ($tree as $require_token) {
                                            $require_tokens[] = $require_token;
                                        }
                                        if (
                                            property_exists($filter, 'where') &&
                                            !empty($filter->where) &&
                                            is_array($filter->where)
                                        ) {
                                            foreach ($filter->where as $where) {
                                                if (
                                                    property_exists($where, 'token') &&
                                                    $where->token === 'token' &&
                                                    property_exists($where, 'operator')
                                                ) {
                                                    switch ($where->operator) {
                                                        case '===' :
                                                            foreach ($require_tokens as $nr => $record) {
                                                                $record = Core::object($record, Core::OBJECT_ARRAY);
                                                                $notification = [];
                                                                if (array_key_exists($nr, $notifications)) {
                                                                    $notification = $notifications[$nr];
                                                                }
                                                                $is_match = Token::compare($record, $notification, [
                                                                    'operator' => '==='
                                                                ]);
                                                                if ($is_match) {

                                                                } else {
                                                                    unset($notifications[$nr]);
                                                                    unset($require_tokens[$nr]);
                                                                }
                                                            }
                                                            break;
                                                        case '!==' :
                                                            foreach ($require_tokens as $nr => $record) {
                                                                $record = Core::object($record, Core::OBJECT_ARRAY);
                                                                $notification = [];
                                                                if (array_key_exists($nr, $notifications)) {
                                                                    $notification = $notifications[$nr];
                                                                }
                                                                $is_match = Token::compare($record, $notification, [
                                                                    'operator' => '==='
                                                                ]);
                                                                if ($is_match) {
                                                                    unset($notifications[$nr]);
                                                                    unset($require_tokens[$nr]);
                                                                }
                                                            }
                                                            break;
                                                    }
                                                }
                                                if (
                                                    property_exists($where, 'key') &&
                                                    property_exists($where, $where->key) &&
                                                    property_exists($where, 'operator')
                                                ) {
                                                    $key = $where->key;
                                                    switch ($where->operator) {
                                                        case '===' :
                                                            foreach ($require_tokens as $nr => $record) {
                                                                $record = Core::object($record, Core::OBJECT_ARRAY);
                                                                if ($record[$key] === $where->$key) {
                                                                    unset($notifications[$nr]);
                                                                    unset($require_tokens[$nr]);
                                                                }
                                                            }
                                                            break;
                                                        case '!==' :
                                                            foreach ($require_tokens as $nr => $record) {
                                                                $record = Core::object($record, Core::OBJECT_ARRAY);
                                                                if ($record[$key] !== $where->$key) {
                                                                    unset($notifications[$nr]);
                                                                    unset($require_tokens[$nr]);
                                                                }
                                                            }
                                                            break;
                                                        case 'in.array' :
                                                            foreach ($require_tokens as $nr => $record) {
                                                                $record = Core::object($record, Core::OBJECT_ARRAY);
                                                                if (
                                                                    !in_array(
                                                                        $record[$key],
                                                                        $where->$key,
                                                                        true
                                                                    )
                                                                ) {
                                                                    unset($notifications[$nr]);
                                                                    unset($require_tokens[$nr]);
                                                                }
                                                            }
                                                            break;
                                                        case '!in.array' :
                                                            foreach ($require_tokens as $nr => $record) {
                                                                $record = Core::object($record, Core::OBJECT_ARRAY);
                                                                if (
                                                                    in_array(
                                                                        $record[$key],
                                                                        $where->$key,
                                                                        true
                                                                    )
                                                                ) {
                                                                    unset($notifications[$nr]);
                                                                    unset($require_tokens[$nr]);
                                                                }
                                                            }
                                                            break;
                                                        default:
                                                            throw new Exception('Unknown operator in where filter...');
                                                    }
                                                }
                                            }
                                        }
                                        if (count($notifications) >= 1) {
                                            //is_new = true
                                        } else {
                                            $is_new = false;
                                            break 3;
                                        }
                                    } else {
                                        $documents = $filter->document;
                                        foreach ($documents as $nr => $document) {
                                            if ($document === $uuid) {
                                                unset($documents[$nr]);
                                            }
                                        }
                                        $filter->document = $documents;
                                        $config->write($url);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if(empty($id)){
            $command = 'chown www-data:www-data ' . $dir_stream . ' -R';
            Core::execute($object, $command);
        }
        return $is_new;
    }

    /**
     * @throws Exception
     */
    public static function clean(App $object, $action, $options=[]){
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
            throw new Exception('Only root & www-data can clean notifications...');
        }
        $url = $object->config('project.dir.data') .
            'Stream' .
            $object->config('ds') .
            'Stream' .
            $object->config('extension.json');
        $config = $object->data_read($url);
        $is_stream = false;
        if($config && $config->has('stream.notification')) {
            foreach ($config->data('stream.notification') as $stream) {
                if (
                    property_exists($stream, 'action') &&
                    $stream->action === $action &&
                    property_exists($stream, 'clean') &&
                    property_exists($stream->clean, 'frequency')
                ) {
                    $is_stream = $stream;
                    break;
                }
            }
        }
        if(empty($is_stream)){
            return;
        }
        ddd($is_stream);
    }


    /**
     * @throws FileWriteException
     * @throws ObjectException
     * @throws Exception
     */
    public static function create(App $object, $action='', $config=false, $tokens=[], $notification='') {
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
            throw new Exception('Only root & www-data can create notifications...');
        }
        $url = $object->config('project.dir.data') .
            'Stream' .
            $object->config('ds') .
            'Stream' .
            $object->config('extension.json');
        $uuid = Core::uuid();
        $dir_stream = $object->config('project.dir.data') .
            'Stream' .
            $object->config('ds');
        $dir_stream_document =
            $dir_stream .
            'Document' .
            $object->config('ds');
        Dir::create($dir_stream_document, Dir::CHMOD);
        $write = $notification;
        File::write($dir_stream_document . $uuid . '.stream', $write);
        $number_tokens = Token::filter($tokens, [
            'where' => [
                0 => [
                    'key' => 'type',
                    'type' => [
                        Token::TYPE_INT,
                        Token::TYPE_FLOAT,
                        Token::TYPE_HEX,
                    ],
                    'operator' => 'in.array'
                ]
            ]
        ]);
        $add_filter_not_in_array = false;
        if (!empty($number_tokens)) {
            $add_filter_not_in_array = true;
        }
        $config_notifications = $config->get('stream.notification');
        $is_found = false;
        $is_where = false;
        foreach ($config_notifications as $nr => $config_notification) {
            if (
                property_exists($config_notification, 'action') &&
                $config_notification->action === $action
            ) {
                $is_found = $config_notification;
                unset($config_notifications[$nr]);
                break;
            }
        }
        if ($is_found) {
            if (
                property_exists($is_found, 'create') &&
                property_exists($is_found->create, 'filter') &&
                !empty($is_found->create->filter) &&
                is_array($is_found->create->filter)
            ) {
                foreach ($is_found->create->filter as $filter) {
                    if (
                        property_exists($filter, 'where') &&
                        !empty($filter->where) &&
                        is_array($filter->where)
                    ) {
                        foreach ($filter->where as $where) {
                            if (
                                property_exists($where, 'token') &&
                                $where->token === 'token' &&
                                $add_filter_not_in_array === false
                            ) {
                                $is_where = $where;
                                break;
                            }
                            if ($add_filter_not_in_array) {
                                if (
                                    property_exists($where, 'operator') &&
                                    $where->operator === '!in.array'
                                ) {
                                    if(!is_array($filter->document)){
                                        $filter->document = [];
                                    }
                                    $filter->document[] = $uuid;
                                }
                            }
                        }
                        if (count($filter->where) === 1 && $is_where !== false) {
                            if(!is_array($filter->document)){
                                $filter->document = [];
                            }
                            $filter->document[] = $uuid;
                        }
                    }
                }
            }
            $config_notifications[] = $is_found;
            $result = [];
            foreach ($config_notifications as $config_notification) {
                $result[] = $config_notification;
            }
            $config->set('stream.notification', $result);
            $config->write($url);
        }
        if(empty($id)){
            $command = 'chown www-data:www-data ' . $dir_stream . ' -R';
            Core::execute($object, $command);
        }
    }
}