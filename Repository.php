<?php namespace Sframe;
use Sutil\Database\Query\Table as TableQuery;
use Sutil\Database\DB;

abstract class Repository
{
    protected $_connection;
    protected $_table;
    protected $_primary_key = 'id';

    protected $_create_time = 'create_time';
    protected $_update_time = 'update_time';
    protected $_delete_time; // soft delete | delete_time



    protected function _connection()
    {
        return DB::connect($this->_connection);
    }

    protected function _table()
    {
        return $this->_connection()->table($this->_table);
    }

    protected function _sql($sql, $bind)
    {
        return $this->_connection()->sql($sql, $bind);
    }

    /**
     * Insert
     * @param array $data
     * @param $last_insert_id
     * @return bool
     */
    protected function _performInsert($data, &$last_insert_id = 0)
    {
        $this->_dataBuild($data);
        if ($this->_create_time && empty($data[$this->_create_time])) {
            $data[$this->_create_time] = $this->_datetime();
        }
        if ($this->_update_time && empty($data[$this->_update_time])) {
            $data[$this->_update_time] = $this->_datetime();
        }
        $result = $this->_table()->insert($data);
        if ($result) {
            $last_insert_id = $this->_connection()->lastInsertId();
        }
        return $result;
    }

    /**
     * Update
     * @param $data
     * @param mixed $cond
     * @param mixed $value
     * @return bool
     */
    protected function _performUpdate($data, $where = null)
    {
        $this->_dataBuild($data);
        if ($this->_update_time && empty($data[$this->_update_time])) {
            $data[$this->_update_time] = $this->_datetime();
        }
        return $this->_table()->update($data, $where);
    }

    /**
     * Save
     */
    protected function _performSave($data, $where = null, &$id = null)
    {
        $table = $this->_table();
        if ($this->_primary_key) {
            $is_exists = $id = $table->select($this->_primary_key)->fetchOne();
        } else {
            $is_exists = $table->exists();
        }
        if ($is_exists) {
            return $this->_performUpdate($data, $where);
        } else {
            return $this->_performInsert($data, $id);
        }
    }

    /**
     * Delete
     * @param mixed $cond
     * @param mixed $value
     * @param bool $is_drop
     * @return bool
     */
    protected function _performDelete($where = null, $is_drop = false)
    {
        if ($this->_delete_time && $is_drop == false) {
            return $this->_table()->update([$this->_delete_time => $this->_datetime()], $where);
        } else {
            return $this->_table()->delete($where);
        }
    }

    /**
     * Overwrite for rebuild the data for CUD
     * @param $data
     */
    protected function _dataBuild(&$data)
    {
    }

    /**
     * Get current datetime
     */
    protected function _datetime()
    {
        return D_DATETIME;
    }
}