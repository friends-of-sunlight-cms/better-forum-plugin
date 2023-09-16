<?php

use Sunlight\Util\Request;
use SunlightExtend\BetterForum\Component\IconPanelRenderer;

return function (array $args) {
    global $_admin;
    // pluginpage
    if (
        $_admin->currentModule === 'content-editpluginpage'
        && $GLOBALS['type_idt'] == 'bf-group'
    ) {
        // disabling editing elements
        $GLOBALS['editscript_enable_meta'] = false;
        $GLOBALS['editscript_enable_heading'] = false;
        $GLOBALS['editscript_enable_content'] = false;
        $GLOBALS['editscript_enable_layout'] = false;
        $GLOBALS['editscript_enable_show_heading'] = false;
    }

    // forum
    if ($_admin->currentModule === 'content-editforum') {

        $this->enableEventGroup('betterforum');

        $iconPanelRenderer = new IconPanelRenderer($this, (int)Request::get('id'));
        $GLOBALS['editscript_setting_extra'] .= $iconPanelRenderer->render();
    }
};