<?php

/**
 * @file
 * Definition of Drupal\legal\Plugin\views\field\UnserializedListField
 */

namespace Drupal\legal\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("unserialized_list")
 */
class UnserializedListField extends FieldPluginBase {

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $extras = unserialize($values->{$this->field_alias});
    return [
      '#theme' => 'item_list',
      '#items' => $extras,
    ];
  }
}
