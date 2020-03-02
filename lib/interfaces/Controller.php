<?php

namespace Interfaces;

interface Controller {

    function get($get, $post, $session);

    function post($get, $post, $session);
}