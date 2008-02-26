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
     * The treeview helper. Not the renderer.
     *
     */
    private $_treeView;

    /**
     * Tree
     *
     * @var Phprojekt_Tree_Node_Database
     */
    private $tree;

    /**
     * SQL where
     *
     * @var array
     */
    private $_where = array();

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
            die();
        }
        $projects = Phprojekt_Loader::getModel('Project', 'Project');
        $this->_tree = new Phprojekt_Tree_Node_Database($projects, 1);
        $this->_tree->setup();
        // $this->_treeView = new Default_Helpers_TreeView($this->_tree);
        // $this->_treeView->makePersistent();

        /* Get the current item id */
        $this->_params = $this->_request->getParams();

        if (isset($this->_params['id'])) {
            $this->_itemid = (int) $this->_params['id'];
        }
    }

    /**
     * Returns the current treeview
     *
     * @return Default_Helpers_TreeView
     */
    public function getTreeView ()
    {
        return $this->_treeView;
    }

    /**
     * Return the list form render helper.
     *
     * @return Phprojekt_RenderHelper
     */
    public function getFormView ()
    {
        $instance = Default_Helpers_FormViewRenderer::getInstance();
        $action = $this->_request->getActionName();
        switch ($action) {
            case 'default':
            case 'list':
                break;
            case 'display':
                if (null !== $this->getModelObject()) {
                    $instance->setModel($this->getModelObject());
                }
                break;
            case 'edit':
                if ($this->_itemid > 0) {
                    $instance->setModel($this->getModelObject()->find($this->_itemid));
                }
                break;
            case 'save':
                if ($this->_itemid > 0) {
                    $instance->setModel($this->getModelObject()->find($this->_itemid));
                } else if (null !== $this->getModelObject()) {
                    $instance->setModel($this->getModelObject());
                }
                break;
        }
        return $instance;
    }

    /**
     * Return the list view render helper.
     *
     * @return Phprojekt_RenderHelper
     */
    public function getListView ()
    {
        $instance = Default_Helpers_ListViewRenderer::getInstance();
        if (null !== $this->getModelObject() && null === $instance->getModel()) {
            $instance->setModel($this->getModelObject());
        }
        return $instance;
    }

    /**
     * Return the filter view render helper.
     *
     * @return Phprojekt_RenderHelper
     */
    public function getFilterView ()
    {
        $session = $this->_getCurrentSessionModule();
        $instance = Default_Helpers_FilterViewRenderer::getInstance();
        $filters = $instance->getModel();
        $fields = $instance->getFields();
        if (! empty($session->filters) && empty($filters)) {
            $instance->setModel($session->filters);
        }
        if (empty($fields)) {
            $fields = $this->getModelObject()->getFieldsForFilter();
            $instance->setFields($fields);
        }
        return $instance;
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
        $this->forward('list');
    }

    /**
     * Add a Filter
     * Save the POST values from the filter into the session where
     *
     * List Action
     *
     * @return void
     */
    public function addFilterAction ()
    {
        $field = Inspector::sanitize('alpha', $this->_params['filterField'], $messages);
        $rule  = Inspector::sanitize('alpha', $this->_params['filterRule'], $messages);
        $text  = Inspector::sanitize('alpha', $this->_params['filterText'], $messages);

        $newFilter = array('field' => $field,
                           'rule'  => $rule,
                           'text'  => $text);
        $this->addFilter($newFilter);
        $this->forward('list');
    }

    /**
     * Delete a Filter
     *
     * List Action
     *
     * @return void
     */
    public function deleteFilterAction ()
    {
        $id = (int) $this->_params['filterId'];
        $this->deleteFilter($id);
        $this->forward('list');
    }

    /**
     * Set the name of the session with the module and the id
     *
     * @return Zend_Session_Namespace
     */
    private function _getCurrentSessionModule ()
    {
        $session = new Zend_Session_Namespace();
        $id = (int) $session->currentProjectId;
        $index = $this->getRequest()->getModuleName() . $id;
        return new Zend_Session_Namespace($index);
    }

    /**
     * Add a filter into the session array
     * only if the filter don�t exists
     *
     * @param array $newFilter Filter data
     *
     * @return array All the filters
     */
    public function addFilter ($newFilter)
    {
        $session = $this->_getCurrentSessionModule();
        $filters = (! empty($session->filters)) ? $session->filters : array();
        $found = false;
        foreach ($filters as $filter) {
            if ((strcmp($filter['field'], $newFilter['field']) == 0) && (strcmp($filter['rule'], $newFilter['rule']) == 0) && (strcmp($filter['text'], $newFilter['text']) == 0)) {
                $found = true;
            }
        }
        if (! $found) {
            $newFilter['id'] = count($filters);
            $filters[] = $newFilter;
            ;
        }
        $session->filters = $filters;
    }

    /**
     * Delete a filter with the parsed id
     *
     * @param integer $id The id of the filter in the session
     *
     * @return void
     */
    public function deleteFilter ($id)
    {
        $session = $this->_getCurrentSessionModule();
        $filters = (! empty($session->filters)) ? $session->filters : array();
        if ($id == "-1") {
            $filters = array();
        } else {
            if (isset($filters[$id])) {
                unset($filters[$id]);
            }
        }
        $session->filters = $filters;
    }

    /**
     * Add a string into the where clause
     * but don�t keep it into the session
     *
     * @param string $string SQL where clause
     *
     * @return void
     */
    public function addWhere ($string)
    {
        $this->_where[] = $string;
    }

    /**
     * Return the saved where clause
     *
     * @return string SQL where clause
     */
    public function getWhere ()
    {
        $session = $this->_getCurrentSessionModule();
        if (empty($this->_where)) {
            $this->_where = (! empty($session->where)) ? $session->where : array();
        }
        $filters = (! empty($session->filters)) ? $session->filters : array();
        foreach ($filters as $filter) {
            $this->_where[] = $this->_applyFilter($filter['field'], $filter['rule'], $filter['text']);
        }
        if (! empty($this->_where)) {
            return implode(' AND ', $this->_where);
        } else {
            return null;
        }
    }

    /**
     * Delivers the inner part of the IndexAction using ajax
     *
     * List Action
     *
     * @return void
     */
    public function componentIndexAction ()
    {

    }

    /**
     * Delivers the inner part of the Listaction using ajax
     *
     * List Action
     *
     * @return void
     */
    public function componentListAction ()
    {

    }

    /**
     * Ajax part of displayAction
     *
     * Form Action
     *
     * @todo Not implemented yet
     * @return void
     */
    public function componentDisplayAction ()
    {

    }

    /**
     * Ajaxified part of the edit action
     *
     * Form Action
     *
     * @todo Not implemented yet
     * @return void
     */
    public function componentEditAction ()
    {

    }

    /**
     * Toggle a open/close a node
     *
     * List Action
     *
     * @todo to be removed
     *
     * @return void
     */
    public function toggleNodeAction ()
    {
        $currentActiveTree = Default_Helpers_TreeView::findPersistant();
        $currentActiveTree->toggleNode();
        $this->forward('list', $this->getRequest()->getControllerName(), $this->getRequest()->getModuleName());
    }

    /**
     * List all the data using the model for get it
     * We store the id of the shown project in the session, as other modules
     * and the indexcontroller might depend on that to define the current active
     * object
     * The default filter is the projectId
     * for get all the record from the current project
     *
     * List Action
     *
     * @return void
     */
    public function listAction ()
    {
        $db = Zend_Registry::get('db');
        /* Save the last project id into the session */
        /* @todo: Sanitize ID / Request parameter */
        $session = new Zend_Session_Namespace();
        if (isset($session->currentProjectId)) {
            $projectId = $session->currentProjectId;
        } else {
            $projectId = 0;
        }
        $this->addWhere($db->quoteInto('projectId = ?', $projectId));
        $this->getListView()->setModel($this->getModelObject()->fetchAll($this->getWhere()));
    }

    /**
     * Abandon current changes and return to the default view
     *
     * Form Action
     *
     * @return void
     */
    public function cancelAction ()
    {

    }

    /**
     * Displays the a single item for add an Item
     *
     * Form Action
     *
     * @return void
     */
    public function displayAction ()
    {
        $this->listAction();
    }

    /**
     * Example for json convert.
     *
     * @todo remove if not longer necessary
     * @return void
     */
    public function jsonListAction ()
    {
        $view = $this->_params['view'];

        $req = $this->getRequest();
        if ('tree' == $view) {
            $tree = new Phprojekt_Tree_Node_Database($this->getModelObject(), 1);
            $tree->setup();
            echo Phprojekt_Converter_Json::convertTree($tree);
        } else {
        	$db = Zend_Registry::get('db');
            // Parse the "sort" paramter send by the grid.
            // Dojo prefixes a column name with "-" for indicating a descending sort.
            $sort = $req->getParam('sort');
            if ($sort) {
                $sort = '-' == $sort[0] ? (substr($sort, 1).' DESC') : $sort;
            } else {
                $sort = null;
            }
            // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
            // so lets apply this to the query set. This is also used for loading a
            // grid on demand (initially only a part is shown, scrolling down loads what is needed).
            $count = $this->getRequest()->getParam('count');
            $offset = $this->getRequest()->getParam('start');
            
            $model = $this->getModelObject();
            // FIXXXXXXXXMEEEEEEE
            // the ollowing array needs to be generated depending on the available columns to filter for, this is hardcoded to "Todo"!!!!!!
            $possibleFields = array('priority', 'startDate', 'endDate', 'title','id');
            $where = array();
            foreach ($possibleFields as $k) {
                $value = $this->_params['filter'][$k];
                if ($value) {
                    // I dont know if this is the right way, i would expect to use prepared statements!!!!!!
                  $this->addWhere($db->quoteInto("$k = ?", $value));
                }
            }
            $session = new Zend_Session_Namespace();
        	if (isset($session->currentProjectId)) {
            	$projectId = $session->currentProjectId;
            	$this->addWhere($db->quoteInto('parent = ?', $projectId));
       	 	} 
        	
        	$records = $this->getModelObject()->fetchAll($this->getWhere(), $sort, $count, $offset);
        	$records = count($records) > 0 ? $records:null;
            echo Phprojekt_Converter_Json::convert($records);
        }

        exit;
    }

    /**
     * Displays the edit screen for the current item
     * Use the model module for get the data
     *
     * Form Action
     *
     * @return void
     */
    public function editAction ()
    {
        $this->listAction();
        if ($this->_itemid < 1) {
            $this->forward('display');
        } else {
            /* History */
            // $this->getFormView()->getModel()->find($this->_itemid);
            //$db = Zend_Registry::get('db');
            //$history = new Phprojekt_History(array('db' => $db));
            //$this->_smarty->historyData = $history->getHistoryData($this->getModelObject(), $this->_itemid);
            //$this->_smarty->dateFieldData = array('formType' => 'datetime');
            //$this->_smarty->userFieldData = array('formType' => 'userId');
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
        $this->listAction();
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
     * Render all the views that are not already renders
     *
     * @return void
     */
    protected function _generateOutput ()
    {
        /* Get the last project ID */
        $session = new Zend_Session_Namespace();
        $write   = true;
        $read    = true;

        $this->view->webpath = Zend_Registry::get('config')->webpath;

        if (isset($session->currentProjectId)) {
            $this->view->projectId = $session->currentProjectId;
            $this->view->projectName = $session->currentProjectName;
            $rights = new Phprojekt_RoleRights(null, $session->currentProjectId, $this->getRequest()->getModuleName());
            //$write = $rights->hasRight('write');
            //$read = $rights->hasRight('read') ? true : $write;
        }
        if (!isset($this->view->message)) {
            $this->view->message = '';
        }
        $this->view->params     = $this->_params;
        $this->view->itemid     = $this->_itemid;
        $this->view->modules    = $this->_submodules;
        $this->view->write      = $write;
        $this->view->read       = $read;

        $this->view->tree       = $this->_tree;
        $this->view->filterView = $this->getFilterView()->render();
        $this->view->listView   = $this->getListView()->render();
        $this->view->formView   = $this->getFormView()->render();
        $this->render('index');
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
    public function forward ($action, $controller = null, $module = null, array $params = null)
    {
        $this->_canRender = false;
        $this->_forward($action, $controller, $module, $params);
    }

    /**
     * Make the SQL where clause
     *
     * @param string $field The field in the database
     * @param string $rule  The rule clause
     * @param string $text  The text to seacrh
     *
     * @return string SQL where clause
     */
    private function _applyFilter ($field, $rule, $text)
    {
        $db = Zend_Registry::get('db');
        switch ($rule) {
            case 'begins':
                $w = $field . " LIKE " . $db->quote("$text%");
                break;
            case 'ends':
                $w = $field . " LIKE " . $db->quote("%$text");
                break;
            case 'exact':
                $w = $field . " = " . $db->quote($text);
                break;
            case 'mayor':
                $w = $field . " > " . $db->quote($text);
                break;
            case 'mayorequal':
                $w = $field . " >= " . $db->quote($text);
                break;
            case 'minorequal':
                $w = $field . " <= " . $db->quote($text);
                break;
            case 'minor':
                $w = $field . " < " . $db->quote($text);
                break;
            case 'not like':
                $w = $field . " NOT LIKE " . $db->quote("%$text%");
                break;
            default:
                $w = $field . " LIKE " . $db->quote("%$text%");
                break;
        }
        return $w;
    }
}