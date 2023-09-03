<?php

namespace SunlightExtend\BetterForum\Component;

use Sunlight\Core;
use Sunlight\Database\Database as DB;
use Sunlight\Extend;
use Sunlight\Page\Page;
use Sunlight\Post\Post;
use Sunlight\Settings;
use SunlightExtend\BetterForum\Component\Filter\BetterForumTreeFilter;

class ForumReader
{
    /** @var int */
    private $parentId;
    /** @var int */
    private $maxDepth;
    /** @var array */
    private $ids = [];
    /** @var array */
    private $pages = [];
    /** @var array */
    private $groups = [];

    /** @var bool */
    private $initialited = false;
    /** @var array */
    private $userQuery;

    /** @var string */
    private $typeIdt;

    /**
     * @param int $parentId
     * @param array $userQuery
     */
    public function __construct(int $parentId, array $userQuery)
    {
        $this->parentId = $parentId;
        $this->maxDepth = 2;

        $this->userQuery = $userQuery;

        $this->typeIdt = Core::$pluginManager
            ->getPlugins()
            ->getExtend('better-forum')
            ->getOptions()['extra']['group_idt'];
    }

    /**
     * Return childrens ids
     *
     * @return array
     */
    public function getIds(): array
    {
        $this->init();
        return $this->ids;
    }

    /**
     * Return page groups with extra data
     *
     * @return array
     */
    public function getGroups(): array
    {
        $this->init();
        return $this->groups;
    }

    private function init(): void
    {
        if (!$this->initialited) {
            $this->loadPages();
            if (count($this->pages) > 0) {
                $this->groupingPages();
                $extra = $this->getExtraData($this->ids);
                $this->pairPagesAndExtras($extra);
            }

            $this->initialited = true;
        }
    }

    /**
     * Load children forum pages
     */
    private function loadPages(): void
    {
        $pages = Page::getFlatTree(
            $this->parentId,
            $this->maxDepth,
            new BetterForumTreeFilter([]),
            ['perex']
        );
        $pages = Page::getTreeReader()->extractChildren($pages, $this->parentId, true);

        $this->pages = $pages;
    }

    /**
     * Convert pages to group
     */
    private function groupingPages(): void
    {
        $ids = [];
        $groups = [];
        foreach ($this->pages as $page) {
            if ($page['type'] == Page::FORUM) {
                // set category name by parent title
                $groups[$page['node_parent']]['group_name'] = $groups[$page['node_parent']]['group_name'] ?? '';
                // add row
                $groups[$page['node_parent']]['rows'][$page['id']] = $page;
                $ids[] = $page['id'];
            } elseif (
                $page['type'] == Page::PLUGIN
                && $page['type_idt'] == $this->typeIdt
            ) {
                // if type is bf-group then set only group_name
                $groups[$page['id']] = ['group_name' => $page['title'], 'rows' => []];
            }
        }

        // remove groups without childs
        foreach ($groups as $k => $group) {
            if (count($group['rows']) === 0) {
                unset($groups[$k]);
            }
        }

        // event
        Extend::call('page.' . $this->typeIdt . '.groups', [
            'groups' => &$groups
        ]);

        $this->ids = $ids;
        $this->groups = $groups;
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
            "SELECT p.id,p.home,p.xhome,p.author,p.guest,p.time,p.subject,pg.slug as topic_slug,
                t.subject as topic_title,t.bumptime as topic_bumptime,
                (SELECT COUNT(*) FROM " . DB::table('post') . " WHERE type=" . Post::FORUM_TOPIC . " and home = p.home and xhome=-1) as count_topics,
                (SELECT COUNT(*) FROM " . DB::table('post') . " WHERE type=" . Post::FORUM_TOPIC . " and home = p.home and xhome!=-1) as count_answers,
                " . $this->userQuery['column_list'] . " 
                FROM " . DB::table('post') . " p " . $this->userQuery['joins'] . "
                LEFT JOIN " . DB::table('post') . " t ON(t.id = p.xhome)
                LEFT JOIN " . DB::table('page') . " pg ON(pg.id = p.home)
                WHERE p.id IN(SELECT MAX(id) FROM " . DB::table('post') . " WHERE type = " . Post::FORUM_TOPIC . " and home IN(" . DB::arr($ids) . ") 
                GROUP BY home)",
            'home'
        );

        return $result;
    }

    /**
     * Returns lastest post for topics
     *
     * @param array $ids
     * @return array
     */
    public function lastestAnswers(array $ids): array
    {
        $answers = [];
        if (count($ids) > 0) {
            $answers = DB::queryRows(
                "SELECT topic.id AS topic_id,topic.subject AS topic_subject,p.author,p.guest,p.time,page.slug as topic_slug,"
                . $this->userQuery['column_list'] . " 
            FROM " . DB::table('post') . " AS p 
            JOIN " . DB::table('post') . " AS topic ON(topic.type=" . Post::FORUM_TOPIC . " AND topic.id=p.xhome) 
            JOIN " . DB::table('page') . " AS page ON(topic.home=page.id) 
            " . $this->userQuery['joins'] . " 
            WHERE p.type=" . Post::FORUM_TOPIC . " AND p.home IN(" . DB::arr($ids) . ") AND p.xhome!=-1 
            ORDER BY p.id DESC LIMIT " . Settings::get('extratopicslimit')
            );
        }

        return $answers;
    }

    /**
     * Pair loaded pages with extra data
     *
     * @param array $extra
     */
    private function pairPagesAndExtras(array $extra): void
    {
        foreach ($this->groups as $groupId => $group) {
            foreach ($group['rows'] as $forumId => $forumData) {
                $extraData = [];
                if (isset($extra[$forumId])) {
                    $extraData['count_topics'] = $extra[$forumId]['count_topics'];
                    $extraData['count_answers'] = $extra[$forumId]['count_answers'];
                    unset($extra[$forumId]['count_topics'], $extra[$forumId]['count_answers']);
                    $extraData['latest_post'] = $extra[$forumId];
                }
                $this->groups[$groupId]['rows'][$forumId]['_extra'] = $extraData;
            }
        }
    }
}
