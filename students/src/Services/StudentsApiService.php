<?php

namespace Drupal\students\Services;

use Drupal\students\Services\UserCheckAcces;
use Symfony\Component\HttpFoundation\JsonResponse;

class StudentsApiService{

    private $db;
    private $hasPermissions;

    public function __construct(UserCheckAccess $access){
        $this->db = \Drupal::database();
        $this->hasPermissions = $access->accessWebservice();
    }

    public function get(){

        $query = \Drupal::request()->query;
        $filter = null;
        $result = null;

        if(isset($query) && count($query)>0){
            $filter = $this->normlizeQueryString($query);
            $order = $filter['order'];
            $sort = $filter['sort'];
        }

        // $result = $connection->query("select * from {students} ",[],['fetch' => \PDO::FETCH_ASSOC,]);

        if($filter){
            
            $result = db_select('students','s')
            ->fields('s', array('id', 'name', 'faculty_number','gender'))
            ->orderBy($order, $sort)
            ->execute();
            $result = $result->fetchAll(); 
        }else{
            $result = db_select('students','s')
            ->fields('s', array('id', 'name', 'faculty_number','gender'))
            ->execute();
            $result = $result->fetchAll(); 
        }
        return new JsonResponse($result);

    }
    public function normlizeQueryString($query){

        if(!$query) return; 
        $params = [
            'order' => strtolower(str_replace(' ', '_',$query->get('order'))), 
            'sort' => $query->get('sort'),
        ];
        return $params;
    }
}