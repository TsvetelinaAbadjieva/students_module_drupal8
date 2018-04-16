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
use Drupal\students\Form\StudentsTableForm;
use Drupal\Core\Link;

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

        
        

        // $header = array(
        //     array('data' => t('ID'), 'field' => 'id', 'sort'=>'asc'),
        //     array('data' => t('Name'), 'field' => 'name', 'sort'=>'asc'),
        //     array('data' => t('Faculty Number'), 'field' => 'faculty_number', 'sort'=>'asc'),
        //     array('data' => t('Gender'), 'field' => 'gender', 'sort' => 'asc'),
        //  );

        if($accessEdit && $accessDelete){
            $header[] = array('data' => t('Operations'),  'colspan'=> 2);
        }

        elseif($accessEdit){
            $header [] = array('data' => t('Operations'), );
        }
        elseif($accessDelete){
            $header[] = array('data' => t('Operations'), );
        }

        $query = \Drupal::request()->query;
        $filter = null;

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
        } else {
            $result = db_select('students','s')
            ->fields('s', array('id', 'name', 'faculty_number','gender'))
            ->execute();
            $result = $result->fetchAll(); 
        }

        if($result){
            foreach($result as $row){

                $data[$count]['data']['id'] = $row->id;
                $data[$count]['data']['name'] = $row->name;
                $data[$count]['data']['faculty_number'] = $row->faculty_number;
                $data[$count]['data']['gender'] = $row->gender;

                // $data[$count]['data']['tid'] = [
                //     '#type' => 'hidden',
                //     '#value' => $row->id(),
                //     '#attributes' => [
                //       'class' => ['term-id'],
                //     ],
                //   ];
                // $data[$count]['data']['parent'] = [
                //     '#type' => 'hidden',
                //     // Yes, default_value on a hidden. It needs to be changeable by the
                //     // javascript.
                //     '#default_value' => 0,
                //     '#attributes' => [
                //       'class' => ['term-parent'],
                //     ],
                //   ];
                // $data[$count]['data']['depth'] = [
                //     '#type' => 'hidden',
                //     // Same as above, the depth is modified by javascript, so it's a
                //     // default_value.
                //     '#default_value' => 1,
                //     '#attributes' => [
                //       'class' => ['term-depth'],
                //     ],
                // ];

                if($accessEdit){
                    $data[$count]['data']['edit'] = Link::createFromRoute($this->t('Edit'), 'student.edit', ['id' => $row->id], ['attributes' => ['class' => ['button']]]);
                }

                if($accessDelete){
                    // $data[$count]['data']['delete'] = new FormattableMarkup('<a href=:uri class="use-ajax button" id="delete-item_'.$row->id.'">delete</a>',[':uri' => '/drupal/student/delete/'.$row->id]);
                    $data[$count]['data']['delete'] = Link::createFromRoute($this->t('Delete'), 'student.delete', ['id' => $row->id], ['attributes' => ['class' => ['button', 'use-ajax']]]);
                }

                $data[$count]['id'] = "student-item-".$row->id;
                $data[$count]['class']= array('draggable');

                $data[$count]['data']['weight'] = [
                    '#type' => 'weight',
                    '#delta' => $count,
                    '#title' => $this->t('Weight for added term'),
                    '#title_display' => 'invisible',
                    '#default_value' => $count,
                    '#attributes' => [
                      'class' => ['row-weight'],
                    ],
                  ];

                // $data[$count]['weight'] =[
                //     '#type' => 'weight',
                //     '#delta' => $count,
                //     '#default_value' => $count,
                //     '#attributes'  => [
                //         'class' => 'row-weight'
                //     ]
                // ]; 

                $count ++;
            }
        } 
        else {
            $this->t('No data available');
        } 
            

       $extra = [
           'accessEdit'=> $accessEdit,
           'accessDelete'=> $accessDelete,
           'data' => $data,
       ];

       $form = \Drupal::formBuilder()->getForm('Drupal\students\Form\StudentsTableForm', $extra);
    //    drupal_attach_tabledrag($build, $options);
        return $form;

            // return $build;
    }

    public function addStudent(){
        $access = $this->service->accessForEdit();
        // $loggerFactory->$this->loggerFactory->get('default')->debug($access);
        if(!$access){
            return  new RedirectResponse(base_path().'/students');
            // return  new Response(new Url('/students'));
        }
        else{
            $form = \Drupal::formBuilder()->getForm('Drupal\students\Form\CustomFormSettings',null);
            return $form;
        }
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
                    drupal_set_message('Impossible to edit non existing row!');
                    return new RedirectResponse(base_path().'/students');
            }
        }        
    }

    public function deleteStudent1($id){

        $access = $this->service->accessForDelete();
        if(!$access){
            return new Response('Access denied');
        }
        else {
            
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

            $id = isset($id) && (int)$id ? (int)$id : false;
            if($id){

               if(!$this->findStudentById($id)){

                drupal_set_message('Impossible to delete non existing row!');
                return new RedirectResponse(base_path().'/students');
               }
               else {

                    $query = \Drupal::database()->delete('students');
                    $query->condition('id', $id);
                    $query->execute();   
                    if($query){

                        $response->addCommand(new RemoveCommand('#student-item-'.$id, drupal_set_message('Record successfuly deleted')));
                        return $response;

                    }else{
                        drupal_set_message('Impossible to delete non existing row!');

                        return new RedirectResponse(base_path().'/students');
                    }   
               }
            }
            else return false;
        }
    }


}
