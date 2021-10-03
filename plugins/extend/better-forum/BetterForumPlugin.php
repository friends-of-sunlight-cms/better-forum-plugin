<?php

namespace SunlightExtend\BetterForum;

use Sunlight\Extend;
use Sunlight\Plugin\Action\PluginAction;
use Sunlight\Plugin\ExtendPlugin;
use Sunlight\Router;
use Sunlight\User;
use Sunlight\Util\Request;
use Sunlight\Util\UrlHelper;

class BetterForumPlugin extends ExtendPlugin
{
    public const ICON_DIR_PATH = SL_ROOT . 'upload/betterforum';
    public const ICON_FILE = 'forum-%d.png';
    public const GROUP_IDT = 'bf-group';

    public function initialize(): void
    {
        parent::initialize();

        Extend::regm([
            'admin.head' => [$this, 'onAdminHead'],
            'page.plugin.reg' => [$this, 'onPluginPageReg'],
            'page.plugin.' . self::GROUP_IDT => [$this, 'onPluginPageScript'],
            'page.plugin.' . self::GROUP_IDT . '.delete.do' => [$this, 'onPluginPageDelete'],
            'admin.page.editscript' => [$this, 'onPluginPageEditScript'],
            'admin.mod.content-editforum.before' => [$this, 'onBeforeForumEdit'],
        ]);
    }

    /**
     * Inject custom CSS and JS
     *
     * @param array $args
     */
    function onAdminHead(array $args): void
    {
        $args['css'][] = $this->getWebPath() . '/Resources/bf-admin.css';
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
        // pluginpage
        if (
            Request::get('p') === 'content-editpluginpage'
            && $GLOBALS['type_idt'] == BetterForumPlugin::GROUP_IDT
        ) {
            // disabling editing elements
            $GLOBALS['editscript_enable_meta'] = false;
            $GLOBALS['editscript_enable_heading'] = false;
            $GLOBALS['editscript_enable_content'] = false;
            $GLOBALS['editscript_enable_layout'] = false;
            $GLOBALS['editscript_enable_show_heading'] = false;
        }
    }

    /**
     * Modify editforum script - add icon panel
     *
     * @param array $args
     */
    public function onBeforeForumEdit(array $args): void
    {
        if (isset($_GET['id']) && $this->getConfig()->offsetGet('show_icon_panel')) {

            $forumId = (int)Request::get('id');
            $iconPath = self::composeIconPath($forumId);

            $fmanLink = '#';
            if (User::hasPrivilege('fileadminaccess')) {
                $fmanLink = UrlHelper::appendParams(
                    Router::generate('admin/index.php?p=fman'),
                    "dir=" . urlencode(self::ICON_DIR_PATH)
                );
            }

            $iconPanel = "<table id='icon-panel'>
                        <thead><tr><th colspan='2'>" . _lang('betterforum.forum.iconpanel.caption') . " <small>(" . $this->getOption('name') . " plugin)</small></th></tr></thead>
                        <tbody>
                            <tr>
                                <th>
                                    <a href='" . $fmanLink . "' target='_blank'>
                                        <img src='./images/icons/fman/dir.png' class='icon' alt='dir'>
                                    </a>
                                </th>
                                <td class='icon-panel-" . (is_dir(self::ICON_DIR_PATH) ? "ok" : "err") . "'>" . self::ICON_DIR_PATH . "</td>
                            </tr>
                            <tr>
                                <th>
                                    <a href='" . $iconPath . "' data-lightbox='icon'>
                                        <img src='./images/icons/fman/image.png' class='icon' alt='preview'>
                                    </a>
                                </th>
                                <td class='icon-panel-" . (is_file($iconPath) ? "ok" : "err") . "'>" . $iconPath . "</td>
                            </tr>
                        </tbody>
                    </table>";

            // render output
            $args['output'] = $iconPanel . $args['output'];
        }
    }

    /**
     * @param int $forumId
     * @return string
     */
    public static function composeIconPath(int $forumId): string
    {
        return self::ICON_DIR_PATH . '/' . sprintf(self::ICON_FILE, $forumId);
    }

    /**
     * ============================================================================
     *  EXTEND CONFIGURATION
     * ============================================================================
     */

    /**
     * Plugin config
     * @return array
     */
    protected function getConfigDefaults(): array
    {
        return [
            'show_icon_panel' => true,
            'show_topics' => true,
            'show_answers' => true,
            'show_latest' => true,
            'show_latest_answers' => true,
            'pos_latest_answers' => 1, // 0 = at the top; 1 = at the bottom
        ];
    }

    public function getAction(string $name): ?PluginAction
    {
        if ($name === 'config') {
            return new Configuration($this);
        }
        return parent::getAction($name);
    }
}

