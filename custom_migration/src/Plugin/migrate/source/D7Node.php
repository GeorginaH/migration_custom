<?php
namespace Drupal\custom_migration\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;
use Drupal\node\Plugin\migrate\source\d7\Node;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Row;

/**
 * Drupal 7 node source from database.
 *
 * @MigrateSource(
 *   id = "d7_node_custom",
 *   source_provider = "node"
 * )
 */
class D7Node extends Node {

  /**
   * The D7 translation value indicating entity translation.
   */
  const ENTITY_TRANSLATION_ENABLED = 4;

  /**
   * Check if this bundle is entity-translatable.
   *
   * @return bool
   *   Whether the bundle uses entity translation.
   */
  protected function isEntityTranslatable() {
    // Cannot determine this without entity bundle.
    if (!isset($this->configuration['node_type'])) {
      return FALSE;
    }

    $variable = 'language_content_type_' . $this->configuration['node_type'];
    $translation = $this->variableGet($variable, 0);
    return $translation == self::ENTITY_TRANSLATION_ENABLED;
  }

  /**
   * {@inheritdoc}
   */
  protected function handleTranslations(SelectInterface $query) {
    // If entity translations are not enabled, do nothing.
    if (!$this->isEntityTranslatable()) {
      return;
    }

    // Entity translation data is kept in the entity_translation table.
    $query->join('entity_translation', 'et',
      "et.entity_type = :entity_type AND et.entity_id = n.nid",
      [':entity_type' => 'node']
    );

    // Use only originals, or only translations, depending on our configuration.
    $operator = empty($this->configuration['translations']) ? '=' : '<>';
    $query->condition('et.source', '', $operator);

    // A list of fields to override from the 'entity_translation' table.
    $override = [
      'language' => 'language',
      'uid' => 'uid',
      'status' => 'status',
      'translate' => 'translate',
      'created' => 'created',
      'changed' => 'changed',
      'vid' => 'revision_id',
    ];
    $fields =& $query->getFields();
    foreach ($override as $alias => $et_column) {
      unset($fields[$alias]);
      $query->addField('et', $et_column, $alias);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
      // Get field identifiers.
    foreach (array_keys($this->getFields('node', $row->getSourceProperty('type'))) as $field) {
      $nid = $row->getSourceProperty('nid');
      $language = $row->getSourceProperty('language');
      // Get field values.
      $row->setSourceProperty($field, $this->getFieldValues('node', $field, $nid, NULL, $language));

      if (!empty($this->configuration['translations'])) {
        // Gets the translated title saved in title_field.
        $title = $row->getSourceProperty('title_field');
        if ($title) {
          $title = reset($title);
          $row->setSourceProperty('title', $title['value']);
        }
      }
    }
    // Make sure we always have a translation set.
    if ($row->getSourceProperty('tnid') == 0) {
      $row->setSourceProperty('tnid', $row->getSourceProperty('nid'));
    }
    return FieldableEntity::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues($entity_type, $field, $entity_id, $revision_id = NULL, $language = NULL) {
    $table = (isset($revision_id) ? 'field_revision_' : 'field_data_') . $field;
    $query = $this->select($table, 't')
      ->fields('t')
      ->condition('entity_type', $entity_type)
      ->condition('entity_id', $entity_id)
      ->condition('deleted', 0);
    if (isset($revision_id)) {
      $query->condition('revision_id', $revision_id);
    }
    //Migrate only EN
    $lang = $query->orConditionGroup()
    ->condition('language', $language)
    ->condition('language', 'und');
    $query->condition($lang);
    $values = [];
    foreach ($query->execute() as $row) {
      foreach ($row as $key => $value) {
        $delta = $row['delta'];
        if (strpos($key, $field) === 0) {
          $column = substr($key, strlen($field) + 1);
          $values[$delta][$column] = $value;
        }
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids = parent::getIds();
    if (!empty($this->configuration['translations'])
      && $this->isEntityTranslatable()
    ) {
      // With Entity Translation, each translation has the same node ID.
      // To uniquely identify a row, we therefore need both nid and
      // language.
      $ids['language'] = [
        'type' => 'string',
        'alias' => 'et',
      ];
    }
    return $ids;
  }

}
