<?php
/**
 * Tree class
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */

/**
 * Represents an node of a tree and provides iterator abilities.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
class Phprojekt_Tree_Node_Database implements IteratorAggregate
{
    /**
     * The char that separates a tree path in the database
     *
     */
    const NODE_SEPARATOR = '/';

    /**
     * Parent Node
     *
     * @var Phprojekt_Tree_Node
     */
    protected $_parentNode = null;

    /**
     * Array of nodes
     *
     * @var array
     */
    protected $_children = array();

    /**
     * The active record pattern
     *
     * @var Phprojekt_ActiveRecord_Abstract
     */
    protected $_activeRecord = null;

    /**
     * The requested id. We don't use the normal ID.
     *
     * @var integer
     */
    protected $_requestedId = null;

    /**
     * Holds an index of all notes in the subtree.
     *
     * @var array
     */
    protected $_index = array();


    /**
     * Initialize a new node and sets the tree the node belongs to
     *
     * @param Phprojekt_Tree_Storage_Interface $tree
     */
    public function __construct(Phprojekt_ActiveRecord_Abstract $activeRecord, $id = NULL)
    {
        $this->_activeRecord = $activeRecord;

        if (null !== $id) {
            $this->_requestedId = $id;
        }

    }

    /**
     * Reinialize
     *
     * @see delete()
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
     * Initialize a new root node
     *
     * @return Phprojekt_Tree_Node_Database
     */
    public function create()
    {
        $this->_activeRecord         = clone $this->_activeRecord;
        $this->_activeRecord->path   = self::NODE_SEPARATOR;
        $this->_activeRecord->parent = NULL;
        $this->_activeRecord->save();

        $this->_requestedId = $this->id;

        return $this;
    }

    /**
     * Setup a tree
     *
     * @param Phprojekt_Filter_Interface  $filter
     * @param Phprojekt_Compare_Interface $comparer
     *
     * @throws Phprojekt_Tree_Node_Exception If no id was requested (see constructor)
     *
     * @return Phprojekt_Tree_Node_Database
     */
    public function setup(Phprojekt_Filter_Interface $filter = null, Phprojekt_Compare_Interface $comparer = null)
    {
        if (null !== $this->_requestedId) {
            $database = $this->_activeRecord->getAdapter();
            $table    = $this->_activeRecord->getTableName();
            $select   = $database->select();

            $select->from($table, 'path')
                   ->where($database->quoteInto('id = ?', $this->_requestedId))
                   ->limit(1);

            $rootPath = $database->fetchOne($select);

            if (null !== $rootPath) {
                $rows = $this->_activeRecord->fetchAll(
                            $database->quoteInto("path LIKE ?", $rootPath . '%')
                          . ' OR '
                          . $database->quoteInto("id = ?", $this->id),
                            'path');

                $this->_index = array();
                foreach ($rows as $record) {
                    $node = null;

                    if ($record->id == $this->_requestedId) {
                        $node = $this;
                        $this->_activeRecord = $record;
                    } elseif (array_key_exists($record->parent, $this->_index)) {
                        $node = new Phprojekt_Tree_Node_Database($record);
                        $this->_index[$node->parent]->appendNode($node);
                    }

                    if (null !== $node) {
                        $this->_index[$node->id] = $node;
                    }
                }
                return $this;
            } else {
                throw
                  new Phprojekt_Tree_Node_Exception('Requested node not found');
            }
        }

        throw new Phprojekt_Tree_Node_Exception('You have to set a requested '
                                              . 'treeid in the constructor');
    }

    /**
     * Move upstairs until reach the root node.
     *
     * @return Phprojekt_Tree_Node_Database
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
     * Append a new node
     *
     * @param Phprojekt_Tree_Node_Database $node
     *
     * @throws Phprojekt_Tree_Node_Exception
     *
     * @return Phprojekt_Tree_Node_Database
     */
    public function appendNode(Phprojekt_Tree_Node_Database $node)
    {
        if (null !== $node->_activeRecord) {

            if (null === $node->id) {
                $node->_activeRecord->parent = $this->id;
                $node->_activeRecord->path = sprintf('%s%s%s', $this->path, $this->id, self::NODE_SEPARATOR);
                $node->_activeRecord->save();
            }

            $node->setParentNode($this);
            $this->_children[$node->id] = $node;

            return $this;
        }

        throw new Phprojekt_Tree_Node_Exception('Only nodes with a valid '
                                              . 'active record can be added');
    }

    /**
     * Recursive delete children
     *
     * @param array $children
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
     * Delete a node an all subnodes
     *
     * @throws Zend_Db_Exception
     *
     * @return void
     */
    public function delete()
    {
        if (null === $this->id) {
            throw new Phprojekt_Tree_Node_Exception('Node not setup yet');
        }

        $table    = $this->_activeRecord->getTableName();
        $database = $this->_activeRecord->getAdapter();
        /* @var $database Zend_Db_Adapter_Abstract */
        try {
            $database->beginTransaction();
            $database->delete($table,
                              $database->quoteInto('path LIKE ?', $this->path . '%'));

            $this->_deleteChildren($this->getChildren());

            unset ($this->_activeRecord);
            $this->_initialize();

        } catch (Exception $e) {
            $database->rollBack();
            throw $e;
        }

        $database->commit();
    }

    /**
     * Set the parent node
     *
     * @param Phprojekt_Tree_Node_Database $node
     *
     * @return Phprojekt_Tree_Node_Database
     */
    public function setParentNode(Phprojekt_Tree_Node_Database $node)
    {
        $this->_parentNode = $node;

        return $this;
    }

    /**
     * Overwrite getter to access protected properties as read-only
     *
     * @param string $key
     *
     * @return mixed
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
        }
    }

    /**
     * Pass all sets to the active record
     *
     * @param integer $key
     * @param mixed $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        if (null !== $this->_activeRecord) {
            /* dont allow to set the tree dependent stuff */
            if (!in_array($key, array('id', 'path', 'parent'))) {
                $this->_activeRecord->$key = $value;
            }
        }
    }

    /**
     * Returns a node from the current tree.
     *
     * @param integer $id
     *
     * @return Phprojekt_Tree_Node_Database
     */
    public function getNodeById($id)
    {
        return $this->getRootNode()->_getFromIndex($id);
    }

    /**
     * Returns a node from the tree intern index, that is maintained
     * by the root node
     *
     * @param  integer $id
     *
     * @return Phprojekt_Tree_Node_Database
     */
    protected function _getFromIndex($id)
    {
        if ($this->isRootNodeForCurrentTree()) {
            if (array_key_exists($id, $this->_index)) {
                return $this->_index[$id];
            }
        } else {
            return $this->getRootNode()->_getFromIndex($id);
        }

        return null;
    }

    /**
     * Check if the current node is the root node of a received tree.
     * This does not mean that its an absolute root node. Use isRootNode to
     * check this
     *
     * @return boolean
     */
    public function isRootNodeForCurrentTree()
    {
        return null === $this->_parentNode;
    }

    /**
     * Returns true if node is a root node
     *
     * @return boolean
     */
    public function isRootNode()
    {
        return null === $this->parent;
    }

    /**
     * Implementation of IteratorAggregate::getIterator.
     * Makes possible to use nodes with iterators
     *
     * @return RecursiveIteratorIterator
     */
    public function getIterator ()
	{
		return new RecursiveIteratorIterator(
			new Phprojekt_Tree_Node_Iterator($this),
			RecursiveIteratorIterator::SELF_FIRST);
	}

	/**
	 * Returns the parent node of that node or null
	 * if the node doesnt have a parent
	 *
	 * @return null|Phprojekt_Tree_Node
	 */
    public function getParentNode()
    {
        return $this->_parentNode;
    }

    /**
     * Returns the depth of the current node relativ
     * to the tree it belongs to. The lowest number is 0 (root node)
     *
     * @return integer
     */
    public function getDepth()
    {
        /*
         * We need to count the tree path ourself and NOT call getDepth on the
         * root node, as if we can be the tree and we will call us into a loop
         */
        return substr_count($this->path, self::NODE_SEPARATOR)
               - substr_count($this->getRootNode()->path, self::NODE_SEPARATOR);
    }

    /**
     * Returns an array of child nodes of the node
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Returns true if the node has child nodes
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return count ($this->_children) > 0;
    }

    /**
     * Returns the first node of a tree. This also resets the
     * internal pointer of child entries. If the node doesn't have
     * any children NULL is returned.
     *
     * @return null|Phprojekt_Tree_Node
     */
    public function getFirstChild()
    {
        if ($this->hasChildren()) {
            return reset($this->_children);
        }
    }
}
