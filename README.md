# Console Toolkit plugin for Craft CMS 3.x

## Requirements

This plugin requires:
  - [Craft CMS 3.0.0](https://craftcms.com/news/craft-3) or later.

## Installation

To install the plugin, follow these instructions.

```BASH
cd {craft app folder}
composer config repositories.blasvicco.consoletoolkit vcs https://github.com/blasvicco/consoletoolkit.git
composer require blasvicco/console-toolkit
./craft install/plugin console-toolkit
```

## Console Toolkit Overview

This plugin add two console command to craft:
  - One is a re saving entries tool to test that all the entries can be save without error. In case of error a log file will be generated in `{craft app folder}/storage/logs/save-fails.logs`.
  - The other one is to remove empty fields for native and super table field type.

## Using Console Toolkit

In order to execute the `re-saving-entries`:

```BASH
cd {craft app folder}
./craft console-toolkit/re-saving-entries/run
```

In order to execute the `remove-empty-fields`:

```BASH
cd {craft app folder}
./craft console-toolkit/remove-empty-fields/run
```

Brought to you by [Blas Vicco](https://github.com/blasvicco)
