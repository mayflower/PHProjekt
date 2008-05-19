<?php
/**
 * Search Controller for PHProjekt 6
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id: IndexController.php 635 2008-04-02 19:32:05Z david $
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Search Controller for PHProjekt 6
 *
 * The controller will get all the actions for return the search results
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class SearchController extends IndexController
{
    /**
     * Search words class
     *
     * @var Phprojekt_Search_Words
     */
    private $_search = null;

    /**
     * Init function
     *
     * @return void
     */
    public function init ()
    {
        $this->_search = new Phprojekt_Search_Default();
        parent::init();
    }

    /**
     * Search for words
     *
     * @requestparam string $words    The words for seach
     * @requestparam string $operator AND/OR
     *
     * @return void
     */
    public function jsonSearchAction()
    {
        $words    = $this->getRequest()->getParam('words');
        $operator = $this->getRequest()->getParam('operator', 'AND');

        $results = array();
        $results  = $this->_search->search($words, $operator);

        echo $this->_json->convert($results);
    }
}