<?php

namespace Drupal\custom_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\migrate\process\SubProcess;
use Drupal\migrate\Row;

/**
 * Provides a 'AlterParagraph' migrate process plugin to get all pids joined
 * not only other languages but also originals pids.
 *
 * @MigrateProcessPlugin(
 *  id = "alter_paragraph",
 * )
 */
class AlterParagraph extends SubProcess {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $current_pg = $row->getDestinationProperty('field_paragraph');
    $query = \Drupal::database()->select('node__field_paragraph', 'nfp');
    $query->addField('nfp', 'field_paragraph_target_id', 'target_id');
    $query->addField('nfp', 'field_paragraph_target_revision_id', 'target_revision_id');
    $query->condition('entity_id', $row->getSourceProperty('tnid'));

    $original_pg = $query->execute()->fetchAll();
    // Converts to array.
    array_walk($original_pg, function (&$item , $key) {
      $item = (array) $item ;
    });

    $merged = array_merge_recursive($original_pg, $current_pg);
    $new = [];
    foreach ($merged as $k => $v) {
      $new[$v['target_id']] = $v;
    }

    if (!$merged && is_null($current_pg)) {
      foreach ($original_pg as $k => $v) {
        $new[$v['target_id']] = $v;
      }
    }
    if (!$original_pg && !$merged) {
      foreach ($current_pg as $k => $v) {
        $new[$v['target_id']] = $v;
      }
    }
    $new = array_values($new);
    $row->setDestinationProperty('field_paragraph', $new);

  }

}
