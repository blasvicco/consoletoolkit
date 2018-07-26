<?php
/**
 * Console Toolkit plugin for Craft CMS 3.x
 *
 * A couple of util commands for the craft console.
 *
 * @link      https://github.com/blasvicco
 * @copyright Copyright (c) 2018 Blas Vicco
 */

/**
 * @author    Blas Vicco
 * @package   ConsoleToolkit
 * @since     1.0.1
 */
return [
  'Console Toolkit plugin loaded' => 'Console Toolkit plugin loaded',

  // Remove native fields service
  'Removing Field: {id} - {handle}' => 'Removing Field: {id} - {handle}',
  'WARNING: Field {id} - {handle} not able to delete properly' => 'WARNING: Field {id} - {handle} not able to delete properly',
  'Removing Matrix Table Field: {field} table ({table})' => 'Removing Matrix Table Field: {field} table ({table})',
  'Removing Matrix Table Column: {field} table {table}.{column})' => 'Removing Matrix Table Column: {field} table {table}.{column})',
  'WARNING: Not able to trigger Before delete field hook' => 'WARNING: Not able to trigger Before delete field hook',
  'WARNING: Not able to trigger After delete field hook' => 'WARNING: Not able to trigger After delete field hook',

  // Remove Super Table fields service
  'Removing Super Table Field: {id} - {handle} - table {table}' => 'Removing Super Table Field: {id} - {handle} - table {table}',

  // Re Saving Entries controller
  'Saving entry: Id {id} title {title}' => 'Saving entry: Id {id} title {title}',
];
