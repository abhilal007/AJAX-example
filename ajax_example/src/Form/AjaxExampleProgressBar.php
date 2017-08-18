<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 *Progress bar example.
 */
class AjaxExampleProgressBar extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_progressbar';
  }

  /**
   * Build a landing-page form for the progress bar example.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['time'] = \Drupal::time()->getRequestTime();
    // We make a DIV which the progress bar can occupy. You can see this in use
    // in ajax_example_progressbar_callback().
    $form['status'] = [
      '#title' => $this->t("progress-status"),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
      // Here we set up our AJAX callback handler.
        'callback' => '::prompt',
      // Tell FormAPI about our progress bar.
        'progress' => [
          'type' => 'bar',
          'message' => $this->t('Execute..'),
        // Have the progress bar access this URL path.
          'url' => Url::fromUri('internal:/examples/ajax_example/progressbar/progress/' . $form['time']),
        // The time interval for the progress bar to check for updates.
          'interval' => 1000,
        ],
      ],
    ];

    return $form;

  }

  /**
   * Our submit handler.
   *
   * This handler spends some time changing a variable and sleeping, and then
   * finally returns a form element which marks the #progress-status DIV as
   * completed.
   *
   * While this is occurring, ajax_example_progressbar_progress() will be called
   * a number of times by the client-sid JavaScript, which will poll the variable
   * being set here.
   *
   * @see \Drupal\ajax_example\Controller\AjaxExampleController::progressbarProgress
   */
  public function prompt(array $form, FormStateInterface $form_state) {
    $variable_name = 'example_progressbar_' . $form['time'];
    $response = new AjaxResponse();
    $config = \Drupal::config('ajaxexample.settings');
    $config->set($variable_name, 10)->save();
    sleep(2);
    $config->set($variable_name, 40)->save();
    sleep(2);
    $config->set($variable_name, 70)->save();
    sleep(2);
    $config->set($variable_name, 90)->save();
    sleep(2);
    $response->addCommand(new HtmlCommand('#progress-status', $this->t('Executed.')));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
