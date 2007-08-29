<?php
/**
 * History Module Controller for PHProjekt 6.0
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
 * History Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class History_IndexController extends IndexController
{
    /**
     * Init the Module object
     *
     * @return Zend_Item object
     */
    public function getModelsObject()
    {
        $db = Zend_Registry::get('db');

        return Phprojekt_Loader::getModel('History', 'History', array('db' => $db));
    }

    /**
     * Save the project id to 0 and then call the parent action
     *
     * @return void
     */
    public function setListView()
    {
        /* Get the last project ID */
        $session = new Zend_Session_Namespace();
        $session->lastProjectId = 0;

        parent::setListView();
    }

    /**
     * Redefine the action for no use forms
     *
     * @return void
     */
    public function displayAction()
    {
        $this->_forward('list');
    }

    /**
     * Redefine the action for no use forms
     *
     * @return void
     */
    public function editAction()
    {
        $this->_forward('list');
    }

    /**
     * Redefine the action for no use forms
     *
     * @return void
     */
    public function saveAction()
    {
        $this->_forward('list');
    }

    /**
     * Redefine the action for no use forms
     *
     * @return void
     */
    public function deleteAction()
    {
        $this->_forward('list');
    }
}