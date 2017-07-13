<?php

/**
 * @file
 * Contains \Drupal\ajax_example\Controller\AjaxExampleController.
 */

namespace Drupal\ajax_example\Controller;

use \Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation;

/**
 * Controller routines for block example routes.
 */
class AjaxExampleController extends ControllerBase{

  /**
   * A simple controller method to explain what the block example is about.
   */
  public function description() {
    $output['intro']['#markup'] = $this->t('The AJAX example module provides many examples of AJAX including forms, links, and AJAX commands.');
    $output['list']['#theme'] = 'item_list';
    $output['list']['#items'][] = \Drupal::l(t('Simplest AJAX Example'), Url::fromRoute('ajax_example.simplest'));
    return $output;
  }
public function ajax_example_progressbar_progress($time) {
  $variable_name = '';
  $progress = array(
    'message' => $this->t('Starting execute...'),
    'percentage' => -1,
  );

  $completed_percentage = \Drupal::config('example_progressbar_' . $time, 0)->get($variable_name);
  if ($completed_percentage) {
    $progress['message'] = $this->t('Executing...');
    $progress['percentage'] = $completed_percentage;
  }

  \Drupal::JsonResponse($progress);
}
}
