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

require_once 'Zend/Controller/Action.php';

/* Default_Helpers_ListView */
require_once (PHPR_CORE_PATH . '/Default/Helpers/ListView.php');

/* Default_Helpers_FormView */
require_once (PHPR_CORE_PATH . '/Default/Helpers/FormView.php');

/* Default_Models_Default */
require_once (PHPR_CORE_PATH . '/Default/Models/Default.php');

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
    public $_treeViewSeted = false;

    /**
     * Set true if is seted the listView
     *
     * @var boolean
     */
    public $_listViewSeted = false;

    /**
     * Set true if is seted the formView
     *
     * @var boolean
     */
    public $_formViewSeted = false;

    /**
     * Array with the all the data for render
     * 'listData' => All the data for list
     * 'formData' => All the data for form
     * 'treeData' => All the data for trees
     *
     * @var array
     */
    public $_data = array('listData','formData','treeData');

    /**
     * Object model with all the specific data
     *
     * @var Phprojekt_Item object
     */
    public $_oModels = '';

    /**
     * How many columns will have the form
     *
     * @var integer
     */
    public $_formColumns  = 2;

    /**
     * Init function
     * Get the Smarty instance
     * and the data for list and form
     *
     * @param void
     * @return void
     */
    public function init()
    {
        /* Get the smarty object */
        $this->_smarty           = Zend_Registry::get('view');

        $this->_oModels          = $this->getModelsObject();

        /* Stuff for list View */
        $this->_data['listData'] = $this->_oModels->getListData();

        /* Stuff for form View */
        $this->_data['formData'] = $this->_oModels->getFormData();
    }

    /**
     * Standard action
     */
    public function indexAction()
    {
        $this->msg = '&nbsp;';
        $this->buttons =  $this->_oModels->getButtonsForm('display');
        $this->generateOutput();
    }

    /**
     * Render the treeView
     *
     * @param void
     * @return void
     */
    public function setTreeView($output)
    {
        $this->treeView = $this->_render('tree');
    }

    /**
     * Render the listView
     *
     * @param void
     * @return void
     */
    public function setListView()
    {
        $this->_listViewSeted = true;
        $oListView = new Default_Helpers_ListView($this);
        $this->_data['listData'] = $this->_oModels->getListData();
        $this->titles = $oListView->getTitles($this->_data['listData']);
        $this->lines  = $oListView->getItems($this->_data['listData']);
        $this->listView = $this->_render('list');
    }

    /**
     * Render the formView
     *
     * @param void
     * @return void
     */
    public function setFormView($id = 0)
    {
        $this->_formViewSeted = true;
        $oFormView      = new Default_Helpers_FormView($this);
        $this->columns  = $this->_formColumns;
        if ($id == 0) {
            $this->_data['formData'] = $this->_oModels->getFormData($id);
        }
        $this->fields   = $oFormView->getFields($this->_data['formData']);
        $this->formView = $this->_render('form');
    }

    /**
     * Return true if not have access
     */
    public function accessDenied()
    {
        return false;
    }

    /**
     * If the Action don´t exists, call indexAction
     *
     * @param string method - Action method
     * @param array  args   - Arguments for the Action
     * @return Zend_Exception
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

    /**
     * Set a value into the smarty object for render it
     *
     * @param string name - Name of the value for render
     * @param mix value   - Value for the var
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
     * @param string name - Name of the value
     * @return mix - The value of the var
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
     * @param string template - Which var of the index.tpl
     * @return void
     */
    public function _render($template) {
        switch ($template) {
            case 'tree':
                /* Set treeview */
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
     * @param void
     * @return void
     */
    public function generateOutput()
    {
        if (!$this->_treeViewSeted) {
            /* Set treeview */
            $this->setTreeView($this->_render('tree'));
        }

        if (!$this->_listViewSeted) {
            /* Set listview */
            $this->setListView();
        }

        if (!$this->_formViewSeted) {
            /* Set formview */
            $this->setFormView();
        }
    }

    /**
     * Get the model object
     * This function must be redefined in each module
     *
     * @param void
     * @return array - All the fields for list
     */
    public function getModelsObject()
    {
        $oModels = new Default_Models_Default();
        return $oModels;
    }
}