<?php
/**
 * Default Controller for PHProjekt 6
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
 * Default Setting Controller for PHProjekt 6
 *
 * This controller gives you the possibility you creae easy
 * settings for the modules by extendin this controller
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
class SettingController extends IndexController
{       	
    /**
     * Save the values into a given global
     * table that is maintained by the Setting module
     *
     * @return void

    public function jsonSaveAction()
    {
        $model  = Phprojekt_Loader::getModel('Setting', 'Setting');
        $model->find($this->getRequest()->getModuleName());
        $modelKeys = array_keys($model->configuration);
        foreach ($modelKeys as $key) {
            if ($this->getRequest()->getParam($key, false) !== false) {
                $model->$key = $this->getRequest()->getParam($key);
            }
        }

        $model->save();
    }
     */
    
    /* Get a list of all the modules that have setting
     *
     * @return void
     */

    

}
