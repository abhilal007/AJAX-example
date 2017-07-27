<?php

namespace Drupal\ajax_example\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\examples\Utility\DescriptionTemplateTrait;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller routines for block example routes.
 */
class AjaxExampleController extends ControllerBase {
  use DescriptionTemplateTrait;

  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'ajax_example';
  }

  /**
   * Get the progress bar execution status, as JSON.
   *
   * This is the menu handler for
   * examples/ajax_example/progressbar/progress/$time.
   *
   * This function is our wholly arbitrary job that we're checking the status for.
   * In this case, we're reading a system variable that is being updated by
   * ajax_example_progressbar_callback().
   *
   * We set up the AJAX progress bar to check the status every second, so this
   * will execute about once every second.
   *
   * The progress bar JavaScript accepts two values: message and percentage. We
   * set those in an array and in the end convert it JSON for sending back to the
   * client-side JavaScript.
   *
   * @param int $time
   *   Timestamp.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function progressbarProgress($time) {
    $progress = [
      'message' => t('Starting execute...'),
      'percentage' => -1,
    ];

    $completed_percentage = \Drupal::config('ajaxexample.settings')->get('example_progressbar_' . $time);

    if ($completed_percentage) {
      $progress['message'] = t('Executing...');
      $progress['percentage'] = $completed_percentage;
    }

    return new JsonResponse($progress);
  }

}
