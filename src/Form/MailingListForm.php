<?php

namespace Drupal\gm_mailing_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\gm_mailing_list\MailigListHelperTrait;

class MailingListForm extends FormBase {

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
  public function getFormId() {
    return 'gm_mailing_list_form';
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
  public function buildForm(array $form, FormStateInterface $form_state) {

    // $user_id = (\Drupal::currentUser()->id()) ? \Drupal::currentUser()->id() : NULL;

    // $form['forname'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Prénom'),
    // ];

    // $form['name'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Nom'),
    // ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#attributes' => array(
        'autocomplete' => 'off',
        'autocorrect' => 'off',
        'autocapitalize' => 'none',
        'spellcheck' => 'off',
      ),
    ];

    $form['code_postal'] = [
      '#type' => 'textfield',
      '#title' => t('Code postal'),
      '#autocomplete_route_name' => 'gm_mailing_list.find_code_postal',
      '#prefix' => '<div id="gm-donateur-adresse">',
			'#suffix' => '</div>',
      '#description' => '<div class="ripple"></div>',
      '#required' => TRUE,
      '#attributes' => array(
        'autocomplete' => 'off',
        'autocorrect' => 'off',
        'autocapitalize' => 'none',
        'spellcheck' => 'off',
      ),
    ];

    $country = MailigListHelperTrait::builArrayCountry();

    $form['country'] = [
      // '#title' => $this->t('Pays'),
      '#type' => 'select',
      '#options' => $country,
      '#required' => TRUE,
      '#default_value' => 'fr',
      '#attributes' => array(
        'autocomplete' => 'off',
        'autocorrect' => 'off',
        'autocapitalize' => 'none',
        'spellcheck' => 'off',
      ),
    ];

    $form['accept'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('J\'ai lu et j\'accepte les mentions d\'information relatives au recueil de mes données personnelles ci-dessous.'),
      '#required' => TRUE,
    ];

    $form['#attached']['library'][] = 'gm_mailing_list/gm-design-section';

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit_new'] = [
      '#type' => 'submit',
      '#value' => $this->t('Je m\'inscris'),
    ];

    return $form;

  }

	/**
   * Validate the title and the checkbox of the form
   * 
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * 
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $name = !empty($form_state->getValue('name')) ? $form_state->getValue('name') : NULL;
    $forname = !empty($form_state->getValue('forname')) ? $form_state->getValue('forname') : NULL;
    $code_postal = (int)$form_state->getValue('code_postal');
    $accept = $form_state->getValue('accept');

    if ($name && strlen($forname) < 2) {
      $form_state->setErrorByName('forname', $this->t('The forname must be at least 3 characters long.'));
    }

    if ($forname && strlen($name) < 2) {
      $form_state->setErrorByName('name', $this->t('The name must be at least 3 characters long.'));
    }

    if (!is_int($code_postal)){
      $form_state->setErrorByName('code_postal', $this->t('Veuillez renseigner un code postal valide avant de continuer.'));
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $service = \Drupal::service('gm_mailing_list.builder.data');
    // $name = !empty($form_state->getValue('name')) ? $form_state->getValue('name') : NULL;
    // $forname = !empty($form_state->getValue('forname')) ? $form_state->getValue('forname') : NULL;
    $name = NULL;
    $forname = NULL;
    $email = $form_state->getValue('email');
    // $prefix = !empty($form_state->getValue('prefix')) ? $form_state->getValue('prefix') : 0;
    // $phone = !empty($form_state->getValue('phone')) ? $form_state->getValue('phone') : 0;
    $code_postal = !empty($form_state->getValue('code_postal')) ? (int)$form_state->getValue('code_postal') : NULL;
    $country = !empty($form_state->getValue('country')) ? $form_state->getValue('country') : NULL;
    // prepare messqger service 
    $messenger = \Drupal::messenger();
    // Check if email exist
    $ids = \Drupal::entityQuery('user')
			->condition('mail', $email)
			->range(0, 1)
			->execute();
    // If the email no exist in db store it else sho a generic message.
    if (!empty($ids)) {
      $messenger->addMessage('Merci de vous être inscrit à notre newsletters.');
    }else{
      // Store in the db
      $store_in_db = MailigListHelperTrait::storeMailSubscriber($name, $forname, $email, 1, $code_postal, $country, 'defaulf');
      // Send notification
      $sujet = t('Votre inscription à la newsletter.');
			$import_media_service = \Drupal::service('gm_adhesion.helpers_system');
			$import_media_service->sendMailSystem($email, 'Monsieur', $sujet, 'msg_inscription_news');
			$import_media_service->sendMailNotification($email, 'Monsieur', $sujet, 'msg_inscription_news');
      $messenger->addMessage('Vous avez été ajouté à la liste d\'information.');
    }
    // Redirect to home.
    // $form_state->setRedirect('<front>');
  }

}