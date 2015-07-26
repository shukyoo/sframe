<?php

class TestController extends BaseController
{
    public function actIndex()
    {
        echo 'index';
    }


    public function actTest()
    {
        $a = 11;

        include $this->template('test/hello');
    }
}

