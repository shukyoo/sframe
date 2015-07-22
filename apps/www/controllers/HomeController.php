<?php

class HomeController extends \Sframe\Controller
{

    public function route($path_arr)
    {
        if (is_numeric($path_arr[1])) {
            return ['show', [$path_arr[1]]];
        } elseif ($path_arr[1] == 'hello') {
            return 'test';
        }
    }

    public function init()
    {
        echo 'start -- ';
    }

    public function actIndex()
    {
        echo 'index';
    }

    public function actShow($id)
    {
        echo $id;
    }

    public function actTest()
    {
        echo 'test';
    }

    public function actTt()
    {
        echo 'tt';
    }
}

