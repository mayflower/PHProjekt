<?php
/**
 * Display a tree and saves the current status for Module/Controller
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core Helpers
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * Display a tree and saves the current status for Module/Controller
 * user. Furthermore it handles open/close status in the session and also
 * manages a way to save/find trees and their internal status after
 * a page request. Tree status can also be written to backing storage
 * (e.g. databases).
 * !NOTE We might use RecursiveTreeIterator in the future, but I'm not
 * sure if this will work the functionallity we need
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core Helpers
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
class Default_Helpers_TreeView
{
    /**
     * The session namespace the class uses to store information
     * about nodes
     *
     */
    const SESSION_NAMESPACE = 'Phprojekt_Tree';

    /**
     * The tree that should be displayed
     *
     * @var Phprojekt_Tree_Node_Database
     */
    protected $_tree = null;

    /**
     * A unique name
     *
     * @var string
     */
    public $name = null;

    /**
     * If true, the root node is also displayed, normally this is not what
     * you want.
     *
     * @var boolean
     */
    public $displayRootNode = true;

    /**
     * The request object form the front controller
     *
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request = null;

    /**
     * Initialize
     *
     * @param Phprojekt_Tree_Node_Database $tree The tree to display
     * @param string                       $name A name to identify the tree view
     */
    public function __construct(Phprojekt_Tree_Node_Database $tree = null, $name = null)
    {
        if (null !== $tree) {
            $this->setTree($tree);
        }

        $this->name     = $name;
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();
    }

    /**
     * Set the tree that should be handled by the view helper
     *
     * @param Phprojekt_Tree_Node_Database $tree Set the tree that should be displayed
     *
     * @return void
     */
    public function setTree(Phprojekt_Tree_Node_Database $tree)
    {
        $this->_tree = $tree;
    }

    /**
     * Returns the tree, that is currently handled by the view helper
     *
     * @return Phprojekt_Tree_Node_Database
     */
    public function getTree()
    {
        return $this->_tree;
    }

    /**
     * Renders the tree and returns the rendered output as HTML
     *
     * @param Default_Helpers_Smarty $smarty   An instance of the template engine
     * @param string                 $template optional, if given, we try to load this
     *                                         template instead of the default 'tree.tpl'
     *
     * @throws Phprojekt_Tree_Node_Exception if tree was not setup correctly
     *
     * @return string
     */
    public function renderer(Default_Helpers_Smarty $smarty, $template = 'tree.tpl')
    {
        $smarty->tree = $this->_calculateOpenNodes($this->_tree);

        $this->_request = Zend_Controller_Front::getInstance()->getRequest();

        $smarty->treeIdentifier = $this->getIdentifier();

        return $smarty->render($template);
    }

    /**
     * Moves the initialized TreeView helper onto a stack in the
     * seession to identifier the tree after a page request.
     * Use findPersistent to receive the tree again after a tree request
     *
     * @see findPersistent()
     *
     * @return void
     */
    public function makePersistent()
    {
        $session = new Zend_Session_Namespace('Tree');

        $activeRecord   = $this->_tree->getActiveRecord();
        $treeIdentifier = $this->getIdentifier();

        /*
         * To find our model after a page request we need this information
         * We can also use the classname itself to identifier, as we don't
         * expect customized moduls to appear during an page request, but
         * its more clear using Phprojekt_Loader everywhere, so we need this
         * data
         */
        $node = $this->_tree->getRootNode();
        $info = array('module' => Phprojekt_Loader::getModuleFromObject($activeRecord),
                      'model'  => Phprojekt_Loader::getModelFromObject($activeRecord),
                      'rootId' => (null === $node) ? null : $node->id,
                      'name'   => $this->name);

        $session->forest[$treeIdentifier] = $info;

    }

    /**
     * Returns an identifier for a tree. Trees are identified by
     * the table name of the active record and a given given name.
     * Two trees with the same name and the same activerecord class share
     * the same internal state.
     *
     * @return string
     */
    public function getIdentifier()
    {
        $tableName = $this->_tree->getActiveRecord()->getTableName();

        return substr(sha1($tableName . $this->name), 0, 6);
    }

    /**
     * Finds the current changed/toggled tree by searching the session
     * for the tree identifier (normaly a 6 character shortened sha1)
     * provided by the request and initialize the tree object if something
     * is found, otherwise null is returned. You can easily use ths function
     * to receive a tree an toggle it then.
     *
     * @return null|Phprojekt_TreeView_Helper
     */
    public static function findPersistant()
    {
        $db      = Zend_Registry::get('db');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $session = new Zend_Session_Namespace('Tree');
        /* @var $request Zend_Controller_Request_Abstract  */

        $treeIdentifier = $request->getParam('tree', null);
        if (array_key_exists($treeIdentifier, (array) $session->forest)) {
            $treeInfo = $session->forest[$treeIdentifier];
            $model    = Phprojekt_Loader::getModelFactory($treeInfo['module'], $treeInfo['model'], array('db' => $db));
            $tree     = new Phprojekt_Tree_Node_Database($model, $treeInfo['rootId']);
            $tree->setup();
            return new self($tree, $treeInfo['name']);
        }

        return null;
    }

    /**
     * Writes a internal representation of open/close states from session to a
     * storing backend, usually the database. Returns true on success
     *
     * ! NOTE: Call this method on logout!
     *
     * @return boolean
     */
    public function writeToBackingStore()
    {
        throw new Exception('Not implemented yet');
    }

    /**
     * Toggle on off for a node. If no id is given it tries to figure
     * the tree id out from a standard value.
     *
     * @param intenger $id The node id to toggle
     *
     * @throws Exception if given id doesnt exists on tree
     *
     * @return void
     */
    public function toogleNode($id = null)
    {
        if (null === $id) {
            $id = (integer) $this->_request->getParam('treeid');
        }

        if (false === is_integer($id)) {
            throw new InvalidArgumentException('id must be an integer');
        }

        $openNodes = &$this->_getOpenNodesFromSession();

        if (true === array_key_exists($id, $openNodes)) {
            unset($openNodes[$id]);
        } else {
            $openNodes[$id] = true;
        }
    }

    /**
     * Returns a reference of the session namespace that identifies the
     * open nodes for this tree. If there are no openNodes an empty
     * array is returned.
     *
     * @return array
     */
    protected function &_getOpenNodesFromSession()
    {
        $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);

        if (false === is_array($session->openNodes)) {
            $session->openNodes = array();
        }

        $treeIdentifier = $this->getIdentifier();
        if (false === array_key_exists($treeIdentifier, (array) $session->openNodes)) {
            $session->openNodes[$treeIdentifier] = array();
        }

        return $session->openNodes[$treeIdentifier];
    }


    /**
     * Recursive check if tree / branch schould be shown. Returns an
     * array of nodes that should be rendered. if displayRootNode is set
     * the root node (normally invisible) is also displayed
     *
     * @param Phprojekt_Tree_Node_Database $parentNode The parent node
     *
     * @return array
     */
    protected function _calculateOpenNodes(Phprojekt_Tree_Node_Database $parentNode)
    {
        $openNodes = $this->_getOpenNodesFromSession();

        $nodes = array();

        if (true === $parentNode->isRootNode()
         && true === $this->displayRootNode) {
            $nodes[] = $parentNode;
        }

        foreach ($parentNode->getChildren() as $node) {
            /* dirty hack so we dont get into the complete deepth of the iterator */
            if ($node->getDepth() == $parentNode->getDepth()) {
                continue;
            }
            if (array_key_exists($parentNode->id, $openNodes)) {
                $nodes[] = $node;
                $nodes   = array_merge($nodes, $this->_calculateOpenNodes($node));
            }
        }
        return $nodes;
    }
}
