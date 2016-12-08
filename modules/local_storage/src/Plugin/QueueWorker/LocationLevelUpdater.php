<?php

namespace Drupal\commerce_stock_local\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\commerce_stock_local\StockStorageAPI;

/**
 * A Commerce Stock worker.
 *
 * @QueueWorker(
 *   id = "commerce_stock_local_location_level_updater",
 *   title = @Translation("Commerce Stock Local location level updater"),
 *   cron = {"time" = 10}
 * )
 */
class LocationLevelUpdater extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Get the Stock Storage API.
    $stock_api = new StockStorageAPI();
    // Get the active locations.
    $locations = $stock_api->getLocationList(TRUE);
    // Update.
    foreach ($locations as $location_id => $location) {
      // Update the stock level.
      $stock_api->updateLocationStockLevel($location_id, $data);
    }
  }

}
