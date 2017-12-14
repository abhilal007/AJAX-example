<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Dynamically-enabled form with graceful no-JS degradation.
 *
 * Example of a form with portions dynamically enabled or disabled, but
 * with graceful degradation in the case of no javascript.
 *
 * The idea here is that certain parts of the form don't need to be displayed
 * unless a given option is selected, but then they should be displayed and
 * configured.
 *
 * The third $no_js_use argument is strictly for demonstrating operation
 * without javascript, without making the user/developer turn off javascript.
 */
class AjaxExampleDynamicSectionsDegardes extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_dynamicsectiondegardes';
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state, $no_js_use = FALSE) {
    $url = Url::fromUri('internal:/examples/ajax-example/dynamic-sections-no_js');
    $link = Link::fromTextAndUrl($this->t('examples/ajax-example/dynamic-sections-no-js'), $url)->toString();

    // Prepare link for multiple arguments.
    $urltwo = Url::fromUri('internal:/examples/ajax-example/dynamic-sections');
    $linktwo = Link::fromTextAndUrl($this->t('examples/ajax-example/dynamic-sections'), $urltwo)->toString();
    // Attach the CSS and JS we need to show this with and without javascript.
    // Without javascript we need an extra "Choose" button, and this is
    // hidden when we have javascript enabled.
    $form['#attached']['library'][] = 'ajax_example/ajax_example.dropdown';

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This example demonstrates a form which dynamically creates various sections based on the configuration in the form.
      It deliberately allows graceful degradation to a non-javascript environment.
      In a non-javascript environment, the "Choose" button next to the select control
      is displayed; in a javascript environment it is hidden by the module CSS.
      The basic idea here is that the form is built up based on
      the selection in the question_type_select field, and it is built the same
      whether we are in a javascript/AJAX environment or not.

      Try the @link and the @link1.', ['@link' => $linktwo, '@link1' => $link]),
    ];
    $form['question_type_select'] = [
      '#type' => 'select',
      '#title' => t('Question style'),
      '#options' => [
        'Choose question style' => 'Choose question style',
        'Multiple Choice' => 'Multiple Choice',
        'True/False' => 'True/False',
        'Fill-in-the-blanks' => 'Fill-in-the-blanks',
      ],

      '#ajax' => [
        'wrapper' => 'questions-fieldset-wrapper',
        'callback' => '::prompt',
      ],
    ];
    // The CSS for this module hides this next button if JS is enabled.
    $form['question_type_submit'] = [
      '#type' => 'submit',
      '#value' => t('Choose'),
      '#attributes' => ['class' => ['next-button']],
    // No need to validate when submitting this.
      '#limit_validation_errors' => [],
      '#validate' => [],
    ];

    // This simply allows us to demonstrate no-javascript use without
    // actually turning off javascript in the browser. Removing the #ajax
    // element turns off AJAX behaviors on that element and as a result
    // ajax.js doesn't get loaded.
    if ($no_js_use) {
      // Remove the #ajax from the above, so ajax.js won't be loaded.
      unset($form['question_type_select']['#ajax']);
    }

    // This fieldset just serves as a container for the part of the form
    // that gets rebuilt.
    $form['questions_fieldset'] = [
      '#type' => 'fieldset',
    // These provide the wrapper referred to in #ajax['wrapper'] above.
      '#prefix' => '<div id="questions-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];
    if (!empty($form_state->getValue('question_type_select'))) {

      $form['questions_fieldset']['question'] = [
        '#markup' => t('Who was the first president of the U.S.?'),
      ];
      $question_type = $form_state->getValue('question_type_select');

      switch ($question_type) {
        case 'Multiple Choice':
          $form['questions_fieldset']['question'] = [
            '#type' => 'radios',
            '#title' => t('Who was the first president of the United States'),
            '#options' => [
              'George Bush' => 'George Bush',
              'Adam McGuire' => 'Adam McGuire',
              'Abraham Lincoln' => 'Abraham Lincoln',
              'George Washington' => 'George Washington',
            ],

          ];
          break;

        case 'True/False':
          $form['questions_fieldset']['question'] = [
            '#type' => 'radios',
            '#title' => $this->t('Was George Washington the first president of the United States?'),
            '#options' => [
              'George Washington' => 'True',
              0 => 'False',
            ],
            '#description' => $this->t('Click "True" if you think George Washington was the first president of the United States.'),
          ];
          break;

        case 'Fill-in-the-blanks':
          $form['questions_fieldset']['question'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Who was the first president of the United States'),
            '#description' => $this->t('Please type the correct answer to the question.'),
          ];
          break;
      }

      $form['questions_fieldset']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit your answer'),
      ];
    }
    return $form;
  }

  /**
   * Final submit handler.
   *
   * Reports what values were finally set.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This is only executed when a button is pressed, not when the AJAXfield
    // select is changed.
    // Now handle the case of the next, previous, and submit buttons.
    // Only submit will result in actual submission, all others rebuild.
    if ($form_state->getValue('question_type_submit') == 'Choose') {
      $form_state->setValue('question_type_select', $form_state->getUserInput()['question_type_select']);
      $form_state->setRebuild();
    }

    if ($form_state->getValue('submit') == 'Submit your answer') {
      $form_state->setRebuild(FALSE);
      $answer = $form_state->getValue('question');
      print_r($answers);
      // Special handling for the checkbox.
      if ($answer == 1 && $form['questions_fieldset']['question']['#type'] == 'checkbox') {
        $answer = $form['questions_fieldset']['question']['#title'];
      }
      if ($answer == $this->t('George Washington')) {
        drupal_set_message($this->t('You got the right answer: @answer', ['@answer' => $answer]));
      }
      else {
        drupal_set_message($this->t('Sorry, your answer (@answer) is wrong', ['@answer' => $answer]));
      }
      return;
    }
    // Sets the form to be rebuilt after processing.
    $form_state->setRebuild();
  }

  /**
   * Callback for the select element.
   *
   * This just selects and returns the questions_fieldset.
   */
  public function prompt($form, $form_state) {
    return $form['questions_fieldset'];
  }

}
