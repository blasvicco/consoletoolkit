<?php
/**
 * Console Toolkit plugin for Craft CMS 3.x
 *
 * A couple of util commands for the craft console.
 *
 * @link      https://github.com/blasvicco
 * @copyright Copyright (c) 2018 Blas Vicco
 */

namespace blasvicco\consoletoolkit\console\controllers;

use blasvicco\consoletoolkit\ConsoleToolkit;

use Craft;
use yii\console\Controller;

/**
 * RemoveEmptyFields Command
 *
 * @author    Blas Vicco
 * @package   ConsoleToolkit
 * @since     1.0.1
 */
class RemoveEmptyFieldsController extends Controller {
  // Public Methods
  // =========================================================================

  /**
   * Handle console-toolkit/remove-empty-fields/run console commands
   *
   * @return mixed
   */
  public function actionRun() {
    set_time_limit(0);
    try {
      ConsoleToolkit::getInstance()->RemoveNativeEmptyFields->removeFields();
      ConsoleToolkit::getInstance()->RemoveNativeEmptyFields->removeMatrixFields(Craft::$app->fields->getAllFields());
      if (ConsoleToolkit::getInstance()->RemoveSuperTableEmptyFields) {
        ConsoleToolkit::getInstance()->RemoveSuperTableEmptyFields->removeFields(Craft::$app->fields->getAllFields());
      }
    } catch (\Exception $e) {
      echo $e->getMessage()."\n";
      return -1;
    }

    return 0;
  }

}
