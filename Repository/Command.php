<?php namespace Sframe\Repository;

use Sutil\Database\DB;
use Sutil\Database\ConnectionInterface;

abstract class Command
{
    protected $_database;

    protected $_table;

    protected $_primary_key = 'id';

    protected $_create_time = 'create_time';
    protected $_update_time = 'update_time';
    protected $_delete_time = false; // delete_time

    /**
     * @var \Sutil\Database\Query\Table
     */
    protected $_query;

    /**
     * @var ConnectionInterface
     */
    protected $_connection;

    public function __construct()
    {
        $this->_connection = DB::connect($this->_database);
        $this->_query = $this->_connection->table($this->_table);
    }


    public static function save($data, $id = null)
    {
        if ($id) {
            return self::update($data, $id);
        } else {
            return self::create($data);
        }
    }

    public static function create($data, &$last_insert_id = null)
    {
        $instance = new static;
        return $instance->_performInsert($data, $last_insert_id);
    }


    public static function update($data, $id = null)
    {
        $instance = new static;
        return $instance->_performUpdate($data, $id);
    }


    public static function delete($id = null)
    {
        $instance = new static;
        return $instance->_performDelete($id);
    }




    protected function _performInsert($data, &$last_insert_id = null)
    {
        if ($this->_create_time) {
            $data[$this->_create_time] = $this->_datetime();
        }
        if ($this->_update_time) {
            $data[$this->_update_time] = $this->_datetime();
        }
        $result = $this->_query->insert($data);
        $last_insert_id = $this->_connection->lastInsertId();
        return $result;
    }


    protected function _performUpdate($data, $id = null)
    {
        if (null !== $id) {
            $this->_query = $this->_query->where($this->_primary_key, $id);
        }
        if ($this->_update_time) {
            $data[$this->_update_time] = $this->_datetime();
        }
        return $this->_query->update($data);
    }


    protected function _performDelete($id = null)
    {
        if (null !== $id) {
            $this->_query = $this->_query->where($this->_primary_key, $id);
        }
        if ($this->_delete_time) {
            return $this->_query->update([$this->_delete_time => $this->_datetime()]);
        } else {
            return $this->_query->delete();
        }
    }


    protected function _datetime()
    {
        return D_DATETIME;
    }
}