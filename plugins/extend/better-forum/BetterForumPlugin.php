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
            'page.plugin.reg' => [$this, 'onPluginPageReg'],
            'page.plugin.' . self::GROUP_IDT => [$this, 'onPluginPageScript'],
            'page.plugin.' . self::GROUP_IDT . '.group_infos' => [$this, 'onPluginPageGroupInfos'],
            'page.plugin.' . self::GROUP_IDT . '.delete.confirm' => [$this, 'onPluginPageDelete'],
            'page.plugin.' . self::GROUP_IDT . '.delete.do' => [$this, 'onPluginPageDelete'],
            'admin.page.plugin.' . self::GROUP_IDT . '.edit' => [$this, 'onPluginPageEdit'],
            'admin.page.editscript' => [$this, 'onPluginPageEditScript'],
        ]);
    }

    /**
     * Page type registration event
     *
     * @param array $args
     */
    public function onPluginPageReg(array $args): void
    {
        $args['infos'][self::GROUP_IDT] = _lang('betterforum.type.group.label');
    }

    /**
     * Web script registration
     *
     * @param array $args
     */
    public function onPluginPageScript(array $args): void
    {
        $args['script'] = __DIR__ . '/Resources/script/page-forum-group.php';
    }

    /**
     * Setting up additional information for a 'group' page
     *
     * @param array $args
     */
    public function onPluginPageGroupInfos(array $args): void
    {
    }

    /**
     * Page dependency processing
     *
     * @param array $args
     */
    public function onPluginPageDeleteConfirm(array $args): void
    {
    }

    /**
     * Page deletion processing
     *
     * @param array $args
     */
    public function onPluginPageDelete(array $args): void
    {
        // if the page has no dependencies, then just allow deleting
        $args['handled'] = true;
    }


    /**
     * Modification of the page editing form
     *
     * @param array $args
     */
    public function onPluginPageEditScript(array $args): void
    {
        if (
            Request::get('p') === 'content-editpluginpage'
            && $GLOBALS['type_idt'] == BetterForumPlugin::GROUP_IDT
        ) {
            // disabling editing elements
            $GLOBALS['editscript_enable_meta'] = false;
            $GLOBALS['editscript_enable_perex'] = false;
            $GLOBALS['editscript_enable_heading'] = false;
            $GLOBALS['editscript_enable_content'] = false;
            $GLOBALS['editscript_enable_layout'] = false;
            $GLOBALS['editscript_enable_show_heading'] = false;
        }
    }

    /**
     * Edit preparation
     *
     * @param array $args
     */
    public function onPluginPageEdit(array $args): void
    {
    }

    /**
     * Plugin config
     * @return array
     */
    protected function getConfigDefaults(): array
    {
        return [
            'show_topics' => true,
            'show_answers' => true,
            'show_latest' => true,
        ];
    }
}

