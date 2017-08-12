<?php

namespace Drupal\ajax_example\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\examples\Utility\DescriptionTemplateTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;
use Drupal\Component\Utility\SafeMarkup;


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

  /**
   * Demonstrates a clickable AJAX-enabled link using the 'use-ajax' class.
   *
   * Because of the 'use-ajax' class applied here, the link submission is done
   * without a page refresh.
   *
   * When using the AJAX framework outside the context of a form or a renderable
   * array of type 'link', you have to include ajax.js explicitly.
   *
   * @return array
   *   Form API array.
   *
   * @ingroup ajax_example
   */
  public function ajaxExampleRenderLinkRa() {

    $build['my_div'] = [
      '#markup' => $this->t('The link below has been rendered as an element with the #ajax property, so if
javascript is enabled, ajax.js will try to submit it via an AJAX call instead
of a normal page load. The URL also contains the "/nojs/" magic string, which
is stripped if javascript is enabled, allowing the server code to tell by the
URL whether JS was enabled or not, letting it do different things based on that.'),
    ];
    $build['ajax_link'] = [
      '#type' => 'link',
      '#title' => t('Click here'),
    // Note the /nojs portion of the href - if javascript is enabled,
    // this part will be stripped from the path before it is called.
      '#href' => 'ajax_link_callback/nojs/',
      '#id' => 'ajax_link',
      '#ajax' => [
        'wrapper' => 'myDiv',
        'method' => 'html',
      ],
    ];
    return $build;
  }

  /**
   *
   */
  public function ajaxExampleRenderLink() {
    $attachments['#attached']['library'][] = 'system/drupal.ajax';
    $explanation = $this->t("
The link below has the <i>use-ajax</i> class applied to it, so if
javascript is enabled, ajax.js will try to submit it via an AJAX call instead
of a normal page load. The URL also contains the '/nojs/' magic string, which
is stripped if javascript is enabled, allowing the server code to tell by the
URL whether JS was enabled or not, letting it do different things based on that.");
    $output = "<div>" . $explanation . "</div>";
    // The use-ajax class is special, so that the link will call without causing
    // a page reload. Note the /nojs portion of the path - if javascript is
    // enabled, this part will be stripped from the path before it is called.
    $value = ['attributes' => ['class' => ['use-ajax']]];
    $url = Url::fromUri('internal:/ajax_link_callback/nojs/', $value);
    $link = Link::fromTextAndUrl($this->t('Click here'), $url)->toString();
    $output .= "<div id='myDiv'></div><div>$link</div>";
    return $output;
  }

  /**
   * Callback for link example.
   *
   * Takes different logic paths based on whether Javascript was enabled.
   * If $type == 'ajax', it tells this function that ajax.js has rewritten
   * the URL and thus we are doing an AJAX and can return an array of commands.
   *
   * @param string $type
   *   Either 'ajax' or 'nojs. Type is simply the normal URL argument to this URL.
   *
   * @return string|array
   *   If $type == 'ajax', returns an array of AJAX Commands.
   *   Otherwise, just returns the content, which will end up being a page.
   *
   * @ingroup ajax_example
   */
  public function ajaxlinkresponse($type = 'ajax') {
    if ($type == 'ajax') {
      $output = $this->t("This is some content delivered via AJAX");
      $response = new AjaxResponse();
      $response->addCommand(new AppendCommand('#myDiv', $output));

      // See ajax_example_advanced.inc for more details on the available commands
      // and how to use them.
      // $page = array('#type' => 'ajax', '#commands' => $commands);
      // ajax_deliver($response);
      return $response;
    }
    else {
      $output = $this->t("This is some content delivered via a page load.");
      return $output;
    }
  }

  public function ajax_example_simple_user_autocomplete_callback($string = "") {

  $matches = array();
  $string = "root";
  if ($string) {
    $db = \Drupal::database();
    $result = $db->select('users')
      ->fields('users', array('name', 'uid'))
      ->condition('name', $db->like($string) . '%', 'LIKE')
      ->range(0, 10)
      ->execute();
    foreach ($result as $user) {
      // In the simplest case (see user_autocomplete), the key and the value are
      // the same. Here we'll display the uid along with the username in the
      // dropdown.
      $matches[] = array('value' => $user->name, 'label' => $user->uid);
      $matches[$user->name] = SafeMarkup::check_plain($user->name) . " (uid=$user->uid)";
    }
  }

  return new JsonResponse($matches);
}

}
