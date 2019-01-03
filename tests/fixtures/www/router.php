<?php

if (preg_match('#/login$#', $_SERVER["REQUEST_URI"])) {
    include __DIR__ . '/web/login.html';
}

else if (preg_match('/\.(?:png|jpg|jpeg|gif)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

else {
    return false;
}
