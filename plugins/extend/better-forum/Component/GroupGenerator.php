<?php

namespace SunlightExtend\BetterForum\Component;

use Sunlight\Core;
use Sunlight\Database\Database as DB;
use Sunlight\Extend;
use Sunlight\GenericTemplates;
use Sunlight\Page\Page;
use Sunlight\Post\Post;
use Sunlight\Post\PostService;
use Sunlight\Router;
use Sunlight\Template;
use Sunlight\User;
use Sunlight\Util\ConfigurationFile;
use Sunlight\Util\StringManipulator;
use SunlightExtend\BetterForum\BetterForumPlugin;
use SunlightExtend\BetterForum\Component\Filter\BetterForumTreeFilter;

class GroupGenerator
{
    /** @var ConfigurationFile */
    private $config;
    /** @var int */
    private $parentId;
    /** @var int */
    private $maxDepth;
    /** @var array */
    private $forumExtraData = [];
    /** @var array data for queries with users */
    private $userQuery;
    /** @var bool */
    private $hl = true;

    /**
     * @param int $parentForumGroupId
     */
    public function __construct(int $parentForumGroupId)
    {
        // plugin config
        $this->config = Core::$pluginManager->getExtend('better-forum')->getConfig();

        $this->parentId = $parentForumGroupId;
        $this->maxDepth = 2;

        $this->userQuery = User::createQuery('p.author');
    }

    /**
     * @return string
     */
    public function render()
    {
        $output = "";
        $pages = $this->getPages(true);

        // event
        Extend::call('page.' . BetterForumPlugin::GROUP_IDT . '.pages', [
            'pages' => &$pages
        ]);

        if (count($pages)>0) {
            // forum group data
            $pg = $this->preparePageGroups($pages);
            $this->forumExtraData = $this->getExtraData($pg['ids']);

            // render tables
            foreach ($pg['groups'] as $group) {
                $output .= $this->renderTable($group['rows'], $group['group_name']);
            }
        } else {
            $output .= "<p>" . _lang('global.nokit') . "</p>";
        }

        return $output;
    }

    /**
     * @param array $pages
     * @return array [ids:array, groups:array]
     */
    private function preparePageGroups(array $pages): array
    {
        $ids = [];
        $groups = [];
        foreach ($pages as $page) {
            if ($page['type'] == Page::FORUM) {
                // set category name by parent title
                $groups[$page['node_parent']]['group_name'] = $groups[$page['node_parent']]['group_name'] ?? '';
                // add row
                $groups[$page['node_parent']]['rows'][$page['id']] = $page;
                $ids[] = $page['id'];
            } elseif (
                $page['type'] == Page::PLUGIN
                && $page['type_idt'] == BetterForumPlugin::GROUP_IDT
            ) {
                // if type is bf-group then set only group_name
                $groups[$page['id']] = ['group_name' => $page['title'], 'rows' => []];
            }
        }

        // remove groups without rows
        foreach ($groups as $k => $group) {
            if (count($group['rows']) === 0) {
                unset($groups[$k]);
            }
        }

        // event
        Extend::call('page.' . BetterForumPlugin::GROUP_IDT . '.groups', [
            'groups' => &$groups
        ]);

        return ['ids' => $ids, 'groups' => $groups];
    }

    /**
     * @param array $rows
     * @param string $groupName
     * @return string
     */
    private function renderTable(array $rows, string $groupName = ''): string
    {
        // reset highlight
        $this->hl = true;

        // render table
        $output = "\n<table class='topic-table'>\n<thead>
        <tr>
            <th colspan = '2'> " . ($groupName !== '' ? _e($groupName) : _lang('betterforum.list.category')) . "</th>";
        $output .= ($this->config->offsetGet('show_topics') ? "<th>" . _lang('betterforum.list.topics') . "</th>" : "");
        $output .= ($this->config->offsetGet('show_answers') ? "<th>" . _lang('betterforum.list.answers') . "</th>" : "");
        $output .= ($this->config->offsetGet('show_latest') ? "<th>" . _lang('global.lastanswer') . "</th>" : "");
        $output .= "</tr>
        </thead>\n<tbody>\n";

        foreach ($rows as $row) {
            // render row
            $output .= $this->renderRow($row);
            // change highlight
            $this->hl = !$this->hl;
        }
        $output .= " </tbody> \n</table> ";

        return $output;
    }

    /**
     * @param ForumIcon $icon
     * @param array $rowData
     * @return string
     */
    private function renderRow(array $rowData)
    {
        $countTopics = $this->forumExtraData[$rowData['id']]['count_topics'] ?? 0;
        $countAnswers = $this->forumExtraData[$rowData['id']]['count_answers'] ?? 0;
        $latestPost = (isset($this->forumExtraData[$rowData['id']])
            ? $this->renderLatestPost($this->forumExtraData[$rowData['id']])
            : "---"
        );

        // icon
        $icon = new ForumIcon();
        $icon->name = ($countAnswers > 0 ? 'normal' : 'new');
        $icon->path = Template::image('icons/topic-' . $icon->name . '.png');
        $icon->alt = _lang('posts.topic.' . $icon->name);

        // event
        Extend::call('page.' . BetterForumPlugin::GROUP_IDT . '.item', [
            'item' => &$rowData,
            'icon' => &$icon,
            'count_topics' => $countTopics,
            'count_answers' => $countAnswers,
        ]);

        return " <tr class='topic-" . _e($icon->name) . ($this->hl ? ' topic-hl' : '') . "'>
            <td class='topic-icon-cell'>
                <a href='" . Router::page($rowData['id'], $rowData['slug']) . "'>
                    <img src='" . _e($icon->path) . "' alt='" . _e($icon->alt) . "'>
                </a>
            </td>
            <td class='topic-main-cell'>
                <a href='" . Router::page($rowData['id'], $rowData['slug']) . "'> " . $rowData['title'] . "</a><br>
                <small> "
            . ($rowData['perex'] !== '' ? StringManipulator::ellipsis($rowData['perex'], 64) : '') .
            "</small>
            </td>"
            . ($this->config->offsetGet('show_topics') ? "<td>" . $countTopics . "</td>" : "")
            . ($this->config->offsetGet('show_answers') ? "<td>" . $countAnswers . "</td>" : "")
            . ($this->config->offsetGet('show_latest') ? "<td>" . $latestPost . "</td>" : "")
            . "</tr> \n";
    }


    private function renderLatestPost(array $data): string
    {
        if ($data['author'] != -1) {
            $lastAuthor = Router::userFromQuery($this->userQuery, $data, ['class' => 'post-author', 'max_len' => 16]);
        } else {
            $lastAuthor = "<span class='post-author-guest'> " . StringManipulator::ellipsis(PostService::renderGuestName($data['guest']), 16) . "</span> ";
        }

        $topicTitle = $data['topic_title'] ?? $data['subject'];
        $topicId = ($data['xhome'] != -1 ? $data['xhome'] : $data['id']);
        return '<span class="answer-latest">
                <a href="' . Router::topic($topicId, $data['topic_slug']) . '" title="' . $topicTitle . '">' . StringManipulator::ellipsis($topicTitle, 24) . '</a><br>
                <small class="post-info"><em>' . $lastAuthor . '</em> (' . GenericTemplates::renderTime($data['topic_bumptime'] ?? $data['time'], 'post') . ')</small>
                </span>';
    }

    /**
     * Returns children pages
     *
     * @param bool $onlyChildrens
     * @return array
     */
    private function getPages(bool $onlyChildrens = false): array
    {
        $pages = Page::getFlatTree(
            $this->parentId,
            $this->maxDepth,
            new BetterForumTreeFilter([]),
            ['perex']
        );

        if ($onlyChildrens) {
            $pages = Page::getTreeReader()->extractChildren($pages, $this->parentId, true);
        }
        return $pages;
    }


    /**
     * Returns latest answer, topic count and answer count
     *
     * @param array $ids
     * @return array[]|bool
     */
    private function getExtraData(array $ids)
    {
        $result = DB::queryRows(
            "SELECT p . id,p . home,p . xhome,p . author,p . guest,p . time,p . subject,pg . slug as topic_slug,
                t . subject as topic_title,t . bumptime as topic_bumptime,
                (SELECT COUNT(*) FROM " . DB::table('post') . " WHERE type = " . Post::FORUM_TOPIC . " and home = p . home and xhome = -1) as count_topics,
                (SELECT COUNT(*) FROM " . DB::table('post') . " WHERE type = " . Post::FORUM_TOPIC . " and home = p . home and xhome != -1) as count_answers,
                " . $this->userQuery['column_list'] . " 
                FROM " . DB::table('post') . " p " . $this->userQuery['joins'] . "
                LEFT JOIN " . DB::table('post') . " t ON(t . id = p . xhome)
                LEFT JOIN " . DB::table('page') . " pg ON(pg . id = p . home)
                WHERE p . id IN(SELECT MAX(id) FROM " . DB::table('post') . " WHERE type = " . Post::FORUM_TOPIC . " and home IN(" . DB::arr($ids) . ") 
                GROUP BY home)",
            'home'
        );

        return $result;
    }
}
