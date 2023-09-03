<?php

use Sunlight\Core;
use Sunlight\Extend;
use Sunlight\Hcm;
use Sunlight\User;
use SunlightExtend\BetterForum\BetterForumPlugin;
use SunlightExtend\BetterForum\Component\ForumReader;
use SunlightExtend\BetterForum\Component\Renderer;

defined('SL_ROOT') or exit;

// titulek
$_index->title = $_page['title'];

// obsah
Extend::call('page.bf-group.content.before', $extend_args);
if ($_page['content'] != "") {
    $output .= Hcm::parse($_page['content']) . "\n\n<div class='hr group-hr'><hr></div>\n\n";
}
Extend::call('page.bf-group.content.after', $extend_args);

$userQuery = User::createQuery('p.author');

$bfp = Core::$pluginManager->getPlugins()->getExtend('better-forum');

$forumReader = new ForumReader($id, $userQuery);
$renderer = new Renderer(
    $bfp,
    $forumReader->getGroups(),
    $userQuery
);

// prepare latest answers
$latestAnswers = '';
if ($bfp->getConfig()['show_latest_answers']) {
    $answers = $forumReader->lastestAnswers($forumReader->getIds());
    $latestAnswers = $renderer->renderLatestAnswers($answers);
}

// last answer on top
if ($bfp->getConfig()['pos_latest_answers'] == 0) {
    $output .= $latestAnswers;
}

// render table with groups
$output .= $renderer->render();

// last answer on bottom
if ($bfp->getConfig()['pos_latest_answers'] == 1) {
    $output .= $latestAnswers;
}