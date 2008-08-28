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
        echo 'dojo.registerModulePath("phpr.Core", "../../../application/Core/Views/dojo/scripts");';

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
        $modules = array();
        $files   = scandir(PHPR_CORE_PATH);
        foreach ($files as $file) {
            if ($file != '.'  &&
                $file != '..' &&
                $file != '.svn' &&
                $file != 'Default' &&
                $file != 'Phprojekt') {
                echo 'dojo.registerModulePath("phpr.'.$file.'", "../../../application/'.$file.'/Views/dojo/scripts");';
                if (is_dir(PHPR_CORE_PATH.'/'.$file.'/Views/dojo/scripts/')) {
                    $scripts = scandir(PHPR_CORE_PATH.'/'.$file.'/Views/dojo/scripts/');
                } else {
                    $scripts = array();
                }
                $this->_modules[] = $file;
                if ($file != 'Core') {
                    echo $this->_getModuleScripts($scripts, $file);
                } else {
                    echo $this->_getCoreModuleScripts($scripts);
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
        foreach ($this->_modules as $module) {
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
     * If some file don´t exist, the Default file is called for it
     *
     * @return string
     */
    private function _getModuleScripts($scripts, $module)
    {
        $useDefault         = array();
        $useDefault['Form'] = true;
        $useDefault['Grid'] = true;
        $useDefault['Tree'] = true;
        $useDefault['Main'] = true;
        $output             = '';

        foreach ($scripts as $script) {
            if (substr($script, -3) == '.js') {
                if ($script == 'Form.js') {
                    $useDefault['Form'] = false;
                }
                if ($script == 'Grid.js') {
                    $useDefault['Grid'] = false;
                }
                if ($script == 'Tree.js') {
                    $useDefault['Tree'] = false;
                }
                if ($script == 'Main.js') {
                    $useDefault['Main'] = false;
                }
                $output .= file_get_contents(PHPR_CORE_PATH.'/'.$module.'/Views/dojo/scripts/'.$script);
            }
        }
        if ($useDefault['Main']) {
            $output .= $this->_getDefaultScript($module, $useDefault);
        }
        return $output;
    }

    /**
     * Provide a Default class for the modules
     *
     * @param string $module     The name of the module
     * @param array  $useDefault An array contain if exist or not the module file
     *
     * @return string
     */
    private function _getDefaultScript($module, $useDefault)
    {
        $_grid = ($useDefault['Grid']) ? 'Default' : $module;
        $_form = ($useDefault['Form']) ? 'Default' : $module;
        $_tree = ($useDefault['Tree']) ? 'Default' : $module;

        return '
        dojo.provide("phpr.'.$module.'.Main");

        dojo.declare("phpr.'.$module.'.Main", phpr.Default.Main, {
            constructor:function() {
                this.module = "'.$module.'";
                this.loadFunctions(this.module);

                this.gridWidget = phpr.'.$_grid.'.Grid;
                this.formWidget = phpr.'.$_form.'.Form;
                this.treeWidget = phpr.'.$_tree.'.Tree;
            }
        });
        ';
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
            if (substr($script, -3) != '.js' && substr($script, 0, 1) != '.') {
                $coreScripts = scandir(PHPR_CORE_PATH.'/Core/Views/dojo/scripts/'.$script);
                if (in_array('Main.js', $coreScripts)) {
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