<?php namespace Sframe;
use Sutil\Validation\Validator;

abstract class Input
{
    protected $_input;
    protected $_data;
    protected $_is_valid = false;
    protected $_message = '';

    public function __construct($input = null, $invalid_call = null, $valid_call = null)
    {
        $this->_input = (null === $input) ? Request::all() : $input;
        $this->_data = $this->_prepare($this->_input);
        $this->_is_valid = Validator::check($this->_data, $this->_rules(), $this->_message);
    }

    /**
     * Prepare input data
     */
    protected function _prepare($input)
    {
        return $input;
    }

    /**
     * Implement and set rules
     */
    abstract protected function _rules();

    /**
     * If valid
     * @return boolean
     */
    public function isValid()
    {
        return $this->_is_valid;
    }

    /**
     * Get error message
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Get all data
     * @return array
     */
    public function data()
    {
        return $this->_data;
    }

    /**
     * Get value
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }


    public function withMessage()
    {
        \Sutil\Session\Session::set('_input_msg', $this->getMessage());
        return $this;
    }

    public function withInput()
    {
        \Sutil\Session\Session::set('_input_data', $this->_input);
        return $this;
    }

    public function redirect($uri)
    {
        redirect($uri);
    }

}