<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\User;

/**
 *
 */
class AjaxExampleAutocompleteAuthor extends FormBase {

/**
 * {@inheritdoc}
 */
public function getFormID() {
  return 'ajax_example_autocompleteauthor';
}

/**
 * {@inheritdoc}
 */
public function buildForm(array $form, FormStateInterface $form_state) {
  $form['intro'] = [
    '#markup' => '<div>' . t("This example uses a user autocomplete to dynamically change a node title autocomplete using #ajax.
      This is a way to get past the fact that we have no other way to provide context to the autocomplete function.
      It won't work very well unless you have a few users who have created some content that you can search for.") . '</div>',
  ];

  $form['author'] = [
    '#type' => 'textfield',
    '#title' => t('Choose the username that authored nodes you are interested in'),
  // Since we just need simple user lookup, we can use the simplest function
  // of them all, user_autocomplete().
    '#autocomplete_path' => 'user/autocomplete',
    '#ajax' => [
      'callback' => '::ajax_example_node_by_author_ajax_callback',
      'wrapper' => 'autocomplete-by-node-ajax-replace',
    ],
  ];

  // This form element with autocomplete will be replaced by #ajax whenever the
  // author changes, allowing the search to be limited by user.
  $form['node'] = [
    '#type' => 'textfield',
    '#title' => t('Choose a node by title'),
    '#prefix' => '<div id="autocomplete-by-node-ajax-replace">',
    '#suffix' => '</div>',
    '#disabled' => TRUE,
  ];

  // When the author changes in the author field, we'll change the
  // autocomplete_path to match.
  if (!empty($form_state['values']['author'])) {
    $author = user_load_by_name($form_state['values']['author']);
    if (!empty($author)) {
      $autocomplete_path = 'examples/ajax_example/node_by_author_autocomplete/' . $author->uid;
      $form['node']['#autocomplete_path'] = $autocomplete_path;
      $form['node']['#title'] = t('Choose a node title authored by %author', ['%author' => $author->name]);
      $form['node']['#disabled'] = FALSE;
    }
  }

  $form['actions'] = [
    '#type' => 'actions',
  ];

  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Submit'),
  ];

  return $form;
}

/**
 * {@inheritdoc}
 */
public function validateForm(array &$form, FormStateInterface $form_state) {
  $title = $form_state['values']['node'];
  $author = $form_state['values']['author'];
  $matches = [];

  // We must have a valid user.
  $account = user_load_by_name($author);
  if (empty($account)) {
    $form_state->setErrorByName($form['author'], t('You must choose a valid author username'));
    return;
  }
  // This preg_match() looks for the last pattern like [33334] and if found
  // extracts the numeric portion.
  $result = preg_match('/\[([0-9]+)\]$/', $title, $matches);
  if ($result > 0) {
    // If $result is nonzero, we found a match and can use it as the index into
    // $matches.
    $nid = $matches[$result];
    // Verify that it's a valid nid.
    $node = Node::load($nid);
    if (empty($node)) {
      $form_state->setErrorByName($form['node'], t('Sorry, no node with nid %nid can be found', ['%nid' => $nid]));
      return;
    }
    // BUT: Not everybody will have javascript turned on, or they might hit ESC
    // and not use the autocomplete values offered. In that case, we can attempt
    // to come up with a useful value. This is not absolutely necessary, and we
    // *could* just emit a form_error() as below. Here we'll find the *first*
    // matching title and assume that is adequate.
    else {
      $db = Database::getConnection();
      $nid = $db->select('node')
        ->fields('node', ['nid'])
        ->condition('uid', $account->uid)
        ->condition('title', $db->escapeLike($title) . '%', 'LIKE')
        ->range(0, 1)
        ->execute()
        ->fetchField();
    }

    // Now, if we somehow found a nid, assign it to the node. If we failed, emit
    // an error.
    if (!empty($nid)) {
      $form_state->setValue('node', $nid);
    }
    else {
      $form_state->setErrorByName($form['node'], t('Sorry, no node starting with %title can be found', ['%title' => $title]));
    }

  }

  /**
   * AJAX callback for author form element.
   */
  public function ajax_example_node_by_author_ajax_callback($form, $form_state) {
    return $form['node'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = Node::load($form_state->getValue('node'));
    $account = User::load($node->uid);
    drupal_set_message(t('You found node %nid with title !title_link, authored by !user_link',
    [
      '%nid' => $node->nid,
      '!title_link' => l($node->title, 'node/' . $node->nid),
      '!user_link' => theme('username', ['account' => $account]),
    ]
    ));
  }

}
