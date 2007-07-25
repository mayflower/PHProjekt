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
     * Controller Object
     *
     * @var Zend_Controller_Action object
     */
    public $controller = '';

    /**
     * Constructor
     *
     * @param Zend_Controller_Action $controller The action controller
     */
    public function __construct($controller)
    {
        $translate         = Zend_Registry::get('translate');
        $this->_translator = $translate;
        $this->controller  = $controller;
    }

    /**
     * Make all the input fields and return and arrar for
     * use in smarty.
     *
     * @return array The data for show in the template
     */
    public function getFields()
    {
        $fields = array();

        foreach ($this->controller->data['formData'] as $field => $fieldData) {
            $fields[] = array(
            'description' => $this->_translator->translate($fieldData['label']),
            'field'       => $this->getFormOutput($field, $fieldData));
        }

        $countFields = count($fields);
        $modFields   = $countFields % $this->controller->formColumns;
        if ($modFields != 0) {
            for ($index = $modFields; $index < $this->controller->formColumns; $index++) {
                $fields[] = '&nbsp';
            }
        }

        return $fields;
    }

    /**
     * Make a input form deppend on the type of the field
     *
     * @param string $field     The name of the field
     * @param array  $fieldData Array with data of the field
     *
     * @return string           The HTML output for the field
     */
    public function getFormOutput($field, $fieldData)
    {
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
      *
      * @return void
      */
    public function indexAction()
    {
        $this->displayAction();
    }

    /**
      * Abandon current changes and return to the default view
      *
      * @return void
      */
    public function cancelAction()
    {
        $this->controller->msg = '&nbsp;';
        $this->controller->setFormView();
        $this->controller->generateOutput();
        $this->controller->render('index');
    }

    /**
     * Ajax part of displayAction
      *
      * @return void
     */
    public function componentDisplayAction()
    {
    }

    /**
     * Ajaxified part of the edit action
      *
      * @return void
     */
    public function componentEditAction()
    {
    }

    /**
     * Deletes a certain item
      *
      * @return void
     */
    public function deleteAction()
    {
        $request = $this->controller->getRequest()->getParams();
        if (!isset($request['id'])) {
            $this->displayAction();
        } else {
            $params  = $this->controller->getRequest()->getParams();
            $buttons = $this->controller->oModels->getButtonsForm('display');
            $message = $this->_translator->translate('Deleted');

            $this->controller->oModels->deleteData($params);

            $this->controller->msg     = $message;
            $this->controller->buttons = $buttons;
            $this->controller->generateOutput();
            $this->controller->render('index');
        }
    }

    /**
     * Displays a single item
      *
      * @return void
     */
    public function displayAction()
    {
        $action  = $this->controller->oModels->getActionForm('display');
        $buttons = $this->controller->oModels->getButtonsForm('display');

        $this->controller->formAction = $action;
        $this->controller->buttons    = $buttons;

        $this->controller->generateOutput();
        $this->controller->render('index');
    }

    /**
     * Displays the edit screen for the current item
      *
      * @return void
     */
    public function editAction()
    {
        $request = $this->controller->getRequest()->getParams();
        if (!isset($request['id'])) {
            $this->displayAction();
        } else {
            $id       = intval($request['id']);
            $formData = $this->controller->oModels->getFormData($id);
            $action   = $this->controller->oModels->getActionForm('edit', $id);
            $buttons  = $this->controller->oModels->getButtonsForm('edit', $id);

            $this->controller->data['formData'] = $formData;
            $this->controller->formAction       = $action;
            $this->controller->buttons          = $buttons;

            $this->controller->generateOutput($id);
            $this->controller->render('index');
        }
    }

    /**
     * Saves the current item
      *
      * @return void
     */
    public function saveAction()
    {
        $params  = $this->controller->getRequest()->getParams();
        $message = $this->_translator->translate('Saved');
        $buttons = $this->controller->oModels->getButtonsForm('display');
        //$this->controller->errors = 'error!';

        $this->controller->oModels->saveData($params);

        $this->controller->msg     = $message;
        $this->controller->buttons = $buttons;

        $this->controller->generateOutput();
        $this->controller->render('index');
    }
}