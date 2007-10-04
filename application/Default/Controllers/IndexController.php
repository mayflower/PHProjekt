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
 * The indexcontroller have all the helper for use:
 * - Smarty = For make all the templates
 * - ListView = For correct values on the list view
 * - FormView = For make different form inputs for each type of field
 * - TreeView = For make the tree view
 *
 * All action do the nessesary job and then call the generateOutput
 * by postDispatch()
 * This function draw all the views that are not already rendered.
 * So you in each action you can render one view
 * and let that the generateOutput render the others.
 *
 * The class contain the model var for get the module model object
 * that return all the data for process
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class IndexController extends Zend_Controller_Action
{
    /**
     * Smarty object
     *
     * @var Smarty
     */
    protected $_smarty;

    /**
     * Helper for list view
     *
     * @var Default_Helpers_ListView
     *
     */
    protected $_listView;

    /**
     * Helper for form view
     *
     * @var Default_Helpers_FormView
     */
    protected $_formView;

    /**
     * Decide if we are able to render. On forwards we don't want to render,
     * so we just bypass the postDispatch
     *
     * @var boolean
     */
    protected $_canRender = true;

    /**
     * Array with the all the data for render
     * 'listData' => All the data for list
     * 'treeData' => All the data for trees
     *
     * @var array
     */
    public $data = array('listData','treeData');

    /**
     * Object model with all the specific data
     *
     * @var Phprojekt_Item
     */
    protected $_model;

    /**
     * Current item ID
     *
     * @var int
     */
    protected $_itemid = 0;

    /**
     * Current params request
     *
     * @var array
     */
    protected $_params = array();

    /**
     * How many columns will have the form
     *
     * @todo Not implemented yet
     *
     * @var integer
     */
    const FORM_COLUMNS = 2;

    /**
     * Identifies the tree view
     *
     * @see _render();
     */
    const TREE_VIEW = 0;

    /**
     * Identifies the form view
     *
     * @see _render();
     */
    const FORM_VIEW = 1;

    /**
     * Identifies the tree view
     *
     * @see _render();
     */
    const LIST_VIEW = 2;

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
    public function init()
    {
        $config = Zend_Registry::get('config');
        Zend_Registry::set('canRender', true);

        try {
            Phprojekt_Auth::isLoggedIn();
        }
        catch (Phprojekt_Auth_Exception $ae) {
            if ($ae->getCode() == 1) {

                /* user not logged in, display login page */
                $this->_redirect($config->webpath.'index.php/Login/index');
                die();
            }
        }

        $db       = Zend_Registry::get('db');
        $projects = Phprojekt_Loader::getModel('Project', 'Project', array('db' => $db));
        $tree     = new Phprojekt_Tree_Node_Database($projects, 1);

        $this->_smarty = Zend_Registry::get('view');
        $this->_model  = $this->getModelObject();

        $this->_treeView = new Default_Helpers_TreeView($tree);
        $this->_treeView->makePersistent();

        /* Get the current item id */
        $this->_params = $this->_request->getParams();
        if (isset($this->_params['id'])) {
            $this->_itemid = (int) $this->_params['id'];
        }

        /* For smarty */
        $this->_smarty->params = $this->_params;
        $this->_smarty->itemid = $this->_itemid;
    }

    /**
     * Standard action
     * Use the list action
     *
     * List Action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->forward('list');
    }

    /**
     * Adds a single filter to the current view
     *
     * List Action
     *
     * @return void
     */
    public function addFilterAction()
    {
        $this->setListView();
        $this->_smarty->message = 'Filter Added';
    }

    /**
     * Delivers the inner part of the IndexAction using ajax
     *
     * List Action
     *
     * @return void
     */
    public function componentIndexAction()
    {
    }

    /**
     * Delivers the inner part of the Listaction using ajax
     *
     * List Action
     *
     * @return void
     */
    public function componentListAction()
    {
    }

    /**
     * Toggle a open/close a node
     *
     * List Action
     *
     * @return void
     */
    public function toggleNodeAction()
    {
        $currentActiveTree = Default_Helpers_TreeView::findPersistant();
        $currentActiveTree->toggleNode();

        $this->forward('list', $this->_request->getControllerName(),
                        $this->_request->getModuleName());
    }

    /**
     * List all the data using the model for get it
     *
     * List Action
     *
     * @return void
     */
    public function listAction()
    {
        $this->_smarty->message = '&nbsp;';
    }

    /**
     * Remove a filter
     *
     * List Action
     *
     * @return void
     */
    public function removeFilterAction()
    {
        $this->_smarty->message = 'Filter Removed';
    }

    /**
     * Sort the list view
     *
     * List Action
     *
     * @return void
     */
    public function sortAction()
    {
        $this->_smarty->message = '&nbsp;';
    }

    /**
     * Abandon current changes and return to the default view
     *
     * Form Action
     *
     * @return void
     */
    public function cancelAction()
    {
        $this->_smarty->message = '&nbsp;';
    }

    /**
     * Ajax part of displayAction
     *
     * Form Action
     *
     * @return void
     */
    public function componentDisplayAction()
    {
    }

    /**
     * Ajaxified part of the edit action
     *
     * Form Action
     *
     * @return void
     */
    public function componentEditAction()
    {
    }

    /**
     * Displays the a single item for add an Item
     *
     * Form Action
     *
     * @return void
     */
    public function displayAction()
    {
    }

    /**
     * Displays the edit screen for the current item
     * Use the model module for get the data
     *
     * Form Action
     *
     * @return void
     */
    public function editAction()
    {
        if ($this->_itemid < 1) {
            $this->forward('display');
        } else {
            /* History */
            $db                           = Zend_Registry::get('db');
            $history                      = new Phprojekt_History(array('db' => $db));
            $this->_smarty->historyData   = $history->getHistoryData($this->_model, $this->_itemid);
            $this->_smarty->dateFieldData = array('formType' => 'datetime');
            $this->_smarty->userFieldData = array('formType' => 'userId');
        }
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
    public function saveAction()
    {
        if (null !== $this->_itemid) {
            $this->_model->find($this->_itemid);
        }

        foreach ($this->_params as $k => $v) {
            if ($this->_model->keyExists($k)) {
                $this->_model->$k = $v;
            }
        }

        if ($this->_model->recordValidate()) {
            $this->_model->save();
            $this->_smarty->message = 'Saved';
        } else {
            $this->_smarty->errors = $this->_model->getError();
        }
    }

    /**
     * Deletes a certain item
     *
     * Form Action
     *
     * @return void
     */
    public function deleteAction()
    {
        if ($this->_itemid < 1) {
            $this->forward('display');
        } else {
            $this->_model->find($this->_itemid)->delete();
            $this->_smarty->message = 'Deleted';
        }
    }

    /**
     * Render the tree view
     *
     * @return void
     */
    protected function _setTreeView()
    {
        $this->_smarty->treeView = $this->_render(self::TREE_VIEW);
    }

    /**
     * Render the listView using the data from the model
     *
     * The paging Helper see the returned rows and calculate the number of pages.
     * Then, assing to the smarty class the nessesary variables for render the paging.
     *
     * The actual page is stored in the session name "projectID + Module".
     * So each, module have and own session in each project.
     *
     * The number of items per page, is from the user configuration.
     *
     * @return void
     */
    protected function _setListView()
    {
        $this->_smarty->assign_by_ref('records', $this->_model->fetchAll());
        $this->_smarty->listView = $this->_render(self::LIST_VIEW);
    }

    /**
     * Render the formView using the data from the model
     *
     * If the function give the id, the values of this item will show.
     *
     * @return void
     */
    protected function _setFormView()
    {
        $this->_smarty->columns  = IndexController::FORM_COLUMNS;
        $this->_smarty->model    = $this->_model;
        $this->_smarty->formView = $this->_render(self::FORM_VIEW);
    }

    /**
     * Render a template
     *
     * @param string $template Which var of the index.tpl
     *
     * @return void
     */
    protected function _render($template)
    {
        switch ($template) {
        case self::TREE_VIEW:
                return $this->_treeView->renderer($this->_smarty);
            break;
        case self::FORM_VIEW:
                return $this->view->render('form.tpl');
            break;
        case self::LIST_VIEW:
                return $this->view->render('list.tpl');
            break;
        default:
                return $this->view->render($template . '.tpl');
            break;
        }
    }

    /**
     * Render all the views that are not already renders
     *
     * @return void
     */
    protected function _generateOutput()
    {
        /* Get the last project ID */
        $session = new Zend_Session_Namespace();

        if (isset($session->lastProjectId)) {
            $this->_smarty->projectId   = $session->lastProjectId;
            $this->_smarty->projectName = $session->lastProjectName;
        }

        $this->_smarty->list       = Default_Helpers_ListView::getInstance();
        $this->_smarty->module     = $this->_request->getModuleName();
        $this->_smarty->controller = $this->_request->getControllerName();
        $this->_smarty->action     = $this->_request->getActionName();
        $this->_smarty->breadcrumb = $this->_request->getModuleName();
        $this->_smarty->modules    = $this->_model->getSubModules();

        if (null === $this->_smarty->treeView) {
            $this->_setTreeView();
        }

        if (null === $this->_smarty->listView) {
            $this->_setListView();
        }

        if (null === $this->_smarty->formView) {
            $this->_setFormView();
        }

        $this->render('index');
    }

    /**
     * Get the model object
     * This function must be redefined in each module
     *
     * @return array All the fields for list
     */
    public function getModelObject()
    {
        $class = Phprojekt_Loader::getModel($this->getRequest()->getModuleName(),
                                            $this->getRequest()->getModuleName());
        if (null === $class) {
            $class = Phprojekt_Language::getModel('Default', 'Default');
        }

        return $class;
    }

    /**
     * Redefine the postDispatch function
     * After all action, this functions is called
     *
     * The function will call the generateOuput and render for show the layout
     *
     * Is disable only if you set the canRender to false,
     * for example, the canRender is seted to false before each _forward,
     * for no draw nothing, forward the action and then draw the correct layout
     *
     * @return void
     */
    public function postDispatch()
    {
        if (true === $this->_canRender) {
            $this->_generateOutput();
        }
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