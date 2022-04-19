<?php

namespace Drupal\finnavia_flight_info\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\finnavia_flight_info\Controller\FinnaviaFlightController;

/**
 * Provides a 'Flight info' Block.
 *
 * @Block(
 *   id = "finnavia_flight_block",
 *   admin_label = @Translation("Flight info block"),
 *   category = @Translation("Finnavia flight info"),
 * )
 */
class FlightInfoBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $finnavia_controller = new FinnaviaFlightController();
    $rendering_in_block = $finnavia_controller->renderFlightInfo();
    return $rendering_in_block;
  }
}
