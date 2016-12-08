<?php

namespace Drupal\Tests\commerce_stock\Unit;

use Drupal\commerce_stock\StockManager;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_stock\StockManager
 * @group commerce_stock
 */
class StockManagerTest extends UnitTestCase {

  /**
   * The stock manager.
   *
   * @var \Drupal\commerce_stock\StockManager
   */
  protected $stockManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->stockManager = new StockManager();
  }

  /**
   * ::covers addService
   * ::covers listServices
   * ::covers listServiceIds
   */
  public function testServices() {
    $mock_builder = $this->getMockBuilder('Drupal\commerce_stock\StockServiceInterface')
      ->disableOriginalConstructor();

    $first_service = $mock_builder->getMock();
    $first_service->expects($this->any())
      ->method('getName')
      ->willReturn('Stock service 1');
    $first_service->expects($this->any())
      ->method('getId')
      ->willReturn('stock_service_1');

    $second_service = $mock_builder->getMock();
    $second_service->expects($this->any())
      ->method('getName')
      ->willReturn('Stock service 2');
    $second_service->expects($this->any())
      ->method('getId')
      ->willReturn('stock_service_2');

    $this->stockManager->addService($first_service);
    $this->stockManager->addService($second_service);

    $expectedServices = [$first_service, $second_service];
    $services = $this->stockManager->listServices();
    $this->assertEquals($expectedServices, $services, 'The manager has the expected services');

    $service_ids = [
      'stock_service_1' => 'Stock service 1',
      'stock_service_2' => 'Stock service 2',
    ];
    $this->assertEquals($service_ids, $this->stockManager->listServiceIds());
  }

}
