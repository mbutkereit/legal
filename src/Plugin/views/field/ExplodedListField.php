<?php

/**
 * @file
 * Definition of Drupal\legal\Plugin\views\field\NodeTypeFlagger
 */

namespace Drupal\legal\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("exploded_list")
 */
class ExplodedListField extends FieldPluginBase {
  /**
   * {@inheritdoc}
   */
  public function query() {

  }
  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $build = array(
      '#type' => 'markup',
      '#markup' =>      $this->field_alias,
    );
    return $build;
    $extras = explode("\r\n", $values->{$this->field_alias});
    return theme('item_list', $extras);
  }
}