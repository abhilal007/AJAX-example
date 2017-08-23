<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Generate a changing number of checkboxes.
 */
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
    $form['howmany_select'] = [
      '#title' => t('How many checkboxes do you want?'),
      '#type' => 'select',
      '#options' => [1 => 1, 2 => 2, 3 => 3, 4 => 4],
      '#default_value' => $num_checkboxes,
      '#ajax' => [
        'callback' => '::prompt',
        'wrapper' => 'checkboxes-div',
      // 'method' defaults to replaceWith, but valid values also include
      // append, prepend, before and after.
      // 'method' => 'replaceWith',
      // 'effect' defaults to none. Other valid values are 'fade' and 'slide'.
      // See AjaxExampleAutoTextfields for an example of 'fade'.
        'effect' => 'slide',
      // 'speed' defaults to 'slow'. You can also use 'fast'
      // or a number of milliseconds for the animation to last.
      // 'speed' => 'slow',
      // Don't show any throbber...
        'progress' => ['type' => 'none'],
      ],
    ];

    $form['checkboxes_fieldset'] = [
      '#title' => t("Generated Checkboxes"),
      // The prefix/suffix provide the div that we're replacing, named by
      // #ajax['wrapper'] above.
      '#prefix' => '<div id="checkboxes-div">',
      '#suffix' => '</div>',
      '#type' => 'fieldset',
      '#description' => $this->t('This is where we get automatically generated checkboxes'),
    ];

    for ($i = 1; $i <= $num_checkboxes; $i++) {
      $form['checkboxes_fieldset']["checkbox$i"] = [
        '#type' => 'checkbox',
        '#title' => "Checkbox $i",
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
 * Callback for autocheckboxes.
 *
 * Callback element needs only select the portion of the form to be updated.
 * Since #ajax['callback'] return can be HTML or a renderable array (or an
 * array of commands), we can just return a piece of the form.
 * See @link examples/ajax-example/advanced-commands for more details
 * on AJAX framework commands.
 *
 * @return array
 *   Renderable array (the checkboxes fieldset)
 */
  public function prompt($form, FormStateInterface $form_state) {
    return $form['checkboxes_fieldset'];
  }

}
