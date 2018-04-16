<?php

namespace Drupal\students\Controller;
 
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\students\Client\MyClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;

use Drupal\students\Services\StudentsApiService;
use Drupal\students\Services\UserCheckAccess;



/**
 * Provides a Demo Resource
 *
 * @RestResource(
 *   id = "students_resource",
 *   label = @Translation("Students Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/students_resource"
 *   }
 * )
 */
class ApiStudentController extends ControllerBase{

    protected $client;
    protected $logger;
    protected $access;
    protected $request;
     
    public function __construct(StudentsApiService $client, LoggerChannelFactoryInterface $logger, UserCheckAccess $access, RequestStack $request){

        $this->client = $client;
        $this->logger = $logger;
        $this->access = $access;
        $this->request = $request;
        
     }

     public static function create(ContainerInterface $container){
        
        $client = $container->get('students.get');
        $logger = $container->get('logger.factory');
        $access = $container->get('students.check_access');
        $request = $container->get('request_stack');

        return new static($client, $logger, $access, $request);
    } 

    public function data(){
        return [
            'key1'=>'value1',
            'key2'=> 'value2'
        ];
    }

    public function request($method, $endpoint, $query, $body){
        $response = [];
        $response = $this->httpClient->{$method}(
            $this->base_uri.$endpoint,
            $this->buildOptions($query, $body)
        );
        if(!$response){
            sleep(2);
        }
        return $response;
    }
    /**
     * implements @hook_form()
     */
    public function build(){
        $data = json_decode($this->request('GET','/form/show',['Content-type'=> 'application/json'],json_encode($this->data())));
        return new Response($data);
    }
    public function addStudent(Request $request){
        print_r('In add Student');
    }
    public function editStudent(Request $request, $id){
        print_r('In edit Student');
    }
    public function deleteStudent($id){
        print_r('In delete Student');
    }
    public function getStudents(){
        print_r('In get Students');

        $access = $this->client->accessForRead();
        kint($this->client);

        if(!$access){
            $response = [
                'message' => 'Access denied',
                'status' => ' Error 401 - forbidden'
            ];
            return new JsonResponse($response);
        }

        $this->client->get();
    }
}