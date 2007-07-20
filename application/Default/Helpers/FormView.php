<?php
/**
 * Form View helper class
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * Form View helper class
 *
 * This class is for draw the form
 * And process the form actions
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Helpers_FormView
{
    /**
     * Translator object
     *
     * @var Zend_Log object
     */
    private $_translator = '';

    /**
     * ActionController Object
     *
     * @var Zend_Controller_Action object
     */
    public $_actionController = '';

    /**
     * Constructor
     *
     * @param Zend_Controller_Action actionController
     * @return void
     */
    public function __construct($actionController)
    {
        $translate = Zend_Registry::get('translate');
        $this->_translator = $translate;

        $this->_actionController = $actionController;
    }

    /**
     * Make all the input fields and return and arrar for
     * use in smarty.
     *
     * @param void
     * @return array - The data for show in the template
     */
    public function getFields() {

        $fields = array();

        foreach ($this->_actionController->_data['formData'] as $field => $fieldData) {
            $fields[] = $this->_translator->translate($fieldData['showName'])
                        . "&nbsp;"
                        . $this->getFormOutput($field, $fieldData);
        }

        $countFields = count($fields);
        $modFields   = $countFields % $this->_actionController->_formColumns;
        if ($modFields != 0) {
            for ($index = $modFields; $index < $this->_actionController->_formColumns; $index++) {
                $fields[] = '&nbsp';
            }
        }

        return $fields;
    }

    /**
     * Make a input form deppend on the type of the field
     *
     * @param string field    - The name of the field
     * @param array fieldData - Array with data of the field
     * @return string         - The HTML output for the field
     */
    public function getFormOutput($field, $fieldData) {

        $outout = '';
        switch ($fieldData['type']) {
            case 'hidden':
                $output = '<input type="hidden" '
                          . 'name="' . $field . '"'
                          . 'value="' . $fieldData['value'] .'"'
                          . ' />';
                break;
            default:
                $output = '<input type="text" '
                          . 'name="' . $field . '"'
                          . 'value="' . $fieldData['value'] .'"'
                          . ' />';
                break;
        }

        return $output;
    }

    /**
      * Default action
      */
    public function indexAction()
    {
        $this->displayAction();
    }

    /**
      * Abandon current changes and return to the default view
      */
    public function cancelAction()
    {
        $this->_actionController->msg = '&nbsp;';
        $this->_actionController->setFormView();
        $this->_actionController->generateOutput();

        $this->_actionController->render('index');
    }

    /**
     * Ajax part of displayAction
     */
    public function componentDisplayAction()
    {
    }

    /**
     * Ajaxified part of the edit action
     */
    public function componentEditAction()
    {
    }

    /**
     * Deletes a certain item
     */
    public function deleteAction()
    {
        $request = $this->_actionController->getRequest()->getParams();
        if (!isset($request['id'])) {
            $this->displayAction();
        } else {
            $this->_actionController->_oModels->deleteData($this->_actionController->getRequest()->getParams());
            $this->_actionController->msg = 'Deleted';
            $this->_actionController->generateOutput();

            $this->_actionController->render('index');
        }
    }

    /**
     * Displays a single item
     */
    public function displayAction()
    {
        $this->_actionController->formAction =  $this->_actionController->_oModels->getActionForm('display');
        $this->_actionController->buttons =  $this->_actionController->_oModels->getButtonsForm('display');
        $this->_actionController->generateOutput();

        $this->_actionController->render('index');
    }

    /**
     * Displays the edit screen for the current item
     */
    public function editAction()
    {
        $request = $this->_actionController->getRequest()->getParams();
        if (!isset($request['id'])) {
            $this->displayAction();
        } else {
            $id = intval($request['id']);

            $this->_actionController->_data['formData'] = $this->_actionController->_oModels->getFormData($id);
            $this->_actionController->formAction =  $this->_actionController->_oModels->getActionForm('edit',$id);
            $this->_actionController->buttons =  $this->_actionController->_oModels->getButtonsForm('edit',$id);
            $this->_actionController->generateOutput();

            $this->_actionController->render('index');
        }
    }

    /**
     * Saves the current item
     */
    public function saveAction()
    {
        $this->_actionController->_oModels->saveData($this->_actionController->getRequest()->getParams());

        $this->_actionController->msg = 'Saved';
        //$this->_actionController->errors = 'error!';
        $this->_actionController->buttons =  $this->_actionController->_oModels->getButtonsForm('display');
        $this->_actionController->generateOutput();

        $this->_actionController->render('index');
    }
}