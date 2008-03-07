<?php
/**
 * Default Controller for PHProjekt 6
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Default Controller for PHProjekt 6
 *
 * The controller will get all the actions
 * and run the nessesary stuff for each one
 *
 * The class contain the model var for get the module model object
 * that return all the data for process
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class IndexController extends Zend_Controller_Action
{
    /**
     * Boolean var for render or not
     *
     * @var boolean
     */
    private $_canRender = true;

    /**
     * Submodules
     *
     * @var array
     */
    protected $_submodules = array();

    /**
     * Init function
     *
     * First check if is a logued user, if not is redirect to the login form.
     *
     * The function inicialize all the Helpers,
     * collect the data from the Model Object for list and form
     * and inicialited the Project Tree view
     *
     * @return void
     */
    public function init ()
    {
        try {
            Phprojekt_Auth::isLoggedIn();
        } catch (Phprojekt_Auth_UserNotLoggedInException $ae) {
            /* user not logged in, display login page */
            $logger = Zend_Registry::get('log');
            $logger->debug((string) $ae);
            $this->_redirect(Zend_Registry::get('config')->webpath . 'index.php/Login/index');
            exit;
        }
    }

    /**
     * Standard action
     * Use the list action
     *
     * List Action
     *
     * @return void
     */
    public function indexAction ()
    {
        $this->view->modules = $this->_submodules;
        $this->view->webpath = Zend_Registry::get('config')->webpath;
        $this->render('index');
    }

    /**
     * Returns a tree for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @return void
     */
    public function jsonTreeAction()
    {
        $tree = new Phprojekt_Tree_Node_Database($this->getModelObject(), 1);
        $tree->setup();
        echo Phprojekt_Converter_Json::convertTree($tree);
    }

    /**
     * Returns the list for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam integer count ...
     * @requestparam integer start ...
     *
     * @return void
     */
    public function jsonListAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set. This is also used for loading a
        // grid on demand (initially only a part is shown, scrolling down loads what is needed).
        $count     = (int) $this->getRequest()->getParam('count');
        $offset    = (int) $this->getRequest()->getParam('start');
     	$projectId = (int) $this->getRequest()->getParam('nodeId');

     	if (empty($projectId)) {
     	    $records = $this->getModelObject()->fetchAll(null, null, $count, $offset);
     	} else {
        	$records = $this->getModelObject()->fetchAll('projectId = ' . $projectId, null, $count, $offset);
     	}

        echo Phprojekt_Converter_Json::convert($records);
    }

    /**
     * Saves the current item
     * Save if you are add one or edit one.
     * Use the model module for get the data
     *
     * NOTE: You MUST validate the data before save.
     *
     * If there is an error, we show it.
     *
     * Form Action
     *
     * @return void
     */
    public function saveAction ()
    {
        if (null !== $this->_itemid) {
            $this->getModelObject()->find($this->_itemid);
        }

        try {
            Default_Helpers_Save::save($this->getModelObject(), $this->getRequest()->getParams());
            $this->view->message = 'Saved';
        } catch (Exception $e) {
            $this->view->errors = $this->getModelObject()->getError();
        }
    }

    /**
     * Deletes a certain item
     *
     * Form Action
     *
     * @return void
     */
    public function deleteAction ()
    {
        if ($this->_itemid < 1) {
            $this->forward('display');
        } else {
            $this->getModelObject()->find($this->_itemid)->delete();
            $this->view->message = 'Deleted';
        }
        $this->listAction();
    }

    /**
     * Get the model object
     * This function must be redefined in each module
     *
     * @return Phprojekt_Model_Interface
     */
    public function getModelObject ()
    {
        static $object = null;
        if (null === $object) {
            $object = Phprojekt_Loader::getModel($this->getRequest()->getModuleName(), $this->getRequest()->getModuleName());
            if (null === $object) {
                $object = Phprojekt_Language::getModel('Default', 'Default');
            }
        }
        return $object;
    }

    /**
     * Set various variables. Sometimes this is needed as
     * internally things are rendered before postDispatch or even _generateOutput was
     * called but we need some view variables to generate urls right
     *
     * @return void
     */
    public function preDispatch()
    {
        $this->view->module     = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action     = $this->getRequest()->getActionName();
    }

    /**
     * The function will call the Zend _forward function
     * But set first the canRender to false for no draw nothing
     *
     * @param string $action     The new action to display
     * @param string $controller The new controller to display
     * @param string $module     The new module to display
     * @param array  $params     The params for the new request
     *
     * @return void
     */
    public function forward($action, $controller = null, $module = null, array $params = null)
    {
        $this->_canRender = false;
        $this->_forward($action, $controller, $module, $params);
    }

}