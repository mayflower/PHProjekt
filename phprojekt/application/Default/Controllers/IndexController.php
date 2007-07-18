<?php
/**
 * Default Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

require_once 'Zend/Controller/Action.php';

/**
 * Default Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class IndexController extends Zend_Controller_Action
{

    /**
     * Smarty object
     * @var Smarty
     */
    protected $_smarty;

    /**
     * Tree View output
     *
     * @var string
     */
    public $treeView = '';

    /**
     * List View output
     *
     * @var string
     */
    public $listView = '';

    /**
     * Detail View output
     *
     * @var string
     */
    public $detailView = '';

    /**
     * Init function
     * Get the Smarty instance and asign the tree, list and detail views
     *
     * @param void
     *
     * @return void
     */
    public function init() {
        /* Get the smarty object */
        $this->_smarty = Zend_Registry::get('view');

        /* Set treeview */
        $this->setTreeView($this->_helper->viewRenderer->view->render('tree.tpl'));

        /* Set listview */
        $this->setListView($this->_helper->viewRenderer->view->render('list.tpl'));

        /* Set detailview */
        $this->setDetailView($this->_helper->viewRenderer->view->render('detail.tpl'));
    }

    /**
     * Assign an output to the tree view
     *
     * @param string $output The output to show
     *
     * @return void
     */
    public function setTreeView($output)
    {
        $this->_smarty->treeView = $output;
    }

    /**
     * Assign an output to the list view
     *
     * @param string $output The output to show
     *
     * @return void
     */
    public function setListView($output)
    {
        $this->_smarty->listView = $output;
    }

    /**
     * Assign an output to the detail view
     *
     * @param string $output The output to show
     *
     * @return void
     */
    public function setDetailView($output)
    {
        $this->_smarty->detailView = $output;
    }

    /**
     * Get the tree output
     *
     * @param void 
     *
     * @return string The output to show
     */
    public function getTreeView()
    {
        return $this->_smarty->treeView;
    }

    /**
     * Get the list output
     *
     * @param void 
     *
     * @return string The output to show
     */
    public function getListView()
    {
        return $this->_smarty->listView;
    }

    /**
     * Get the detail output
     *
     * @param void 
     *
     * @return string The output to show
     */
    public function getDetailView($output)
    {
        return $this->_smarty->detailView;
    }

	/**
	 * Return true if not have access 
     *
	 */
	public function accessDenied()
	{
        return false;
	}

    /**
     * Standard action
     *
     * @return void
     *
     */
    public function indexAction()
    {
        $translate = Zend_Registry::get('translate');
        $this->setListView($translate->_("solved"));
    }

    /**
     * If the Action don´t exists, call indexAction
     *
     * @param string method - Action method
     * @param array  args   - Arguments for the Action
     * @return Zend_Exception
     *
     */
    public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            // If the action method was not found,
            // forward to the index action            
            return $this->_forward('index');  
        }
        // all other methods throw an exception
        throw new Exception('Invalid method "' . $method . '" called');
    }
}
