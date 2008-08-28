<?php
/**
 * JavaScript Controller for PHProjekt 6
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
 * JavaScript Controller for PHProjekt 6
 *
 * The controller will return all the js files for the modules
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
class JsController extends IndexController
{
    /**
     * Standard action
     *
     * @return void
     */
    public function indexAction()
    {
        echo 'dojo.registerModulePath("phpr", "../../../application/Default/Views/dojo/scripts/system");';
        echo 'dojo.registerModulePath("phpr.Default", "../../../application/Default/Views/dojo/scripts");';
        echo 'dojo.registerModulePath("phpr.Core", "../../../application/Core/Views/dojo/scripts");';

        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/system/phpr.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/system/Component.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/system/grid.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/system/roundedContentPane.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/system/Store.js');

        // Default Folder
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/Main.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/Tree.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/Grid.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/Form.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/field.js');

        // Core Folder
        echo file_get_contents(PHPR_CORE_PATH.'/Core/Views/dojo/scripts/Main.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Core/Views/dojo/scripts/Tree.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Core/Views/dojo/scripts/Grid.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Core/Views/dojo/scripts/Form.js');

        // Load all modules and make and array of it
        $modules = array();
        $files   = scandir(PHPR_CORE_PATH);
        foreach ($files as $file) {
            if ($file != '.'  &&
                $file != '..' &&
                $file != '.svn' &&
                $file != 'Default' &&
                $file != 'Phprojekt') {
                echo 'dojo.registerModulePath("phpr.'.$file.'", "../../../application/'.$file.'/Views/dojo/scripts");';
                $scripts = scandir(PHPR_CORE_PATH.'/'.$file.'/Views/dojo/scripts/');
                if (in_array('Main.js', $scripts)) {
                    $modules[] = $file;
                }
                foreach ($scripts as $script) {
                    if ($file != 'Core' && substr($script, -3) == '.js') {
                        echo file_get_contents(PHPR_CORE_PATH.'/'.$file.'/Views/dojo/scripts/'.$script);
                    } else if ($file == 'Core' && substr($script, -3) != '.js' && substr($script, 0, 1) != '.') {
                        // Get all the Core scripts
                        $coreScripts = scandir(PHPR_CORE_PATH.'/'.$file.'/Views/dojo/scripts/'.$script);
                        if (in_array('Main.js', $coreScripts)) {
                            $modules[] = $script;
                        }
                        foreach ($coreScripts as $coreScript) {
                            if (substr($coreScript, -3) == '.js') {
                                echo file_get_contents(PHPR_CORE_PATH.'/'.$file.'/Views/dojo/scripts/'.$script.'/'.$coreScript);
                            }
                        }
                    }
                }
            }
        }

        echo 'dojo.provide("phpr.Main");';

        // Lang Files
        echo 'dojo.requireLocalization("phpr.Default", "Default");';

        echo '
        dojo.declare("phpr.Main", null, {
            constructor: function(/*String*/webpath, /*String*/currentModule, /*Int*/rootProjectId) {
                phpr.module           = currentModule;
                phpr.webpath          = webpath;
                phpr.rootProjectId    = rootProjectId;
                phpr.currentProjectId = rootProjectId ;
                phpr.nls              = dojo.i18n.getLocalization("phpr.Default", "Default");
        ';
        foreach ($modules as $module) {
            echo '
            this.'.$module.' = new phpr.'.$module.'.Main();
            ';
        }

        // The load method of the currentModule is called
        echo '
                dojo.publish(phpr.module + ".load");
            }
        });
        ';
    }
}