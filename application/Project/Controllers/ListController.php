<?php
/**
 * List Project Module Controller for PHProjekt 6.0
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
 * List Project Module Controller for PHProjekt 6.0
 *
 * The list controller is and extension of the indexController
 * and use a Helper class for do the job.
 * This is because the listControllers from other modules must
 * have the function of the indexController
 * and the listControllers functions.
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
class Project_ListController extends Project_IndexController
{
    /**
     * Enter description here...
     *
     * @var unknown_type
     */
    protected $_oListView = null;

    /**
     * Initialize
     */
    public function init ()
    {
        parent::init();
        $this->_oListView = new Default_Helpers_ListView($this);
    }

    /**
     * Adds a single filter to the current view
     *
     * @return void
     */
    public function addFilterAction()
    {
        $this->_oListView->addFilterAction();
    }

    /**
     * Delivers the inner part of the IndexAction using ajax
     *
     * @return void
     */
    public function componentIndexAction()
    {
        $this->_oListView->componentIndexAction();
    }

    /**
     * Delivers the inner part of the Listaction using ajax
     *
     * @return void
     */
    public function componentListAction()
    {
        $this->_oListView->componentEditAction();
    }

    /**
     * Default action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_oListView->indexAction();
    }

    /**
     * List all the data
     *
     * @return void
     */
    public function listAction()
    {
        $this->_oListView->listAction();
    }

    /**
     * Remove a filter
     *
     * @return void
     */
    public function removeFilterAction()
    {
        $this->_oListView->removeFilterAction();
    }

    /**
     * Sort the list view
     *
     * @return void
     */
    public function sortAction()
    {
        $this->_oListView->sortFilterAction();
    }
}