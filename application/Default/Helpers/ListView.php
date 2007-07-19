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
 * The class will recive and array with data and draw a list view.
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
     */
    private $_translator = '';

    /**
     * Number of columns
     */
    public $_columns = '';
    
    /**
     * Number of rows
     */
    public $_rows = '';

    public function __construct($data = array())
    {
        $translate = Zend_Registry::get('translate');
        $this->_translator = $translate;

        /* Get how many columns and rows have */
        if (!empty($data)) {
            $this->_columns = count($data[0]);
            $this->_rows    = count($data);
        } else { 
            $this->_columns = 0;
            $this->_rows    = 0;
        }
    }
    
    /**
     * Return an array with the translated titles
     *
     * @param array data - The all data values for render
     * @return array     - The titles translated
     */
    public function getTitles($data = array()) {

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
     * @param array data - The all data values for render
     * @return array     - An array with all the rows
     */
    public function getItems($data = array()) {

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
}
