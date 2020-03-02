<?php

namespace Library;

class  DecorationRole implements \Interfaces\Controller{
    private $page;
    public function __construct(\Interfaces\Controller $page){
        $this->page = $page;
    }
public function get($get, $post, $session){
    if (!empty($_SESSION["logeado"])){
        if ($_SESSION['logeado']==true){
            return $this->page->get($get, $post, $session);
        } else {
            header('Location: index.php?page=login');
        }
      }else {
        header('Location: index.php?page=login');     
      }
    }

public function post($get, $post, $session){
    if (!empty($_SESSION["logeado"])){
        if ($_SESSION['logeado']==true){
            return $this->page->post($get, $post, $session);
        } else {
            header('Location: index.php?page=login');
        }
      }else {
        header('Location: index.php?page=login');   
      }
}
}