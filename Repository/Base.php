<?php namespace Sframe\Repository;

use Sutil\Database\DB;
use Sutil\Database\ConnectionInterface;

abstract class Base
{
    protected $_database;

    /**
     * @var ConnectionInterface
     */
    protected $_connection;


    public function __construct()
    {
        $this->_connection = DB::connect($this->_database);
    }


    public function connection()
    {
        return $this->_connection;
    }
}