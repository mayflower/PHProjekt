<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * JavaScript Controller.
 * The controller will return all the js files for the modules.
 */
class JsController extends IndexController
{
    /**
     * Array with all the modules found.
     *
     * @var array
     */
    private $_modules = array();

    /**
     * Array with all the templates by module.
     *
     * @var array
     */
    private $_templates = array();

    /**
     * Collect all the js files and return it as one.
     *
     * @return void
     */
    public function indexAction()
    {

        $scripttext = "";
        // System files, must be parsed in this order
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/phpr.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/MetadataStore.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/GarbageCollector.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Component.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Form.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Grid.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Store.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Date.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/Tree.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/FrontendMessage.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/PageManager.js');
        $scripttext .= file_get_contents(PHPR_CORE_PATH . '/Default/Views/dojo/scripts/system/ViewManager.js');

        // Default Folder
        $scripts = scandir(PHPR_CORE_PATH . '/Default/Views/dojo/scripts');
        $scripttext .= $this->_getModuleScripts(PHPR_CORE_PATH . DIRECTORY_SEPARATOR, $scripts, 'Default');

        // Core Folder
        $scripts = scandir(PHPR_CORE_PATH . '/Core/Views/dojo/scripts');
        $scripttext .= $this->_getModuleScripts(PHPR_CORE_PATH . DIRECTORY_SEPARATOR, $scripts, 'Core');

        // Load all the system modules and make and array of it
        $scripttext .= $this->_processModuleDirectory(PHPR_CORE_PATH . DIRECTORY_SEPARATOR);

        // Load all the user modules and make and array of it
        $scripttext .= $this->_processModuleDirectory(PHPR_USER_CORE_PATH);

        $scripttext .= 'dojo.provide("phpr.Main");';

        $scripttext .= '
            dojo.declare("phpr.Main", null, {
                constructor:function(/*Int*/rootProjectId, /*String*/language) {
                    phpr.DefaultModule    = "Project";
                    phpr.viewManager      = new phpr.Default.System.ViewManager();
                    phpr.pageManager      = new phpr.Default.System.PageManager();
                    phpr.module           = phpr.pageManager.getStateFromWindow().moduleName;
                    phpr.submodule        = null;
                    phpr.rootProjectId    = rootProjectId;
                    phpr.currentProjectId = rootProjectId ;
                    phpr.currentUserId    = 0;
                    phpr.language         = language;
                    phpr.config           = new Array();
                    phpr.serverFeedback   = new phpr.ServerFeedback();
                    phpr.date             = new phpr.Default.System.Date();
                    phpr.loading          = new phpr.loading();
                    phpr.DataStore        = new phpr.DataStore();
                    phpr.InitialScreen    = new phpr.InitialScreen();
                    phpr.BreadCrumb       = new phpr.BreadCrumb();
                    phpr.frontendMessage  = new phpr.Default.System.FrontendMessage();
                    phpr.tree             = new phpr.Default.System.Tree();
                    phpr.regExpForFilter  = new phpr.regExpForFilter();
                    phpr.garbageCollector = new phpr.Default.System.GarbageCollector();
                    phpr.globalModuleUrl  = "index.php/Core/module/jsonGetGlobalModules";
                    phpr.tutorialAnchors = {};
        ';

        foreach ($this->_modules as $module) {
            if (isset($this->_subModules[$module]) && !empty($this->_subModules[$module])) {
                $subModules = join(",", $this->_subModules[$module]);
            } else {
                $subModules = '';
            }
            $scripttext .= '
                phpr.pageManager.register(
                    new phpr.' . $module . '.Main([' . $subModules . ']));
            ';
        }

        // The load method of the currentModule is called
        $scripttext .= '
                    phpr.pageManager.initialPageLoad();
                }
            });
        ';

        $templatetext = 'var __phpr_templateCache = {};';
        $templatetext .= $this->_collectTemplates();

        $this->_send($templatetext . $scripttext);
    }

    /**
     * Collect all the js files in the module folder, and return it as one.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>name</b> Name of the module to consult.
     * </pre>
     *
     * @return void
     */
    public function moduleAction()
    {
        $scripttext = '';

        $module = Cleaner::sanitize('alnum', $this->getRequest()->getParam('name', null));
        $module = ucfirst(str_replace(" ", "", $module));

        // Load the module
        if (is_dir(PHPR_USER_CORE_PATH . $module . '/Views/dojo/scripts/')) {
            $scripts = scandir(PHPR_USER_CORE_PATH . $module . '/Views/dojo/scripts/');
        } else {
            $scripts = array();
        }

        $scripttext .= $this->_getModuleScripts(PHPR_USER_CORE_PATH, $scripts, $module);

        $scripttext .= '
            phpr.pageManager.deregister(\'' . $module . '\');
            phpr.pageManager.register(
                new phpr.' . $module . '.Main()
            );
        ';

        $this->_send($this->_collectTemplates() . $scripttext);
    }

    /**
     * Collect all the template files that has been found so far and returns the 
     * coresponding javascript string.
     *
     * @return string
     */
    private function _collectTemplates()
    {
        $templatetext = '';
        // Preload the templates and save them into __phpr_templateCache
        foreach ($this->_templates as $templateData) {
            $content = json_encode($templateData['contents']);
            $templatetext .= '
                __phpr_templateCache["phpr.' . $templateData['module'] . '.template.' . $templateData['name']
                . '"] = ' . $content . ';';
        }

        return $templatetext;
    }

    /**
     * Get all the Modules scripts.
     * In the process also collect the templates.
     *
     * @param string $path    Path to the module directory.
     * @param array  $scripts All the modules into the Module folder.
     * @param string $module  The module name.
     *
     * @return string Content of the files.
     */
    private function _getModuleScripts($path, $scripts, $module)
    {
        $output = '';
        foreach ($scripts as $script) {
            if (substr($script, -3) == '.js') {
                $output .= file_get_contents($path . $module . '/Views/dojo/scripts/' . $script);
            } else if ('template' == $script) {
                if (strstr($module, '/')) {
                    $templateModule = substr(strrchr($module, '/'), 1);
                } else {
                    $templateModule = $module;
                }
                $this->_getTemplates($path . $module . '/Views/dojo/scripts/template/', $templateModule);
            }
        }

        return $output;
    }

    /**
     * Get the Core module scripts.
     * In the process also collect the templates.
     *
     * @param array $scripts All the modules into the Core folder.
     *
     * @return string Content of the files.
     */
    private function _getCoreModuleScripts($scripts)
    {
        $output = '';
        foreach ($scripts as $script) {
            if (substr($script, -3) != '.js' && substr($script, 0, 1) != '.' && 'template' != $script) {
                // Core Modules
                $coreScripts = scandir(PHPR_CORE_PATH . '/Core/Views/dojo/scripts/' . $script);
                if (in_array('Main.js', $coreScripts)) {
                    $this->_modules[] = $script;
                }
                foreach ($coreScripts as $coreScript) {
                    if (substr($coreScript, -3) == '.js') {
                        // Core Module files
                        $output .= file_get_contents(PHPR_CORE_PATH . '/Core/Views/dojo/scripts/'
                            . $script . '/' . $coreScript);
                    } else if ('template' == $coreScript) {
                        // Core Module templates
                        $path = PHPR_CORE_PATH . '/Core/Views/dojo/scripts/' . $script . '/' . $coreScript . '/';
                        $this->_getTemplates($path, 'Core.' . $script);
                    } else if (substr($coreScript, 0, 1) != '.') {
                        // Core Sub Modules
                        $subCoreScripts = scandir(PHPR_CORE_PATH . '/Core/Views/dojo/scripts/'
                            . $script . '/' . $coreScript);
                        if (in_array('Main.js', $subCoreScripts)) {
                            $this->_modules[] = $coreScript;
                        }

                        foreach ($subCoreScripts as $subCoreScript) {
                            if (substr($subCoreScript, -3) == '.js') {
                                // Core Sub Modules files
                                $output .= file_get_contents(PHPR_CORE_PATH . '/Core/Views/dojo/scripts/'
                                    . $script . '/' . $coreScript . '/' . $subCoreScript);
                            } else if ('template' == $subCoreScript) {
                                // Core Sub Modules templates
                                $path = PHPR_CORE_PATH . '/Core/Views/dojo/scripts/'
                                    . $script . '/' . $coreScript . '/' . $subCoreScript . '/';
                                $this->_getTemplates($path, 'Core.' . $coreScript);
                            }
                        }
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Collect all the templates found in the $path directory.
     * Also scan the sub directories.
     *
     * @param string $path   Path for scan.
     * @param string $module Module Name.
     *
     * @return void
     */
    private function _getTemplates($path, $module)
    {
        $templates = scandir($path);
        foreach ($templates as $item) {
            if (!is_dir($path . $item)) {
                if (substr($item, -5) == '.html') {
                    // The item is a valid file
                    $fileContents = file_get_contents($path . $item);
                    $fileContents = str_replace("\n", "", $fileContents);
                    $fileContents = str_replace("\r", "", $fileContents);

                    $this->_templates[] = array('module'   => $module,
                                                'name'     => $item,
                                                'contents' => $fileContents);
                }
            } else {
                // The item is a subdirectory
                if ($item != '.' && $item != '..') {
                    $subItemPath = $path . $item . DIRECTORY_SEPARATOR;
                    foreach (scandir($subItemPath) as $subItem) {
                        if (!is_dir($subItemPath . $subItem) && substr($subItem, -5) == '.html') {
                            // The subitem is a valid file
                            $fileContents = file_get_contents($subItemPath . $subItem);
                            $fileContents = str_replace("\n", "", $fileContents);
                            $fileContents = str_replace("\r", "", $fileContents);

                            $this->_templates[] = array('module'   => $module,
                                                        'name'     => $item . "." . $subItem,
                                                        'contents' => $fileContents);
                        }
                    }
                }
            }
        }
    }

    /**
     * Load all the scritps from a module directory.
     *
     * @param string $path Path to the module directory.
     *
     * @return void
     */
    private function _processModuleDirectory($path)
    {
        $output = "";
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.'  && $file != '..' && $file != 'Default' &&
                    !Phprojekt::getInstance()->isBlockedModule($file) && is_dir($path . '/' . $file . '/Views')) {
                if (is_dir($path . $file . '/Views/dojo/scripts/')) {
                    $scripts = scandir($path . $file . '/Views/dojo/scripts/');
                } else {
                    $scripts = array();
                }
                $this->_modules[]         = $file;
                $this->_subModules[$file] = array();
                if ($file != 'Core') {
                    $output .= $this->_getModuleScripts($path, $scripts, $file);
                    if (is_dir($path . $file . '/SubModules/')) {
                        $subFiles = scandir($path . $file . '/SubModules/');
                        foreach ($subFiles as $subFile) {
                            if ($subFile != '.'  && $subFile != '..') {
                                if (is_dir($path . $file . '/SubModules/' . $subFile . '/Views/dojo/scripts/')) {
                                    $subScripts = scandir($path . $file . '/SubModules/' . $subFile
                                        . '/Views/dojo/scripts/');
                                } else {
                                    $subScripts = array();
                                }
                                $this->_subModules[$file][] = "'" . $subFile . "'";
                                $output .= $this->_getModuleScripts($path, $subScripts, $file . '/SubModules/' . $subFile);
                            }
                        }
                    }
                } else {
                    $output .= $this->_getCoreModuleScripts($scripts);
                    if (is_dir(PHPR_CORE_PATH . '/' . $file . '/SubModules/')) {
                        $subModulesFiles = scandir(PHPR_CORE_PATH . '/' . $file . '/SubModules/');
                        foreach ($subModulesFiles as $subModule) {
                            if ($subModule != '.' && $subModule != '..') {
                                $subFiles = scandir(PHPR_CORE_PATH . '/' . $file . '/SubModules/' . $subModule);
                                foreach ($subFiles as $subFile) {
                                    if ($subFile != '.'  && $subFile != '..') {
                                        if (is_dir(PHPR_CORE_PATH . '/' . $file . '/SubModules/' . $subModule . '/'
                                            . $subFile . '/Views/dojo/scripts/')) {
                                            $subScripts = scandir(PHPR_CORE_PATH . '/' . $file . '/SubModules/'
                                                . $subModule . '/' . $subFile . '/Views/dojo/scripts/');
                                        } else {
                                            $subScripts = array();
                                        }
                                        $this->_subModules[$subModule][] = "'" . $subFile . "'";
                                        $output .= $this->_getModuleScripts($path, $subScripts, $file . '/SubModules/'
                                            . $subModule . '/' . $subFile);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $output;
    }

    private function _send($data) {
        header('Content-Type: application/javascript');
        Phprojekt_CompressedSender::send($data);
    }
}
