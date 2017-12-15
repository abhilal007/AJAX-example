<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;

/**
 *
 */
class AjaxExampleNodeFormAlter extends FormBase {

  protected $connection;

  /**
   *
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_modeformalter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = $form['#node'];
    $form['ajax_example_1'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('AJAX Example 1'),
      '#description' => $this->t('Enable to show second field.'),
      '#default_value' => $node->ajax_example['example_1'],
      '#ajax' => [
        'callback' => '::ajax_example_form_node_callback',
        'wrapper' => 'ajax-example-form-node',
        'effect' => 'fade',
      ],
    ];
    $form['container'] = [
      '#prefix' => '<div id="ajax-example-form-node">',
      '#suffix' => '</div>',
    ];

    // If the state values exist and 'ajax_example_1' state value is 1 or
    // if the state values don't exist and 'example1' variable is 1 then
    // display the ajax_example_2 field.
    if (!empty($form_state->getValue('ajax_example_1')) && $form_state->getValue('ajax_example_1') == 1
      || empty($form_state->getValue()) && $node->ajax_example['example_1']) {

      $form['container']['ajax_example_2'] = [
        '#type' => 'textfield',
        '#title' => $this->t('AJAX Example 2'),
        '#description' => $this->t('AJAX Example 2'),
        '#default_value' => empty($form_state->getValue('ajax_example_2')) ? $node->ajax_example['example_2'] : $form_state->getValue('ajax_example_2'),
      ];
    }
  }

  /**
   * Callback of ajax.
   */
  public function ajax_example_form_node_callback($form, $form_state) {
    return $form['container'];
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue();
    // Move the new data into the node object.
    $node->ajax_example['example_1'] = $values['ajax_example_1'];
    // Depending on the state of ajax_example_1; it may not exist.
    $node->ajax_example['example_2'] = isset($values['ajax_example_2']) ? $values['ajax_example_2'] : '';
  }

  /**
   *
   */
  public function ajax_example_node_prepare($node) {
    if (empty($node->ajax_example)) {
      // Set default values, since this only runs when adding a new node.
      $node->ajax_example['example_1'] = 0;
      $node->ajax_example['example_2'] = '';
    }
  }

  /**
   * Implements hook_node_load().
   *
   * @see ajax_example_form_node_form_alter()
   */
  public function ajax_example_node_load($nodes, $types) {
    $result = $this->connection->query('SELECT * FROM {ajax_example_node_form_alter} WHERE nid IN(:nids)', [':nids' => array_keys($nodes)])->fetchAllAssoc('nid');

    foreach ($nodes as &$node) {
      $node->ajax_example['example_1']
      = isset($result[$node->nid]->example_1) ?
      $result[$node->nid]->example_1 : 0;
      $node->ajax_example['example_2']
      = isset($result[$node->nid]->example_2) ?
      $result[$node->nid]->example_2 : '';
    }
  }

  /**
   * Implements hook_node_insert().
   *
   * @see ajax_example_form_node_form_alter()
   */
  public function ajax_example_node_insert($node) {
    if (isset($node->ajax_example)) {
      $this->connection->insert('ajax_example_node_form_alter')
        ->fields([
          'nid' => $node->nid,
          'example_1' => $node->ajax_example['example_1'],
          'example_2' => $node->ajax_example['example_2'],
        ])
        ->execute();
    }
  }

  /**
   * Implements hook_node_update().
   *
   * @see ajax_example_form_node_form_alter()
   */
  public function ajax_example_node_update($node) {
    if ($this->connection->select('ajax_example_node_form_alter', 'a')->fields('a')->condition('nid', $node->nid, '=')->execute()->fetchAssoc()) {
      $this->connection->update('ajax_example_node_form_alter')
        ->fields([
          'example_1' => $node->ajax_example['example_1'],
          'example_2' => $node->ajax_example['example_2'],
        ])
        ->condition('nid', $node->nid)
        ->execute();
    }
    else {
      // Cleaner than doing it again.
      ajax_example_node_insert($node);
    }
  }

  /**
   * Implements hook_node_delete().
   *
   * @see ajax_example_form_node_form_alter()
   */
  public function ajax_example_node_delete($node) {
    $this->connection->delete('ajax_example_node_form_alter')
      ->condition('nid', $node->nid)
      ->execute();
  }

}
