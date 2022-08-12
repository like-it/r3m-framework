<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-18
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */

use R3m\Io\Module\Database;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

/**
 * @throws ObjectException
 * @throws FileWriteException
 * @throws \Doctrine\ORM\Exception\ORMException
 * @throws \Doctrine\ORM\ORMException
 */
function validate_is_unique_mysql(R3m\Io\App $object, $string='', $field='', $argument=''){
    $table = false;
    $field = false;
    if(property_exists($argument, 'table')){
        $table = $argument->table;
    }
    if(property_exists($argument, 'field')){
        $field = $argument->field;
    }
    if(
        $table &&
        $field
    ){
        $entityManager = Database::entityManager($object, []);
        $repository = $entityManager->getRepository($table);
        $criteria = [];
        if(is_array($field)){
            foreach($field as $attribute){
                $criteria[$attribute] = $object->request($attribute);
            }
            $record = $repository->findOneBy($criteria);
            if($record === null){
                return true;
            } else {
                return false;
            }
        } else {
            $criteria[$field] = $string;
            $record = $repository->findOneBy($criteria);
            if($record === null){
                return true;
            } else {
                return false;
            }
        }

    } else {
        return false;
    }
}
