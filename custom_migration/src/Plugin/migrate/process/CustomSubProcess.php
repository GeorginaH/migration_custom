<?php

namespace Drupal\custom_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Plugin\migrate\process\SubProcess;
use Drupal\migrate\Row;

/**
 * Provides a 'CustomSubProcess' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "custom_subprocess",
 *  handle_multiples = TRUE
 * )
 */
class CustomSubProcess extends SubProcess {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $return = [];
    // Verify if field paragraph is empty.
    if(empty($value[0]) && is_null($value[1])) {
      return $value = [];
    }
    foreach ($value as $key => $new_value) {
      if (empty($new_value)) {
        continue;
      }
      if ($destination_property == 'field_paragraph') {
        foreach ($new_value as $k => $v) {
          if (is_null($v) || is_string($v)) {
            continue;
          }
          if (is_null($v[0])) {
            continue;
          }
          $return[$k] = $this->prepare_simple_row($v, $migrate_executable);
        }
      }
      else {
        $return[$key] = $this->prepare_simple_row($new_value, $migrate_executable);
      }
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

  public function prepare_simple_row($value, MigrateExecutableInterface $migrate_executable) {
    $new_row = new Row($value, []);
    $migrate_executable->processRow($new_row, $this->configuration['process']);
    return $new_row->getDestination();
  }

}
