<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Queue\Pdo;

use Exception;
use InvalidArgumentException;
use Phive\Queue\NoItemAvailableException;
use Phive\Queue\Tests\Handler\PdoHandler;
use Phive\Queue\Tests\Queue\Concurrency;
use Phive\Queue\Tests\Queue\Performance;
use Phive\Queue\Tests\Queue\QueueTest;
use Phive\Queue\Tests\Queue\Types;
use Phive\Queue\Tests\Queue\Util;


abstract class PdoQueueTest extends QueueTest
{
    use Concurrency;
    use Performance;
    use Util;

    public function getUnsupportedItemTypes()
    {
        return [Types::TYPE_BINARY_STRING, Types::TYPE_ARRAY, Types::TYPE_OBJECT];
    }

    /**
     * @dataProvider provideItemsOfUnsupportedTypes
     */
    public function testUnsupportedItemType($item, $type)
    {
        $this->expectException(\Throwable::class);
        $this->expectExceptionMessageMatches('/expects parameter 1 to be string|Binary strings are not identical/');

        $this->queue->push($item);

        if (Types::TYPE_BINARY_STRING === $type && $item !== $this->queue->pop()) {
            $this->fail('Binary strings are not identical');
        }
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     */
    public function testThrowExceptionOnMalformedSql($method)
    {
        $options = self::getHandler()->getOptions();
        $options['table_name'] = uniqid('non_existing_table_name_', true);

        $handler = new PdoHandler($options);
        $queue = $handler->createQueue();

        try {
            $this->callQueueMethod($queue, $method);
        } catch (NoItemAvailableException $e) {
        } catch (\PDOException $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @return void
     */
    public function testThrowExceptionOnUnsupportedDriver(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PDO driver "foobar" is unsupported');
        $pdo = new MockPdo();
        $pdo->driverName = 'foobar';

        $handler = self::getHandler();
        $class = $handler->getQueueClass();

        new $class($pdo, $handler->getOption('table_name'));
    }
}
