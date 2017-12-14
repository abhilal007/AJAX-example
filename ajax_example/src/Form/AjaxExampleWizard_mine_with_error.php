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
      '#markup' =>$this->t('This example is a step-by-step wizard. The @link does it without page reloads; the @link1 is the same code but simulates a non-javascript environment, showing it with page reloads.', ['@link' => $linktwo, '@link1' => $link]),
    ];

    // $form_state['storage'] has no specific drupal meaning, but it is
    // traditional to keep variables for multistep forms there.
    //$step['step'] = !empty($form_state->getStorage()) ? $form_state->getStorage() : 1 ;

    //$form_state->setStorage($step);
    $form['step'] = [
      '#type' => 'hidden',
      '#value' => !empty($form_state->getValue('step')) ? $form_state->getValue('step') : 1,
    ];
    if(empty($form_state->getValue('step')))
      $form_state->setValue('step', 1);
    $step_name = $form_state->getValue('step');
    switch ($step_name) {
      case 1:
        $form['step1'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Step 1: Personal details'),
        ];
        $form['step1']['name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Your name'),
          '#default_value' => empty($form_state->getValue(['step1', 'name']) ? '' : $form_state->getValue(['step1', 'name'])),
          '#required' => TRUE,
        ];
        break;

      case 2:
        $form['step2'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Step 2: Street address info'),
        ];
        $form['step2']['address'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Your street address'),
          '#default_value' => empty($form_state->getValue(['step1', 'address']) ? '' : $form_state->getValue(['step1', 'address'])),
          '#required' => TRUE,
        ];
        break;

      case 3:
        $form['step3'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Step 3: City info'),
        ];
        $form['step3']['city'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Your city'),
          '#default_value' => empty($form_state->getValue(['step1', 'city']) ? '' : $form_state->getValue(['step1', 'city'])),
          '#required' => TRUE,
        ];
        break;
    }
    if ($step_name == 3) {
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t("Submit your information"),
      ];
    }
    if ($step_name < 3) {
      $form['next'] = [
        '#type' => 'submit',
        '#value' => $this->t('Next step'),
        '#ajax' => [
          'wrapper' => 'wizard-form-wrapper',
          'callback' => '::functionajax',
        ],
      ];
    }
    if ($step_name > 1) {
      $form['prev'] = [
        '#type' => 'submit',
        '#value' => $this->t("Previous step"),

      // Since all info will be discarded, don't validate on 'prev'.
        '#limit_validation_errors' => [],
      // #submit is required to use #limit_validation_errors.
        '#submit' => ['::submitForm'],
        '#ajax' => [
          'wrapper' => 'wizard-form-wrapper',
          'callback' => '::fuctionajax',
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
  public function functionajax($form, $form_state) {
    if ($form_state->getTriggeringElement()['#value'] == $this->t('Next step')) {
        $inc = $form_state->getValue('step');
        $inc++;
        $form_state->setValue('step', $inc);
      }
      elseif ($form_state->getTriggeringElement()['#value']->__toString() == $this->t('Previous step')) {
        $dec = $form_state->getValue('step');
        $dec--;
        $form_state->setValue('step', $dec);
      }
    return $form;
  }

  /**
   * Submit function for ajax_example_wizard.
   *
   * In AJAX this is only submitted when the final submit button is clicked,
   * but in the non-javascript situation, it is submitted with every
   * button click.
   */


    // Save away the current information.

public function submitForm(array &$form, FormStateInterface $form_state) {
      /*if ($form_state->getTriggeringElement()['#value'] == $this->t('Next step')) {
        $inc = $form_state->getValue('step');
        $inc++;
        $form_state->setValue('step', $inc);
      }
      elseif ($form_state->getTriggeringElement()['#value']->__toString() == $this->t('Previous step')) {
        $dec = $form_state->getValue('step');
        $dec--;
        $form_state->setValue('step', $dec);
      }*/
      if ($form_state->getTriggeringElement()['#value'] == $this->t('Submit your information')) {
      $value_message = $this->t('Your information has been submitted:') . ' ';
      foreach ($form_state->getValue('value') as $step => $values) {
        $value_message .= "$step: ";
        foreach ($values as $key => $value) {
          $value_message .= "$key=$value, ";
        }
      }
      drupal_set_message($value_message);
      $form_state->setRebuild(FALSE);
      // Redirect to #action, else return.
      return;
    }

  }

}
