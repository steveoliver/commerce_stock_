<?php

namespace Drupal\commerce_stock;

interface StockCheckInterface {

  /**
   * Gets the stock level.
   *
   * @param int $variation_id
   *   The product variation ID.
   * @param array $locations
   *   Array of locations.
   *
   * @return int
   *   The stock level.
   */
  public function getTotalStockLevel($variation_id, array $locations);

  /**
   * Check if product is in stock.
   *
   * @param int $variation_id
   *   The product variation ID.
   * @param array $locations
   *   Array of locations.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsInStock($variation_id, $locations);

  /**
   * Check if product is always in stock.
   *
   * @param int $variation_id
   *   The product variation ID.
   *
   * @return bool
   *    TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsAlwaysInStock($variation_id);

  /**
   * Check if product is managed by stock.
   *
   * @todo - not sure if this is needed - not used at this point!!!
   *
   * @param int $variation_id
   *   The product variation ID.
   *
   * @return bool
   *   TRUE if the product is in stock, FALSE otherwise.
   */
  public function getIsStockManaged($variation_id);

  /**
   * Get list of locations.
   *
   * @param bool $return_active_only
   *   Whether or not only return active locations.
   *
   * @return array
   *   List of locations keyed by ID.
   */
  public function getLocationList($return_active_only = TRUE);

}
