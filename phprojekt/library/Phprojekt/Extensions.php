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
 * Extensions API
 */
class PHProjekt_Extensions {
    /**
     * Postfix for every extension class. We search for the file EXT_NAME.php.
     */
    const EXT_NAME = 'Extension';

    /**
     * Name of the cache namespace used to cache the extension tree
     */
    const CACHE_NS = 'Phprojekt_Extensions';

    private $_extensions = null;
    private $_log        = null;
    private $_config     = null;
    private $_cache      = null;
    private $_path       = null;

    /**
     * Construct a new instance with the given path.
     *
     * The path defines where to search for extensions.
     *
     * @param string $path The directory to search for extensions.
     */
    public function __construct($path) {
        $this->_log     = Phprojekt::getInstance()->getLog();
        $this->_config  = Phprojekt::getInstance()->getConfig();
        $this->_cache   = Phprojekt::getInstance()->getCache();
        $this->_path    = $path;
        $this->_cacheNs = self::CACHE_NS . md5($this->_path);
    }

    /**
     * Intialize the extensions.
     *
     * We use a separate init method that is defined in 
     * Phprojekt_Extension_Abstract. This ensures that
     * we properly loaded and verified the extensions
     * before the extension starts to check for depending
     * extensions.
     */
    public function init() {
        foreach ($this->getExtensions() as $extensionObj) {
            $extensionObj->init();
        }
    }

    /**
     * Return a list of loaded extensions.
     *
     * The extensions are loaded once from either the cache if
     * useCacheForExtensions is set to true in the configuration
     * or read from the path.
     *
     * @see updateExtensionCache
     *
     * @return array
     */
    public function getExtensions() {
        if (is_array($this->_extensions)) {
            /* already read during this request */
            return $this->_extensions;
        }

        if (isset($this->_config->useCacheForExtensions)
            && true == $this->_config->useCacheForExtensions) {
            /* we want to have cached extensions */
            $data = $this->_cache->load($this->_cacheNs);
            if (false === $data) { /* cached */
                $data = $this->updateExtensionsCache($this->_path);
            }
            $this->_extensions = $data;
        } else {
            $this->_extensions = $this->readExtensions($this->_path);
        }

        return $this->_extensions;
    }

    /**
     * Update the extension cache.
     *
     * Updates the extension cache and returns the new list.
     *
     * @return array
     */
    public function updateExtensionsCache() {
        $this->_log->debug('update extension cache');
        $extensions = $this->readExtensions($this->_path);
        if (false === $this->_cache->save($extensions, $this->_cacheNs)) {
            return false;
        }

        $this->_extensions = $extensions;
        return $extensions;
    }

    /**
     * Read the extension tree.
     *
     * This method is used to reread the extension tree. It doesn't cache
     * or do anything else. It just returns the list of extensions.
     * It will try to load the file <directory>/EXT_NAME.php and look
     * for a class named <Directory>_EXT_NAME.
     * E.g.: $extensionPath = application/
     *   application/Debug/Extension.php will be read and 
     *   and the class Debug_Extension will be initialized.
     *
     *
     * @return array
     */
    private function readExtensions($extensionsPath) {
        $extensions = array();
        foreach(scandir($extensionsPath) as $module) {
            if ('.' == $module || '..' == $module) {
                continue;
            }

            $filename = $extensionsPath . DIRECTORY_SEPARATOR
                . $module . DIRECTORY_SEPARATOR . self::EXT_NAME . '.php';

            if (file_exists($filename)) {
                $classname = sprintf("%s_%s", $module, self::EXT_NAME);
                if (class_exists($classname)) {
                    $obj = new $classname;
                    if ($obj instanceof PHProjekt_Extension_Abstract
                        && $this->verifyExtension($obj)) {
                        $extensions[strtolower($module)] = $obj;
                    } else {
                        $this->_log->warn("Class " . $classname
                            . " ignored. Not a PHProjekt extension.");
                    }
                } else {
                    $this->_log->warn("Class " . $classname
                        . " not found in " . $filename);
                }

            }
        }

        return $extensions;
    }

    /**
     * Verify an extension.
     *
     * @param PHProjekt_Extension_Abstract $extensionObject The extension
     * @return boolean
     */
    private function verifyExtension($extensionObject) {
        if (!preg_match('/^[0-9]\.[0-9]{1,2}\.[0-9]{1,2}$/', $extensionObject->getVersion())) {
            $this->_log->warn("Extension " . get_class($extensionObject) . " not verified");
            return false;
        }

        return true;
    }
}
