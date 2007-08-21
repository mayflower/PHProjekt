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
 * All action do the nessesary job and then call the generateOutput.
 * This function draw all the views that are not already rendered.
 * So you in each action you can render one view
 * and let that the generateOutput render the others.
 *
 * The class contain the oModel var for get the module model object
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
    protected $_oListView = null;

    /**
     * Helper for form view
     *
     * @var Default_Helpers_FormView
     */
    protected $_oFormView = null;

    /**
     * Tree view helper to display fancy trees
     *
     * @var Default_Helpers_TreeView
     */
    protected $_oTreeView = null;

    /**
     * Set true if the treeview is set
     *
     * @var boolean
     */
    public $treeViewSet = false;

    /**
     * Set true if the listview is set
     *
     * @var boolean
     */
    public $listViewSet = false;

    /**
     * Set true if the formView is set
     *
     * @var boolean
     */
    public $formViewSet = false;

    /**
     * Array with the all the data for render
     * 'listData' => All the data for list
     * 'formData' => All the data for form
     * 'treeData' => All the data for trees
     *
     * @var array
     */
    public $data = array('listData','formData','treeData');

    /**
     * Object model with all the specific data
     *
     * @var Phprojekt_Item object
     */
    public $oModels = '';

    /**
     * How many columns will have the form
     *
     * !NOTE for developers:
     *      This is not implemented yet
     *
     * @var integer
     */
    public $formColumns  = 2;

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
        try {
            Phprojekt_Auth::isLoggedIn();
        }
        catch (Phprojekt_Auth_Exception $ae) {
            if ($ae->getCode() == 1) {

                /* user not logged in, display login page */
                $config = Zend_Registry::get('config');

                $this->_redirect($config->webpath.'/index.php/Login/index');
                die();
            }
        }

        /* Check the 'index.php' in the url */
        if (false === strstr($this->_request->REQUEST_URI, 'index.php')) {
            $this->_redirect($config->webpath.'/index.php');
        }

        $db       = Zend_Registry::get('db');
        $projects = PHprojekt_Loader::getModel('Project', 'Project', array('db' => $db));
        $tree     = new Phprojekt_Tree_Node_Database($projects, 1);

        $this->_smarty             = Zend_Registry::get('view');
        $this->_smarty->module     = $this->_request->getModuleName();
        $this->_smarty->controller = $this->_request->getControllerName();
        $this->_smarty->action     = $this->_request->getActionName();
        $this->oModels             = $this->getModelsObject();
        $this->data['listData']    = $this->oModels->getListData();
        $this->data['formData']    = $this->oModels->getFormData();

        $this->_oListView = Default_Helpers_ListView::getInstance();
        $this->_oFormView = Default_Helpers_FormView::getInstance($this->_smarty);
        $this->_oTreeView = new Default_Helpers_TreeView($tree);

        $this->_oTreeView->makePersistent();

        /* Save the last project id into the session */
        $request = $this->_request->getParams();
        $session = new Zend_Session_Namespace();
        if (true === isset($request['id'])) {
            if ($this->_request->getModuleName() == 'Project') {
                if ($this->_request->getActionName() == 'list') {
                    $session->lastProjectId = $request['id'];
                    $project = PHprojekt_Loader::getModel('Project', 'Project', array('db' => $db));
                    $project->find($request['id']);
                    $session->lastProjectName = $project->title;
                }
            }
        }

        /* Assign the current project id and name to the templae */
        if (true == isset($session->lastProjectId)) {
            $this->projectId   = $session->lastProjectId;
            $this->projectName = $session->lastProjectName;
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
    public function indexAction()
    {
        $this->_forward('list');
    }

    /**
     * Adds a single filter to the current view
     * And generate the list view again
     *
     * List Action
     *
     * @return void
     */
    public function addFilterAction()
    {
        $this->setListView();
        $this->message = 'Filter Added';
        $this->generateOutput();
        $this->render('index');
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
        $currentActiveTree->toogleNode();

        $this->_forward('list', $this->_request->getControllerName(),
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
        $this->setListView();
        $this->message = '&nbsp;';
        $this->generateOutput();
        $this->render('index');
    }

    /**
     * Remove a filter
     * And generate the list view again
     *
     * List Action
     *
     * @return void
     */
    public function removeFilterAction()
    {
        $this->setListView();
        $this->message = 'Filter Removed';
        $this->generateOutput();
        $this->render('index');
    }

    /**
     * Sort the list view
     * And generate the list view again
     *
     * List Action
     *
     * @return void
     */
    public function sortAction()
    {
        $this->setListView();
        $this->message = '&nbsp;';
        $this->generateOutput();
        $this->render('index');
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
        $this->msg = '&nbsp;';
        $this->setFormView();
        $this->generateOutput();
        $this->render('index');
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
        $this->generateOutput();
        $this->render('index');
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
        $request = $this->_request->getParams();
        if (!isset($request['id'])) {
            $this->_forward('display');
        } else {
            $itemid   = intval($request['id']);
            $formData = $this->oModels->getFormData($itemid);

            $this->itemid           = $itemid;
            $this->data['formData'] = $formData;
            $this->generateOutput($itemid);
            $this->render('index');
        }
    }

    /**
     * Saves the current item
     * Save if you are add one or edit one.
     * Use the model module for get the data
     *
     * !NOTE: You MUST validate the data before save.
     *
     * If there is an error, will showit.
     *
     * Form Action
     *
     * @return void
     */
    public function saveAction()
    {
        $request = $this->_request->getParams();

        $itemid = (isset($request['id'])) ? (int) $request['id'] : null;

        if (null !== $itemid) {
            $this->oModels->find($itemid);
        }

        /* Assign the values */
        foreach ($request as $k => $v) {
            if ($this->oModels->keyExists($k)) {
                $this->oModels->$k = $v;
            }
        }

        /* Validate and save if is all ok */
        if ($this->oModels->recordValidate()) {
            $this->oModels->save();
            $this->message = 'Saved';
        } else {
            $this->errors = $this->oModels->getError();
        }

        $this->itemid = $itemid;
        $this->setTreeView();
        $this->generateOutput();
        $this->render('index');
    }

    /**
     * Deletes a certain item
     * And generate the list view again
     *
     * Form Action
     *
     * @return void
     */
    public function deleteAction()
    {
        $request = $this->_request->getParams();
        if (!isset($request['id'])) {
            $this->_forward('display');
        } else {
            $itemid = intval($request['id']);
            $this->oModels->find($itemid);
            if ($this->oModels->count() > 0) {
                $this->oModels->delete();
            }
            $this->message = 'Deleted';
            $this->itemid  = $itemid;
            $this->generateOutput();
            $this->render('index');
        }
    }

    /**
     * Render the tree view
     *
     * @return void
     */
    public function setTreeView()
    {
        $this->treeViewSet = true;
        $this->treeView    = $this->_render('tree');
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
    public function setListView()
    {
        $this->listViewSet      = true;

        /* Get the last project ID */
        $session = new Zend_Session_Namespace();
        if (true === isset($session->lastProjectId)) {
            $projectId = $session->lastProjectId;
        } else {
            $projectId = 0;
        }

        /* Set the actual page from the request, or from the session */
        $currentProjectModule = $projectId . $this->_request->getModuleName();
        $params = $this->_request->getParams();
        if (true == isset($params['page'])) {
            $actualPage = (int) $params['page'];
            $session = new Zend_Session_Namespace($currentProjectModule);
            $session->actualPage = $actualPage;
        } else {
            $session = new Zend_Session_Namespace($currentProjectModule);
            if (true === isset($session->actualPage)) {
                $actualPage = $session->actualPage;
            } else {
                $actualPage = 0;
            }
            $session->actualPage = $actualPage;
        }

        list($this->data['listData'], $numberOfRows) = $this->oModels->getListData();

        $this->titles   = $this->oModels->getFieldsForList(get_class($this->oModels));
        $this->lines    = $this->data['listData'];

        /* Asign paging values for smarty */
        $config  = Zend_Registry::get('config');
        $perpage = $config->itemsPerPage;
        $paging  = new Default_Helpers_Paging();
        $paging->calculatePages($this, $numberOfRows, $perpage, $actualPage);

        $this->listView = $this->_render('list');
    }

    /**
     * Render the formView using the data from the model
     *
     * If the function give the id, the values of this item will show.
     *
     * @param integer $id Optional, The id of the row
     *
     * @return void
     */
    public function setFormView($id = 0)
    {
        $this->formViewSet = true;
        $this->columns     = $this->formColumns;
        if ($id == 0) {
            $this->data['formData'] = $this->oModels->getFormData($id);
        }

        /* Asign post values */
        $params   = $this->_request->getParams();
        $formData = $this->data['formData'];
        $tmp      = $formData;
        foreach ($formData as $fieldName => $value) {
            if (isset($params[$fieldName])) {
                $tmp[$fieldName]['value'] = $params[$fieldName];
            }
        }
        $this->data['formData'] = $tmp;

        $this->fields   = $this->_oFormView->makeColumns($this->data['formData'], $this->formColumns);
        $this->formView = $this->_render('form');
    }

    /**
     * Return true if not have access
     *
     * !NOTICE:
     *      Not implemented yet
     *
     * @return boolean
     */
    public function accessDenied()
    {
        return false;
    }

    /**
     * If the Action not exists, call indexAction
     *
     * @param string $method Action method
     * @param array  $args   Arguments for the Action
     *
     * @return Zend_Exception
     */
    public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            /* If the action method was not found,
               forward to the index action */
            return $this->_forward('index');
        }

        throw new Exception('Invalid method "' . $method . '" called');
    }

    /**
     * Set a value into the smarty object for render it
     *
     * @param string $name  Name of the value for render
     * @param mix    $value Value for the var
     *
     * @return void
     */
    public function __set($name,$value)
    {
        if (!empty($name)) {
            $this->_smarty->$name = $value;
        }
    }

    /**
     * Get a value from the smarty object
     *
     * @param string $name Name of the value
     *
     * @return mix The value of the var
     */
    public function __get($name)
    {
        if (isset($this->_smarty->$name)) {
            return $this->_smarty->$name;
        } else {
            return null;
        }
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
        case 'tree':
                return $this->_oTreeView->renderer($this->_smarty);
            break;
        case 'form':
                return $this->view->render('form.tpl');
            break;
        case 'list':
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
     * @param integer $id The id of the row
     *
     * @return void
     */
    public function generateOutput($id = 0)
    {
        $this->view->currentId = $this->_request->getParam('id');

        if (!$this->treeViewSet) {
            $this->setTreeView();
        }

        if (!$this->listViewSet) {
            $this->setListView();
        }

        if (!$this->formViewSet) {
            $this->setFormView($id);
        }

        $this->breadcrumb = $this->_request->getModuleName();
        $this->modules    = $this->oModels->getSubModules();
    }

    /**
     * Get the model object
     * This function must be redefined in each module
     *
     * @return array All the fields for list
     */
    public function getModelsObject()
    {
        $oModels = new Default_Models_Default();
        return $oModels;
    }
}