<?php
/**
 * JavaScript Controller for PHProjekt 6
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * JavaScript Controller for PHProjekt 6
 *
 * The controller will return all the js files for the modules
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
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
        $files   = scandir(PHPR_CORE_PATH);
        foreach ($files as $file) {
            if ($file != '.'  &&
                $file != '..' &&
                $file != '.svn' &&
                $file != 'Default') {
                if (is_dir(PHPR_CORE_PATH.'/'.$file.'/Views/dojo/scripts/')) {
                    $scripts = scandir(PHPR_CORE_PATH.'/'.$file.'/Views/dojo/scripts/');
                } else {
                    $scripts = array();
                }
                $this->_modules[] = $file;
                if ($file != 'Core') {
                    echo 'dojo.registerModulePath'
                    . '("phpr.'.$file.'", "../../../application/'.$file.'/Views/dojo/scripts");';
                    echo $this->_getModuleScripts($scripts, $file);
                } else {
                    echo $this->_getCoreModuleScripts($scripts);
                }
            }
        }

        echo 'dojo.provide("phpr.Main");';

        echo '
        dojo.declare("phpr.Main", null, {
            constructor: function(/*String*/webpath, /*String*/currentModule, /*Int*/rootProjectId,/*String*/language) {
                phpr.module           = currentModule;
                phpr.submodule        = null;
                phpr.webpath          = webpath;
                phpr.rootProjectId    = rootProjectId;
                phpr.currentProjectId = rootProjectId ;
                phpr.language         = language;
                phpr.serverFeedback   = new phpr.ServerFeedback();
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

    public function jsonGetTemplateAction()
    {
        $path = (string) $this->getRequest()->getParam('path', null);
        $name = (string) $this->getRequest()->getParam('name', null);

        $path = split("\.", $path);
        $module = $path[1];

        $extendPath = '';
        $count      = 0;
        foreach ($path as $folder) {
            if ($count > 1) {
                $extendPath .= $folder . '/';
            }
            $count++;
        }
        $extendPath .= $name;

        $template = file_get_contents(PHPR_CORE_PATH . '/' . $module . '/Views/dojo/scripts/' . $extendPath);

        $template = str_replace("\n", "", $template);
        $template = str_replace("\r", "", $template);
        $template = addslashes($template);
        echo '"' . $template . '"';
    }

    /**
     * Gets dynamically all the templates and echoes them in Json format
     *
     * @return void
     */
    public function jsonGetAllTemplatesAction()
    {
        $output  = array();
        $modules = array();

        // Create an array with all the modules
        foreach (scandir(PHPR_CORE_PATH) as $item) {
            $itemPath = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $item;
            if (!is_dir($itemPath)) {
                continue;
            }

            if ($item != '.svn' && $item != '.' && $item != '..') {
                $dir = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $item;
                if (is_dir($dir . DIRECTORY_SEPARATOR . 'Controllers')) {
                    $modules[] = array('name' => $item,
                                       'path' => $dir);
                }
            }
        }

        // Read the templates of every module
        foreach ($modules as $module) {
            if ($module['name'] == 'Core') {
                $templatesPaths = array();
                $folders        = scandir($module['path'] . '/Views/dojo/scripts');
                foreach ($folders as $folder) {
                    if (is_dir($module['path'] . '/Views/dojo/scripts/' . $folder) && $folder != '.svn') {
                        if (is_dir($module['path'] . '/Views/dojo/scripts/' . $folder . '/template/')) {
                            $templatesPaths[] = $module['path'] . '/Views/dojo/scripts/' . $folder . '/template/';
                        }
                    }
                }
            } else {
                $templatesPaths   = array();
                $templatesPaths[] = $module['path'] . '/Views/dojo/scripts/template/';
            }

            foreach ($templatesPaths as $templatesPath) {
                if (is_dir($templatesPath)) {
                    foreach (scandir($templatesPath) as $item) {
                        if (!is_dir($templatesPath . $item) && (substr($item, -5) == '.html')) {
                            // The item is a valid file
                            $fileContents = file_get_contents($templatesPath . $item);
                            $fileContents = str_replace("\n", "", $fileContents);
                            $fileContents = str_replace("\r", "", $fileContents);

                            $output[] = array('module'   => $module['name'],
                                              'name'     => $item,
                                              'contents' => $fileContents);
                        } else {
                            // The item is a subdirectory
                            if ($item != '.svn' && $item != '.' && $item != '..') {
                                $subItemPath = $templatesPath . $item . DIRECTORY_SEPARATOR;
                                foreach (scandir($templatesPath . $item) as $subItem) {
                                    if (!is_dir($subItemPath . $subItem) && substr($subItem, -5) == '.html') {
                                        // The subitem is a valid file
                                        $fileContents = file_get_contents($subItemPath . $subItem);
                                        $fileContents = str_replace("\n", "", $fileContents);
                                        $fileContents = str_replace("\r", "", $fileContents);

                                        $output[] = array('module'   => $module['name'],
                                                          'name'     => $item . "." . $subItem,
                                                          'contents' => $fileContents);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $data          = array();
        $data['files'] = $output;
        echo Zend_Json::encode($data);
    }

    /**
     * Get all the Default scripts
     *
     * @return string
     */
    private function _getDefaultScripts()
    {
        $output  = '';
        $scripts = scandir(PHPR_CORE_PATH . '/Default/Views/dojo/scripts');
        foreach ($scripts as $script) {
            if (substr($script, -3) == '.js') {
                $output .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/' . $script);
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
        $output  = '';
        $scripts = scandir(PHPR_CORE_PATH . '/Core/Views/dojo/scripts');
        foreach ($scripts as $script) {
            if (substr($script, -3) == '.js') {
                $output .= file_get_contents(PHPR_CORE_PATH . '/Core/Views/dojo/scripts/' . $script);
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
                $output .= file_get_contents(PHPR_CORE_PATH . '/' . $module . '/Views/dojo/scripts/' . $script);
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
        $output = '';
        foreach ($scripts as $script) {
            if (substr($script, -3) != '.js' && substr($script, 0, 1) != '.') {
                $coreScripts = scandir(PHPR_CORE_PATH . '/Core/Views/dojo/scripts/' . $script);
                if (in_array('Main.js', $coreScripts)) {
                    $output .= 'dojo.registerModulePath'
                        . '("phpr.' . $script . '", "../../../application/Core/Views/dojo/scripts/' . $script.'");';
                    $this->_modules[] = $script;
                }
                foreach ($coreScripts as $coreScript) {
                    if (substr($coreScript, -3) == '.js') {
                        $output .=
                          file_get_contents(PHPR_CORE_PATH . '/Core/Views/dojo/scripts/' . $script . '/' . $coreScript);
                    }
                }
            }
        }
        return $output;
    }
}
