<?php
/**
 * Created by PhpStorm.
 * User: ada
 * Date: 07-21-17
 * Time: 02:28 PM
 */

namespace Drupal\custom_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides a 'EmptyBody' if this it bring a NULL value.
 *
 * @MigrateProcessPlugin(
 *  id = "empty_body"
 * )
 */
class EmptyBody extends ProcessPluginBase {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if(is_null($value[0])) {
      $value = [];
    }
    else $value = [$value];

    return $value;
  }

}
