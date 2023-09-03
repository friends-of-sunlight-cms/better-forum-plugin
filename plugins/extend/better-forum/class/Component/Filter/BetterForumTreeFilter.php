<?php

namespace SunlightExtend\BetterForum\Component\Filter;

use Sunlight\Core;
use Sunlight\Database\Database as DB;
use Sunlight\Database\TreeFilterInterface;
use Sunlight\Database\TreeReader;
use Sunlight\Page\Page;
use Sunlight\User;
use SunlightExtend\BetterForum\BetterForumPlugin;

class BetterForumTreeFilter implements TreeFilterInterface
{
    /** @var array */
    private $options;
    /** @var string */
    private $sql;

    /** @var string */
    private $typeIdt;

    /**
     * Supported keys in $options:
     * ------------------------------------------------------------
     * check_level (1)  check user and page level 1/0
     * check_public (1) check page's public column 1/0
     *
     * @param array $options
     */
    function __construct(array $options)
    {
        // defaults
        $options += [
            'check_level' => true,
            'check_public' => true,
        ];

        $this->options = $options;
        $this->sql = $this->compileSql($options);

        $this->typeIdt = Core::$pluginManager->getPlugins()->getExtend('better-forum')->getOptions()['extra']['group_idt'];
    }

    /**
     * @param array $node
     * @param TreeReader $reader
     * @return bool
     */
    function filterNode(array $node, TreeReader $reader): bool
    {
        return
            /* visibility */ $node['visible']
            /* page level */ && (!$this->options['check_level'] || $node['level'] <= User::getLevel())
            /* page public */ && (!$this->options['check_public'] || User::isLoggedIn() || $node['public'])
            /* type check */ && ($node['type'] == Page::FORUM || $node['type_idt'] == $this->typeIdt);
    }

    /**
     * @param array $invalidNode
     * @param array $validChildNode
     * @param TreeReader $reader
     * @return bool
     */
    function acceptInvalidNodeWithValidChild(array $invalidNode, array $validChildNode, TreeReader $reader): bool
    {
        return true;
    }

    /**
     * @param TreeReader $reader
     * @return string
     */
    function getNodeSql(TreeReader $reader): string
    {
        return $this->sql;
    }

    /**
     * @param array $options
     * @return string
     */
    private function compileSql(array $options): string
    {
        // base conditions
        $sql = '%__node__%.visible=1 AND (%__node__%.type=' . Page::FORUM . ' OR %__node__%.type_idt=' . DB::val($this->typeIdt) . ')';

        if ($options['check_level']) {
            $sql .= ' AND %__node__%.level<=' . User::getLevel();
        }

        return $sql;
    }
}
