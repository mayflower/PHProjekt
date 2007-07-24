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
    public $actionController = '';

    /**
     * Constructor
     *
     * @param Zend_Controller_Action $actionController The action controller
     *
     */
    public function __construct($actionController)
    {
        $translate                     = Zend_Registry::get('translate');
        $this->_translator        = $translate;
        $this->actionController = $actionController;
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

        foreach ($this->actionController->data['formData'] as $field => $fieldData) {
            $fields[] = $this->_translator->translate($fieldData['label'])
                        . "&nbsp;"
                        . $this->getFormOutput($field, $fieldData);
        }

        $countFields = count($fields);
        $modFields   = $countFields % $this->actionController->formColumns;
        if ($modFields != 0) {
            for ($index = $modFields; $index < $this->actionController->formColumns; $index++) {
                $fields[] = '&nbsp';
            }
        }

        return $fields;
    }

    /**
     * Make a input form deppend on the type of the field
     *
     * @param string $field         The name of the field
     * @param array $fieldData  Array with data of the field
     *
     * @return string                     The HTML output for the field
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
        $this->actionController->msg = '&nbsp;';
        $this->actionController->setFormView();
        $this->actionController->generateOutput();

        $this->actionController->render('index');
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
        $request = $this->actionController->getRequest()->getParams();
        if (!isset($request['id'])) {
            $this->displayAction();
        } else {
            $this->actionController->oModels->deleteData($this->actionController->getRequest()->getParams());
            $this->actionController->msg = $this->_translator->_('Deleted');
            $this->actionController->generateOutput();

            $this->actionController->render('index');
        }
    }

    /**
     * Displays a single item
      *
      * @return void
     */
    public function displayAction()
    {
        $this->actionController->formAction =  $this->actionController->oModels->getActionForm('display');
        $this->actionController->buttons       =  $this->actionController->oModels->getButtonsForm('display');
        $this->actionController->generateOutput();

        $this->actionController->render('index');
    }

    /**
     * Displays the edit screen for the current item
      *
      * @return void
     */
    public function editAction()
    {
        $request = $this->actionController->getRequest()->getParams();
        if (!isset($request['id'])) {
            $this->displayAction();
        } else {
            $id = intval($request['id']);

            $this->actionController->data['formData'] = $this->actionController->oModels->getFormData($id);
            $this->actionController->formAction            =  $this->actionController->oModels->getActionForm('edit', $id);
            $this->actionController->buttons                  =  $this->actionController->oModels->getButtonsForm('edit', $id);
            $this->actionController->generateOutput($id);

            $this->actionController->render('index');
        }
    }

    /**
     * Saves the current item
      *
      * @return void
     */
    public function saveAction()
    {
        $this->actionController->oModels->saveData($this->actionController->getRequest()->getParams());

        //$this->actionController->errors = 'error!';
        $this->actionController->msg       = $this->_translator->_('Saved');
        $this->actionController->buttons =  $this->actionController->oModels->getButtonsForm('display');
        $this->actionController->generateOutput();

        $this->actionController->render('index');
    }
}