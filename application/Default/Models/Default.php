<?php
/**
 * Default model class
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
 * Default model class
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Models_Default
{
    /**
     * Default module has no fields for list
     *
     * @param void
     * @return array
     */
    public function getListData()
    {
        return array();
    }

    /**
     * Default module has no fields for form
     *
     * @param void
     * @return array
     */
    public function getFormData()
    {
        return array();
    }
}