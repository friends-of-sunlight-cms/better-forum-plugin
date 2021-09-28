<?php

use Sunlight\Extend;
use Sunlight\Hcm;
use SunlightExtend\BetterForum\BetterForumPlugin;
use SunlightExtend\BetterForum\Component\GroupGenerator;


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

$reader = new GroupGenerator($id);
$output.=$reader->render();