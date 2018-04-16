<?php

namespace Drupal\students\Controller;

use Drupal\students\Controller\ApiController;
use Drupal\students\Form\CustomFormController;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database;
use Drupal\Core\Url;
use Drupal\Component\Render\FormattableMarkup;  
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;



use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Drupal\students\Services\UserCheckAccess;
use Drupal\students\Form\CustomFormSettings;
use Drupal\students\Form\ConfirmDeleteForm;

class StudentController extends ControllerBase{

   

    private $database;
    private $table;
    protected $service;
    protected $loggerFactory;

    public function __construct(UserCheckAccess $service, LoggerChannelFactoryInterface $logger){

        $this->service = $service;
        $this->loggerFactory = $logger;
    }

    public static function create(ContainerInterface $container) {

        $service = $container->get('students.check_access');
        $logger = $container->get('logger.factory');
        return new static($service, $logger);
      }

    public function displayForm(){

        return new Response('In display form');
    }

    public function getAllStudents(){

        $connection = \Drupal::database();
        $rows = [];
        $data = [];
        $count = 1;
        $host = \Drupal::request()->getHost();

        $accessEdit = $this->service->accessForEdit();
        $accessDelete = $this->service->accessForDelete();
        $this->loggerFactory->get('default')->debug($accessEdit);
        $this->loggerFactory->get('default')->debug($accessDelete);
/*        
        kint($access);
        //get user permissions
        $user = \Drupal::currentUser();
        $user_roles = $user->getRoles();
        $roles_permissions = user_role_permissions($user_roles);
*/      

        $header = array(
            array('data' => t('ID'), 'field' => 'id', 'sort'=>'asc'),
            array('data' => t('Name'), 'field' => 'name', 'sort'=>'asc'),
            array('data' => t('Faculty Number'), 'field' => 'faculty_number', 'sort'=>'asc'),
            array('data' => t('Gender'), 'field' => 'gender'),
         );

         if($accessEdit){
            $header [] = array('data' => t('Operations'), 'field'=> 'edit');
        }
        if($accessDelete){
            $header[] = array('data' => t('Operations'), 'field'=> 'delete');
        }

        $query = \Drupal::request()->query;
        $filter = null;
        kint($query);
        if(isset($query) && count($query)>0){
            $filter = $this->service->normlizeQueryString($query);
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
        kint($result);

        // $table_sort = $result->extend('Drupal\Core\Database\Query\TableSortExtender')->orderByHeader($header);
        // $pager = $table_sort->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
        // $result = $pager->execute();
/*        if($result){
            foreach($result as $row){
                foreach($row as $key => $value){
                    $data[$count]['data'][$key] = $value;
                }
                if($accessEdit){
                    $data[$count]['data']['edit'] = new FormattableMarkup('<a href=:uri>edit</a>',[':uri' => '/drupal/student/edit/'.$row['id']]);
                }
                if($accessDelete){
                    $data[$count]['data']['delete'] = new FormattableMarkup('<a href=:uri class="use-ajax" id="delete-item_'.$row['id'].'">delete</a>',[':uri' => '/drupal/student/delete/'.$row['id']]);
                }
                $data[$count]['id'] = "student-item-".$row['id'];
                $data[$count]['class']= array('draggable');

                $data[$count]['weight'] =[
                    '#type' => 'weight',
                    '#delta' => $count,
                    '#default_value' => $row['id'],
                    '#attributes'  => [
                        'class' => 'row-weight'
                    ]
                ]; 
                $count ++;
            }
        } 
*/
        if($result){
            foreach($result as $row){

                $data[$count]['data']['id'] = $row->id;
                $data[$count]['data']['name'] = $row->name;
                $data[$count]['data']['faculty_number'] = $row->faculty_number;
                $data[$count]['data']['gender'] = $row->gender;

                if($accessEdit){
                    $data[$count]['data']['edit'] = new FormattableMarkup('<a href=:uri>edit</a>',[':uri' => '/drupal/student/edit/'.$row->id]);
                }
                if($accessDelete){
                    $data[$count]['data']['delete'] = new FormattableMarkup('<a href=:uri class="use-ajax" id="delete-item_'.$row->id.'">delete</a>',[':uri' => '/drupal/student/delete/'.$row->id]);
                }
                $data[$count]['id'] = "student-item-".$row->id;
                $data[$count]['class']= array('draggable');

                $data[$count]['weight'] =[
                    '#type' => 'weight',
                    '#delta' => $count,
                    '#default_value' => $row->id,
                    '#attributes'  => [
                        'class' => 'row-weight'
                    ]
                ]; 
                $count ++;
            }
           

        } 
        else {
            $this->t('No data available');
        } 
            
        $build = array(
            '#markup' => t('<h3>Students list<h3>')
        );

        $build['students_table'] = array(
            '#theme'   => 'table', 
            '#header'  => $header,
            '#wrapper' => 'table-wrapper',
            '#tree'    => true, 
            '#rows'    => $data,
            '#empty'   => t('No student data are added yet'),
            '#tabledrag' => [
                'action'         => 'order',
                'relationship'   => 'sibling',
                'group'          => 'row-weight',
            ],

            '#attributes' => array(
                'id' => 'students-table'
            ),
            '#attached' => array('library' => array('core/drupal.ajax', 'core/drupal.tabledrag')),
        );

        $build['pager'] = array(
            '#type' => 'pager'
       );
       $options = [
            'action'         => 'order',
            'relationship'   => 'sibling',
            'group'          => 'row-weight',
       ];

    //    drupal_attach_tabledrag('students-table', $options);
            return $build;
        //  return new Response(json_encode($rows));
    }

    public function addStudent(){
        
        $form = \Drupal::formBuilder()->getForm('Drupal\students\Form\CustomFormSettings',null);
            return $form;
        // return new Response('In add Students');
    }

    public function findStudentById($id){

        $id = (isset($id) && (int)$id) ? (int)$id : false;
        if($id){
            $result = db_query("select * from {students} where id = :id limit 1", array(":id" => $id))->fetchObject();           
            if($result){
                return $result;
            }
        }
        else {
            return false;
        }
    }

    public function editStudent($id){

        $access = $this->service->accessForEdit();
        // $loggerFactory->$this->loggerFactory->get('default')->debug($access);
        if(!$access){
            return  new RedirectResponse(base_path().'/students');
            // return  new Response(new Url('/students'));
        }
        else {
            $data = $this->findStudentById($id);

            if($data){
                $form = \Drupal::formBuilder()->getForm('Drupal\students\Form\CustomFormSettings',$data);
                return $form;
            }
            else{
                    return false;
            }
        }
        
        
    }

    public function deleteStudent1($id){

        $access = $this->service->accessForDelete();
        if(!$access){
            return new Response('Access denied');
        }
        else {
            $id = isset($id) && (int)$id ? (int)$id: false;
            if($id){
                $form = \Drupal::formBuilder()->getForm('Drupal\students\Form\ConfirmDeleteForm',$id);
                return $form;
            }
            else return false;
        }
    }

    public function deleteStudent($id){

        $access = $this->service->accessForDelete();
        if(!$access){
            return  new RedirectResponse(base_path().'/students');
        }
        else {

            $response = new AjaxResponse();
            $renderer = \Drupal::service('renderer');

            $id = isset($id) && (int)$id ? (int)$id: false;
            if($id){
                $query = \Drupal::database()->delete('students');
                $query->condition('id', $id);
                $query->execute();   
                if($query){

                    $response->addCommand(new RemoveCommand('#student-item-'.$id));
                    drupal_set_message('Record successfuly deleted');

                    return $response;

                }else{
                    return new Response(t('Delete operation went wrong!'));
                }   
            }
            else return false;
        }
    }

}
