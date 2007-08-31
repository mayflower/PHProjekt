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

interface Phprojekt_Compare_Interface {}


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
     * It should not be edited, if you don't initialize a complete
     * empty tree, as no conversion is done
     */
    const NODE_SEPARATOR = '/';

    /**
     * Parent Node
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
     * columns from the table
     *
     * @var Phprojekt_ActiveRecord_Abstract
     */
    protected $_activeRecord = null;

    /**
     * The id of the initial requested element. We dont use the id here
     * because id is received by the active record pattern and is used
     * to determine if a node was sucessfull received. The requested id itself
     * does nothing say about the current storage status of the node
     *
     * @var integer
     */
    protected $_requestedId = null;

    /**
     * If this is the root node, it holds an simple list of all nodes
     * in the tree. This is used to do fast lookups on a given id
     *
     * @var array
     */
    protected $_index = array();


    /**
     * Initialize a new node and sets the tree the node belongs to
     *
     * @param Phprojekt_ActiveRecord_Abstract $activeRecord The active Record that holds the tree
     * @param integer                         $id           The requested node, that will be the root node
     */
    public function __construct(Phprojekt_ActiveRecord_Abstract $activeRecord, $id = null)
    {
        $this->_activeRecord = $activeRecord;

        if (null !== $id) {
            $this->_requestedId = $id;
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
     * Initialize a new root node
     *
     * @return Phprojekt_Tree_Node_Database
     */
    public function create()
    {
        $this->_activeRecord         = clone $this->_activeRecord;
        $this->_activeRecord->path   = self::NODE_SEPARATOR;
        $this->_activeRecord->parent = null;
        $this->_activeRecord->save();

        $this->_requestedId = $this->id;

        return $this;
    }

    /**
     * Checks if the tree was setup yet, by checking of the node has an id
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
     * @param Phprojekt_Filter_Interface  $filter   A filter to chain
     * @param Phprojekt_Compare_Interface $comparer A comparer doing pre-sorting
     *
     * @throws Phprojekt_Tree_Node_Exception If no id was requested (see constructor)
     * @todo Apply the the pre-sorting comparer
     *
     * @return Phprojekt_Tree_Node_Database
     */
    public function setup(Phprojekt_Filter_Abstract $filter = null, Phprojekt_Compare_Interface $comparer = null)
    {
        if (null === $this->_requestedId) {
            throw new Phprojekt_Tree_Node_Exception(
                                                'You have to set a requested '
                                              . 'treeid in the constructor');
        }

        if (!is_null($comparer)) {

            // We have to apply the pre sorting comparer

            if (Zend_Registry::isRegistered('log')) {
                $oLog = Zend_Registry::get('log');
                $oLog->debug('Pre-sorting requested and not implemented yet');
            }
        }

        $database = $this->getActiveRecord()->getAdapter();
        $table    = $this->getActiveRecord()->getTableName();
        $select   = $database->select();

        $select->from($table, 'path')
               ->where($database->quoteInto('id = ?', $this->_requestedId))
               ->limit(1);

        if (null !== $filter) {
            $filter->filter($select, $this->getActiveRecord()->getAdapter());
        }

        $rootPath = $database->fetchOne($select);

        if (null === $rootPath) {
            throw
              new Phprojekt_Tree_Node_Exception('Requested node not found');
        }

        $where = sprintf("%s OR %s",
                    $database->quoteInto("path LIKE ?", $rootPath . '%'),
                    $database->quoteInto("id = ?", $this->id));

        $rows = $this->_activeRecord->fetchAll($where, 'path');

        $this->_index = array();
        foreach ($rows as $record) {
            $node = null;

            if ($record->id == $this->_requestedId) {
                $node                = $this;
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
    }

    /**
     * Returns active record used to display the tree
     *
     * @return Phprojekt_ActiveRecord_Abstract
     */
    public function getActiveRecord()
    {
        return $this->_activeRecord;
    }

    /**
     * Move upstairs until reach the root node and returns them.
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
     * Append a new node. If it the new was not yet stored in the
     * database (id === null) it's inserted
     *
     * @param Phprojekt_Tree_Node_Database $node The node to append
     *
     * @throws Phprojekt_Tree_Node_Exception If object has no current active record
     *
     * @return Phprojekt_Tree_Node_Database
     */
    public function appendNode(Phprojekt_Tree_Node_Database $node)
    {
        if (null !== $node->_activeRecord) {

            if (null === $node->id) {
                $node->_activeRecord->parent = $this->id;
                $node->_activeRecord->path   = sprintf('%s%s%s',
                                                        $this->path,
                                                        $this->id,
                                                        self::NODE_SEPARATOR);
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
     * @param array &$children Array of children to delete
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
     * @throws Zend_Db_Exception If node is not stored to database or
     *                           was not received yet.
     *
     * @return void
     */
    public function delete()
    {
        if (null === $this->id) {
            throw new Phprojekt_Tree_Node_Exception('Node not received or stored'
                                                   .' from/to the database yet');
        }

        $table    = $this->_activeRecord->getTableName();
        $database = $this->_activeRecord->getAdapter();
        /* @var $database Zend_Db_Adapter_Abstract */
        try {
            $database->beginTransaction();
            $database->delete($table,
                              $database->quoteInto('path LIKE ?', $this->path . '%'));

            $this->_deleteChildren($this->getChildren());

            $this->_activeRecord = null;
            $this->_initialize();

        } catch (Exception $e) {
            $database->rollBack();
            throw $e;
        }

        $database->commit();
    }

    /**
     * Set the parent node.
     * ! NOTE this method is somewhat dumb, it doesnot check if
     * it is a child of the given parent node. You can messup the tree
     * using the method not carefully.
     *
     * @param Phprojekt_Tree_Node_Database $node Parent node
     *
     * @return Phprojekt_Tree_Node_Database
     */
    public function setParentNode(Phprojekt_Tree_Node_Database $node)
    {
        if ($node->id != $this->_activeRecord->parent) {
            /* update move */
            $this->_activeRecord->parent = $node->id;

            $node = $this->_rebuildPaths($this, $node->path . $node->id . self::NODE_SEPARATOR);
            $node->getActiveRecord()->save();
        }

        $this->_parentNode = $node;

        return $this;
    }

    /**
     * Rebuild the paths of a subtree
     *
     * @param Phprojekt_Tree_Node_Database $node     Node to rebuild
     * @param string                       $basePath Path of the parent
     *
     * @return void
     */
    protected function _rebuildPaths(Phprojekt_Tree_Node_Database $node, $basePath)
    {
        $node->_activeRecord->path = $basePath;

        foreach ($node->getChildren() as $id => $child) {
            $node->_children[$id] = $this->_rebuildPaths($child, $basePath . $node->id . self::NODE_SEPARATOR);

            $this->getRootNode()->_index[$child->id] = $node->_children[$id];
            $node->_children[$id]->getActiveRecord()->save();
        }
        return $node;
    }

    /**
     * Overwrite getter to access protected properties as read-only
     *
     * @param string $key Identifier
     *
     * @return mixed
     */
    public function __get($key)
    {
        switch ($key) {
        case 'parentNode':
                return $this->getParentNode();
        default:
            if ($this->_activeRecord instanceof Phprojekt_ActiveRecord_Abstract) {
                return $this->_activeRecord->$key;
            }
        }

        return null;
    }

    /**
     * Pass all sets to the active record
     *
     * @param integer $key   Identifier
     * @param mixed   $value Value
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
     * @param integer $id Id of the node to receive
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
     * @param integer $id Id of the node to receive
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
        return new RecursiveIteratorIterator(new Phprojekt_Tree_Node_Iterator($this),
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
        return count($this->_children) > 0;
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

        return null;
    }
}
