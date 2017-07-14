<?php

/**
 * @file
 * Contains \Drupal\ajax_example\Form\AjaxExampleProgressBar.
 */

namespace Drupal\ajax_example\Form;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class AjaxExampleProgressBar extends FormBase {

   /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_progressbar';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['time'] =  \Drupal::time()->getRequestTime();
  // We make a DIV which the progress bar can occupy. You can see this in use
  // in ajax_example_progressbar_callback().
  $form['status'] = array(
    '#title' => $this->t("progress-status"),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => $this->t('Submit'),
    '#ajax' => array(
      // Here we set up our AJAX callback handler.
      'callback' => 'ajax_example_progressbar_callback',
      // Tell FormAPI about our progress bar.
      'progress' => array(
        'type' => 'bar',
        'message' => $this->t('Execute..'),
        // Have the progress bar access this URL path.
        'url' => Url::fromUri('internal:/examples/ajax_example/progressbar/progress/' . $form['time'] ),
        // The time interval for the progress bar to check for updates.
        'interval' => 1000,
      ),
    ),
  );

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
 * @see ajax_example_progressbar_progress()
 */
public function ajax_example_progressbar_callback($form, &$form_state) {
  $variable_name = 'example_progressbar_' . $form['time'];
  $commands = array();
  \Drupal::config('ajaxexample.settings')
  ->set($variable_name, 10)
  ->save();
  sleep(2);
  \Drupal::config('ajaxexample.settings')
  ->set($variable_name, 40)
  ->save();
  sleep(2);
  \Drupal::config('ajaxexample.settings')
  ->set($variable_name, 70)
  ->save();
  sleep(2);
  \Drupal::config('ajaxexample.settings')
  ->set($variable_name, 90)
  ->save();
  sleep(2);

  $commands[] = new HtmlCommand('#progress-status', $this->t('Executed.'));

  return array(
    '#type' => 'ajax',
    '#commands' => $commands,
  );
}

public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}
