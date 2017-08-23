<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Condition;
use Drupal\node\Entity\Node;

/**
 *An autocomplete form to look up nodes by title.
 *
 * An autocomplete form which looks up nodes by title in the node table,
 * but must keep track of the nid, because titles are certainly not guaranteed
 * to be unique.
 */
class AjaxExampleUniquecomplete extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_uniquecomplete';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['info'] = [
      '#markup' => '<div>' . t("This example does a node autocomplete by title. The difference between this and a username autocomplete is that the node title may not be unique, so we have to use the nid for uniqueness, placing it in a parseable location in the textfield.") . '</div>',
    ];

    $form['node'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Choose a node by title'),
    // The autocomplete path is provided in hook_menu in ajax_example.module.
      '#autocomplete_path' => 'examples/ajax_example/unique_node_autocomplete_callback',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue('node');
    $matches = [];

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
    }
    // BUT: Not everybody will have javascript turned on, or they might hit ESC
    // and not use the autocomplete values offered. In that case, we can attempt
    // to come up with a useful value. This is not absolutely necessary, and we
    // *could* just emit a form_error() as below.
    else {
      $db = Database::getConnection();
      $nid = $db->select('node')
        ->fields('node', ['nid'])
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
      $form_state->setErrorByName($form['node'], $this->t('Sorry, no node starting with %title can be found', ['%title' => $title]));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = Node::load($form_state->getValue(['values', 'node']));
    drupal_set_message($this->t('You found node %nid with title %title', ['%nid' => $node->nid, '%title' => $node->title]));
  }

}
