<?php namespace Sframe\Repository;

abstract class Table extends Base
{
    protected $_model;
    protected $_table;
    protected $_primary_key = 'id';

    protected $_create_time = 'create_time';
    protected $_update_time = 'update_time';
    protected $_delete_time = false; // delete_time


    // ====== Query ======

    public static function findOne($cond = null, $value = null)
    {
        $instance = new static;
        return $instance->_fetchOne($cond, $value);
    }

    public static function findAll($cond = null, $value = null)
    {
        $instance = new static;
        return $instance->_fetchAll($cond, $value);
    }

    protected function _fetchOne($cond = null, $value = null)
    {
        $query = $this->_table($cond, $value);

        if ($this->_model) {
            return $query->fetchRowClass($this->_model);
        } else {
            return $query->fetchRow();
        }
    }

    protected function _fetchAll($cond = null, $value = null, $order_by = null, $limit = null, $page = null)
    {
        $query = $this->_table($cond, $value);
        if ($order_by) {
            $query = $query->orderBy($order_by);
        }
        if ($limit) {
            if (is_array($limit)) {
                $query = $query->limit($limit[0])->offset($limit[1]);
            } else {
                $query = $query->limit($limit, $page);
            }
        }
        if ($this->_model) {
            return $query->fetchAllClass($this->_model);
        } else {
            return $query->fetchAll();
        }
    }



    // ====== Command ======


    public static function count($cond = null, $value = null)
    {
        $instance = new static;
        return $instance->_table($cond, $value)->count();
    }

    public static function save($data, $cond = null, $value = null)
    {
        $instance = new static;
        if ($instance->_table($cond, $value)->exists()) {
            return $instance->_performUpdate($data, $cond, $value);
        } else {
            return $instance->_performInsert($data);
        }
    }

    public static function create($data, &$last_insert_id = null)
    {
        $instance = new static;
        return $instance->_performInsert($data, $last_insert_id);
    }

    public static function update($data, $cond = null, $value = null)
    {
        $instance = new static;
        return $instance->_performUpdate($data, $cond = null, $value = null);
    }

    public static function delete($cond = null, $value = null)
    {
        $instance = new static;
        return $instance->_performDelete($cond = null, $value = null);
    }

    public static function drop($cond = null, $value = null)
    {
        $instance = new static;
        return $instance->_performDelete($cond = null, $value = null, true);
    }



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
        $last_insert_id = $this->connection()->lastInsertId();
        return $result;
    }

    protected function _performUpdate($data, $cond = null, $value = null)
    {
        $this->_dataBuild($data);
        if ($this->_update_time) {
            $data[$this->_update_time] = $this->_datetime();
        }
        return $this->_table($cond, $value)->update($data);
    }

    protected function _performDelete($cond = null, $value = null, $is_drop = false)
    {
        if ($this->_delete_time && $is_drop == false) {
            return $this->_table($cond, $value)->update([$this->_delete_time => $this->_datetime()]);
        } else {
            return $this->_table($cond, $value)->delete();
        }
    }


    protected function _table($cond = null, $value = null)
    {
        if (null === $value && !is_array($cond)) {
            $value = $cond;
            $cond = $this->_primary_key;
        }
        return $this->connection()->table($this->_table, $cond, $value);
    }


    protected function _datetime()
    {
        return D_DATETIME;
    }

    protected function _dataBuild(&$data)
    {
    }
}