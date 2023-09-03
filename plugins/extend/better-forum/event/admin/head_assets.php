<?php

return function (array $args) {
    $args['css'][] = $this->getAssetPath('public/bf-admin.css');
};