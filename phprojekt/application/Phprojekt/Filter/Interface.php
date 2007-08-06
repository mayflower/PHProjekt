<?php
/**
 * Prefiltering
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */

/**
 * Filter provide a easy, chainable way to do post filtering for databases.
 *
 * @todo not supported yet
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
interface Phprojekt_Filter_Interface
{
    /**
     * Filters a select
     *
     * @return void
     */
    public function filter(Zend_Db_Select &$select, Zend_Db_Adapter_Abstract $adapter);

    /**
     * Adds a filter to the chain
     *
     * @param Phprojekt_Filter_Interface $filter
     *
     * @return void
     */
    public function addFilter(Phprojekt_Filter_Interface $filter);
}