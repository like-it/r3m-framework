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
        $entityManager = Database::entityManager($object);

        $uuid = $object->request('uuid');
        $id = $object->request('id');
        if($uuid){
            $qb = $entityManager->createQueryBuilder();
            $record = $qb->select(['entity'])
                ->from($table, 'entity')
                ->where('entity.uuid = :uuid')
                ->andWhere('entity.' . $field . ' = :'  . $field)
                ->setParameters([
                    'uuid' => $uuid,
                    $field => $string
                ])
                ->setMaxResults(1)
                ->getQuery();
//                ->getFirstResult();
        }
        elseif($id){
            $qb = $entityManager->createQueryBuilder();
            $record = $qb->select(['entity'])
                ->from($table, 'entity')
                ->where('entity.id = :id')
                ->andWhere('entity.' . $field . ' = :'  . $field)
                ->setParameters([
                    'id' => $id,
                    $field => $string
                ])
                ->setMaxResults(1)
                ->getQuery()
                ->getFirstResult();
        } else {
            $repository = $entityManager->getRepository($table);
            $criteria = [];
            $criteria[$field] = $string;
            $record = $repository->findOneBy($criteria);
        }


        dd($record);




        if($record === null){
            return true;
        } else {
            return false;
        }


    } else {
        return false;
    }
}
