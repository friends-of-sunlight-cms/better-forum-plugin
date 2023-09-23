<?php

namespace SunlightExtend\BetterForum;

use Sunlight\Action\ActionResult;
use Sunlight\Database\Database as DB;
use Sunlight\Message;
use Sunlight\Page\Page;
use Sunlight\Plugin\Action\RemoveAction as BaseRemoveAction;

class RemoveAction extends BaseRemoveAction
{

    protected function execute(): ActionResult
    {
        $pages = DB::queryRows("SELECT id, title FROM " . DB::table('page') . " WHERE `type`=" . Page::PLUGIN . " AND type_idt='bf-group'");
        if (!empty($pages)) {

            $list = [];
            foreach ($pages as $page) {
                $list[] = sprintf("%s (id: %d)", $page['title'], $page['id']);
            }

            return ActionResult::failure(
                Message::list($list, [
                    'type' => Message::ERROR,
                    'text' => _lang('betterforum.remove.error'),
                    'list' => ['lcfirst' => false, 'escape' => false],
                ])
            );
        }
        return parent::execute();
    }
}