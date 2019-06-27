<?php

namespace Drupal\drupal_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class SimpleForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId()
  {
    return "drupal_form";
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First name'),
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
    ];

    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('send message'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    echo $form_state->getValue('email');
    if(filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)){
      $form_state->setValidationComplete(true);
    }else{
      $form_state->setErrorByName('email', $this->t('this email is not correct'));
    }
    if (strlen($form_state->getValue('first_name')) < 3) {
      $form_state->setErrorByName('name', $this->t('The name is too short. Please enter a full name.'));
    }
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    list($message, $email, $url, $data) = $this->initializeVariable($form_state);
    $this->getNotice($email, $message);
    drupal_set_message($form_state->getValue('message'));
    $this->outputData($data, $url);
  }

  /**
   * @param FormStateInterface $form_state
   * @return array
   */
  public function initializeVariable(FormStateInterface $form_state)
  {
    $message = $form_state->getValue('message');
    $email = $form_state->getValue('email');
    $url = "https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/" . $email . "/?hapikey=2e0d95e7-c768-4ad9-a8f0-13ecc5249b90";
    $firstName = $form_state->getValue('first_name');
    $lastName = $form_state->getValue('last_name');
    $data = array(
      'properties' => [
        [
          'property' => 'firstname',
          'value' => $firstName
        ],
        [
          'property' => 'lastname',
          'value' => $lastName
        ]
      ]
    );
    return array($message, $email, $url, $data);
  }

  /**
   * @param $email
   * @param $message
   */
  public function getNotice($email, $message)
  {
    \Drupal::logger('Drupal Custom Form')->notice('@type: message client - %title.',
      array(
        '@type' => $email,
        '%title' => $message,
      ));
  }

  /**
   * @param $data
   * @param $url
   */
  public function outputData($data, $url)
  {
    $json = json_encode($data, true);

    \Drupal::httpClient()->post($url . '&_format=hal_json', [
      'headers' => [
        'Content-Type' => 'application/json'
      ],
      'body' => $json
    ]);
  }
}
