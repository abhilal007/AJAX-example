<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A simple autocomplete form which just looks up usernames in the user table.
 */
class AjaxExampleAutocomplete extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_autocomplete';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['info'] = [
      '#markup' => '<div>' . $this->t("This example does a simplest possible autocomplete by username. You'll need a few users on your system for it to make sense.") . '</div>',
    ];

    $form['user'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Choose a user (or a people, depending on your usage preference)'),
    // The autocomplete path is provided in routing.
      '#autocomplete_path' => 'examples/ajax_example/simple_user_autocomplete_callback',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
