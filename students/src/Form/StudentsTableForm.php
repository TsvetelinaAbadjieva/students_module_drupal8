<?php

namespace Drupal\students\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Drupal\Component\Render\FormattableMarkup;  

use Symfony\Component\HttpFoundation\RedirectResponse;


//ConfigFormBase Base class for implementing system configuration forms.
//It extends also FORMBASE
class StudentsTableForm extends ConfigFormBase{

/**
*{@inheritdoc}
*Implements getId() from FormBaseInterface
*/

// public function __construct($extra = NULL){
//   isset($extra) ? $this->formId = 'students.edit.view': $this->formId = 'students.add.view';
// }
public function getFormId(){
  
  return 'students.get.view';
}

/**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return [  
      'students.get.view',
    ];  
  }  
/**
*{@inheritdoc}
*implement buildForm() from FormBaseInterface
*/
public function buildForm(array $form, FormStateInterface $form_state,$extra=null){

  $config = $this->config('students.get.view');
   
  $form['actions']['#type'] = 'actions';
  $form['actions']['redirect'] = [
      '#type' => 'link',
      '#title' => $this->t('Add new'),
      '#url' => base_path().'/student',
    ];
    $form['actions']['submit'] = [
        '#type' => 'link',
        '#title' => $this->t('Add new'),
        '#url' => base_path().'/student',
      ];
  

 if($extra){

    $header = array(
        array('data' => t('ID'), 'field' => 'id', 'sort'=>'asc'),
        array('data' => t('Name'), 'field' => 'name', 'sort'=>'asc'),
        array('data' => t('Faculty Number'), 'field' => 'faculty_number', 'sort'=>'asc'),
        array('data' => t('Gender'), 'field' => 'gender', 'sort' => 'asc'),
     );
     if($extra['accessEdit'] && $extra['accessDelete']){
        $header[] = array('data' => t('Operations'),  'colspan'=> 2);
    }
    
    elseif($extra['accessEdit']){
        $header [] = array('data' => t('Operations'), );
    }
    elseif($extra['accessDelete']){
        $header[] = array('data' => t('Operations'), );
    }

    $form['students_table'] = array(
        '#type'   => 'table', 
        '#header'  => $header,
        '#wrapper' => 'table-wrapper',
        '#tree'    => false, 
        '#rows'    => $extra['data'],
        '#empty'   => t('No student data are added yet'),
        // '#tabledrag' => [
        //     'action'         => 'order',
        //     'relationship'   => 'sibling',
        //     'group'          => 'row-weight',
        // ],

        '#attributes' => array(
            'id' => 'students-table'
        ),
        '#attached' => array('library' => array('core/drupal.ajax', 'core/drupal.tabledrag')),
        // '#attached' => array('library' => array('core/drupal.ajax', 'students/students-library')),

    );
    $form['students_table']['#tabledrag'][] = [
        'action'         => 'order',
        'relationship'   => 'sibling',
        'group'          => 'row-weight',
];


    kint($form['students_table']['#rows'][1]);
 }
 
return parent::buildForm($form, $form_state);
}


}
