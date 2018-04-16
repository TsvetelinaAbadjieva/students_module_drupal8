<?php

namespace Drupal\students\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines a confirmation form to confirm deletion of something by id.
 */
class ConfirmDeleteForm extends ConfirmFormBase {

  /**
   * ID of the item to delete.
   *
   * @var int
   */
  protected $id;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {
    $this->id = (int)$id;
    $this->getQuestion();
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo: Do the deletion.
        $query = \Drupal::database()->delete('students');
        $query->condition('id', $this->id);
        $query->execute();   
        if($query){
            drupal_set_message('Record successfuly deleted');
            // $response = new Symfony\Component\HttpFoundation\RedirectResponse(new URL('students.get'));
            // return $response;
            $this->getCancelUrl();
        }else{
            return new Response(t('Delete operation went wrong!'));
        }   
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "student.delete.confirm";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('students.get');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to delete %id?', ['%id' => $this->id]);
  }

}