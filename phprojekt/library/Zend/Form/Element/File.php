<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Form
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Element_Xhtml */
require_once 'Zend/Form/Element/Xhtml.php';

/**
 * Zend_Form_Element 
 * 
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: File.php 11296 2008-09-08 19:46:57Z thomas $
 */
class Zend_Form_Element_File extends Zend_Form_Element_Xhtml
{
    /**
     * @const string Plugin loader type
     */
    const TRANSFER_ADAPTER = 'TRANSFER_ADAPTER';

    /**
     * @var string Default view helper
     */
    public $helper = 'formFile';

    /**
     * @var Zend_File_Transfer_Adapter_Abstract
     */
    protected $_adapter;

    /**
     * Set plugin loader
     * 
     * @param  Zend_Loader_PluginLoader_Interface $loader 
     * @param  string $type 
     * @return Zend_Form_Element_File
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader, $type)
    {
        $type = strtoupper($type);

        if ($type != self::TRANSFER_ADAPTER) {
            return parent::setPluginLoader($loader, $type);
        }

        $this->_loaders[$type] = $loader;
        return $this;
    }

    /**
     * Get Plugin Loader
     * 
     * @param  string $type 
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader($type)
    {
        $type = strtoupper($type);

        if ($type != self::TRANSFER_ADAPTER) {
            return parent::getPluginLoader($type);
        }

        if (!array_key_exists($type, $this->_loaders)) {
            require_once 'Zend/Loader/PluginLoader.php';
            $loader = new Zend_Loader_PluginLoader(array(
                'Zend_File_Transfer_Adapter' => 'Zend/File/Transfer/Adapter/',
            ));
            $this->setPluginLoader($loader, self::TRANSFER_ADAPTER);
        }

        return $this->_loaders[$type];
    }

    /**
     * Add prefix path for plugin loader
     * 
     * @param  string $prefix 
     * @param  string $path 
     * @param  string $type 
     * @return Zend_Form_Element_File
     */
    public function addPrefixPath($prefix, $path, $type = null)
    {
        $type = strtoupper($type);
        if (!empty($type) && ($type != self::TRANSFER_ADAPTER)) {
            return parent::addPrefixPath($prefix, $path, $type);
        }

        if (empty($type)) {
            $pluginPrefix = rtrim($prefix, '_') . '_Transfer_Adapter';
            $pluginPath   = rtrim($path, DIRECTORY_SEPARATOR) . '/Transfer/Adapter/';
            $loader    = $this->getPluginLoader(self::TRANSFER_ADAPTER);
            $loader->addPrefixPath($pluginPrefix, $pluginPath);
            return parent::addPrefixPath($prefix, $path, null);
        }

        $loader = $this->getPluginLoader($type);
        $loader->addPrefixPath($prefix, $path);
        return $this;
    }

    /**
     * Set transfer adapter
     * 
     * @param  string|Zend_File_Transfer_Adapter_Abstract $adapter 
     * @return Zend_Form_Element_File
     */
    public function setTransferAdapter($adapter)
    {
        if ($adapter instanceof Zend_File_Transfer_Adapter_Abstract) {
            $this->_adapter = $adapter;
        } elseif (is_string($adapter)) {
            $loader = $this->getPluginLoader(self::TRANSFER_ADAPTER);
            $class  = $loader->load($adapter);
            $this->_adapter = new $class;
        } else {
            require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception('Invalid adapter specified');
        }

        return $this;
    }

    /**
     * Get transfer adapter
     *
     * Lazy loads HTTP transfer adapter when no adapter registered.
     * 
     * @return Zend_File_Transfer_Adapter_Abstract
     */
    public function getTransferAdapter()
    {
        if (null === $this->_adapter) {
            $this->setTransferAdapter('Http');
        }
        return $this->_adapter;
    }

    /**
     * Add Validator; proxy to adapter
     * 
     * @param  string|Zend_Validate_Interface $validator 
     * @param  bool $breakChainOnFailure 
     * @param  mixed $options 
     * @return Zend_Form_Element_File
     */
    public function addValidator($validator, $breakChainOnFailure = false, $options = array())
    {
        $adapter = $this->getTransferAdapter();
        $adapter->addValidator($validator, $options);
        return $this;
    }

    /**
     * Add multiple validators at once; proxy to adapter
     * 
     * @param  array $validators 
     * @return Zend_Form_Element_File
     */
    public function addValidators(array $validators)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->addValidators($validators);
        return $this;
    }

    /**
     * Add multiple validators at once, overwriting; proxy to adapter
     * 
     * @param  array $validators 
     * @return Zend_Form_Element_File
     */
    public function setValidators(array $validators)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->setValidators($validators);
        return $this;
    }

    /**
     * Retrieve validator by name; proxy to adapter
     * 
     * @param  string $name 
     * @return Zend_Validate_Interface|null
     */
    public function getValidator($name)
    {
        $adapter = $this->getTransferAdapter();
        return $adapter->getValidator($name);
    }

    /**
     * Retrieve all validators; proxy to adapter
     * 
     * @return array
     */
    public function getValidators()
    {
        $adapter = $this->getTransferAdapter();
        return $adapter->getValidators();
    }

    /**
     * Remove validator by name; proxy to adapter
     * 
     * @param  string $name 
     * @return Zend_Form_Element_File
     */
    public function removeValidator($name)
    {
        $adapter = $this->getTransferAdapter();
        $adapter->removeValidator($name);
        return $this;
    }

    /**
     * Remove all validators; proxy to adapter
     * 
     * @return Zend_Form_Element_File
     */
    public function clearValidators()
    {
        $adapter = $this->getTransferAdapter();
        $adapter->clearValidators();
        return $this;
    }

    /**
     * Validate upload
     * 
     * @param  string $value 
     * @param  mixed $context 
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $adapter = $this->getTransferAdapter();
        $this->setValue($adapter->getFileName($this->getName()));

        if (!$this->isRequired()) {
            $adapter->setOptions(array('ignoreNoFile' => true));
        } else {
            $adapter->setOptions(array('ignoreNoFile' => false));
            if ($this->autoInsertNotEmptyValidator() and
                   !$this->getValidator('NotEmpty'))
            {
                $validators = $this->getValidators();
                $notEmpty   = array('validator' => 'NotEmpty', 'breakChainOnFailure' => true);
                array_unshift($validators, $notEmpty);
                $this->setValidators($validators);
            }
        }

        if($adapter->receive($value)) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve error codes; proxy to transfer adapter
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->getTransferAdapter()->getErrors();
    }

    /**
     * Retrieve error messages; proxy to transfer adapter
     * 
     * @return array
     */
    public function getMessages()
    {
        return $this->getTransferAdapter()->getMessages();
    }

    /**
     * Set the upload destination
     * 
     * @param  string $path 
     * @return Zend_Form_Element_File
     */
    public function setDestination($path)
    {
        $this->getTransferAdapter()->setDestination($path, $this->getName());
        return $this;
    }

    /**
     * Get the upload destination
     * 
     * @return string
     */
    public function getDestination()
    {
        return $this->getTransferAdapter()->getDestination($this->getName());
    }
}
