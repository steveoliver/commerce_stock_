<?php

namespace Drupal\commerce_stock_local;

use Drupal\commerce_stock\StockServiceInterface;
use Drupal\commerce_stock\StockConfiguration;

class LocalStockService implements StockServiceInterface {

  /**
   * The stock checker.
   *
   * @var \Drupal\commerce_stock\StockCheckInterface
   */
  protected $stockChecker;

  /**
   * The stock updater.
   *
   * @var \Drupal\commerce_stock\StockUpdateInterface
   */
  protected $stockUpdater;

  /**
   * The stock configuration.
   *
   * @var \Drupal\commerce_stock\StockConfigurationInterface
   */
  protected $stockConfiguration;

  /**
   * Constructor for the service.
   */
  public function __construct() {
    // Create the objects needed.
    $this->stockChecker = new LocalStockStorageAPI();
    $this->stockUpdater = $this->stockChecker;
    $this->stockConfiguration = new StockConfiguration($this->stockChecker);
  }

  /**
   * Get the name of the service.
   */
  public function getName() {
    return 'Local stock';
  }

  /**
   * Get the ID of the service.
   */
  public function getId() {
    return 'local_stock';
  }

  /**
   * Gets the stock checker.
   *
   * @return \Drupal\commerce_stock\StockCheckInterface
   *   The stock checker.
   */
  public function getStockChecker() {
    return $this->stockChecker;
  }

  /**
   * Gets the stock updater.
   *
   * @return \Drupal\commerce_stock\StockUpdateInterface
   *   The stock updater.
   */
  public function getStockUpdater() {
    return $this->stockUpdater;
  }

  /**
   * Gets the stock Configuration.
   *
   * @return \Drupal\commerce_stock\StockConfigurationInterface
   *   The stock Configuration.
   */
  public function getConfiguration() {
    return $this->stockConfiguration;
  }

}
