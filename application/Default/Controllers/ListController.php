<?php
/**
 * List Controller for PHProjekt 6.0
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

/* IndexController */
require_once ('IndexController.php');

/* Default_Helpers_ListView */
require_once (PHPR_CORE_PATH . '/Default/Helpers/ListView.php');

/**
 * List Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class ListController extends IndexController
{
	/**
	 * Adds a single filter to the current view
	 */
	public function addFilterAction()
	{
        $this->render('index');
	}

	/**
	 * Delivers the inner part of the IndexAction using ajax
	 */
	public function componentIndexAction()
	{
        $this->_helper->viewRenderer->setNoRender();
	}

	/**
	 * Delivers the inner part of the Listaction using ajax
	 */
	public function componentListAction()
    {
        $this->_helper->viewRenderer->setNoRender();
	}

	/**
	 * The Default-Action: displays the list page 
	 */
	public function indexAction()
	{
        return $this->_forward('list');
	}

	/**
	 * List all the data 
	 */
	public function listAction()
    {
        /*
        * data from the List Class like
        * '0' => array('Name','Description'),
        * '1' => array('Projecto 1','Test<br />a e i o u'),
        * '2' => array('Projecto 2','Test2'),
        * '3' => array('Projecto 3','Test<br />k a ñ')
        * );
        */
        $data = array();

        /* List Actions */
        $oListView = new Default_Helpers_ListView($data);
        $this->titles = $oListView->getTitles($data); 
        $this->lines  = $oListView->getItems($data); 
        $this->setListView($this->_render('list'));

        $this->render('index');
	}

	/**
	 *  Remove a filter
	 */
	public function removeFilterAction()
	{
        $this->render('index');
	}

	/**
	 * Sort the list view
	 */
	public function sortAction()
	{
        $this->render('index');
	}
}
