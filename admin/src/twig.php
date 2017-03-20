<?php

$twig->addFunction(new Twig_Function('url_segment', function ($id = 0) {
    $segments = explode('/', $_SERVER['REQUEST_URI']);

    if (isset($segments[$id])) {
        return $segments[$id];
    }

    return false;
}));