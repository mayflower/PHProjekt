<?php
/**
 * Todo Module Controller for PHProjekt 6.0
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
 * Default Todo Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Todo_IndexController extends IndexController
{
    /**
     * How many columns will have the form
     *
     * @var integer
     */
    const FORM_COLUMNS = 1;

    /**
     * Init the Module object
     *
     * @return Zend_Item object
     */
    public function getModelsObject()
    {
        $db = Zend_Registry::get('db');

        return Phprojekt_Loader::getModel('Todo', 'Todo', array('db' => $db));
    }
}