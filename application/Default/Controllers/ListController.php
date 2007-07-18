<?php
/**
 * List Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    CVS: $Id: 
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

require_once ('IndexController.php');

/**
 * List Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
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
