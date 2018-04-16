<?php

namespace Drupal\students\Services;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class for check UserAccess
 */
class UserCheckAccess{

    private $permissions = [];
    private $user;

    public function __construct(){

        $this->user = \Drupal::currentUser();
        $user_roles = $this->user->getRoles();
        $this->permissions = user_role_permissions($user_roles);
        // kint($this->permissions['guest']);
        // kint($this->user->hasPermission('delete students content', $this->permissions['guest']));

    }

    public function accessForEdit(){
       if(!$this->user->isAuthenticated()) {
           return false;
       }
       return in_array('edit students content', $this->permissions['guest']);  

    //    return $this->user->hasPermission('edit students content');  
        
    }
    
    public function accessForRead(){

        if(!$this->user->isAuthenticated()) {
            return false;
        }
        return in_array('access students content', $this->permissions['guest']);  
    }

    public function accessForDelete(){
        if(!$this->user->isAuthenticated()) {
            return false;
        }
        return in_array('delete students content', $this->permissions['guest']);  

        // return $this->user->hasPermission('delete students content');  

    }
    public function accessWebservice(){
        if(!$this->user->isAuthenticated()) {
            return false;
        }
        return in_array('access webservice', $this->permissions['guest']);  

        // return $this->user->hasPermission('delete students content');  

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