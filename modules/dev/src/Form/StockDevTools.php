<?php

namespace Drupal\commerce_stock_dev\Form;

use Drupal\commerce\Context;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_stock_storage\StockStorageAPI;

class StockDevTools extends FormBase {

  /**
   * The availability manager.
   *
   * @var \Drupal\commerce\AvailabilityManager
   */
  protected $availabilityManager;

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\Entity\Store
   */
  protected $currentStore;

  /**
   * The stock manager.
   *
   * @var \Drupal\commerce_stock\StockManager
   */
  protected $stockManager;

  /**
   * The Commerce Stock Storage API.
   *
   * @var \Drupal\commerce_stock_storage\StockStorageAPI
   */
  protected $stockStorageApi;

  /**
   * The Commerce Stock stock availability checker.
   *
   * @var \Drupal\commerce_stock\StockAvailabilityChecker
   */
  protected $stockAvailabilityChecker;

  /**
   * The product variation storage.
   *
   * @var \Drupal\commerce_product\ProductVariationStorage
   */
  protected $variationStorage;

  /**
   * Constructs a new StockDevTools form.
   */
  public function __construct() {
    $this->availabilityManager = \Drupal::service('commerce.availability_manager');
    $this->currentStore = \Drupal::service('commerce_store.store_context')->getStore();
    $this->stockAvailabilityChecker = \Drupal::service('commerce.availability_checker.stock_availability_checker');
    $this->stockManager = \Drupal::service('commerce.stock_manager');
    $this->stockStorageApi = new StockStorageAPI();
    $this->variationStorage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_variation');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_stock_dev_tools';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['s_api'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock API'),
    ];

    // Availability.
    $form['s_api']['availability'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock Availability'),
    ];
    $form['s_api']['availability']['prod_vid'] = [
      '#type' => 'number',
      '#title' => t('Product ID'),
      '#step' => '1',
      '#default_value' => '1',
      '#required' => TRUE,
    ];
    $form['s_api']['availability']['location_ids'] = [
      '#type' => 'textfield',
      '#title' => t('Location ID'),
      '#description' => t('A comma separated list of IDs.'),
      '#default_value' => '1,2',
      '#size' => 60,
      '#maxlength' => 50,
      '#required' => TRUE,
    ];
    $form['s_api']['availability']['check_stock'] = [
      '#type' => 'submit',
      '#value' => t('Check Stock using Stock Storage API'),
      '#submit' => ['::submitCheckStockForm'],
    ];

    // Locations.
    $form['s_api']['locations'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock Locations'),
    ];
    $form['s_api']['locations']['get_list'] = [
      '#type' => 'submit',
      '#value' => t('Get List of locations'),
      '#submit' => ['::submitGetLocations'],
    ];
    $form['s_api']['locations']['active_only'] = [
      '#type' => 'checkbox',
      '#title' => t('Only show active locations'),
      '#default_value' => TRUE,
    ];

    // Transactions.
    $form['s_api']['transactions'] = [
      '#type' => 'fieldset',
      '#title' => t('Transactions'),
    ];

    $form['s_api']['transactions']['prod_id'] = [
      '#type' => 'number',
      '#title' => t('Product ID'),
      '#default_value' => '1',
      '#step' => '1',
      '#required' => TRUE,
    ];
    $form['s_api']['transactions']['location'] = [
      '#type' => 'number',
      '#title' => t('Location'),
      '#default_value' => '1',
      '#step' => '1',
      '#required' => TRUE,
    ];
    $form['s_api']['transactions']['zone'] = [
      '#type' => 'textfield',
      '#title' => t('Zone/Bins'),
      '#description' => t('The location zone (bins)'),
      '#size' => 60,
      '#maxlength' => 50,
    ];
    $form['s_api']['transactions']['quantity'] = [
      '#type' => 'number',
      '#title' => t('Quantity'),
      '#default_value' => '1',
      '#step' => '0.01',
      '#required' => TRUE,
    ];
    $form['s_api']['transactions']['create_transaction'] = [
      '#type' => 'submit',
      '#value' => t('Create transaction'),
      '#submit' => ['::submitCreateTransaction'],
    ];
    $form['s_api']['transactions']['update_product_location_level'] = [
      '#type' => 'submit',
      '#value' => t('Update Product location level'),
      '#submit' => ['::submitUpdateProductInventoryLocationLevel'],
    ];

    // Typed Transactions.
    $form['s_api']['typed_transactions'] = [
      '#type' => 'fieldset',
      '#title' => t('Typed Transactions'),
    ];
    $form['s_api']['typed_transactions']['transaction_notes'] = [
      '#type' => 'textfield',
      '#title' => t('note'),
      '#description' => t('The location zone (bins)'),
      '#size' => 60,
      '#maxlength' => 50,
    ];
    $form['s_api']['typed_transactions']['receiveStock'] = [
      '#type' => 'submit',
      '#value' => t('receiveStock'),
      '#submit' => ['::submitReceiveStock'],
    ];
    $form['s_api']['typed_transactions']['order_id'] = [
      '#type' => 'number',
      '#title' => t('Order ID'),
      '#default_value' => '1',
      '#step' => '1',
      '#required' => TRUE,
    ];
    $form['s_api']['typed_transactions']['user_id'] = [
      '#type' => 'number',
      '#title' => t('User ID'),
      '#default_value' => '1',
      '#step' => '1',
      '#required' => TRUE,
    ];
    $form['s_api']['typed_transactions']['sellStock'] = [
      '#type' => 'submit',
      '#value' => t('sellStock'),
      '#submit' => ['::submitSellStock'],
    ];
    $form['s_api']['typed_transactions']['returnStock'] = [
      '#type' => 'submit',
      '#value' => t('returnStock'),
      '#submit' => ['::submitReturnStock'],
    ];
    $form['s_api']['typed_transactions']['to_location'] = [
      '#type' => 'number',
      '#title' => t('Move to location'),
      '#default_value' => '2',
      '#step' => '1',
      '#required' => TRUE,
    ];
    $form['s_api']['typed_transactions']['to_zone'] = [
      '#type' => 'textfield',
      '#title' => t('Move to zone'),
      '#description' => t('The location zone (bins)'),
      '#size' => 60,
      '#maxlength' => 50,
    ];
    $form['s_api']['typed_transactions']['moveStock'] = [
      '#type' => 'submit',
      '#value' => t('moveStock'),
      '#submit' => ['::submitMoveStock'],
    ];

    $form['s_am'] = [
      '#type' => 'fieldset',
      '#title' => t('Availability Services'),
    ];
    $form['s_am']['check'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock Availability'),
    ];
    $form['s_am']['check']['prod_to_check_id'] = [
      '#type' => 'number',
      '#title' => t('Product ID'),
      '#step' => '1',
      '#default_value' => '1',
      '#required' => TRUE,
    ];
    $form['s_am']['check']['prod_to_check_qty'] = [
      '#type' => 'number',
      '#title' => t('Qty.'),
      '#step' => '1',
      '#default_value' => '1',
      '#required' => TRUE,
    ];
    $form['s_am']['check']['check1'] = [
      '#type' => 'submit',
      '#value' => t('Check Stock using Stock Availability Checker'),
      '#submit' => ['::submitStockAvailabilityCheck'],
    ];
    $form['s_am']['check']['check2'] = [
      '#type' => 'submit',
      '#value' => t('Check Stock using Availability Manager'),
      '#submit' => ['::submitAvailabilityManagerCheck'],
    ];

    $form['s_sm'] = [
      '#type' => 'fieldset',
      '#title' => t('Stock Manager'),
    ];
    $form['s_sm']['list'] = [
      '#type' => 'submit',
      '#value' => t('List all services'),
      '#submit' => ['::submitStockManagerList'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message(t('Form submitted.'));
  }

  /**
   * Submit handler for checking stock.
   */
  public function submitCheckStockForm(array &$form, FormStateInterface $form_state) {
    $prod_id = $form_state->getValue('prod_vid');
    $location_ids = explode(',', $form_state->getValue('location_ids'));
    $stock_level = $this->stockStorageApi->getTotalStockLevel($prod_id, $location_ids);
    drupal_set_message(t('Stock level is: @stock_level', ['@stock_level' => $stock_level]));
  }

  /**
   * Submit handler for getting list of stock locations.
   */
  public function submitGetLocations(array &$form, FormStateInterface $form_state) {
    $active_only = $form_state->getValue('active_only');
    $locations = $this->stockStorageApi->getLocationList($active_only);
    drupal_set_message(t('Locations: @locations', ['@locations' => print_r($locations, TRUE)]));
  }

  /**
   * Submit handler for creating Commerce Stock Storage API stock transactions.
   */
  public function submitCreateTransaction(array &$form, FormStateInterface $form_state) {
    $variation_id = $form_state->getValue('prod_vid');
    $location_id = $form_state->getValue('location');
    $zone = $form_state->getValue('zone');
    $quantity = $form_state->getValue('quantity');
    $unit_cost = NULL;
    $options = [];
    $this->stockStorageApi->createTransaction($variation_id, $location_id, $zone, $quantity, $unit_cost, 1, $options);
  }

  /**
   * Submit handler for updating inventory location level.
   */
  public function submitUpdateProductInventoryLocationLevel(array &$form, FormStateInterface $form_state) {
    $variation_id = $form_state->getValue('prod_vid');
    $location_id = $form_state->getValue('location');
    $this->stockStorageApi->updateLocationStockLevel($location_id, $variation_id);
  }

  /**
   * Submit handler for Commerce Stock stock availability check.
   */
  public function submitStockAvailabilityCheck(array &$form, FormStateInterface $form_state) {
    $variation_id = $form_state->getValue('prod_to_check_id');
    $prod_qty = $form_state->getValue('prod_to_check_qty');
    $product_variation = $this->variationStorage->load($variation_id);
    if (!isset($product_variation)) {
      drupal_set_message(t('Cannot load product!'));
      return;
    }
    $context = new Context($this->currentUser(), $this->currentStore);
    if ($this->stockAvailabilityChecker->check($product_variation, $prod_qty, $context)) {
      drupal_set_message(t('Available'));
    }
    else {
      drupal_set_message(t('Not Available'));
    }
  }

  /**
   * Submit handler for Commerce Core availability manager check.
   */
  public function submitAvailabilityManagerCheck(array &$form, FormStateInterface $form_state) {
    $variation_id = $form_state->getValue('prod_to_check_id');
    $prod_qty = $form_state->getValue('prod_to_check_qty');
    $product_variation = $this->variationStorage->load($variation_id);
    if (!isset($product_variation)) {
      drupal_set_message(t('Cannot load product!'));
      return;
    }
    $context = new Context($this->currentUser(), $this->currentStore);
    $available = $this->availabilityManager->check($product_variation, $prod_qty, $context);
    if ($available) {
      drupal_set_message(t('Available'));
    }
    else {
      drupal_set_message(t('Not Available'));
    }
  }

  /**
   * Submit handler for listing stock managers.
   */
  public function submitStockManagerList(array &$form, FormStateInterface $form_state) {
    $services = $this->stockManager->listServices();
    drupal_set_message(print_r($services, TRUE));
  }

  /**
   * Submit handler for stock receive operation.
   */
  public function submitReceiveStock(array &$form, FormStateInterface $form_state) {
    $variation_id = $form_state->getValue('prod_vid');
    $location_id = $form_state->getValue('location');
    $zone = $form_state->getValue('zone');
    $quantity = $form_state->getValue('quantity');
    $message = $form_state->getValue('transaction_notes');
    $unit_cost = NULL;
    $product_variation = $this->variationStorage->load($variation_id);
    $this->stockManager->receiveStock($product_variation, $location_id, $zone, $quantity, $unit_cost, $message);
    drupal_set_message('Received stock!');
  }

  /**
   * Submit handler for stock sell operation.
   */
  public function submitSellStock(array &$form, FormStateInterface $form_state) {
    $variation_id = $form_state->getValue('prod_vid');
    $location_id = $form_state->getValue('location');
    $zone = $form_state->getValue('zone');
    $quantity = $form_state->getValue('quantity');
    $order_id = $form_state->getValue('order_id');
    $user_id = $form_state->getValue('user_id');
    $message = $form_state->getValue('transaction_notes');
    $unit_cost = NULL;
    $product_variation = $this->variationStorage->load($variation_id);
    $this->stockManager->sellStock($product_variation, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message);
  }

  /**
   * Submit handler for stock return.
   */
  public function submitReturnStock(array &$form, FormStateInterface $form_state) {
    $variation_id = $form_state->getValue('prod_vid');
    $location_id = $form_state->getValue('location');
    $zone = $form_state->getValue('zone');
    $quantity = $form_state->getValue('quantity');
    $order_id = $form_state->getValue('order_id');
    $user_id = $form_state->getValue('user_id');
    $message = $form_state->getValue('transaction_notes');
    $unit_cost = NULL;
    $product_variation = $this->variationStorage->load($variation_id);
    $this->stockManager->returnStock($product_variation, $location_id, $zone, $quantity, $unit_cost, $order_id, $user_id, $message);
  }

  /**
   * Submit handler for stock move.
   */
  public function submitMoveStock(array &$form, FormStateInterface $form_state) {
    $variation_id = $form_state->getValue('prod_vid');
    $from_location_id = $form_state->getValue('location');
    $to_location_id = $form_state->getValue('to_location');
    $from_zone = $form_state->getValue('zone');
    $to_zone = $form_state->getValue('to_zone');
    $quantity = $form_state->getValue('quantity');
    $message = $form_state->getValue('transaction_notes');
    $unit_cost = NULL;
    $product_variation = $this->variationStorage->load($variation_id);
    $this->stockManager->moveStock($product_variation, $from_location_id, $to_location_id, $from_zone, $to_zone, $quantity, $unit_cost, $message);
  }

}
