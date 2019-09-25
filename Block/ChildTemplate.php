<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Core\Block;

use Magento\Framework\View\Element\AbstractBlock;

class ChildTemplate extends Template
{

    public function __call($method, $args)
    {
        $parent = $this->getParentBlock();
        if ($parent) {
            return call_user_func_array([$parent, $method], $args);
        }
        return null;
    }

    public function __isset($name)
    {
        $parent = $this->getParentBlock();
        if ($parent) {
            return isset($parent->{$name});
        }
        return false;
    }

    public function __get($name)
    {
        $parent = $this->getParentBlock();
        if ($parent) {
            return $parent->{$name};
        }
        return null;
    }

    /**
     * Retrieve child block by name
     *
     * @param string $alias
     * @return AbstractBlock|bool
     */
    public function getChildBlock($alias)
    {
        $parent = $this->getParentBlock();
        $layout = $parent->getLayout();
        if (!$layout) {
            return false;
        }
        $name = $layout->getChildName($parent->getNameInLayout(), $alias);
        if ($name) {
            return $layout->getBlock($name);
        }
        return false;
    }

    /**
     * Retrieve child block HTML
     *
     * @param string $alias
     * @param boolean $useCache
     * @return  string
     */
    public function getChildHtml($alias = '', $useCache = true)
    {
        $parent = $this->getParentBlock();
        $layout = $parent->getLayout();
        if (!$layout) {
            return '';
        }
        $name = $parent->getNameInLayout();
        $out = '';
        if ($alias) {
            $childName = $layout->getChildName($name, $alias);
            if ($childName) {
                $out = $layout->renderElement($childName, $useCache);
            }
        } else {
            foreach ($layout->getChildNames($name) as $child) {
                $out .= $layout->renderElement($child, $useCache);
            }
        }

        return $out;
    }

    /**
     * Render output of child child element
     *
     * @param string $alias
     * @param string $childChildAlias
     * @param bool $useCache
     * @return string
     */
    public function getChildChildHtml($alias, $childChildAlias = '', $useCache = true)
    {
        $parent = $this->getParentBlock();
        $layout = $parent->getLayout();
        if (!$layout) {
            return '';
        }
        $childName = $layout->getChildName($parent->getNameInLayout(), $alias);
        if (!$childName) {
            return '';
        }
        $out = '';
        if ($childChildAlias) {
            $childChildName = $layout->getChildName($childName, $childChildAlias);
            $out = $layout->renderElement($childChildName, $useCache);
        } else {
            foreach ($layout->getChildNames($childName) as $childChild) {
                $out .= $layout->renderElement($childChild, $useCache);
            }
        }
        return $out;
    }

    /**
     * Retrieves sorted list of child names
     *
     * @return array
     */
    public function getChildNames()
    {
        $parent = $this->getParentBlock();
        $layout = $parent->getLayout();
        if (!$layout) {
            return [];
        }
        return $layout->getChildNames($parent->getNameInLayout());
    }

    /**
     * Get a group of child blocks
     *
     * Returns an array of <alias> => <block>
     * or an array of <alias> => <callback_result>
     * The callback currently supports only $this methods and passes the alias as parameter
     *
     * @param string $groupName
     * @return array
     */
    public function getGroupChildNames($groupName)
    {
        $parent = $this->getParentBlock();
        return $parent->getLayout()->getGroupChildNames($parent->getNameInLayout(),
            $groupName);
    }

}
