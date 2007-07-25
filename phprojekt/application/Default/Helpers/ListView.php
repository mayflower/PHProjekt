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
     * Controller Object
     *
     * @var Zend_Controller_Action object
     */
    public $controller = '';

    /**
     * Constructor
     *
     * @param Zend_Controller_Action $controller Action object controller
     *
     * @return void
     */
    public function __construct($controller)
    {
        $translate         = Zend_Registry::get('translate');
        $this->_translator = $translate;
        $this->controller  = $controller;
    }

    /**
     * Return an array with the translated titles
     *
     * @return array The titles translated
     */
    public function getTitles()
    {
        $data = $this->controller->data['listData'];
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
     * @return array An array with all the rows
     */
    public function getItems()
    {
        $data  = (array) $this->controller->data['listData'];

        array_shift($data);
        return $data;
    }

    /**
     * Adds a single filter to the current view
      *
      * @return void
     */
    public function addFilterAction()
    {
        $this->controller->setListView();
        $this->controller->message = 'Filter Added';
        $this->controller->generateOutput();
        $this->controller->render('index');
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
        $this->controller->setListView();
        $this->controller->message = '&nbsp;';
        $this->controller->generateOutput();
        $this->controller->render('index');
    }

    /**
     * Remove a filter
     *
     * @return void
     */
    public function removeFilterAction()
    {
        $this->controller->setListView();
        $this->controller->message = 'Filter Removed';
        $this->controller->generateOutput();
        $this->controller->render('index');
    }

    /**
     * Sort the list view
     *
     * @return void
     */
    public function sortAction()
    {
        $this->controller->setListView();
        $this->controller->message = '&nbsp;';
        $this->controller->generateOutput();
        $this->controller->render('index');
    }
}