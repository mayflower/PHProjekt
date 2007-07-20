<?php
/**
 * List View helper class
 *
 * This class is for draw the list view
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
 * List view helper
 *
 * The class process the info for show the list data
 * ans all the acctions from the controller
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Helpers_ListView
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
     * @param Zend_Controller_Action object actionController
     * @return void
     */
    public function __construct($actionController)
    {
        $translate = Zend_Registry::get('translate');
        $this->_translator = $translate;

        $this->_actionController = $actionController;
    }
    
    /**
     * Return an array with the translated titles
     *
     * @param void
     * @return array - The titles translated
     */
    public function getTitles() {

        $data = $this->_actionController->_data['listData'];
        $titles = array();

        if (empty($data)) {
            return '&nbsp;';
        }

        foreach ($data[0] as $titleData) {
            $titles[] = $this->_translator->translate($titleData);
        }

        return $titles;
    }

    /**
     * Return an array with all the items
     *
     * @param void
     * @return array - An array with all the rows
     */
    public function getItems() {

        $data = $this->_actionController->_data['listData'];
        $items = array();

        if (empty($data)) {
            return $items;
        }

        foreach ($data as $key => $itemData) {
            if ($key > 0) { // Ommit the titles
                $items[$key] = array();
                foreach ($itemData as $field) {
                    if (empty($field)) {
                        $field = "&nbsp;";
                    }
                    $items[$key][] = $field;
                }
            }

        }

        return $items;
    }

	/**
	 * Adds a single filter to the current view
	 */
	public function addFilterAction()
    {
        $this->_actionController->setListView();
        $this->_actionController->msg = 'Filter Added';

        $this->_actionController->generateOutput();
        $this->_actionController->render('index');
	}

	/**
	 * Delivers the inner part of the IndexAction using ajax
	 */
	public function componentIndexAction()
	{
	}

	/**
	 * Delivers the inner part of the Listaction using ajax
	 */
	public function componentListAction()
    {
	}

	/**
     * Default action
	 */
	public function indexAction()
    {
        $this->listAction();
	}

	/**
	 * List all the data 
	 */
	public function listAction()
    {
        $this->_actionController->setListView();
        $this->_actionController->msg = '&nbsp;';

        $this->_actionController->generateOutput();
        $this->_actionController->render('index');
	}

	/**
	 *  Remove a filter
	 */
	public function removeFilterAction()
    {
        $this->_actionController->setListView();
        $this->_actionController->msg = 'Filter Removed';

        $this->_actionController->generateOutput();
        $this->_actionController->render('index');
	}

	/**
	 * Sort the list view
	 */
	public function sortAction()
	{
        $this->_actionController->setListView();
        $this->_actionController->msg = '&nbsp;';

        $this->_actionController->generateOutput();
        $this->_actionController->render('index');
	}
}
