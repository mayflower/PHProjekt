<?php
/**
 * Project Module Controller for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

/* Default IndexController*/
require_once (PHPR_CORE_PATH . '/Default/Controllers/IndexController.php');

/* Project_Models_Project*/
require_once (PHPR_CORE_PATH . '/Project/Models/Project.php');

/**
 * Default Project Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Project_IndexController extends IndexController
{
    /**
     * How many columns will have the form
     *
     * @var integer
     */
    public $_formColumns  = 1;

    /**
     * Init the Module object
     *
     * @param void
     * @return Zend_Item object
     */
    public function getModelsObject()
    {
        $db = Zend_Registry::get('db');
        $oModels = new Project_Models_Project(array('db' => $db));

        return $oModels;
    }
}