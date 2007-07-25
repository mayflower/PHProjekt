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
        /* Get the smarty object */
        $this->_smarty = Zend_Registry::get('view');
        $this->oModels = $this->getModelsObject();

        /* Stuff for list View */
        $this->data['listData'] = $this->oModels->getListData();

        /* Stuff for form View */
        $this->data['formData'] = $this->oModels->getFormData();
    }

    /**
     * Standard action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->msg     = '&nbsp;';
        $this->buttons =  $this->oModels->getButtonsForm('display');
        $this->generateOutput();
    }

    /**
     * Render the tree view
     *
     * @return void
     */
    public function setTreeView()
    {
        $this->treeView = $this->_render('tree');
    }

    /**
     * Render the listView
     *
     * @return void
     */
    public function setListView()
    {
        $this->_listViewSeted   = true;
        $oListView              = new Default_Helpers_ListView($this);
        $this->data['listData'] = $this->oModels->getListData();

        $this->titles   = $oListView->getTitles($this->data['listData']);
        $this->lines    = $oListView->getItems($this->data['listData']);
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
        $oFormView           = new Default_Helpers_FormView($this);
        $this->columns       = $this->formColumns;
        if ($id == 0) {
            $this->data['formData'] = $this->oModels->getFormData($id);
        }
        $this->fields   = $oFormView->getFields($this->data['formData']);
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
        /* all other methods throw an exception */
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
    private function _render($template)
    {
        switch ($template) {
        case 'tree':
                /* Set treeview */
                $this->view->phprojekt_version = "PHProjekt 6 - Charon";
                return $this->_helper->viewRenderer->view->render('tree.tpl');
                break;
        case 'form':
                /* Set formview */
                return $this->_helper->viewRenderer->view->render('form.tpl');
                break;
        default:
        case 'list':
                /* Set listview */
                return $this->_helper->viewRenderer->view->render('list.tpl');
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
        if (!$this->treeViewSeted) {
            /* Set treeview */
            $this->setTreeView($this->_render('tree'));
        }

        if (!$this->listViewSeted) {
            /* Set listview */
            $this->setListView();
        }

        if (!$this->formViewSeted) {
            /* Set formview */
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