<?php namespace Sframe;

use Sutil\Database\DB;
use Sutil\Database\ConnectionInterface;

abstract class Repository
{
    protected $_database;

    /**
     * @var ConnectionInterface
     */
    protected $_connection;

    protected $_model;
    protected $_table;
    protected $_primary_key = 'id';

    protected $_create_time = 'create_time';
    protected $_update_time = 'update_time';
    protected $_delete_time = false; // delete_time


    public function __construct()
    {
        $this->_connection = DB::connect($this->_database);
        $this->_init();
    }

    protected function _init()
    {
    }

    protected function _connection()
    {
        return $this->_connection;
    }

    /**
     * Table query
     * @param mixed $cond
     * @param mixed $value
     * @return \Sutil\Database\Query\Table
     */
    protected function _table($cond = null, $value = null)
    {
        return $this->_connection()->table($this->_table, $cond, $value);
    }

    /**
     * SQL query
     * @param string $sql
     * @param mixed $bind
     * @return \Sutil\Database\Query\Sql
     */
    protected function _sql($sql, $bind = null)
    {
        return $this->_connection()->sql($sql, $bind);
    }


    // ====== R =======


    /**
     * Facade for basic query
     */
    public static function find($cond, $selection = null)
    {
        $instance = new static;
        return $instance->_find($cond, $selection);
    }

    public static function all($selection = null)
    {
        $instance = new static;
        return $instance->_find(null, $selection);
    }


    /**
     * Fetch model(or data) by primary key(or conditions)
     * @param $cond
     * @param string|array $selection
     * @return array|false|object
     */
    protected function _find($cond, $selection = null)
    {
        $table = $this->_table()->select($selection);
        if (is_array($cond)) {
            $table = $table->where($cond);
            if ($this->_model) {
                return $table->fetchAllClass($this->_model);
            } else {
                return $table->fetchAll();
            }
        } else {
            $table = $table->where($this->_primary_key, $cond);
            if ($this->_model) {
                return $table->fetchRowClass($this->_model);
            } else {
                return $table->fetchRow();
            }
        }
    }




    // ====== CUD =======


    /**
     * Facade for CUD call statically
     */
    public static function save($data, $cond = null, $value = null, &$id = null)
    {
        $instance = new static;
        return $instance->save($data, $cond, $value, $id);
    }

    public static function create($data, &$last_insert_id = null)
    {
        $instance = new static;
        return $instance->_performInsert($data, $last_insert_id);
    }

    public static function update($data, $cond = null, $value = null)
    {
        $instance = new static;
        return $instance->_performUpdate($data, $cond, $value);
    }

    public static function delete($cond = null, $value = null)
    {
        $instance = new static;
        return $instance->_performDelete($cond, $value);
    }

    public static function drop($cond = null, $value = null)
    {
        $instance = new static;
        return $instance->_performDelete($cond, $value, true);
    }


    /**
     * Save
     */
    protected function _performSave($data, $cond = null, $value = null, &$id = null)
    {
        $table = $this->_table($cond, $value);
        if ($this->_primary_key) {
            $is_exists = $id = $table->select($this->_primary_key)->fetchOne();
        } else {
            $is_exists = $table->exists();
        }
        if ($is_exists) {
            return $this->_performUpdate($data, $cond, $value);
        } else {
            return $this->_performInsert($data, $id);
        }
    }

    /**
     * Insert
     * @param array $data
     * @param $last_insert_id
     * @return bool
     */
    protected function _performInsert($data, &$last_insert_id = null)
    {
        $this->_dataBuild($data);
        if ($this->_create_time) {
            $data[$this->_create_time] = $this->_datetime();
        }
        if ($this->_update_time) {
            $data[$this->_update_time] = $this->_datetime();
        }
        $result = $this->_table()->insert($data);
        $last_insert_id = $this->_connection()->lastInsertId();
        return $result;
    }

    /**
     * Update
     * @param $data
     * @param mixed $cond
     * @param mixed $value
     * @return bool
     */
    protected function _performUpdate($data, $cond = null, $value = null)
    {
        $this->_dataBuild($data);
        if ($this->_update_time) {
            $data[$this->_update_time] = $this->_datetime();
        }
        return $this->_table($cond, $value)->update($data);
    }

    /**
     * Delete
     * @param mixed $cond
     * @param mixed $value
     * @param bool $is_drop
     * @return bool
     */
    protected function _performDelete($cond = null, $value = null, $is_drop = false)
    {
        if ($this->_delete_time && $is_drop == false) {
            return $this->_table($cond, $value)->update([$this->_delete_time => $this->_datetime()]);
        } else {
            return $this->_table($cond, $value)->delete();
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