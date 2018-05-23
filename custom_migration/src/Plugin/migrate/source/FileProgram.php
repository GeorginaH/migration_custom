<?php

namespace Drupal\custom_migration\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Custom source plugin to get field file program.
 *
 * @MigrateSource(
 *   id = "d7_fileprogram"
 * )
 */
class FileProgram extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('field_data_field_file_program', 'ffp')
      ->fields('ffp', [
        'field_file_program_fid'
      ]);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['field_file_program_fid']['type'] = 'integer';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'field_file_program_fid' => $this->t('File ID'),
    );
  }

}
