<?php

use Phive\Queue\Db\Pdo\SqliteQueue;

class SqlitePdoHandler extends AbstractHandler
{
    /**
     * @var \PDO
     */
    protected static $conn;

    public function prepare()
    {
        self::$conn->exec('DROP TABLE IF EXISTS queue');
        self::$conn->exec('CREATE TABLE queue(id INTEGER PRIMARY KEY AUTOINCREMENT, eta integer NOT NULL, item blob NOT NULL)');
    }

    public function shutdown()
    {
        self::$conn->exec('DROP TABLE IF EXISTS queue');
        self::$conn = null;
    }

    protected function setup()
    {
        $dsn = sprintf('sqlite:%s/phive_tests.sq3', sys_get_temp_dir());
        self::$conn = new \PDO($dsn);
        //self::$conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
    }

    protected function createQueue()
    {
        return new SqliteQueue(self::$conn, 'queue');
    }
}