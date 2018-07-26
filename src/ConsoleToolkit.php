<?php
/**
 * Console Toolkit plugin for Craft CMS 3.x
 *
 * A couple of util commands for the craft console.
 *
 * @link      https://github.com/blasvicco
 * @copyright Copyright (c) 2018 Blas Vicco
 */

namespace blasvicco\consoletoolkit;

use blasvicco\consoletoolkit\services\RemoveNativeFields as RemoveNativeFieldsService;
use blasvicco\consoletoolkit\services\RemoveSuperTableFields as RemoveSuperTableFieldsService;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;

/**
 * Class ConsoleToolkit
 *
 * @author    Blas Vicco
 * @package   ConsoleToolkit
 * @since     1.0.0
 *
 * @property  RemoveNativeFieldsService $removeNativeFields
 * @property  RemoveSuperTableFieldsService $removeSuperTableFields
 */
class ConsoleToolkit extends Plugin {
  // Static Properties
  // =========================================================================

  /**
   * @var ConsoleToolkit
   */
  public static $plugin;

  // Public Properties
  // =========================================================================

  /**
   * @var string
   */
  public $schemaVersion = '1.0.0';

  // Public Methods
  // =========================================================================

  /**
   * @inheritdoc
   */
  public function init() {
    parent::init();
    self::$plugin = $this;

    if (Craft::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'blasvicco\consoletoolkit\console\controllers';
    }

    $this->setComponents([
      'RemoveNativeEmptyFields' => RemoveNativeFieldsService::class,
    ]);

    $plugin = Craft::$app->plugins->getPlugin('super-table', true);
    if (isset($plugin)) {
      $this->setComponents([
        'RemoveSuperTableEmptyFields' => RemoveSuperTableFieldsService::class,
      ]);
    }

    Craft::info(
      Craft::t(
        'console-toolkit',
        '{name} plugin loaded',
        ['name' => $this->name]
      ),
      __METHOD__
    );
  }

}
