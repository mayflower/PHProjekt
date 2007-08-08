<?php
/**
 * Default Controller for PHProjekt 6.0
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
 * Default Controller for PHProjekt 6.0
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
     * Set true if is seted the treeView
     *
     * @var boolean
     */
    public $treeViewSeted = false;

    /**
     * Set true if is seted the listView
     *
     * @var boolean
     */
    public $listViewSeted = false;

    /**
     * Set true if is seted the formView
     *
     * @var boolean
     */
    public $formViewSeted = false;

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
     * @var integer
     */
    public $formColumns  = 2;

    /**
     * Init function
     * Get the Smarty instance
     * and the data for list and form
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

        $db       = Zend_Registry::get('db');
        $projects = PHprojekt_Loader::getModelFactory('Project', 'Project', array('db' => $db));
        $tree     = new Phprojekt_Tree_Node_Database($projects, 1);
        $tree->setup();

        $this->_smarty             = Zend_Registry::get('view');
        $this->_smarty->module     = $this->getRequest()->getModuleName();
        $this->_smarty->controller = $this->getRequest()->getControllerName();
        $this->_smarty->action     = $this->getRequest()->getActionName();
        $this->oModels             = $this->getModelsObject();
        $this->data['listData']    = $this->oModels->getListData();
        $this->data['formData']    = $this->oModels->getFormData();

        $this->_oListView = new Default_Helpers_ListView($this);
        $this->_oFormView = Default_Helpers_FormView::getInstance($this->_smarty);
        $this->_oTreeView = new Default_Helpers_TreeView($tree);

        $this->_oTreeView->makePersistent();
    }

    /**
     * Standard action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_forward('list');
    }

    /**
     * Adds a single filter to the current view
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
     * List Action
     *
     * @return void
     */
    public function componentIndexAction()
    {
    }

    /**
     * Delivers the inner part of the Listaction using ajax
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
     * List all the data
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
     * Form Action
     *
     * @return void
     */
    public function componentDisplayAction()
    {
    }

    /**
     * Ajaxified part of the edit action
     * Form Action
     *
     * @return void
     */
    public function componentEditAction()
    {
    }

    /**
     * Displays the a single item
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
     * Form Action
     *
     * @return void
     */
    public function editAction()
    {
        $request = $this->getRequest()->getParams();
        if (!isset($request['id'])) {
            $this->_forward('display');
        } else {
            $id       = intval($request['id']);
            $formData = $this->oModels->getFormData($id);

            $this->data['formData'] = $formData;
            $this->generateOutput($id);
            $this->render('index');
        }
    }

    /**
     * Saves the current item
     * Form Action
     *
     * @return void
     */
    public function saveAction()
    {
        $request = $this->getRequest()->getParams();

        $parent = (isset($request['parent'])) ? (int) $request['parent'] : 1;
        $requestedId = (isset($request['id'])) ? (int) $request['id'] : null;

        $parentNode = new Phprojekt_Tree_Node_Database($this->oModels,$parent);
        $newNode = new Phprojekt_Tree_Node_Database($this->oModels, $requestedId);


        if (isset($request['id'])) {
            $newNode->setup();
        }
        $parentNode->setup();

        foreach ($request as $k => $v) {
            if ($newNode->getActiveRecord()->keyExists($k)) {
                $newNode->$k = $v;
            }
        }

        if ($newNode->getActiveRecord()->recordValidate()) {
            if (null === $requestedId || $newNode->parent !== $parentNode->id)
                $parentNode->appendNode($newNode);
            else
                $newNode->getActiveRecord()->save();

            $this->message = 'Saved';
        } else {
            $this->errors = $newNode->getActiveRecord()->getError();
        }

        $this->setTreeView();

        $this->generateOutput();
        $this->render('index');
    }

    /**
     * Deletes a certain item
     * Form Action
     *
     * @return void
     */
    public function deleteAction()
    {
        $request = $this->getRequest()->getParams();
        if (!isset($request['id'])) {
            $this->_forward('display');
        } else {
            $id = intval($request['id']);
            $this->oModels->find($id);
            if ($this->oModels->count() > 0) {
                $this->oModels->delete();
            }
            $this->message = 'Deleted';
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
        $this->treeViewSeted = true;
        $this->treeView = $this->_render('tree');
    }

    /**
     * Render the listView
     *
     * @return void
     */
    public function setListView()
    {
        $this->listViewSeted    = true;
        $this->data['listData'] = $this->oModels->getListData();

        $this->titles   = $this->_oListView->getTitles($this->data['listData']);
        $this->lines    = $this->data['listData'];
        $this->listView = $this->_render('list');
    }

    /**
     * Render the formView
     *
     * @param integer $id Optional, The id of the row
     *
     * @return void
     */
    public function setFormView($id = 0)
    {
        $this->formViewSeted = true;
        $this->columns       = $this->formColumns;
        if ($id == 0) {
            $this->data['formData'] = $this->oModels->getFormData($id);
        }

        /* Asign post values */
        $params   = $this->getRequest()->getParams();
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
        $this->view->currentId = $this->getRequest()->getParam('id');

        if (!$this->treeViewSeted) {
            $this->setTreeView();
        }

        if (!$this->listViewSeted) {
            $this->setListView();
        }

        if (!$this->formViewSeted) {
            $this->setFormView($id);
        }
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