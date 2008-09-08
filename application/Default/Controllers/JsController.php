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
    private $_modules = array();

    /**
     * Collect all the js files and return it as one
     *
     * @return void
     */
    public function indexAction()
    {
        echo 'dojo.registerModulePath("phpr", "../../../application/Default/Views/dojo/scripts/system");';
        echo 'dojo.registerModulePath("phpr.Default", "../../../application/Default/Views/dojo/scripts");';
        echo 'dojo.registerModulePath("phpr.Default.nls", "../../../application/Default");';
        echo 'dojo.registerModulePath("phpr.Core", "../../../application/Core/Views/dojo/scripts");';
        echo 'dojo.registerModulePath("phpr.Core.nls", "../../../application/Core");';

        // System files, must be parsed in this order
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/system/phpr.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/system/Component.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/system/grid.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/system/roundedContentPane.js');
        echo file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/system/Store.js');

        // Default Folder
        echo $this->_getDefaultScripts();

        // Core Folder
        echo $this->_getCoreScripts();

        // Load all modules and make and array of it
        $files   = scandir(PHPR_CORE_PATH);
        foreach ($files as $file) {
            if ($file != '.'  &&
                $file != '..' &&
                $file != '.svn' &&
                $file != 'Default' &&
                $file != 'Phprojekt') {
                if (is_dir(PHPR_CORE_PATH.'/'.$file.'/Views/dojo/scripts/')) {
                    $scripts = scandir(PHPR_CORE_PATH.'/'.$file.'/Views/dojo/scripts/');
                } else {
                    $scripts = array();
                }
                $this->_modules[] = $file;
                if ($file != 'Core') {
                	echo 'dojo.registerModulePath("phpr.'.$file.'", "../../../application/'.$file.'/Views/dojo/scripts");';
                	echo 'dojo.registerModulePath("phpr.'.$file.'.nls", "../../../application/'.$file.'");';
                    echo $this->_getModuleScripts($scripts, $file);
                } else {
                    echo $this->_getCoreModuleScripts($scripts);
                }
            }
        }

        echo 'dojo.provide("phpr.Main");';

        // Lang Files
        echo 'dojo.requireLocalization("phpr.Default.nls", "default");';
        foreach ($this->_modules as $module) {
        	echo 'dojo.requireLocalization("phpr.'.$module.'.nls", "default");';
        }
        
        echo '
        dojo.declare("phpr.Main", null, {
            constructor: function(/*String*/webpath, /*String*/currentModule, /*Int*/rootProjectId) {
                phpr.module           = currentModule;
                phpr.webpath          = webpath;
                phpr.rootProjectId    = rootProjectId;
                phpr.currentProjectId = rootProjectId ;
                phpr.nls              = dojo.i18n.getLocalization("phpr.Default.nls", "default");
        ';
        foreach ($this->_modules as $module) {
            echo '
                this.'.$module.' = new phpr.'.$module.'.Main();
                phpr.'.$module.'.nls = dojo.i18n.getLocalization("phpr.'.$module.'.nls", "default");
            ';
        }

        // The load method of the currentModule is called
        echo '
                dojo.publish(phpr.module + ".load");
            }
        });
        ';
    }

    /**
     * Get all the Default scripts
     *
     * @return string
     */
    private function _getDefaultScripts()
    {
        $output = '';
        $scripts = scandir(PHPR_CORE_PATH.'/Default/Views/dojo/scripts');
        foreach ($scripts as $script) {
            if (substr($script, -3) == '.js') {
                $output .= file_get_contents(PHPR_CORE_PATH.'/Default/Views/dojo/scripts/'.$script);
            }
        }
        return $output;
    }

    /**
     * Get all the Core scripts
     *
     * @return string
     */
    private function _getCoreScripts()
    {
        $output = '';
        $scripts = scandir(PHPR_CORE_PATH.'/Core/Views/dojo/scripts');
        foreach ($scripts as $script) {
            if (substr($script, -3) == '.js') {
                $output .= file_get_contents(PHPR_CORE_PATH.'/Core/Views/dojo/scripts/'.$script);
            }
        }
        return $output;
    }

    /**
     * Get all the Modules scripts
     *
     * @return string
     */
    private function _getModuleScripts($scripts, $module)
    {
        $output = '';
        foreach ($scripts as $script) {
            if (substr($script, -3) == '.js') {
                $output .= file_get_contents(PHPR_CORE_PATH.'/'.$module.'/Views/dojo/scripts/'.$script);
            }
        }
        return $output;
    }

    /**
     * Get the Core module scripts
     *
     * @param array $scripts All the modules into the Core folder
     *
     * @return string
     */
    private function _getCoreModuleScripts($scripts)
    {
        foreach ($scripts as $script) {
            if (substr($script, -3) != '.js' && substr($script, 0, 1) != '.' && ($script != 'nls')) {
                $coreScripts = scandir(PHPR_CORE_PATH.'/Core/Views/dojo/scripts/'.$script);
                if (in_array('Main.js', $coreScripts)) {
                    echo 'dojo.registerModulePath("phpr.'.$script.'", "../../../application/Core/Views/dojo/scripts/'.$script.'");';
                    echo 'dojo.registerModulePath("phpr.'.$script.'.nls", "../../../application/Core/Views/dojo/scripts/'.$script.'");';
                    $this->_modules[] = $script;
                }
                foreach ($coreScripts as $coreScript) {
                    if (substr($coreScript, -3) == '.js') {
                        echo file_get_contents(PHPR_CORE_PATH.'/Core/Views/dojo/scripts/'.$script.'/'.$coreScript);
                    }
                }
            }
        }
    }
}