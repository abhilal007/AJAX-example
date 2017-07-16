<?php

/**
 * @file
 * Contains \Drupal\ajax_example\Form\AjaxExampleAutocheckboxes.
 */

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AjaxExampleAutocheckboxes extends FormBase {

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
    $num_checkboxes = !empty($form_state->getValue('howmany_select')) ? $form_state->getValue('howmany_select') : 1;
  $form['howmany_select'] = array(
    '#title' => t('How many checkboxes do you want?'),
    '#type' => 'select',
    '#options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4),
    '#default_value' => $num_checkboxes,
    '#ajax' => array(
      'callback' => '::prompt',
      'wrapper' => 'checkboxes-div',
      // 'method' defaults to replaceWith, but valid values also include
      // append, prepend, before and after.
      // 'method' => 'replaceWith',
      // 'effect' defaults to none. Other valid values are 'fade' and 'slide'.
      // See ajax_example_autotextfields for an example of 'fade'.
      'effect' => 'slide',
      // 'speed' defaults to 'slow'. You can also use 'fast'
      // or a number of milliseconds for the animation to last.
      // 'speed' => 'slow',
      // Don't show any throbber...
      'progress' => array('type' => 'none'),
    ),
  );

  $form['checkboxes_fieldset'] = array(
    '#title' => t("Generated Checkboxes"),
    // The prefix/suffix provide the div that we're replacing, named by
    // #ajax['wrapper'] above.
    '#prefix' => '<div id="checkboxes-div">',
    '#suffix' => '</div>',
    '#type' => 'fieldset',
    '#description' => t('This is where we get automatically generated checkboxes'),
  );

  for ($i = 1; $i <= $num_checkboxes; $i++) {
    $form['checkboxes_fieldset']["checkbox$i"] = array(
      '#type' => 'checkbox',
      '#title' => "Checkbox $i",
    );
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
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
    return $form['checkboxes_fieldset'];
  }
}
