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
     * Tree view helper to display fancy trees
     *
     * @var Default_Helpers_TreeView
     */
    protected $_treeView;

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
    public $models;

    /**
     * How many columns will have the form
     *
     * @todo Not implemented yet
     *
     * @var integer
     */
    const FORM_COLUMNS = 2;

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
        try {
            Phprojekt_Auth::isLoggedIn();
        }
        catch (Phprojekt_Auth_Exception $ae) {
            if ($ae->getCode() == 1) {

                /* user not logged in, display login page */

                $this->_redirect($config->webpath.'/index.php/Login/index');
                die();
            }
        }

        $db       = Zend_Registry::get('db');
        $projects = Phprojekt_Loader::getModel('Project', 'Project', array('db' => $db));
        $tree     = new Phprojekt_Tree_Node_Database($projects, 1);

        $this->_smarty             = Zend_Registry::get('view');
        $this->_smarty->module     = $this->_request->getModuleName();
        $this->_smarty->controller = $this->_request->getControllerName();
        $this->_smarty->action     = $this->_request->getActionName();
        $this->models              = $this->getModelsObject();
        $this->data['listData']    = $this->models->getListData();
        $this->data['formData']    = $this->models->getFormData();

        $this->_listView = Default_Helpers_ListView::getInstance();
        $this->_formView = Default_Helpers_FormView::getInstance($this->_smarty);
        $this->_treeView = new Default_Helpers_TreeView($tree);

        $this->_treeView->makePersistent();

        /* Save the last project id into the session */

        /* @todo: Sanitize ID / Request parameter */
        $request = $this->_request->getParams();
        $session = new Zend_Session_Namespace();
        if (isset($request['id'])) {
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
        if (isset($session->lastProjectId)) {
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
        $currentActiveTree->toggleNode();

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
            $itemid   = (int) $request['id'];
            $formData = $this->models->getFormData($itemid);


            /* History */
            $db                  = Zend_Registry::get('db');
            $history             = new Phprojekt_History(array('db' => $db));
            $this->historyData   = $history->getHistoryData($this->models, $itemid);
            $this->dateFieldData = array('formType' => 'datetime');
            $this->userFieldData = array('formType' => 'userId');

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
        $request = $this->_request->getParams();

        $itemid = (isset($request['id'])) ? (int) $request['id'] : null;

        if (null !== $itemid) {
            $this->models->find($itemid);
        }

        /* Assign the values */
        foreach ($request as $k => $v) {
            if ($this->models->keyExists($k)) {
                $this->models->$k = $v;
            }
        }

        /* Validate and save if is all ok */
        if ($this->models->recordValidate()) {
            $this->models->save();
            $this->message = 'Saved';
        } else {
            $this->errors = $this->models->getError();
        }

        $this->itemid = $itemid;
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

            $itemid = (int) $request['id'];
            $this->models->find($itemid)->delete();

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
            $currentPage = (int) $params['page'];
            $session = new Zend_Session_Namespace($currentProjectModule);
            $session->currentPage = $currentPage;
        } else {
            $session = new Zend_Session_Namespace($currentProjectModule);
            if (true === isset($session->currentPage)) {
                $currentPage = $session->currentPage;
            } else {
                $currentPage = 0;
            }
            $session->currentPage = $currentPage;
        }

        list($this->data['listData'], $numberOfRows) = $this->models->getListData();

        $this->titles   = $this->models->getFieldsForList(get_class($this->models));
        $this->lines    = $this->data['listData'];

        /* Asign paging values for smarty */
        $config  = Zend_Registry::get('config');
        $perpage = $config->itemsPerPage;
        Default_Helpers_Paging::calculatePages($this, $numberOfRows, $perpage, $currentPage);

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
        $this->columns     = IndexController::FORM_COLUMNS;
        if ($id == 0) {
            $this->data['formData'] = $this->models->getFormData($id);
        }

        /* Assign post values */
        $params     = $this->_request->getParams();
        $formData   = $this->data['formData'];
        $tmp        = $formData;
        $fieldNames = array_keys($formData);
        foreach ($fieldNames as $fieldName) {
            if (isset($params[$fieldName])) {
                $tmp[$fieldName]['value'] = $params[$fieldName];
            }
        }
        $this->data['formData'] = $tmp;

        $this->fields   = $this->_formView->makeColumns($this->data['formData'], IndexController::FORM_COLUMNS);
        $this->formView = $this->_render('form');
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
                return $this->_treeView->renderer($this->_smarty);
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
        $this->modules    = $this->models->getSubModules();
    }

    /**
     * Get the model object
     * This function must be redefined in each module
     *
     * @return array All the fields for list
     */
    public function getModelsObject()
    {
        $models = new Default_Models_Default();
        return $models;
    }
}