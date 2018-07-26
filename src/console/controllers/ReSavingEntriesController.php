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

use Craft;
use craft\elements\Entry;
use yii\console\Controller;

/**
 * ReSavingEntries Command
 *
 * @author    Blas Vicco
 * @package   ConsoleToolkit
 * @since     1.0.0
 */
class ReSavingEntriesController extends Controller {
  // Public Methods
  // =========================================================================

  /**
   * Handle console-toolkit/re-saving-entries/run console commands
   *
   * @return mixed
   */
  public function actionRun() {
    set_time_limit(0);
    $entries = Entry::find()->all();
    foreach ($entries as $entry) {
      $msg = Craft::t(
        'console-toolkit',
        'Saving entry: Id {id} title {title}',
        ['id' => $entry->id, 'title' => $entry->title]
      );
      try {
        echo "\n" . $msg;
        Craft::$app->elements->saveElement($entry);
        echo " 😁\n";
      } catch (\Exception $e) {
        echo " 🤬\n";
        $msg .= " FAIL\n";
        $msg .= $e->getMessage()."\n";
        file_put_contents('/var/www/app/storage/logs/save-fails.logs', $msg, FILE_APPEND);
      }
    }
    return 0;
  }

}
