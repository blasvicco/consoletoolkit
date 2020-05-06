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
use craft\base\Component;
use craft\db\Query;

/**
 * @author    Blas Vicco
 * @package   ConsoleToolkit
 * @since     1.0.1
 */
class RemoveNativeFields extends Component {

  const FIELD_TYPES = [
    'craft\fields\Checkboxes',
    'craft\fields\Color',
    'craft\fields\Date',
    'craft\fields\Dropdown',
    'craft\fields\Entries',
    'craft\fields\Lightswitch',
    'craft\fields\Number',
    'craft\fields\PlainText',
    'craft\fields\RadioButtons',
    'craft\fields\Table',
    'craft\redactor\Field'
  ];

  // Public Methods
  // =========================================================================

  public function removeFields() {
    $data = (new Query())
      ->select(['F.id', 'F.handle', 'F.type'])
      ->from([Craft::$app->getDb()->tablePrefix.'fields F'])
      ->leftJoin('craft_fieldlayoutfields FLF', 'FLF.fieldId = F.id')
      ->where(['FLF.id' => NULL, 'F.type' => $this::FIELD_TYPES])
      ->groupBy(['F.id'])
      ->all();
    foreach ($data as $record) {
      try {
        $count = (new Query())
        ->from(Craft::$app->getDb()->tablePrefix.'content C')
        ->where('C.field_' . $record['handle'] . ' IS NOT NULL')
        ->count('C.id');
        if ($count == 0) {
          echo Craft::t(
            'console-toolkit',
            'Removing Field: {id} - {handle}',
            ['id' => $record['id'], 'handle' => $record['handle']]
          ) . "\n";
          Craft::$app->fields->deleteFieldById($record['id']);
          Craft::$app->getDb()->getSchema()->refresh();
        }
      } catch(\Exception $e) {
        echo Craft::t(
          'console-toolkit',
          'WARNING: Field {id} - {handle} not able to delete properly',
          ['id' => $record['id'], 'handle' => $record['handle']]
        ) . "\n";
      }
    }
  }

  public function removeMatrixFields(array $fields) {
    foreach ($fields as $field) {
      $this->checkField($field);
    }
  }

  // Private Methods
  // =========================================================================

  private function checkField($field) {
    $className = get_class($field);
    if ($className == 'craft\fields\Matrix') {
      $this->removeIfEmpty($field);
    }
  }

  private function removeIfEmpty($field) {
    $tableName = $field->contentTable;
    if (Craft::$app->getDb()->tableExists($tableName) == FALSE) {
      echo Craft::t(
        'console-toolkit',
        'WARNING: Field {id} - {handle} not able to delete properly',
        ['id' => $field->id, 'handle' => $field->handle]
      ) . "\n";
      return;
    }

    $count = (new Query())
      ->from($tableName . ' MT')
      ->count('MT.id');
    if ($count == 0) {
      echo Craft::t(
        'console-toolkit',
        'Removing Matrix Table Field: {field} table ({table})',
        ['field' => $field, 'table' => $this->rawTableName($tableName)]
      ) . "\n";
      $this->deleteField($field);
      return;
    }

    $blockTypes = Craft::$app->matrix->getBlockTypesByFieldId($field->id);
    $blocksTotal = count($blockTypes);
    foreach ($blockTypes as $blockType) {
      $fields = $blockType->getFields();
      $fieldsTotal = count($fields);
      foreach ($fields as $innerField) {
        $column = 'field_' . $blockType->handle . '_' . $innerField->handle;
        if (Craft::$app->getDb()->columnExists($tableName, $column)) {
          $count = (new Query())
            ->from($tableName . ' MT')
            ->where($column . ' is not NULL')
            ->count('MT.id');
          if ($count == 0) {
            echo Craft::t(
              'console-toolkit',
              'Removing Matrix Table Column: {field} table {table}.{column})',
              ['field' => $field, 'table' => $this->rawTableName($tableName), 'column' => $column]
            ) . "\n";
            Craft::$app->getDb()->createCommand()
              ->dropColumn($tableName, $column)
              ->execute();
            $this->deleteField($innerField);
            --$fieldsTotal;
          }
        }
      }
      if ($fieldsTotal == 0) {
        Craft::$app->matrix->deleteBlockType($blockType);
        --$blocksTotal;
      }
    }

    if ($blocksTotal == 0) {
      $this->deleteField($field);
    }
  }

  private function rawTableName($tableName) {
    return str_replace(['{{%', '}}'], '', $tableName);
  }

  private function deleteField($field) {
    try {
      // Fire a 'beforeDeleteField' event
      if (Craft::$app->fields->hasEventHandlers(Craft::$app->fields::EVENT_BEFORE_DELETE_FIELD)) {
        Craft::$app->fields->trigger(Craft::$app->fields::EVENT_BEFORE_DELETE_FIELD, new FieldEvent(['field' => $field]));
      }
      if (!$field->beforeDelete()) {
        return FALSE;
      }
    } catch (\Exception $e) {
      echo Craft::t(
        'console-toolkit',
        'WARNING: Not able to trigger Before delete field hook'
      ) . "\n";
    }

    $transaction = Craft::$app->getDb()->beginTransaction();
    try {
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
        Craft::$app->fields->trigger(Craft::$app->fields::EVENT_AFTER_DELETE_FIELD, new FieldEvent(['field' => $field]));
      }
    } catch (\Exception $e) {
      echo Craft::t(
        'console-toolkit',
        'WARNING: Not able to trigger After delete field hook'
      ) . "\n";
    }

    return TRUE;
  }

}
