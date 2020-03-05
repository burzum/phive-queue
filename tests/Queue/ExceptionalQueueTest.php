<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\ExceptionalQueue;
use Phive\Queue\QueueException;
use PHPUnit\Framework\TestCase;

class ExceptionalQueueTest extends TestCase
{
    use Util;

    protected $innerQueue;
    protected $queue;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->innerQueue = $this->getQueueMock();
        $this->queue = new ExceptionalQueue($this->innerQueue);
    }

    public function testPush()
    {
        $item = 'foo';

        $this->innerQueue->expects($this->once())->method('push')
            ->with($this->equalTo($item));

        $this->queue->push($item);
    }

    public function testPop()
    {
        $this->innerQueue->expects($this->once())->method('pop');
        $this->queue->pop();
    }

    public function testCount()
    {
        $this->innerQueue->expects($this->once())->method('count')
            ->will($this->returnValue(42));

        $this->assertSame(42, $this->queue->count());
    }

    public function testClear()
    {
        $this->innerQueue->expects($this->once())->method('clear');
        $this->queue->clear();
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     */
    public function testThrowOriginalQueueException($method)
    {
        $this->expectException(QueueException::class);

        $this->innerQueue->expects($this->once())->method($method)
            ->will($this->throwException(new QueueException($this->innerQueue)));

        $this->callQueueMethod($this->queue, $method);
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     */
    public function testThrowWrappedQueueException($method)
    {
        $this->expectException(QueueException::class);
        $this->innerQueue->expects($this->once())->method($method)
            ->will($this->throwException(new \Exception()));

        $this->callQueueMethod($this->queue, $method);
    }
}
