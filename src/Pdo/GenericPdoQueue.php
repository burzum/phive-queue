<?php

namespace Phive\Queue\Pdo;

use Phive\Queue\NoItemAvailableException;

class GenericPdoQueue extends PdoQueue
{
    /**
     * @var array
     */
    protected static $popSqls = [
        'pgsql'     => 'SELECT item FROM %s(%d)',
        'firebird'  => 'SELECT item FROM %s(%d)',
        'informix'  => 'EXECUTE PROCEDURE %s(%d)',
        'mysql'     => 'CALL %s(%d)',
        'cubrid'    => 'CALL %s(%d)',
        'ibm'       => 'CALL %s(%d)',
        'oci'       => 'CALL %s(%d)',
        'odbc'      => 'CALL %s(%d)',
    ];

    /**
     * @var string
     */
    private $routineName;

    public function __construct(\PDO $conn, $tableName, $routineName = null)
    {
        parent::__construct($conn, $tableName);

        $this->routineName = $routineName ?: $this->tableName.'_pop';
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $stmt = $this->conn->query($this->getPopSql());
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        if (false === $result) {
            throw new NoItemAvailableException($this);
        }

        return $result;
    }

    public function getSupportedDrivers()
    {
        return array_keys(static::$popSqls);
    }

    protected function getPopSql()
    {
        return sprintf(
            static::$popSqls[$this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME)],
            $this->routineName,
            time()
        );
    }
}