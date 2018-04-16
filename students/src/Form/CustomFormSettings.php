<?php

namespace Drupal\students\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Drupal\Component\Render\FormattableMarkup;  

use Symfony\Component\HttpFoundation\RedirectResponse;


//ConfigFormBase Base class for implementing system configuration forms.
//It extends also FORMBASE
class CustomFormSettings extends ConfigFormBase{

/**
*{@inheritdoc}
*Implements getId() from FormBaseInterface
*/

// public function __construct($extra = NULL){
//   isset($extra) ? $this->formId = 'students.edit.view': $this->formId = 'students.add.view';
// }
public function getFormId(){
  
  return 'student.add.view';
}

/**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return [  
      'student.add.view',
    ];  
  }  
/**
*{@inheritdoc}
*implement buildForm() from FormBaseInterface
*/
public function buildForm(array $form, FormStateInterface $form_state, $extra= null){

  $config = $this->config('student.add.view');  
  isset($extra) ? $form_state->setValue('id', $extra->id): null;
  //will produce a form with a field custom_field
  if($extra){

  $form['id'] = [
      '#type' => 'hidden',
      '#default_value'=> isset($extra)? $extra->id : $config->get('welcome_id'),
  ];
  }
  $form['actions']['redirect-students'] = [
    '#type'   => 'link',
    '#title' => 'Students list',
    '#url' => \Drupal\Core\Url::fromRoute('students.get'),
 ];

  $form['name'] = [
    '#type' => 'textfield',
    '#title'=> $this->t('Name'),
    '#description'=> $this->t('This is name text field'),
    '#default_value'=> isset($extra)? $extra->name : $config->get('welcome_message'),
    '#required' => true
 ];

  $form['gender'] = [
    '#type' => 'radios',
    '#title'=> $this->t('Gender'),
    '#description'=> $this->t('This is gender field'),
    '#default_value'=> isset($extra)? $extra->gender: 'male',
    '#required' => true,
    '#options' => ['male'=> $this->t('male'), 'female' => $this->t('female')]
  ];

  $form['faculty_number'] = array(
    '#type' => 'tel',
    '#title' => $this->t('Faculty number'),
    '#description'=> $this->t('This is faculty number field'),
    '#required' => true,
    '#default_value'=> isset($extra)? $extra->faculty_number : $config->get('welcome_number'),

    '#number' =>$this->t('Field MUST be a number'),
  );
  
  

return parent::buildForm($form, $form_state);
}


/**
 * 
 * {@inheritdoc}
 */
public function validateForm(array &$form, FormStateInterface $form_state){

//validate URL
//  if(!UrlHelper::isValid($form_state->getValue('video'), true)){
//      $form_state->setErrorByName('video', $this->t("Video url %url is invalid"), array('%url'=> $form_state->getValue('video')));
//  }
  
 if(!$form_state->hasValue('name') || strlen($form_state->getValue('name'))<3){
    $form_state->setErrorByName('name', t('Field is required and must be not less than 3 symbols'));
 }

 if(!$form_state->hasValue('faculty_number') || strlen($form_state->getValue('faculty_number'))!= 8){

    $form_state->setErrorByName('faculty_number', t('Field is required number and must be exactly 8 symbols'));
    
 } 
  elseif(!is_numeric($form_state->getValue('faculty_number'))){
    $form_state->setErrorByName('phone_number', t('Field is NOT a number'));
 }
 
}

/**
 * {@inheritdoc} 
 */
public function submitForm(array &$form, FormStateInterface $form_state){
    
    $body = [];
    // foreach ($form_state->getValues() as $key => $value) {
    //     drupal_set_message($key . ': ' . $value);
    //     $body[$key] = $value;
    //   }
    //   print_r($form_state->getValue('id'));

      if(!$form_state->hasValue('id')){
        db_insert('students')
        ->fields(array(
            'name' => $form_state->getValue('name'),
            'faculty_number' => $form_state->getValue('faculty_number'),
            'gender' => $form_state->getValue('gender'),       
         ))->execute();
        } 
        else {
          db_update('students')
          ->fields(array(
            'name'=> $form_state->getValue('name'),
            'faculty_number'=> $form_state->getValue('faculty_number'),
            'gender'=>$form_state->getValue('gender'),
          ))
          ->condition('id', $form_state->getValue('id'))
          ->execute();
          return  new RedirectResponse(base_path().'/students');

           //$form_state->setRedirect(\Drupal\Core\Url::fromRoute('students.get'));
          // return new \Symfony\Component\HttpFoundation\RedirectResponse(\Drupal\Core\Url::fromUserInput('/students')->toString());
 
          // return new \Symfony\Component\HttpFoundation\RedirectResponse('/students');
          // return new RedirectResponse(\Drupal::url('students.get'));
          // return new Url('/students');
        }
        return  new RedirectResponse(base_path().'/students');

      //  parent::submitForm($form, $form_state);
  }


    // public function submitForm(array &$form, FormStateInterface $form_state){
    //   // echo '<pre>';
    //   // var_dump($form_state['values']);
    //   // echo '</pre>';
    //   $body = [];
    //   foreach ($form_state->getValues() as $key => $value) {
    //       drupal_set_message($key . ': ' . $value);
    //       $body[$key] = $value;
    //     }
    //     $options = array(
    //       'headers' => array(
    //       //   'Client-ID:**********',
    //       //   'Authorization: Bearer ********',
    //         'Content-Type:application/json',
    //        ),
    //        'method' => 'POST',
    //       'data'=> drupal_json_encode($body),
    //     );
      
    //  $url = URL::fromRoute('students.students.client', $options);
    //  $form_state->setRedirect($url);
  
    //   $this->config('students.add.view')  
    //     ->set('welcome_message', $form_state->getValue('welcome_message'))
    //     ->set('welcome_video', $form_state->getValue('welcome_video'))  
    //     ->set('phone_number', $form_state->getValue('phone_number'))
    //     ->save();  
        
    //   parent::submitForm($form, $form_state);
    //   }
  
}
