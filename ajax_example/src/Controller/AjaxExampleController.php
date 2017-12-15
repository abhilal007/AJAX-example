<?php

namespace Drupal\ajax_example\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\examples\Utility\DescriptionTemplateTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Controller routines for AJAX example routes.
 */
class AjaxExampleController extends ControllerBase {
  use DescriptionTemplateTrait;
  protected $connection;

  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'ajax_example';
  }

  /**
   *
   */

  public function basicInstructions() {
    return [
      $this->description(),
    ];
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
      'message' => $this->t('Starting execute...'),
      'percentage' => -1,
    ];

    $completed_percentage = \Drupal::config('ajaxexample.settings')->get('example_progressbar_' . $time);

    if ($completed_percentage) {
      $progress['message'] = $this->t('Executing...');
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
      '#markup' => $this->t('
The link below has been rendered as an element with the #ajax property, so if
javascript is enabled, ajax.js will try to submit it via an AJAX call instead
of a normal page load. The URL also contains the "/nojs/" magic string, which
is stripped if javascript is enabled, allowing the server code to tell by the
URL whether JS was enabled or not, letting it do different things based on that.'),
    ];
    $build['ajax_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Click here'),
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

/**
   * It works simply by searching usernames (and of course in Drupal usernames
   * are unique, so can be used for identifying a record.)
   *
   * The returned $matches array has
   * * key: string which will be displayed once the autocomplete is selected
   * * value: the value which will is displayed in the autocomplete pulldown.
   *
   * In the simplest cases (see drupal/ajax_example/form/AjaxExampleAutocomplete.php)
   * these are the same, andnothing needs to be done. However, more complicated
   * autocompletes require more work. Here we demonstrate the difference by
   * displaying the UID along with the username in the dropdown.
   *
   * In the end, though, we'll be doing something with the value that ends up in
   * the textfield, so it needs to uniquely identify the record we want to access.
   * This is demonstrated in ajax_example_unique_autocomplete().
   *
   * @param string $string
   *   The string that will be searched.
   */
  public function ajax_example_simple_user_autocomplete_callback(Connection $connection, $string = "") {
    $this->connection = Connection::$connection;
    $matches = [];

    if ($string) {

      $result = $this->connection->select('users')
        ->fields('users', ['name', 'uid'])
        ->condition('name', $this->connection->like($string) . '%', 'LIKE')
        ->range(0, 10)
        ->execute();
      foreach ($result as $user) {
        // In the simplest case (see user_autocomplete), the key and the value are
        // the same. Here we'll display the uid along with the username in the
        // dropdown.
        $matches[] = ['value' => $user->name, 'label' => $user->uid];
        $matches[$user->name] = SafeMarkup::check_plain($user->name) . " (uid=$user->uid)";
      }
    }

    return new JsonResponse($matches);
  }

  /**
   * Autocomplete callback for nodes by title.
   *
   * Searches for a node by title, but then identifies it by nid, so the actual
   * returned value can be used later by the form.
   *
   * The returned $matches array has
   * - key: The title, with the identifying nid in brackets, like "Some node
   *   title [3325]"
   * - value: the title which will is displayed in the autocomplete pulldown.
   *
   * Note that we must use a key style that can be parsed successfully and
   * unambiguously. For example, if we might have node titles that could have
   * [3325] in them, then we'd have to use a more restrictive token.
   *
   * @param string $string
   *   The string that will be searched.
   */
  public function ajax_example_unique_node_autocomplete_callback($string = "") {
    $this->connection = Connection::$connection;
    $matches = [];
    if ($string) {

      $result = $this->connection->select('node')
        ->fields('node', ['nid', 'title'])
        ->condition('title', $this->connection->escapeLike($string) . '%', 'LIKE')
        ->range(0, 10)
        ->execute();
      foreach ($result as $node) {
        $matches[$node->title . " [$node->nid]"] = SafeMarkup::check_plain($node->title);
      }
    }

    return new JsonResponse($matches);
  }

  /**
   *
   */
  public function ajax_example_node_by_author_node_autocomplete_callback($author_uid, $string = "") {
    $this->connection = Connection::$connection;
    $matches = [];
    if ($author_uid > 0 && trim($string)) {
      $db = Database::getConnection();
      $result = $db->select('node')
        ->fields('node', ['nid', 'title'])
        ->condition('uid', $author_uid)
        ->condition('title', $db->escapeLike($string) . '%', 'LIKE')
        ->range(0, 10)
        ->execute();
      foreach ($result as $node) {
        $matches[$node->title . " [$node->nid]"] = SafeMarkup::check_plain($node->title);
      }
    }

    return new JsonResponse($matches);
  }

}
