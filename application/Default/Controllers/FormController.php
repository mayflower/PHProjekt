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

require_once ('IndexController.php');

/**
 * Form Controller for PHProjekt 6.0
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
	 * Abandon current changes and return to the default view
	 */
	public function cancelAction()
	{
        $this->render('index');
	}

	/**
	 * Ajax part of displayAction
	 */
	public function componentDisplayAction()
	{
        $this->_helper->viewRenderer->setNoRender();
	}

	/**
	 * Ajaxified part of the edit action 
	 */
	public function componentEditAction()
    {
        $this->_helper->viewRenderer->setNoRender();
	}

	/**
	 * Deletes a certain item
	 */
	public function deleteAction()
    {
        $this->render('index');
	}

	/**
	 * displays a single item
	 */
	public function displayAction()
    {
        $this->render('index');
	}

	/**
	 * Displays the edit screen for the current item 
	 */
	public function editAction()
    {
        $this->render('index');
	}

	/**
	 * Default-Action, points to display
	 */
	public function indexAction()
	{
        return $this->_forward('display');
	}

	/**
	 * Saves the current item
	 */
	public function saveAction()
    {
        $this->render('index');
	}
}
