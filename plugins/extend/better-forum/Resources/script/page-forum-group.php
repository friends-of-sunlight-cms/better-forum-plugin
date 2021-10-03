<?php

use Sunlight\Extend;
use Sunlight\Hcm;
use Sunlight\User;
use SunlightExtend\BetterForum\BetterForumPlugin;
use SunlightExtend\BetterForum\Component\ForumReader;
use SunlightExtend\BetterForum\Component\Renderer;


defined('SL_ROOT') or exit;

$idt_type = BetterForumPlugin::GROUP_IDT;

// titulek
$_index->title = $_page['title'];

// obsah
Extend::call('page.' . $idt_type . '.content.before', $extend_args);
if ($_page['content'] != "") {
    $output .= Hcm::parse($_page['content']) . "\n\n<div class='hr group-hr'><hr></div>\n\n";
}
Extend::call('page.' . $idt_type . '.content.after', $extend_args);

$userQuery = User::createQuery('p.author');

/** @var $bfp BetterForumPlugin */
$bfp = BetterForumPlugin::getInstance();

$forumReader = new ForumReader($id, $userQuery);
$renderer = new Renderer(
    $bfp,
    $forumReader->getGroups(),
    $userQuery
);

// prepare latest anwers
$latestAnswers = '';
if ($bfp->getConfig()->offsetGet('show_latest_answers')) {
    $answers = $forumReader->lastestAnswers($forumReader->getIds());
    $latestAnswers = $renderer->renderLatestAnswers($answers);
}

// last answer on top
if ($bfp->getConfig()->offsetGet('pos_latest_answers') == 0) {
    $output .= $latestAnswers;
}

// render table with groups
$output .= $renderer->render();

// last answer on bottom
if ($bfp->getConfig()->offsetGet('pos_latest_answers') == 1) {
    $output .= $latestAnswers;
}
