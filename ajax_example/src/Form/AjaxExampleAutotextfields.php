<?php

/**
 * @file
 * Contains \Drupal\ajax_example\Form\AjaxExampleAutotextfields.
 */

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AjaxExampleAutotextfields extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_autocheckbox';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['ask_first_name'] = array(
    '#type' => 'checkbox',
    '#title' => t('Ask me my first name'),
    '#ajax' => array(
      'callback' => '::prompt',
      'wrapper' => 'textfields',
      'effect' => 'fade',
    ),
  );
  $form['ask_last_name'] = array(
    '#type' => 'checkbox',
    '#title' => t('Ask me my last name'),
    '#ajax' => array(
      'callback' => '::prompt',
      'wrapper' => 'textfields',
      'effect' => 'fade',
    ),
  );

  $form['textfields'] = array(
    '#title' => t("Generated text fields for first and last name"),
    '#prefix' => '<div id="textfields">',
    '#suffix' => '</div>',
    '#type' => 'fieldset',
    '#description' => t('This is where we put automatically generated textfields'),
  );

  // Since checkboxes return TRUE or FALSE, we have to check that
  // $form_state has been filled as well as what it contains.
  if (!empty($form_state->getValue('ask_first_name')) && $form_state->getValue('ask_first_name')) {
    $form['textfields']['first_name'] = array(
      '#type' => 'textfield',
      '#title' => t('First Name'),
    );
  }
  if (!empty($form_state->getValue('ask_last_name')) && $form_state->getValue('ask_last_name')) {
    $form['textfields']['last_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Last Name'),
    );
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Click Me'),
  );

  return $form;
}
 /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Handles switching the available regions based on the selected theme.
   */
  public function prompt($form, FormStateInterface $form_state) {
    return $form['textfields'];
  }
}
