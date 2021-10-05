<?php

namespace SunlightExtend\BetterForum\Component;

use Sunlight\Extend;
use Sunlight\GenericTemplates;
use Sunlight\Post\PostService;
use Sunlight\Router;
use Sunlight\Template;
use Sunlight\Util\StringManipulator;
use SunlightExtend\BetterForum\BetterForumPlugin;

class Renderer
{
    /** @var bool */
    private $hl = false;
    /** @var array */
    private $groups = [];

    /**
     * @param BetterForumPlugin $betterForumPlugin
     * @param array $userQuery
     */
    public function __construct(BetterForumPlugin $betterForumPlugin, array $groups, array $userQuery)
    {
        // plugin config
        $this->config = $betterForumPlugin->getConfig();
        $this->groups = $groups;
        $this->userQuery = $userQuery;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $output = "";
        if (count($this->groups) > 0) {
            // render tables
            foreach ($this->groups as $group) {
                $output .= $this->renderTable($group['rows'], $group['group_name']);
            }
        } else {
            $output .= "<p>" . _lang('global.nokit') . "</p>";
        }

        return $output;
    }

    public function renderLatestAnswers(array $answers): string
    {
        $output = "\n<div class='post-answer-list'>\n<h3>" . _lang('posts.forum.lastact') . "</h3>\n";
        if (count($answers) > 0) {
            $output .= "<table class='topic-latest'>\n";
            foreach ($answers as $answer){
                if ($answer['author'] != -1) {
                    $author = Router::userFromQuery($this->userQuery, $answer);
                } else {
                    $author = "<span class='post-author-guest'>" . PostService::renderGuestName($answer['guest']) . "</span>";
                }
                $output .= "<tr>
                                <td><a href='" . Router::topic($answer['topic_id'], $answer['topic_slug']) . "'>" . $answer['topic_subject'] . "</a></td>
                                <td>" . $author . "</td>
                                <td>" . GenericTemplates::renderTime($answer['time'], 'post') . "</td>
                            </tr>\n";
            }
            $output .= "</table>\n\n";

        } else {
            $output .= "<p>" . _lang('global.nokit') . "</p>";
        }
        $output .= "</div>\n";

        return $output;
    }

    /**
     * @param array $rows
     * @param string $groupName
     * @return string
     */
    private function renderTable(array $rows, string $groupName = ''): string
    {
        // reset highlight
        $this->hl = false;

        // render table
        $output = "\n<table class='topic-table'>\n<thead>
        <tr>
            <th colspan='2'>" . ($groupName !== '' ? _e($groupName) : _lang('betterforum.list.category')) . "</th>";
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
        $output .= "</tbody>\n</table> ";

        return $output;
    }

    /**
     * @param ForumIcon $icon
     * @param array $rowData
     * @return string
     */
    private function renderRow(array $rowData)
    {
        // counters
        $countTopics = $rowData['_extra']['count_topics'] ?? 0;
        $countAnswers = $rowData['_extra']['count_answers'] ?? 0;

        // latest post
        $latestPost = (isset($rowData['_extra']['latest_post'])
            ? $this->renderLatestPost($rowData['_extra']['latest_post'])
            : "---"
        );

        // icon
        $icon = new ForumIcon();

        $customIconFile = BetterForumPlugin::composeIconPath($rowData['id']);
        if (is_file($customIconFile)) {
            $icon->name = 'custom';
            $icon->path = Router::generate($customIconFile);
            $icon->alt = 'custom icon';
        } else {
            $icon->name = ($countAnswers > 0 ? 'normal' : 'new');
            $icon->path = Template::image('icons/topic-' . $icon->name . '.png');
            $icon->alt = _lang('posts.topic.' . $icon->name);
        }

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

}
