# Yii2 Filesystem Monitoring
[![Tests & Lint](https://github.com/wearesho-team/yii2-monitoring-fs/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/wearesho-team/yii2-monitoring-fs/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/yii2-monitoring-fs/v/stable.png)](https://packagist.org/packages/wearesho-team/yii2-monitoring-fs)
[![Total Downloads](https://poser.pugx.org/wearesho-team/yii2-monitoring-fs/downloads.png)](https://packagist.org/packages/wearesho-team/yii2-monitoring-fs)
[![codecov](https://codecov.io/gh/wearesho-team/yii2-monitoring-fs/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/yii2-monitoring-fs)

## Installation

```bash
composer require wearesho-team/yii2-monitoring-fs
```

## Usage

Create instance of [Fs](./src/Control/Fs.php) class and put in it your filesystem adapter.

Simple usage example in console controller:

```php
<?php

use Wearesho\Yii\Monitoring;

class MonitoringController extends \yii\console\Controller
{
    public function actionIndex()
    {
        /** @var \Wearesho\Yii\Filesystem\AdapterInterface $adapter */
        
        $control = new Monitoring\Control\Fs([
            'fs' => $adapter,
            'client' => new \GuzzleHttp\Client(),
        ]);
        
        try {
            $details = $control->execute();
        } catch (\Horat1us\Yii\Monitoring\Exception $exception) {
            $this->stdout($exception->getMessage());
        }
    }
}

```

## Authors
- [Alexander Letnikow](mailto:reclamme@gmail.com)
- [Roman Varkuta](mailto:roman.varkuta@gmail.com)

# License
- [MIT](./LICENSE)
