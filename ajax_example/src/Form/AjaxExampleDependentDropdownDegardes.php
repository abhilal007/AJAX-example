<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class AjaxExampleDependentDropdownDegardes extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_dependentdropdowndegardes';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $no_js_use = FALSE) {
    $options_first = _ajax_example_get_first_dropdown_options();

    // If we have a value for the first dropdown from $form_state['values'] we use
    // this both as the default value for the first dropdown and also as a
    // parameter to pass to the function that retrieves the options for the
    // second dropdown.
    $selected = !empty($form_state->getValue('dropdown_first')) ? $form_state->getValue('dropdown_first') : key($options_first);

    // Attach the CSS and JS we need to show this with and without javascript.
    // Without javascript we need an extra "Choose" button, and this is
    // hidden when we have javascript enabled.
    $form['#attached']['library'][] = 'ajax_example/ajax_eample.dropdown';

    $form['dropdown_first_fieldset'] = [
      '#type' => 'details',
      '#open' => TRUE,
    ];
    $form['dropdown_first_fieldset']['dropdown_first'] = [
      '#type' => 'select',
      '#title' => $this->t('Instrument Type'),
      '#options' => $options_first,
      '#attributes' => ['class' => ['enabled-for-ajax']],

    // The '#ajax' property allows us to bind a callback to the server whenever
    // this form element changes. See ajax_example_autocheckboxes and
    // ajax_example_dependent_dropdown in ajax_example.module for more details.
      '#ajax' => [
        'callback' => '::prompt',
        'wrapper' => 'dropdown-second-replace',
      ],
    ];

    // This simply allows us to demonstrate no-javascript use without
    // actually turning off javascript in the browser. Removing the #ajax
    // element turns off AJAX behaviors on that element and as a result
    // ajax.js doesn't get loaded. This is for demonstration purposes only.
    if ($no_js_use) {
      unset($form['dropdown_first_fieldset']['dropdown_first']['#ajax']);
    }

    // Since we don't know if the user has js or not, we always need to output
    // this element, then hide it with with css if javascript is enabled.
    $form['dropdown_first_fieldset']['continue_to_second'] = [
      '#type' => 'submit',
      '#value' => $this->t('Choose'),
      '#attributes' => ['class' => ['next-button']],
    ];

    $form['dropdown_second_fieldset'] = [
      '#type' => 'details',
      '#open' => TRUE,
    ];
    $form['dropdown_second_fieldset']['dropdown_second'] = [
      '#type' => 'select',
      '#title' => $options_first[$selected] . ' ' . $this->t('Instruments'),
      '#prefix' => '<div id="dropdown-second-replace">',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['enabled-for-ajax']],
    // When the form is rebuilt during processing (either AJAX or multistep),
    // the $selected variable will now have the new value and so the options
    // will change.
      '#options' => _ajax_example_get_second_dropdown_options($selected),
    ];
    $form['dropdown_second_fieldset']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('OK'),
    // This class allows attached js file to override the disabled attribute,
    // since it's not necessary in ajax-enabled form.
      '#attributes' => ['class' => ['enabled-for-ajax']],
    ];

    // Disable dropdown_second if a selection has not been made on dropdown_first.
    if (empty($form_state->getValue('dropdown_first'))) {
      $form['dropdown_second_fieldset']['dropdown_second']['#disabled'] = TRUE;
      $form['dropdown_second_fieldset']['submit']['#disabled'] = FALSE;
      $form['dropdown_second_fieldset']['dropdown_second']['#description'] = $this->t('You must make your choice on the first dropdown before changing this second one.');
    }
    return $form;
  }

  /**
   * Selects just the second dropdown to be returned for re-rendering.
   *
   * @return array
   *   Renderable array (the second dropdown).
   */
  public function prompt(array $form, FormStateInterface $form_state) {
    return $form['dropdown_second_fieldset']['dropdown_second'];

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Switch ($form_state->getTriggeringElement()) {
    // case t('OK'):
    // Submit: We're done.
    if ($form_state->getValue('continue_to_second') == 'Choose') {
      // print_r("Why is this");.
      $form_state->setRebuild();
      if ($form_state->getValue('dropdown_second') == '') {
        return;
      }

    }

    if ($form_state->getValue('submit') == 'OK') {
      drupal_set_message(t('Your values have been submitted. dropdown_first=@first, dropdown_second=@second', ['@first' => $form_state->getValue('dropdown_first'), '@second' => $form_state->getValue('dropdown_second')]));
      return;
    }
    // default:
    // drupal_set_message(t('Your values have been submitted. dropdown_first=@first, dropdown_second=@second', ['@first' => $form_state->getValue('dropdown_first'), '@second' => $form_state->getValue('dropdown_second')]));
    // return;
    // 'Choose' or anything else will cause rebuild of the form and present
    // it again.
    $form_state->setRebuild();
  }

}
