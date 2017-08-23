<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Repopulate a dropdown based on form state.
 */
class AjaxExampleDependentDropdown extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_dependentdropdown';
  }

  /**
   * AJAX-based dropdown example form.
   *
   * A form with a dropdown whose options are dependent on a
   * choice made in a previous dropdown.
   *
   * On changing the first dropdown, the options in the second
   * are updated.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $options_first = _ajax_example_get_first_dropdown_options();
    // If we have a value for the first dropdown from $form_state['values'] we use
    // this both as the default value for the first dropdown and also as a
    // parameter to pass to the function that retrieves the options for the
    // second dropdown.
    $selected = !empty($form_state->getValue('dropdown_first')) ? $form_state->getValue('dropdown_first') : key($options_first);

    $form['dropdown_first'] = [
      '#type' => 'select',
      '#title' => $this->t('Instrument Type'),
      '#options' => $options_first,
      '#default_value' => $selected,
    // Bind an ajax callback to the change event (which is the default for the
    // select form type) of the first dropdown. It will replace the second
    // dropdown when rebuilt.
      '#ajax' => [
      // When 'event' occurs, Drupal will perform an ajax request in the
      // background. Usually the default value is sufficient (eg. change for
      // select elements), but valid values include any jQuery event,
      // most notably 'mousedown', 'blur', and 'submit'.
      // 'event' => 'change',.
        'callback' => '::prompt',
        'wrapper' => 'dropdown-second-replace',
      ],
    ];

    $form['dropdown_second'] = [
      '#type' => 'select',
      '#title' => $options_first[$selected] . ' ' . $this->t('Instruments'),
    // The entire enclosing div created here gets replaced when dropdown_first
    // is changed.
      '#prefix' => '<div id="dropdown-second-replace">',
      '#suffix' => '</div>',
    // When the form is rebuilt during ajax processing, the $selected variable
    // will now have the new value and so the options will change.
      '#options' => _ajax_example_get_second_dropdown_options($selected),
      '#default_value' => !empty($form_state->getValue('dropdown_second')) ? $form_state->getValue('dropdown_second') : '',
    ];
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
    $trigger = (string) $form_state->getTriggeringElement()['#value'];
    switch ($trigger) {
      case 'Submit':
        // Submit: We're done.
        drupal_set_message($this->t('Your values have been submitted. dropdown_first=@first, dropdown_second=@second', [
          '@first' => $form_state->getValue('dropdown_first'),
          '@second' => $form_state->getValue('dropdown_second'),
        ]));
        return;
    }
    // 'Choose' or anything else will cause rebuild of the form and present
    // it again.
    $form_state->setRebuild();
  }

  /**
   * Handles switching the available regions based on the selected theme.
   */
  public function prompt($form, FormStateInterface $form_state) {
    return $form['dropdown_second'];
  }

}
