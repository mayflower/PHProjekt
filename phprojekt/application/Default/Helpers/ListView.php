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
    private $translator = '';

    /**
     * ActionController Object
     *
     * @var Zend_Controller_Action object
     */
    public $actionController = '';

    /**
     * Constructor
     *
     * @param Zend_Controller_Action object actionController
     *
     * @return void
     */
    public function __construct($actionController)
    {
        $translate                     = Zend_Registry::get('translate');
        $this->translator          = $translate;
        $this->actionController = $actionController;
    }

    /**
     * Return an array with the translated titles
     *
     * @return array The titles translated
     */
    public function getTitles()
    {
        $data = $this->actionController->data['listData'];
        $titles = array();

        if (empty($data)) {
            return '&nbsp;';
        }

        foreach ($data[0] as $titleData) {
            $titles[] = $this->translator->translate($titleData);
        }

        return $titles;
    }

    /**
     * Return an array with all the items
     *
     * @return array An array with all the rows
     */
    public function getItems()
    {
        $data = $this->actionController->data['listData'];
        $items = array();

        if (empty($data)) {
            return $items;
        }

        $editLink = PHPR_ROOT_WEB_PATH
            . $this->_actionController->getRequest()->getModuleName() . '/'
            . 'form/'
            . 'edit/'
            . 'id/';

        foreach ($data as $key => $itemData) {
            if ($key > 0) { // Ommit the titles
                $items[$key] = array();
                $first             = 1;
                foreach ($itemData as $field) {
                    if (empty($field)) {
                        $field = "&nbsp;";
                    }
                    if ($first) {
                        $items[$key][] = '<a href="'
                            . $editLink . $key
                            . '">' . $field . '</a>';
                        $first = 0;
                    } else {
                        $items[$key][] = $field;
                    }
                }
            }
        }
        return $items;
    }

    /**
     * Adds a single filter to the current view
      *
      * @return void
     */
    public function addFilterAction()
    {
        $this->actionController->setListView();
        $this->actionController->msg = 'Filter Added';
        $this->actionController->generateOutput();
        $this->actionController->render('index');
    }

    /**
     * Delivers the inner part of the IndexAction using ajax
     *
     * @return void
     */
    public function componentIndexAction()
    {
    }

    /**
     * Delivers the inner part of the Listaction using ajax
     *
     * @return void
     */
    public function componentListAction()
    {
    }

    /**
     * Default action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->listAction();
    }

    /**
     * List all the data
     *
     * @return void
     */
    public function listAction()
    {
        $this->actionController->setListView();
        $this->actionController->msg = '&nbsp;';
        $this->actionController->generateOutput();
        $this->actionController->render('index');
    }

    /**
     * Remove a filter
     *
     * @return void
     */
    public function removeFilterAction()
    {
        $this->actionController->setListView();
        $this->actionController->msg = 'Filter Removed';
        $this->actionController->generateOutput();
        $this->actionController->render('index');
    }

    /**
     * Sort the list view
     *
     * @return void
     */
    public function sortAction()
    {
        $this->actionController->setListView();
        $this->actionController->msg = '&nbsp;';
        $this->actionController->generateOutput();
        $this->actionController->render('index');
    }
}