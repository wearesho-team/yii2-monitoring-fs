# Yii2 Filesystem Monitoring

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
