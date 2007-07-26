<?php
/**
 * Form Controller for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Form Controller for PHProjekt 6.0
 *
 * The form controller is and extension of the indexController
 * and use a Helper class for do the job.
 * This is because the formControllers from other modules must
 * have the function of the indexController
 * and the formControllers functions.
 * Since we can not use a daiamont structure, we use a third class.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class FormController extends IndexController
{
    /**
     * Form view
     *
     * @var Default_Helpers_FormView
     */
    protected $_oFormView = null;

    /**
     * Initialize
     *
     * @return void
     */
    public function init ()
    {
        parent::init();
        $this->_oFormView = new Default_Helpers_FormView($this);
    }

    /**
     * Default action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_oFormView->indexAction();
    }

    /**
     * Abandon current changes and return to the default view
     *
     * @return void
     */
    public function cancelAction()
    {
        $this->_oFormView->cancelAction();
    }

    /**
     * Ajax part of displayAction
     *
     * @return void
     */
    public function componentDisplayAction()
    {
        $this->_oFormView->componentDisplayAction();
    }

    /**
     * Ajaxified part of the edit action
     *
     * @return void
     */
    public function componentEditAction()
    {
        $this->_oFormView->componentEditAction();
    }

    /**
     * Deletes a certain item
     *
     * @return void
     */
    public function deleteAction()
    {
        $this->_oFormView->deleteAction();
    }

    /**
     * Displays the a single item
     *
     * @return void
     */
    public function displayAction()
    {
        $this->_oFormView->displayAction();
    }

    /**
     * Displays the edit screen for the current item
     *
     * @return void
     */
    public function editAction()
    {
        $this->_oFormView->editAction();
    }

    /**
     * Saves the current item
     *
     * @return void
     */
    public function saveAction()
    {
        $this->_oFormView->saveAction();
    }
}