<?php

namespace Drupal\commerce_stock_field;

use Drupal\Core\TypedData\TypedData;

class StockLevelProcessor extends TypedData {

  /**
   * Cached processed level.
   *
   * @var int|null
   */
  protected $processed = NULL;

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    if ($this->processed !== NULL) {
      return $this->processed;
    }
    $item = $this->getParent();
    $entity = $item->getEntity();
    $stockManager = \Drupal::service('commerce.stock_manager');
    $level = $stockManager->getStockLevel($entity);
    $this->processed = $level;
    return $level;
  }

}
