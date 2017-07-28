<?php

namespace Drupal\ajax_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 *
 */
class AjaxExampleAddMore extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ajax_example_addmore';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $no_js_use = FALSE) {
    $url = Url::fromUri('internal:/examples/ajax-example/add-more-nojs/');
    $link = Link::fromTextAndUrl($this->t('non-js version'), $url)->toString();

    // Prepare link for multiple arguments.
    $urltwo = Url::fromUri('internal:/examples/ajax_example/add-more');
    $linktwo = Link::fromTextAndUrl($this->t('AJAX version'), $urltwo)->toString();
    $form['description'] = [
      '#markup' =>t('This example shows an add-more and a remove-last button. The @link does it without page reloads; the @link2 is the same code but simulates a non-javascript environment, showing it with page reloads.',
      ['@link' => $linktwo, '@link2' => $link]),

    ];

    // Because we have many fields with the same values, we have to set
    // #tree to be able to access them.
    $name_field = $form_state->get('num_names');
    $form['#tree'] = TRUE;
    $form['names_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => t('People coming to the picnic'),
    // Set up the wrapper so that AJAX will be able to replace the fieldset.
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    // Build the fieldset with the proper number of names. We'll use
    // $form_state['num_names'] to determine the number of textfields to build.
    if (empty($name_field)) {
      $name_field = $form_state->set('num_names', 1);
    }
    for ($i = 0; $i < $name_field; $i++) {
      $form['names_fieldset']['name'][$i] = [
        '#type' => 'textfield',
        '#title' => t('Name'),
      ];
    }
    $form['names_fieldset']['add_name'] = [
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => ['::ajax_example_add_more_add_one'],
    // See the examples in ajax_example.module for more details on the
    // properties of #ajax.
      '#ajax' => [
        'callback' => '::prompt',
        'wrapper' => 'names-fieldset-wrapper',
      ],
    ];
    if ($form['num_names'] > 1) {
      $form['names_fieldset']['remove_name'] = [
        '#type' => 'submit',
        '#value' => t('Remove one'),
        '#submit' => ['::ajax_example_add_more_remove_one'],
        '#ajax' => [
          'callback' => '::prompt',
          'wrapper' => 'names-fieldset-wrapper',
        ],
      ];
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];

    // This simply allows us to demonstrate no-javascript use without
    // actually turning off javascript in the browser. Removing the #ajax
    // element turns off AJAX behaviors on that element and as a result
    // ajax.js doesn't get loaded.
    // For demonstration only! You don't need this.
    if ($no_js_use) {
      // Remove the #ajax from the above, so ajax.js won't be loaded.
      if (!empty($form['names_fieldset']['remove_name']['#ajax'])) {
        unset($form['names_fieldset']['remove_name']['#ajax']);
      }
      unset($form['names_fieldset']['add_name']['#ajax']);
    }

    return $form;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function prompt($form, $form_state) {
    return $form['names_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function ajax_example_add_more_add_one($form, &$form_state) {
    $name_field = $form_state->get('num_names');
    $add_button = $name_field + 1;
    $form_state->set('num_names', $add_button);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function ajax_example_add_more_remove_one($form, &$form_state) {
    $name_field = $form_state->get('num_names');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_names', $remove_button);
    }
    $form_state->setRebuild(TRUE);
  }

  /**
   * Final submit handler.
   *
   * Reports what values were finally set.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $output = t('These people are coming to the picnic: @names',
    [
      '@names' => implode(', ', $form_state->getValue(['names_fieldset', 'name'])),
    ]
      );
    drupal_set_message($output);
  }



}
