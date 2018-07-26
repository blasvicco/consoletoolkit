<?php
/**
 * Console Toolkit plugin for Craft CMS 3.x
 *
 * A couple of util commands for the craft console.
 *
 * @link      https://github.com/blasvicco
 * @copyright Copyright (c) 2018 Blas Vicco
 */

namespace blasvicco\consoletoolkit\services;

use Craft;
use craft\base\FieldInterface;
use craft\db\Query;
use verbb\supertable\services\SuperTableService;

/**
 * @author    Blas Vicco
 * @package   ConsoleToolkit
 * @since     1.0.1
 */
class RemoveSuperTableFields extends SuperTableService {
  // Public Methods
  // =========================================================================

  public function removeFields(array $fields) {
    foreach ($fields as $field) {
      $this->checkField($field);
    }
  }

  // Private Methods
  // =========================================================================

  private function checkField(FieldInterface $field) {
    $className = get_class($field);
    if ($className == 'verbb\supertable\fields\SuperTableField') {
      $this->removeIfEmpty($field);
    } else if ($className == 'craft\fields\Matrix') {
      $blockTypes = Craft::$app->matrix->getBlockTypesByFieldId($field->id);
      foreach ($blockTypes as $blockType) {
        $this->removeFields($blockType->getFields());
      }
    }
  }

  private function removeIfEmpty(FieldInterface $field) {
    $tableName = $this->getContentTableName($field);
    $count = (new Query())
      ->from($tableName . ' ST')
      ->count('ST.id');
    if ($count == 0) {
      echo Craft::t(
        'console-toolkit',
        'Removing Super Table Field: {id} - {handle} - table {table}',
        ['id' => $field->id, 'handle' => $field->handle, 'table' => $this->rawTableName($tableName)]
      ) . "\n";
      $this->deleteField($field);
    }
  }

  private function rawTableName($tableName) {
    return str_replace(['{{%', '}}'], '', $tableName);
  }

  /**
   * Delete a field.
   *
   * @param FieldInterface $field The field
   * @return bool Whether the field was deleted successfully
   * @throws \Throwable if reasons
   */
  private function deleteField(FieldInterface $field): bool {
    try {
      // Fire a 'beforeDeleteField' event
      if (Craft::$app->fields->hasEventHandlers(Craft::$app->fields::EVENT_BEFORE_DELETE_FIELD)) {
        Craft::$app->fields->trigger(Craft::$app->fields::EVENT_BEFORE_DELETE_FIELD, new FieldEvent(['field' => $field,]));
      }
      if (!$field->beforeDelete()) {
        return false;
      }
    } catch (\Exception $e) {
      echo Craft::t(
        'console-toolkit',
        'WARNING: Not able to trigger Before delete field hook'
      ) . "\n";
    }

    $transaction = Craft::$app->getDb()->beginTransaction();
    try {
      // De we need to delete the content column?
      // Check if this field is inside a Matrix - we need to prefix this content table if so.
      if ($field->context != 'global') {
        $parentFieldContext = explode(':', $field->context);
        if ($parentFieldContext[0] != 'matrixBlockType') {
          $contentTable = Craft::$app->getContent()->contentTable;
          $fieldColumnPrefix = Craft::$app->getContent()->fieldColumnPrefix;
          if (Craft::$app->getDb()->columnExists($contentTable, $fieldColumnPrefix.$field->handle)) {
            Craft::$app->getDb()->createCommand()
              ->dropColumn($contentTable, $fieldColumnPrefix.$field->handle)
              ->execute();
          }
        }
      }

      // Delete the row in fields
      Craft::$app->getDb()->createCommand()
        ->delete('{{%fields}}', ['id' => $field->id])
        ->execute();
      $field->afterDelete();
      $transaction->commit();
    } catch (\Throwable $e) {
      $transaction->rollBack();
      throw $e;
    }

    try {
      // Fire an 'afterDeleteField' event
      if (Craft::$app->fields->hasEventHandlers(Craft::$app->fields::EVENT_AFTER_DELETE_FIELD)) {
        Craft::$app->fields->trigger(Craft::$app->fields::EVENT_AFTER_DELETE_FIELD, new FieldEvent(['field' => $field,]));
      }
    } catch (\Exception $e) {
      echo Craft::t(
        'console-toolkit',
        'WARNING: Not able to trigger After delete field hook'
      ) . "\n";
    }

    return true;
  }

}
