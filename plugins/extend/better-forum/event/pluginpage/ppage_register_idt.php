<?php

return function (array $args) {
    $idt = $this->getOptions()['extra']['group_idt'];
    $args['infos'][$idt] = _lang('betterforum.type.group.label');
};