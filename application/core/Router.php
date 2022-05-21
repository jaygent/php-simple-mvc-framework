<?php

namespace application\core;

use application\core\View;

class Router {

    protected $routes = [];
    protected $param=[];
    protected $data = [];
    
    public function __construct() {
        $arr = require 'application/config/routes.php';
        foreach ($arr as $key => $val) {
            $this->add($key, $val);
        }
    }

    public function add($route, $params) {
        $route = '#^'.$route.'$#';
        $this->routes[$route] = $params;
    }

    public function render() {
        $url = trim($_SERVER['REQUEST_URI'], '/');
        $url=strtok($url,'?');
        $data=[];
        foreach ($this->routes as $route => $params) {
            $this->param=$params;
            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        if (is_numeric($match)) {
                            $match = (int) $match;
                        }
                        $data[$key] = $match;
                    }
                }
                $this->data = array_merge($data,$_GET);
                return true;
            }
        }
        return false;
    }

    public function run(){
        if ($this->render()) {
            $path = 'application\controllers\\'.ucfirst($this->param['controller']).'Controller';
            if (class_exists($path)) {
                $action = $this->param['action'].'Action';
                if (method_exists($path, $action)) {
                    $controller = new $path($this->param,$this->data);
                    $controller->$action();
                } else {
                    View::errorCode(404);
                }
            } else {
              View::errorCode(404);
            }
        } else {
            View::errorCode(404);
        }
    }

}