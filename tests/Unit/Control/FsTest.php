<?php

namespace Wearesho\Yii\Monitoring\Tests\Unit\Control;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Wearesho\Yii;

/**
 * Class FsTest
 * @package Wearesho\Yii\Monitoring\Tests\Unit\Control
 */
class FsTest extends TestCase
{
    use PHPMock;

    protected const BIN_TO_HEX_MOCK = 'bin-to-hex-value';
    protected const PATH = 'test/fstest_123456.txt';

    protected function setUp(): void
    {
        $microtime = $this->getFunctionMock("Wearesho\\Yii\\Monitoring\\Control", 'microtime');
        $microtime->expects($this->once())
            ->willReturn('123456');
        $bin2hex = $this->getFunctionMock("Wearesho\\Yii\\Monitoring\\Control", 'bin2hex');
        $bin2hex->expects($this->once())
            ->willReturn(static::BIN_TO_HEX_MOCK);
    }

    /**
     * @expectedException \Horat1us\Yii\Monitoring\Exception
     * @expectedExceptionMessage Ошибка чтения из файлового хранилища
     * @expectedExceptionCode 1001
     */
    public function testPut(): void
    {
        $fsMock = $this->createMock(Yii\Filesystem\Filesystem::class);
        $fsMock->expects($this->once())
            ->method('put')
            ->with(static::PATH, static::BIN_TO_HEX_MOCK, ['visibility' => 'public'])
            ->willReturnSelf();
        $fs = new Yii\Monitoring\Control\Fs([
            'client' => $this->createMock(Client::class),
            'fs' => $fsMock,
        ]);

        $fs->execute();
    }

    /**
     * @depends testPut
     * @expectedException \Horat1us\Yii\Monitoring\Exception
     * @expectedExceptionMessage Ошибка чтения из файлового хранилища
     * @expectedExceptionCode 1001
     */
    public function testFailedRead(): void
    {
        $fs = new Yii\Monitoring\Control\Fs([
            'client' => $this->createMock(Client::class),
            'fs' => $this->createMock(Yii\Filesystem\Filesystem::class),
        ]);

        $fs->execute();
    }

    /**
     * @depends testPut
     * @expectedException \Horat1us\Yii\Monitoring\Exception
     * @expectedExceptionMessage Ошибка прав доступа файлового хранилища
     * @expectedExceptionCode 1002
     */
    public function testFailedGetVisibility(): void
    {
        $fsMock = $this->createMock(Yii\Filesystem\Filesystem::class);
        $fsMock->expects($this->once())
            ->method('read')
            ->with()
            ->willReturn(static::BIN_TO_HEX_MOCK);
        $fs = new Yii\Monitoring\Control\Fs([
            'client' => $this->createMock(Client::class),
            'fs' => $fsMock,
        ]);

        $fs->execute();
    }

    /**
     * @depends testPut
     * @expectedException \Horat1us\Yii\Monitoring\Exception
     * @expectedExceptionMessage Ошибка публичного чтения из файлового хранилища
     * @expectedExceptionCode 1003
     */
    public function testFailedGetUrl(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('request')
            ->willThrowException(new BadResponseException('Some exception', new Request('get', 'url')));

        $fsMock = $this->createMock(Yii\Filesystem\Filesystem::class);
        $fsMock->expects($this->once())
            ->method('read')
            ->with(static::PATH)
            ->willReturn(static::BIN_TO_HEX_MOCK);
        $fsMock->expects($this->once())
            ->method('getVisibility')
            ->with(static::PATH)
            ->willReturn('public');
        $fs = new Yii\Monitoring\Control\Fs([
            'client' => $client,
            'fs' => $fsMock,
        ]);

        $fs->execute();
    }

    /**
     * @depends testPut
     * @expectedException \Horat1us\Yii\Monitoring\Exception
     * @expectedExceptionMessage Ошибка удаления тестового файлов из файлового хранилища
     * @expectedExceptionCode 1004
     */
    public function testFailedDelete(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], static::BIN_TO_HEX_MOCK));

        $fsMock = $this->createMock(Yii\Filesystem\Filesystem::class);
        $fsMock->expects($this->once())
            ->method('read')
            ->with(static::PATH)
            ->willReturn(static::BIN_TO_HEX_MOCK);
        $fsMock->expects($this->once())
            ->method('getVisibility')
            ->with(static::PATH)
            ->willReturn('public');
        $fsMock->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $fs = new Yii\Monitoring\Control\Fs([
            'client' => $client,
            'fs' => $fsMock,
        ]);

        $fs->execute();
    }

    /**
     * @depends testPut
     */
    public function testSuccess(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], static::BIN_TO_HEX_MOCK));

        $fsMock = $this->createMock(Yii\Filesystem\Filesystem::class);
        $fsMock->expects($this->once())
            ->method('read')
            ->with(static::PATH)
            ->willReturn(static::BIN_TO_HEX_MOCK);
        $fsMock->expects($this->once())
            ->method('getVisibility')
            ->with(static::PATH)
            ->willReturn('public');
        $fsMock->expects($this->once())
            ->method('getAdapter')
            ->willReturn($this->createMock(Yii\Filesystem\S3\Adapter::class));
        $fs = new Yii\Monitoring\Control\Fs([
            'client' => $client,
            'fs' => $fsMock,
        ]);

        $result = $fs->execute();

        $this->assertArrayHasKey('adapter', $result);
    }
}
