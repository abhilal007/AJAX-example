<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 *
 */
class AjaxExampleWizard extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_wizard';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $no_js_use = FALSE) {
    $url = Url::fromUri('internal:/examples/ajax-example/wizard-nojs');
    $link = Link::fromTextAndUrl($this->t('examples/ajax-example/wizard-nojs'), $url)->toString();

    // Prepare link for multiple arguments.
    $urltwo = Url::fromUri('internal:/examples/ajax-example/wizard');
    $linktwo = Link::fromTextAndUrl($this->t('examples/ajax-example/wizard'), $urltwo)->toString();

    $form['#prefix'] = '<div id="wizard-form-wrapper">';
    $form['#suffix'] = '</div>';
    // We want to deal with hierarchical form values.
    $form['#tree'] = TRUE;
    $form['description'] = [
      '#markup' =>t('This example is a step-by-step wizard. The @link does it without page reloads; the @link1 is the same code but simulates a non-javascript environment, showing it with page reloads.', ['@link' => $linktwo, '@link1' => $link]),
    ];

    // $form_state['storage'] has no specific drupal meaning, but it is
    // traditional to keep variables for multistep forms there.
    //$step['step'] = !empty($form_state->getStorage()) ? 1 : $form_state->getStorage() ;

    //$form_state->setStorage($step);

    switch ($step['step']) {
      case 1:
        $form['step1'] = [
          '#type' => 'fieldset',
          '#title' => t('Step 1: Personal details'),
        ];
        $form['step1']['name'] = [
          '#type' => 'textfield',
          '#title' => t('Your name'),
          '#default_value' => empty($form_state->getValue(['step1', 'name']) ? '' : $form_state->getValue(['step1', 'name'])),
          '#required' => TRUE,
        ];
        break;

      case 2:
        $form['step2'] = [
          '#type' => 'fieldset',
          '#title' => t('Step 2: Street address info'),
        ];
        $form['step2']['address'] = [
          '#type' => 'textfield',
          '#title' => t('Your street address'),
          '#default_value' => empty($form_state->getValue(['step1', 'address']) ? '' : $form_state->getValue(['step1', 'address'])),
          '#required' => TRUE,
        ];
        break;

      case 3:
        $form['step3'] = [
          '#type' => 'fieldset',
          '#title' => t('Step 3: City info'),
        ];
        $form['step3']['city'] = [
          '#type' => 'textfield',
          '#title' => t('Your city'),
          '#default_value' => empty($form_state->getValue(['step1', 'city']) ? '' : $form_state->getValue(['step1', 'city'])),
          '#required' => TRUE,
        ];
        break;
    }
    if ($step == 3) {
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => t("Submit your information"),
      ];
    }
    if ($step < 3) {
      $form['next'] = [
        '#type' => 'submit',
        '#value' => t('Next step'),
        '#ajax' => [
          'wrapper' => 'wizard-form-wrapper',
          'callback' => '::prompt',
        ],
      ];
    }
    if ($step > 1) {
      $form['prev'] = [
        '#type' => 'submit',
        '#value' => t("Previous step"),

      // Since all info will be discarded, don't validate on 'prev'.
        '#limit_validation_errors' => [],
      // #submit is required to use #limit_validation_errors.
        '#submit' => ['ajax_example_wizard_submit'],
        '#ajax' => [
          'wrapper' => 'wizard-form-wrapper',
          'callback' => '::prompt',
        ],
      ];
    }

    // This simply allows us to demonstrate no-javascript use without
    // actually turning off javascript in the browser. Removing the #ajax
    // element turns off AJAX behaviors on that element and as a result
    // ajax.js doesn't get loaded.
    // For demonstration only! You don't need this.
    if ($no_js_use) {
      // Remove the #ajax from the above, so ajax.js won't be loaded.
      // For demonstration only.
      unset($form['next']['#ajax']);
      unset($form['prev']['#ajax']);
    }

    return $form;
  }

  /**
   * Wizard callback function.
   *
   * @param array $form
   *   Form API form.
   * @param array $form_state
   *   Form API form.
   *
   * @return array
   *   Form array.
   */
  public function prompt($form, $form_state) {
    return $form;
  }

  /**
   * Submit function for ajax_example_wizard.
   *
   * In AJAX this is only submitted when the final submit button is clicked,
   * but in the non-javascript situation, it is submitted with every
   * button click.
   */
  public function ajax_example_wizard_submit($form, &$form_state) {

    // Save away the current information.
    $current_step = 'step' . $form_state->getStorage('step');
    if (!empty($form_state->getValue($current_step))) {
      $form_state->getStorage($current_step, $form_state->getValue($current_step));
    }

    // Increment or decrement the step as needed. Recover values if they exist.
    if ($form_state->settriggering_element('#value') == t('Next step')) {
      //$form_state->getStorage('step')++;
      // If values have already been entered for this step, recover them from
      // $form_state['storage'] to pre-populate them.
      $step_name = 'step' . $form_state->getStorage('step');
      if (!empty($form_state->getStorage($step_name, $form_state->getValue($step_name)))) {
        $form_state->setStorage($step_name, $form_state->getValue($step_name));
      }
    }
    if ($form_state->settriggering_element('#value') == t('Previous step')) {
      //$form_state['storage']['step']--;
      // Recover our values from $form_state['storage'] to pre-populate them.
      $step_name = 'step' . $form_state->getStorage('step');
      $form_state->getStorage($step_name, $form_state->getValue($step_name));
    }

    // If they're done, submit.
    if ($form_state->settriggering_element('#value') == t('Submit your information')) {
      $value_message = t('Your information has been submitted:') . ' ';
      foreach ($form_state->getStorage($form_state->getValue()) as $step => $values) {
        $value_message .= "$step: ";
        foreach ($values as $key => $value) {
          $value_message .= "$key=$value, ";
        }
      }
      drupal_set_message($value_message);
      $form_state->setRebuild(FALSE);
      return;
    }

    // Otherwise, we still have work to do.
    $form_state->setRebuild(TRUE);
  }
public function submitForm(array &$form, FormStateInterface $form_state) {

}
}
