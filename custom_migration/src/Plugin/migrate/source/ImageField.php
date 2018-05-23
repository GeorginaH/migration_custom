<?php
/**
 * Created by PhpStorm.
 * User: ada
 * Date: 07-13-17
 * Time: 05:51 PM
 */

namespace Drupal\custom_migration\Plugin\migrate\source;


use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Drupal 7 node source from database.
 *
 * @MigrateSource(
 *   id = "image_gallery"
 * )
 */
class ImageField extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Select node in its last revision.
    $query = $this->select('field_data_field_image', 'fim')
      ->fields('fim', [
        'field_image_fid',
        'entity_id',
        'field_image_width',
        'field_image_height'
      ]);
    $query->condition('entity_type', 'node');
    $query->condition('field_image_width', '', '<>');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'entity_id' => $this->t('Media gallery NID'),
      'field_image_fid' => $this->t('Media gallery file FID'),
      'field_image_width' => $this->t('Media gallery width'),
      'field_image_height' => $this->t('Media gallery height')
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['field_image_fid']['type'] = 'integer';
    $ids['field_image_fid']['alias'] = 'fid';
    $ids['entity_id']['type'] = 'integer';
    return $ids;
  }
}
