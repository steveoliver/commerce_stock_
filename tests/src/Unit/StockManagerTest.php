<?php

namespace Drupal\Tests\commerce_stock\Unit;

use Drupal\commerce_stock\StockServiceManager;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_stock\StockServiceManager
 * @group commerce_stock
 */
class StockServiceManagerTest extends UnitTestCase {

  /**
   * The stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManager
   */
  protected $stockServiceManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->stockServiceManager = new StockServiceManager();
  }

  /**
   * ::covers addService
   * ::covers listServices
   * ::covers listServiceIds.
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

    $this->stockServiceManager->addService($first_service);
    $this->stockServiceManager->addService($second_service);

    $expectedServices = [$first_service, $second_service];
    $services = $this->stockServiceManager->listServices();
    $this->assertEquals($expectedServices, $services, 'The manager has the expected services');

    $service_ids = [
      'stock_service_1' => 'Stock service 1',
      'stock_service_2' => 'Stock service 2',
    ];
    $this->assertEquals($service_ids, $this->stockServiceManager->listServiceIds());
  }

}
