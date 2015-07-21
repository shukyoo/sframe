<?php

class HomeController extends \Sframe\Controller
{
    public function act()
    {
        echo $this->router->uri_base;
    }
}

