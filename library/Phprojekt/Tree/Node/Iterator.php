<?php
/**
 * Tree node iterator
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */

/**
 * Iterates over a set of tree nodes. You can use foreach statements
 * to iterate over a tree and its child nodes. As it impelements a
 * recursive iterator, the tree node iterator always iterates over
 * the children nodes.
 * See PHP-SPL for more information about the Iterator interfaces in
 * PHP 5.2.x and above.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
class Phprojekt_Tree_Node_Iterator implements RecursiveIterator
{
    /**
     * Initialize
     *
     * @param array $children An array of children iterated by the iterator.
     */
    function __construct($children)
    {
        if (is_array($children)) {
            $this->_children = $children;
        } else {
            $this->_children = array($children);
        }
    }

    /**
     * Returns the current item
     *
     * @see Iterator::current()
     *
     * @return Phprojekt_Tree_Node
     */
    public function current()
    {
        return current($this->_children);
    }

    /**
     * Returns the id/key for the current entry
     *
     * @see Iterator::key()
     *
     * @return mixed
     */
    public function key()
    {
        return $this->current()->id;
    }

    /**
     * Move forward to the next item
     *
     * @see Iterator::next()
     *
     * @return void
     */
    public function next()
    {
        next($this->_children);
    }

    /**
     * Reset to the first element
     *
     * @see Iterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->_children);
    }

    /**
     * Checks if the current entry is valid
     *
     * @see Iterator::valid()
     *
     * @return boolean
     */
    public function valid()
    {
        return (boolean) $this->current();
    }

    /**
     * Checks if the node has children to move forward to receive them
     * using getChildren() if it has children
     *
     * @see RecursiveIterator::hasChildren()
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return (boolean) $this->current()->hasChildren();
    }

    /**
     * Returns an new iterator for the children of the current node
     *
     * @see RecursiveIterator::getChildren()
     *
     * @return RecursiveIterator
     */
    public function getChildren()
    {
        return new self($this->current()->getChildren());
    }
}
