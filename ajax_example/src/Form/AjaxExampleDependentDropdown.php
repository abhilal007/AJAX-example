<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class AjaxExampleDependentDropdown extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_dependentdropdown';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $options_first = $this->_ajax_example_get_first_dropdown_options();
    // If we have a value for the first dropdown from $form_state['values'] we use
    // this both as the default value for the first dropdown and also as a
    // parameter to pass to the function that retrieves the options for the
    // second dropdown.
    $selected = !empty($form_state->getValue('dropdown_first')) ? $form_state->getValue('dropdown_first') : key($options_first);

    $form['dropdown_first'] = [
      '#type' => 'select',
      '#title' => 'Instrument Type',
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
      '#title' => $options_first[$selected] . ' ' . t('Instruments'),
    // The entire enclosing div created here gets replaced when dropdown_first
    // is changed.
      '#prefix' => '<div id="dropdown-second-replace">',
      '#suffix' => '</div>',
    // When the form is rebuilt during ajax processing, the $selected variable
    // will now have the new value and so the options will change.
      '#options' => $this->_ajax_example_get_second_dropdown_options($selected),
      '#default_value' => !empty($form_state->getValue('dropdown_second')) ? $form_state->getValue('dropdown_second') : '',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
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
    return $form['dropdown_second'];
  }

  /**
   *
   */
  public function _ajax_example_get_first_dropdown_options() {
    // drupal_map_assoc() just makes an array('String' => 'String'...).
    return
    [
      'String' => 'String',
      'Woodwind' => 'Woodwind',
      'Brass' => 'Brass',
      'Percussion' => 'Percussiom',

    ];
  }

  /**
   * Helper function to populate the second dropdown.
   *
   * This would normally be pulling data from the database.
   *
   * @param string $key
   *   This will determine which set of options is returned.
   *
   * @return array
   *   Dropdown options
   */
  public function _ajax_example_get_second_dropdown_options($key = '') {
    $options = [
      t('String') =>
      [
        'Violin' => 'Violin',
        'Viola' => 'Viola',
        'Cello' => 'Cello',
        'Double Bass' => 'Double Bass',

      ],
      t('Woodwind') =>
      [
        'Flute' => 'Flute',
        'Clarinet' => 'Clarinet',
        'Oboe' => 'Oboe',
        'Bassoon' => 'Bassoon',

      ],
      t('Brass') =>
      [
        'Trumpet' => 'Trumpet',
        'Trombone' => 'Trombone',
        'French Horn' => 'French Horn',
        'Euphonium' => 'Euphonium',

      ],
      t('Percussion') =>
      [
        'Bass Drum' => 'Bass Drum',
        'Timpani' => 'Timpani',
        'Snare Drum' => 'Snare Drum',
        'Tambourine' => 'Tambourine',

      ],
    ];
    if (isset($options[$key])) {
      return $options[$key];
    }
    else {
      return [];
    }
  }

}
