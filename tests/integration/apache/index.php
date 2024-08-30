<?php

\session_start();

echo "<pre><code>";

var_export([
    'SERVER' => $_SERVER,
    'COOKIE' => $_COOKIE,
    'GET' => $_GET,
    'POST' => $_POST,
    'REQUEST' => $_REQUEST,
]);

echo "</code></pre>";
