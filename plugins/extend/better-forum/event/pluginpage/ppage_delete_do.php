<?php

return function (array $args) {
    // if the page has no dependencies, then just allow deleting
    $args['handled'] = true;
};