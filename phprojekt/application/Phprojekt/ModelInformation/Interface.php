<?php
/**
 * Convert a model into a json structure.
 * This is usually done by a controller to send data to the
 * client
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * The fields given by the ModelInformation interface
 * are used by a list view and therefore ordered in that way
 */
define('MODELINFO_ORD_LIST', 1);

/**
 * The fields given by the ModelInformation interface
 * are used by a form and therefore ordered in that way
 */
define('MODELINFO_ORD_FORM', 2);

/**
 * The fields given by the ModelInformation interface
 * are used by a filter and therefore ordered in that way
 */
define('MODELINFO_ORD_FILTER', 3);

/**
 * The fields given by the ModelInformation interface
 * are used by something undeclared, therefore we ust a
 * default value.
 */
define('MODELINFO_ORD_DEFAULT', MODELINFO_ORD_LIST);

/**
 * Convert a model into a json structure.
 * This is usally done by a controller to send data to the client.
 * The Phprojekt_Convert_Json takes care that a apporpriate structure
 * is made from the given model.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
interface Phprojekt_ModelInformation_Interface
{
    /**
     * A shortcut to get a list of titles from a model information
     *
     * @param integer $ordering Set the column order of the titles
     *
     * @return array
     */
    public function getTitles($ordering = MODELINFO_ORD_DEFAULT);

    /**
     * Return an array of field information.
     * See /docs/Documentation of the detailed exchange format.odt
     *
     * @return array
     */
     public function getFieldDefinition();
}