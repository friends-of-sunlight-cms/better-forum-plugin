<?php

namespace SunlightExtend\BetterForum\Component;

use Sunlight\Core;
use Sunlight\Plugin\ExtendPlugin;
use Sunlight\Router;
use Sunlight\User;

class IconPanelRenderer
{
    public const ICON_DIR_PATH = 'upload/betterforum/';
    public const ICON_FILE = 'forum-%d.png';

    /** @var ExtendPlugin */
    private $plugin;
    /** @var int */
    private $forumId;

    public function __construct(ExtendPlugin $plugin, int $forumId)
    {
        $this->plugin = $plugin;
        $this->forumId = $forumId;
    }

    public function render(): string
    {
        if (isset($_GET['id']) && $this->plugin->getConfig()['show_icon_panel']) {
            $iconPath = self::composeIconPath($this->forumId);

            $fmanLink = '#';
            if (User::hasPrivilege('fileadminaccess')) {
                $fmanLink = Router::admin('fman', ['query' => ['dir' => self::ICON_DIR_PATH]]);
            }

            // activate lightbox (if available)
            $plugins = Core::$pluginManager->getPlugins();
            if ($plugins->hasExtend('lightbox')) {
                $plugins->getExtend('lightbox')->enableEventGroup('lightbox');
            }

            return _buffer(function () use ($iconPath, $fmanLink) { ?>
                <fieldset>
                    <legend><?= _lang('betterforum.forum.iconpanel.caption') ?>
                        <small>(<?= $this->plugin->getOption('name') ?> plugin)</small>
                    </legend>
                    <table>
                        <tbody>
                        <tr>
                            <td class='icon-panel-<?= (is_dir(SL_ROOT . self::ICON_DIR_PATH) ? "ok" : "err") ?>'>
                                <a href='<?= _e($fmanLink) ?>' target='_blank'>
                                    <img src='<?= Router::path('admin/public/images/icons/fman/dir.png') ?>' class='icon' alt='dir'>
                                </a>
                                <?= Router::path(self::ICON_DIR_PATH) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class='icon-panel-<?= (is_file(SL_ROOT . $iconPath) ? "ok" : "err") ?>'>
                                <a href='<?= _e(Router::path($iconPath)) ?>' data-lightbox='icon'>
                                    <img src='<?= Router::path('admin/public/images/icons/fman/image.png') ?>' class='icon' alt='preview'>
                                </a>
                                <?= _e(Router::path($iconPath)) ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </fieldset>";
            <?php });
        }
        return '';
    }

    public static function composeIconPath(int $forumId): string
    {
        return self::ICON_DIR_PATH . sprintf(self::ICON_FILE, $forumId);
    }
}