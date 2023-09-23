<?php

return function (array $args) {
    $this->enableEventGroup('betterforum');
    $args['script'] = __DIR__ . DIRECTORY_SEPARATOR . '../../script/page_forum_group.php';
};