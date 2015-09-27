<?php namespace Sframe;

use Sutil\Http\Input;

abstract class InputRequest
{
    protected $_data;

    public function __construct($input = null, $invalid_call = null, $valid_call = null)
    {
        if (null === $input) {
            $input = Input::all();
        }
        $this->_prepare($input);
        foreach ($this->_rules() as $field => $rule) {

        }
    }

    /**
     * Prepare input data
     */
    protected function _prepare(&$input)
    {
    }

    /**
     * Implement and set rules
     */
    protected function _rules()
    {
    }

    /**
     * If valid
     * @return boolean
     */
    public function isValid()
    {

    }

    /**
     * Get error message
     * @return string
     */
    public function getMessage()
    {

    }

    /**
     * Get value by key
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : $default;
    }

    /**
     * Get all data
     * @return array
     */
    public function data()
    {
        return $this->_data;
    }

}