<?php
/**
 * Tree class.
 *
 * Represents an node of a tree and provides iterator abilities.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Tree
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */

/**
 * Tree class.
 *
 * Represents an node of a tree and provides iterator abilities.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Tree
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
class Phprojekt_Tree_Node_Database implements IteratorAggregate
{
    /**
     * Name for use with the cache.
     */
    const CACHE_NAME = 'Phprojekt_Tree_Node_Database_setup';

    /**
     * The char that separates a tree path in the database.
     * It should not be edited, if you don't initialize a complete empty tree, as no conversion is done.
     */
    const NODE_SEPARATOR = '/';

    /**
     * Parent Node.
     *
     * @var Phprojekt_Tree_Node
     */
    protected $_parentNode = null;

    /**
     * Array of child nodes objects.
     *
     * @var array
     */
    protected $_children = array();

    /**
     * The active record pattern used to determine the table where
     * our actual tree data is stored as well as receiving the additional
     * columns from the table.
     *
     * @var Phprojekt_ActiveRecord_Abstract
     */
    protected $_activeRecord = null;

    /**
     * The id of the initial requested element.
     * We dont use the id here because id is received by the active record pattern and is used
     * to determine if a node was sucessfull received.
     * The requested id itself does nothing say about the current storage status of the node.
     *
     * @var integer
     */
    protected $_requestedId = null;

    /**
     * If this is the root node, it holds an simple list of all nodes in the tree.
     * This is used to do fast lookups on a given id.
     *
     * @var array
     */
    protected $_index = array();


    /**
     * Initialize a new node and sets the tree the node belongs to.
     *
     * @param Phprojekt_ActiveRecord_Abstract|stdclass $activeRecord The Object that holds the tree.
     * @param integer                                  $id           The requested node, that will be the root node.
     *
     * @return void
     */
    public function __construct($activeRecord, $id = null)
    {
        $this->_activeRecord = $activeRecord;

        if (null !== $id) {
            $this->_requestedId = $id;
        } else if (isset($activeRecord->id)) {
            $this->_requestedId = $activeRecord->id;
        }
    }

    /**
     * Reinialize the tree and reset all internal information.
     *
     * @see delete()
     *
     * @return void
     */
    protected function _initialize()
    {
        $this->_requestedId  = null;
        $this->_parentNode   = null;
        $this->_index        = array();
        $this->_children     = array();
        $this->_activeRecord = null;
    }

    /**
     * Checks if the tree was setup yet, by checking of the node has an id.
     *
     * @return boolean
     */
    public function isSetup()
    {
        return null !== $this->id;
    }

    /**
     * Try to receive a tree/subtree from the database using the
     * active record object to get the name of the tree table.
     * If the requested id is not set using the constructor this method
     * usually fails throwing an exception.
     *
     * @param Phprojekt_Filter_Interface $filter A filter to chain.
     *
     * @throws Phprojekt_Tree_Node_Exception If no id was requested (see constructor)
     *
     * @return Phprojekt_Tree_Node_Database An instance of Phprojekt_Tree_Node_Database.
     */
    public function setup(Phprojekt_Filter_Abstract $filter = null)
    {
        // @todo: fix this, must be possible with requestid === null
        if (null === $this->_requestedId) {
            throw new Phprojekt_Tree_Node_Exception('You have to set a requested treeid in the constructor');
        }

        $cache = Phprojekt::getInstance()->getCache();
        if ($this->_requestedId == 0) {
            return $this;
        } else {
            $database = $this->getActiveRecord()->getAdapter();
            $table    = $this->getActiveRecord()->getTableName();
            $select   = $database->select();

            $select->from(array('t' => $table), array())
                   ->join(array('tt' => $table),
                       sprintf('t.id = %d AND (tt.path like CONCAT(t.path, t.id, "/%%") OR tt.id = t.id)', (int) $this->_requestedId),
                       '*')
                   ->order('path')
                   ->order('id');

            if (null !== $filter) {
                $filter->filter($select, 'tt');
            }

            $treeData = $select->query()->fetchAll(Zend_Db::FETCH_CLASS);
            foreach ($treeData as $index => $record) {
                foreach ($record as $key => $value) {
                    $newKey = Phprojekt_ActiveRecord_Abstract::convertVarFromSql($key);
                    $treeData[$index]->$newKey = $value;
                }
            }

            foreach ($treeData as $record) {
                $node = null;
                if ($record->id == $this->_requestedId) {
                    $node                = $this;
                    $this->_activeRecord = $record;
                } elseif (array_key_exists($record->projectId, $this->_index)) {
                    $node = new Phprojekt_Tree_Node_Database($record);
                    $this->_index[$record->projectId]->appendNode($node);
                }

                if (null !== $node) {
                    $this->_index[$node->id] = $node;
                }
            }

            $object = $this;
            $cache->save($this, self::CACHE_NAME);

            // Delete the session for re-calculate the rights
            $sessionName     = 'Phprojekt_Tree_Node_Database-applyRights';
            $rightsNamespace = new Zend_Session_Namespace($sessionName);
            $rightsNamespace->unsetAll();
        }

        return $this->applyRights($object);
    }

    /**
     * Returns a set of records of the given active record that are associated
     * with the selected tree nodes.
     *
     * For example, you can get all todo recrods of projects in a given subtree.
     *
     * @param $model The active record used to get the data
     * @param $count How many records should be retreived. null if unlimited.
     * @param $offset The initial offset. null for no offset.
     */
    public function getRecordsFor(Phprojekt_ActiveRecord_Abstract $model, $count = null, $offset = null, $where = null)
    {
        $projectIds = array_keys($this->_index);
        if (count($projectIds) == 0) {
            return array();
        } else {
            $database = $model->getAdapter();

            if (null !== $where) {
                $where .= " AND ";
            }

            $where .= $database->quoteInto('project_id IN (?)', $projectIds);
            return $model->fetchAll($where, null, $count, $offset);
        }
    }

    /**
     * Delete the projects where the user don't have access.
     *
     * @param Phprojekt_Tree_Node_Database $object Tree class.
     *
     * @return Phprojekt_Tree_Node_Database The tree class with only the allowed nodes.
     */
    public function applyRights(Phprojekt_Tree_Node_Database $object)
    {
        if (Phprojekt_Auth::isAdminUser()) {
            return $object;
        }

        $projectIds   = array_keys($object->_index);
        // We don't use the effective user id here to make access management more simple. This way, a user really needs
        // read access to be able to look at a project.
        $rights       = Phprojekt_Right::getRightsForItems(1, 1, Phprojekt_Auth::getUserId(), $projectIds);
        $currentRight = Phprojekt_Acl::ALL;
        foreach ($object as $index => $node) {
            $currentRight = isset($rights[$node->id]) ? $rights[$node->id] : $currentRight;
            /* delete node cannot update the iterator reference, so we check if it's still in the index or already
             * removed */
            if ((Phprojekt_Acl::READ & $currentRight) <= 0 && isset($object->_index[$node->id])) {
                $object->deleteNode($object, $node->id);
            }
        }

        return $object;
    }

    /**
     * Delete a children node from the current tree, but leave it in the 
     * database
     *
     * @param Phprojekt_Tree_Node_Database $object Tree class.
     * @param integer                      $id     IF for delete.
     *
     * @return void
     */
    private function deleteNode(Phprojekt_Tree_Node_Database $object, $id)
    {
        if (isset($object->_children[$id])) {
            unset($object->_children[$id]);
            unset($object->_index[$id]);
        } else {
            foreach ($object->_children as $children) {
                $this->deleteNode($children, $id);
            }
        }
    }

    /**
     * Returns active record used to display the tree.
     *
     * @return Phprojekt_ActiveRecord_Abstract An instance of Phprojekt_ActiveRecord_Abstract.
     */
    public function getActiveRecord()
    {
        if (!$this->_activeRecord instanceof Phprojekt_ActiveRecord_Abstract) {
            $model = new Project_Models_Project();
            $this->_activeRecord = $model->find($this->_requestedId);
            if (!$this->_activeRecord instanceof Phprojekt_ActiveRecord_Abstract) {
                throw new Exception("Requested TreeID not found or no permissions");
            }
        }

        return $this->_activeRecord;
    }

    /**
     * Move upstairs until reach the root node and returns them.
     *
     * @return Phprojekt_Tree_Node_Database An instance of Phprojekt_Tree_Node_Database.
     */
    public function getRootNode()
    {
        if ($this->isRootNodeForCurrentTree()) {
            return $this;
        } else {
            return $this->getParentNode()->getRootNode();
        }
    }

    /**
     * Append a new node.
     * If it the new was not yet stored in the database (id === null) it's inserted.
     *
     * @param Phprojekt_Tree_Node_Database $node The node to append.
     *
     * @throws Phprojekt_Tree_Node_Exception If object has no current active record.
     *
     * @return Phprojekt_Tree_Node_Database An instance of Phprojekt_Tree_Node_Database.
     */
    public function appendNode(Phprojekt_Tree_Node_Database $node)
    {
        if (null !== $node->_activeRecord) {
            if (null === $node->id || $node->id == 0) {
                $node->_activeRecord->projectId = (int) $this->id;
                $node->_activeRecord->path      = sprintf('%s%s%s', $this->path, $this->id, self::NODE_SEPARATOR);
                $node->_activeRecord->save();
                self::deleteCache();
            }

            $node->setParentNode($this);
            $this->_children[$node->id] = $node;

            return $this;
        }
    }

    /**
     * Recursive delete children.
     *
     * @param array &$children Array of children to delete.
     *
     * @return void
     */
    private function _deleteChildren(&$children)
    {
        foreach ($children as $k => $child) {
            if ($child->hasChildren()) {
                $this->_deleteChildren($child->getChildren());
            } else {
                unset ($children[$k]);
            }
        }
    }

    /**
     * Delete a node an all subnodes.
     * ! NOTE this method uses transaction locking.
     *
     * @throws Zend_Db_Exception If node is not stored to database or was not received yet.
     *
     * @return void
     */
    public function delete()
    {
        if (null === $this->id) {
            throw new Phprojekt_Tree_Node_Exception('Node not received or stored'
                . ' from/to the database yet');
        }

        if ($this->id == 1) {
            throw new Phprojekt_Tree_Node_Exception('You can not delete the Invisible Root');
        }

        $table    = $this->getActiveRecord()->getTableName();
        $database = Phprojekt::getInstance()->getDb();
        $database->delete($table, $database->quoteInto('path LIKE ?', $this->path . '%'));
        $children = $this->getChildren();
        $this->_deleteChildren($children);
        $this->_initialize();
        self::deleteCache();
    }

    /**
     * Set the parent node.
     * !NOTE this method is somewhat dumb, it doesnot check if
     * it is a child of the given parent node.
     * You can messup the tree using the method not carefully.
     *
     * @param Phprojekt_Tree_Node_Database $node Parent node.
     *
     * @return Phprojekt_Tree_Node_Database An instance of Phprojekt_Tree_Node_Database.
     */
    public function setParentNode(Phprojekt_Tree_Node_Database $node)
    {
        if ($node->id != $this->_activeRecord->projectId) {
            // Update move
            $this->getActiveRecord()->projectId = (int) $node->id;
            $node = $this->_rebuildPaths($this, $node->path . $node->id . self::NODE_SEPARATOR);
            $node->getActiveRecord()->save();
            self::deleteCache();
        }

        $this->_parentNode = $node;

        return $this;
    }

    /**
     * Rebuild the paths of a subtree.
     *
     * @param Phprojekt_Tree_Node_Database $node     Node to rebuild.
     * @param string                       $basePath Path of the parent.
     *
     * @return void
     */
    protected function _rebuildPaths(Phprojekt_Tree_Node_Database $node, $basePath)
    {
        if ($node->_activeRecord->path != $basePath) {
            $node->getActiveRecord()->path = $basePath;

            foreach ($node->getChildren() as $id => $child) {
                $node->_children[$id] = $this->_rebuildPaths($child, $basePath . $node->id . self::NODE_SEPARATOR);

                $this->getRootNode()->_index[$child->id] = $node->_children[$id];
                $node->_children[$id]->getActiveRecord()->parentSave();
                self::deleteCache();
            }
        }

        return $node;
    }

    /**
     * Overwrite getter to access protected properties as read-only.
     *
     * @param string $key Identifier
     *
     * @return mixed Value of the var.
     */
    public function __get($key)
    {
        switch ($key) {
            case 'parentNode':
                return $this->getParentNode();
                break;
            default:
                if (null !== $this->_activeRecord) {
                    return $this->_activeRecord->$key;
                }
                break;
        }

        return null;
    }

    /**
     * Pass all sets to the active record.
     *
     * @param integer $key   Identifier.
     * @param mixed   $value Value.
     *
     * @return void
     */
    public function __set($key, $value)
    {
        if (null !== $this->_activeRecord) {
            // Don't allow to set the tree dependent stuff
            if (!in_array($key, array('id', 'path'))) {
                $this->getActiveRecord()->$key = $value;
            }
        }
    }

    /**
     * Overwrite isset.
     *
     * @param string $key Identifier.
     *
     * @return boolean True if exists.
     */
    public function __isset($key)
    {
        $objectvars = get_object_vars($this);
        return isset($objectvars[$key]) || isset($this->getActiveRecord()->$key);
    }

    /**
     * Overwrite calls.
     *
     * Act as a proxy for the active record.
     *
     * @param string $name      Name of the function.
     * @param array  $arguments Arguments.
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array(array($this, $name), $this->getActiveRecord(), $arguments);
        }

        return false;
    }

    /**
     * Returns a node from the current tree.
     *
     * @param integer $id ID of the node to receive.
     *
     * @return Phprojekt_Tree_Node_Database An instance of Phprojekt_Tree_Node_Database.
     */
    public function getNodeById($id)
    {
        return $this->getRootNode()->_getFromIndex($id);
    }

    /**
     * Returns a node from the tree intern index,
     * that is maintained by the root node.
     *
     * @param integer $id ID of the node to receive.
     *
     * @return Phprojekt_Tree_Node_Database|null An instance of Phprojekt_Tree_Node_Database.
     */
    protected function _getFromIndex($id)
    {
        if ($this->isRootNodeForCurrentTree()) {
            if (array_key_exists($id, $this->_index)) {
                return $this->_index[$id];
            }
        }

        return null;
    }

    /**
     * Check if the current node is the root node of a received tree.
     * This does not mean that its an absolute root node.
     * Use isRootNode to check this.
     *
     * @return boolean
     */
    public function isRootNodeForCurrentTree()
    {
        return null === $this->_parentNode;
    }

    /**
     * Returns true if node is a root node.
     *
     * @return boolean
     */
    public function isRootNode()
    {
        return null === $this->projectId;
    }

    /**
     * Implementation of IteratorAggregate::getIterator.
     * Makes possible to use nodes with iterators.
     *
     * @return RecursiveIteratorIterator
     */
    public function getIterator()
    {
        return new RecursiveIteratorIterator(new Phprojekt_Tree_Node_Iterator($this),
            RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * Returns the parent node of that node or null if the node doesnt have a parent.
     *
     * @return null|Phprojekt_Tree_Node
     */
    public function getParentNode()
    {
        return $this->_parentNode;
    }

    /**
     * Returns the depth of the current node relativ to the tree it belongs to.
     * The lowest number is 0 (root node).
     *
     * @return integer Depth.
     */
    public function getDepth()
    {
        // We need to count the tree path ourself and NOT call getDepth on the
        // root node, as if we can be the tree and we will call us into a loop
        return substr_count($this->path, self::NODE_SEPARATOR)
            - substr_count($this->getRootNode()->path, self::NODE_SEPARATOR);
    }

    /**
     * Returns a formatted string for display purpose.
     *
     * @param string Name of property to include in the output.
     *
     * @return string Name with "...." x depth.
     */
    public function getDepthDisplay($value)
    {
        return str_repeat('', $this->getDepth()) . $this->$value;
    }

    /**
     * Returns an array of child nodes of the node.
     *
     * @return array Array of child.
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Returns true if the node has child nodes.
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return count($this->_children) > 0;
    }

    /**
     * Returns the first node of a tree.
     * This also resets the internal pointer of child entries.
     * If the node doesn't have any children NULL is returned.
     *
     * @return null|Phprojekt_Tree_Node
     */
    public function getFirstChild()
    {
        if ($this->hasChildren()) {
            return reset($this->_children);
        }

        return null;
    }

    /**
     * Pass-through to hasField method of active record.
     */
    public function hasField($field)
    {
        return $this->getActiveRecord()->hasField($field);
    }

    /**
     * Delete the tree cache.
     *
     * @return void;
     */
    public static function deleteCache()
    {
        $cache = Phprojekt::getInstance()->getCache();
        $cache->remove(self::CACHE_NAME);
    }
}
