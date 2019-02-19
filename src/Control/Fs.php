<?php

declare(strict_types=1);

namespace Wearesho\Yii\Monitoring\Control;

use GuzzleHttp;
use League\Flysystem\AdapterInterface;
use yii\di;
use yii\base;
use Horat1us\Yii\Monitoring;
use Wearesho\Yii\Filesystem\Filesystem;

/**
 * Class Fs
 * @package Wearesho\Yii\Monitoring\Control
 */
class Fs extends Monitoring\Control
{
    public const CODE_FS_CONTENTS = 1001;
    public const CODE_PERMISSIONS = 1002;
    public const CODE_PUBLIC_CONTENTS = 1003;
    public const CODE_DELETE = 1004;

    /** @var string|array|Filesystem */
    public $fs = 'fs';

    /** @var string|array|GuzzleHttp\ClientInterface */
    public $client = GuzzleHttp\ClientInterface::class;

    /**
     * @throws base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->fs = di\Instance::ensure($this->fs, Filesystem::class);
        $this->client = di\Instance::ensure($this->client, GuzzleHttp\ClientInterface::class);
    }

    /**
     * @throws \Throwable
     * @return array|null
     */
    public function execute(): ?array
    {
        $contents = bin2hex(random_bytes(512));
        $path = 'test/fstest_' . microtime(true) . '.txt';
        $this->fs->put($path, $contents, [
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
        ]);

        $fsContents = $this->fs->read($path);
        $this->assertEquals(
            $contents,
            $fsContents,
            "Ошибка чтения из файлового хранилища",
            static::CODE_FS_CONTENTS
        );

        $fsVisibility = $this->fs->getVisibility($path);
        $this->assertEquals(
            AdapterInterface::VISIBILITY_PUBLIC,
            $fsVisibility,
            "Ошибка прав доступа файлового хранилища",
            static::CODE_PERMISSIONS
        );

        $publicUrl = $this->fs->getUrl($path);
        try {
            $response = $this->client->request('get', $publicUrl);
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            throw new Monitoring\Exception(
                "Ошибка публичного чтения из файлового хранилища",
                static::CODE_PUBLIC_CONTENTS,
                null,
                $e
            );
        }
        $publicContents = (string)$response->getBody();
        $this->assertEquals(
            $contents,
            $publicContents,
            "Ошибка сверки содержимого тестового файла файлового хранилища",
            static::CODE_PUBLIC_CONTENTS
        );

        $this->fs->delete($path);
        if ($this->fs->has($path)) {
            throw new Monitoring\Exception(
                "Ошибка удаления тестового файлов из файлового хранилища",
                static::CODE_DELETE
            );
        }

        return [
            'adapter' => get_class($this->fs->getAdapter()),
        ];
    }
}
