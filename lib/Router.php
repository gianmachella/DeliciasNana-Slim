<?php

namespace Library;

class Router {
    private $routes = array();

    function addRoute(string $path, \Interfaces\Controller $target){
        if (!isset($this->routes["#".$path."#"])){
            $this->routes["#".$path."#"] = $target;
            return true;
        } else {
            return false;
        }
    }

    function match(string $path){
        foreach ($this->routes as $regex => $target) {
            $res = preg_match_all($regex, $path);
            if ($res>0){
                return $target;
            }
        }
        return null;
    }

    function verRoute(){
        return $this->routes;
    }
}