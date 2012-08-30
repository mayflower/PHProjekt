<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Tree node iterator.
 *
 * Iterates over a set of tree nodes.
 * You can use foreach statements to iterate over a tree and its child nodes.
 * As it impelements a recursive iterator, the tree node iterator always iterates over
 * the children nodes.
 * See PHP-SPL for more information about the Iterator interfaces in PHP 5.2.x and above.
 */
class Phprojekt_Tree_Node_Iterator implements RecursiveIterator
{
    /**
     * Initialize.
     *
     * @param array $children An array of children iterated by the iterator.
     *
     * @return void
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
     * Returns the current item.
     *
     * @see Iterator::current()
     *
     * @return Phprojekt_Tree_Node An instance of Phprojekt_Tree_Node.
     */
    public function current()
    {
        return current($this->_children);
    }

    /**
     * Returns the id/key for the current entry.
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
     * Move forward to the next item.
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
     * Reset to the first element.
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
     * Checks if the current entry is valid.
     *
     * @see Iterator::valid()
     *
     * @return boolean True for valid.
     */
    public function valid()
    {
        return (boolean) $this->current();
    }

    /**
     * Checks if the node has children to move forward to receive them
     * using getChildren() if it has children.
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
     * Returns an new iterator for the children of the current node.
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
