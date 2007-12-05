<?php
/**
 * Project model class
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
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
 * Project model class
 *
 * The class of each model return the data for show
 * on the list and the form view
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_Models_Project extends Phprojekt_Item_Abstract
{
    /**
     * Return wich submodules use this module
     *
     * Per now is just a fix array for test.
     * This fucntion must return the correct relation between
     * users - projects - modules
     *
     * @return array
     */
    public function getSubModules()
    {
        //select all sobmodules with read rights from  db
        $session = new Zend_Session_Namespace();
        $allModulesArray= array('Todo','Note','Timecard');
        $modulesArray = array();
        $rights = new Phprojekt_RoleRights($session->currentProjectId,
        'Project');
        foreach ($allModulesArray as $module) {
            $right =  $rights->hasRight('read', $module) ? true :
            $rights->hasRight('write', $module);
            if ($right) {
                $modulesArray[] = $module;
            }
        }

        return $modulesArray;

    }

}
