<?php

namespace Drupal\commerce_stock_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class StockTransactions2 extends FormBase {

  /**
   * The product variation storage.
   *
   * @var \Drupal\commerce_product\ProductVariationStorage
   */
  protected $productVariationStorage;

  /**
   * The stock manager.
   *
   * @var \Drupal\commerce_stock\StockManager
   */
  protected $stockManager;

  /**
   * Constructs a StockTransactions2 object.
   */
  public function __construct() {
    $this->productVariationStorage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_variation');
    $this->stockManager = \Drupal::service('commerce.stock_manager');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_stock_transactions2';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $request = \Drupal::request();
    if ($request->query->has('commerce_product_v_id')) {
      $variation_id = $request->query->get('commerce_product_v_id');
    }
    else {
      return $this->redirect('commerce_stock_ui.stock_transactions1');
    }

    $product_variation = $this->productVariationStoragetorage->load($variation_id);
    $stockService = $this->stockManager->getService($product_variation);
    $locations = $stockService->getStockChecker()->getLocationList(TRUE);
    $location_options = [];
    foreach ($locations as $location_id => $location) {
      $location_options[$location_id] = $location['name'];
    }

    $form['transaction_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Transaction type'),
      '#options' => [
        'receiveStock' => $this->t('Receive stock'),
        'sellStock' => $this->t('Sell stock'),
        'returnStock' => $this->t('Return stock'),
        'moveStock' => $this->t('Move stock'),
      ],
    ];
    $form['product_variation_id'] = [
      '#type' => 'value',
      '#value' => $variation_id,
    ];
    $form['source'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Source location'),
    ];
    $form['source']['source_location'] = [
      '#type' => 'select',
      '#title' => $this->t('Location'),
      '#options' => $location_options,
    ];
    $form['source']['source_zone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zone/Bins'),
      '#description' => $this->t('The location zone (bins)'),
      '#size' => 60,
      '#maxlength' => 50,
    ];
    $form['target'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Move target'),
      '#states' => [
        'visible' => [
          ':input[name="transaction_type"]' => ['value' => 'moveStock'],
        ],
      ],
    ];
    $form['target']['target_location'] = [
      '#type' => 'select',
      '#title' => $this->t('Target Location'),
      '#options' => $location_options,
    ];
    $form['target']['target_zone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zone/Bins'),
      '#description' => $this->t('The location zone (bins)'),
      '#size' => 60,
      '#maxlength' => 50,
    ];
    $form['user'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Optional user'),
      '#target_type' => 'user',
      '#selection_handler' => 'default',
      '#states' => [
        'visible' => [
          ':input[name="transaction_type"]' => [
            ['value' => 'sellStock'],
            ['value' => 'returnStock'],
          ],
        ],
      ],

    ];
    $form['order'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Optional order number'),
      '#target_type' => 'commerce_order',
      '#selection_handler' => 'default',
      '#states' => [
        'visible' => [
          ':input[name="transaction_type"]' => [
            ['value' => 'sellStock'],
            ['value' => 'returnStock'],
          ],
        ],
      ],
    ];
    $form['transaction_qty'] = [
      '#type' => 'number',
      '#title' => $this->t('Quentity'),
      '#default_value' => '1',
      '#step' => '0.01',
      '#required' => TRUE,
    ];
    $form['transaction_note'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Note'),
      '#description' => $this->t('A note for the transaction'),
      '#maxlength' => 64,
      '#size' => 64,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $transaction_type = $form_state->getValue('transaction_type');
    $product_variation_id = $form_state->getValue('product_variation_id');
    $source_location = $form_state->getValue('source_location');
    $source_zone = $form_state->getValue('source_zone');
    $qty = $form_state->getValue('transaction_qty');
    $transaction_note = $form_state->getValue('transaction_note');
    $product_variation = $this->productVariationStorage->load($product_variation_id);
    if ($transaction_type == 'receiveStock') {
      $this->stockManager->receiveStock($product_variation, $source_location, $source_zone, $qty, NULL, $transaction_note);
    }
    elseif ($transaction_type == 'sellStock') {
      $order_id = $form_state->getValue('order');;
      $user_id = $form_state->getValue('user');;
      $this->stockManager->sellStock($product_variation, $source_location, $source_zone, $qty, NULL, $order_id, $user_id, $transaction_note);
    }
    elseif ($transaction_type == 'returnStock') {
      $order_id = $form_state->getValue('order');;
      $user_id = $form_state->getValue('user');;
      $this->stockManager->returnStock($product_variation, $source_location, $source_zone, $qty, NULL, $order_id, $user_id, $transaction_note);
    }
    elseif ($transaction_type == 'moveStock') {
      $target_location = $form_state->getValue('target_location');
      $target_zone = $form_state->getValue('target_zone');
      $this->stockManager->moveStock($product_variation, $source_location, $target_location, $source_zone, $target_zone, $qty, NULL, $transaction_note);
    }
  }

}
