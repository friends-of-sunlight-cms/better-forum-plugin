<?php

namespace SunlightExtend\BetterForum;

use Sunlight\Extend;
use Sunlight\Plugin\ExtendPlugin;
use Sunlight\Util\Request;

class BetterForumPlugin extends ExtendPlugin
{
    public const GROUP_IDT = 'bf-group';

    public function initialize(): void
    {
        parent::initialize();

        Extend::regm([
            'page.plugin.reg' => [$this, 'onPagePluginReg'],
            'page.plugin.' . self::GROUP_IDT => [$this, 'onForumGroup'],
            'admin.page.editscript' => [$this, 'onPageEditScript']
        ]);
    }

    public function onPagePluginReg(array $args): void
    {
        $args['infos'][self::GROUP_IDT] = _lang('betterforum.type.group.label');
    }

    public function onForumGroup(array $args): void
    {
        $args['script'] = __DIR__ . '/Resources/script/page-forum-group.php';
    }

    public function onPageEditScript($args): void
    {
        if (
            Request::get('p') === 'content-editpluginpage'
            && $GLOBALS['type_idt'] == BetterForumPlugin::GROUP_IDT
        ) {
            $GLOBALS['editscript_enable_meta'] = false;
            $GLOBALS['editscript_enable_perex'] = false;
            $GLOBALS['editscript_enable_heading'] = false;
            $GLOBALS['editscript_enable_content'] = false;
            $GLOBALS['editscript_enable_layout'] = false;
            $GLOBALS['editscript_enable_show_heading'] = false;
        }
    }

    protected function getConfigDefaults(): array
    {
        return [
            'show_topics' => true,
            'show_answers' => true,
            'show_latest' => true,
        ];
    }
}

