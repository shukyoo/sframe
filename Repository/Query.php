<?php namespace Sframe\Repository;

use Sutil\Database\ConnectionInterface;

abstract class Repository
{
    /**
     * @var ConnectionInterface
     */
    protected $_connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->_connection = $connection;
    }

    protected function _table($table, $where_cond = null, $where_value = null)
    {
        return $this->_connection->table($table, $where_cond, $where_value);
    }

    protected function _sql($sql, $bind = null)
    {
        return $this->_connection->sql($sql, $bind);
    }

    protected function _lastInsertId()
    {
        return $this->_connection->lastInsertId();
    }
}