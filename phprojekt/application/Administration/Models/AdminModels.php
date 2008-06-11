<?php
/**
 * A model that receives information about administration controllers
 * of other modules
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
/**
 * A model that receives information about administration controllers
 * of other modules
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Administration_Models_AdminModels extends EmptyIterator implements Phprojekt_Model_Interface
{
    /**
     * The fancy and nice name of a module
     *
     * @var string
     */
    protected $_name;

    /**
     * The module Id
     *
     * @var integer
     */
    protected $_moduleId;

    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Configuration objects
     *
     * @var array
     */
    protected $_objects = array();

    /**
     * A list of directories that are not included in the search.
     * Usually Default and PHProjekt
     *
     * @var array
     */
    protected static $_excludePatterns = array('Default', 'Phprojekt', 'Administration');

     /**
     * The configuration array contains a list of
     * predefined renderable settings that are
     * stored in global table by the admin module.
     *
     * @todo change this to dojo interaction specifications
     *
     * @var array
     */
    protected static $_defaultConfiguration = array(array('type'  => 'selectbox',
                                                          'key'   => 'activated',
                                                          'range' => array(array('id'   => 1,
                                                                                 'name' => 'On'),
                                                                           array('id'   => 2,
                                                                                 'name' => 'Off')),
                                                          'label' => 'Module activated?'),
                                                    array('type'  => 'label',
                                                          'key'   => 'name',
                                                          'label' => ''),
                                                    array('type'  => 'label',
                                                          'key'   => 'module',
                                                          'label' => ''));

    /**
     * The actual configuration, merged from the default configuration and the module configuration
     *
     * @var array
     */
    protected $_configuration = array();

    /**
     * Name of the properties that are readonly (without the leading _)
     * All other protected properties are hidden
     *
     * @var array
     */
    private $_readOnlyProperties = array('name', 'module', 'configuration');

    /**
     * Add a module to the ignore list.
     * A ignored module is not received using
     * fetchAll() but it can be received using find().
     * Returns TRUE on success otherwise FALSE
     *
     * @param string $name Directory name of the module that should be ignored
     *
     * @return boolean
     */
    public function ignoreModule($name)
    {
        if (!in_array($name, self::$_excludePattern)) {
            self::$_excludePattern[] = $name;
            return true;
        }
        return false;
    }

    /**
     * Remove a module from the ignore list.
     * This method is case sensitive. It returns TRUE
     * on success otherwise FALSE
     *
     * @param string $name Directory name of the module that should be included again
     *
     * @return boolean
     */
    public function unignoreModule($name)
    {
        if (($key = array_search($name, self::$_defaultConfiguration)) !== false) {
            unset(self::$_excludePattern[$key]);
            return true;
        }
        return false;
    }

    /**
     * Set the administration configuration and merge it with
     * the default configuration.
     *
     * @param array $configuration The configuration to use
     *
     * @return void
     */
    public function setConfiguration(array $configuration)
    {
        $configuration = array_merge(self::$_defaultConfiguration, $configuration);

        foreach ($configuration as $config) {
            /* we need to create an assoc array as this is easier to
               find our configs again */
            $this->_configuration[$config['key']] = $config;
        }

        $this->_informationManager = new Phprojekt_ModelInformation_Default($this->_configuration);
    }

    /**
     * Implementation of fetchAll for AdminModules.
     * Returns a set of modules available and have administration sections
     *
     * @see Phprojekt_Model_Interface::fetchAll()
     *
     * @return array
     */
    public function fetchAll ()
    {
        $results = array();
        foreach (scandir(PHPR_CORE_PATH) as $dir) {
            $path = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $dir;
            if ($dir == '.' || $dir == '..' || in_array($dir, self::$_excludePatterns)) {
                continue;
            }
            if (is_dir($path)) {
                /* @todo Optimize */
                $instance = clone $this;
                $instance->setConfiguration($instance->configuration);
                $instance->_loadFromDatabase();
                if ($instance->find($dir) !== false) {
                    $results[] = $instance;
                } else {
                    unset($instance);
                }
            }
        }
        return $results;
    }

    /**
     * We only implement read only properties
     *
     * @param string $key Name of the property
     *
     * @return mixed
     */
    public function __get ($key)
    {
        if (in_array($key, $this->_readOnlyProperties)) {
            $key = '_' . $key;
            return $this->$key;
        }

        if (in_array($key, $this->_objects)) {
            return $this->_objects[$key]->value;
        }

        return null;
    }

    /**
     * Overwrite the set so we set directly on the configuration
     * objects
     *
     * @param string $key   Property name
     * @param mixed  $value Property value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->_objects)) {
            $this->_objects[$key]->value = $value;
        } else if (array_key_exists($key, $this->_configuration)) {
            $object         = Phprojekt_Loader::getModel('Default', 'Configuration');
            $object->module = $this->_moduleId;
            $object->key    = $key;
            $object->value  = $value;

            $this->_objects[$key] = $object;
        }
    }

    /**
     * Find a specific admin module based on the name and
     * tries to load the containing class
     *
     * @see Phprojekt_Model_Interface::find()
     *
     * @return Phprojekt_Model_Interface
     */
    public function find()
    {
        $module = ucfirst(func_get_arg(0));
        $moduleClass = sprintf('%s_AdminController', $module);
        try {
            Phprojekt_Loader::loadClass($moduleClass);

            /* workaround as php 5.2 doesnot support late static bindings */
            $vars            = get_class_vars($moduleClass);
            $this->_name     = (empty($vars['name'])) ? $module : $vars['name'];
            $this->_moduleId = Phprojekt_Module::getId($module, 1);
            $this->setConfiguration($vars['configuration']);
            $this->_loadFromDatabase();
            return $this;
        } catch (Zend_Exception $ze) {
            // .. file not found
            return false;
        }
    }

    /**
     * Load configuration from database
     *
     * @return void
     */
    protected function _loadFromDatabase()
    {
        $model   = Phprojekt_Loader::getModel('Default', 'Configuration');
        $quoted  = $model->getAdapter()->quoteInto('moduleId = ?', $this->_moduleId);
        $fetched = $model->fetchAll($quoted);

        if (is_array($fetched)) {
            foreach ($fetched as $object) {
                $this->_objects[$object->key] = $object;
            }
        }
    }

    /**
     * Get the information manager
     *
     * @see Phprojekt_Model_Interface::getInformation()
     *
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation ()
    {
        return $this->_informationManager;
    }

    /**
     * Save is not supported.
     * You cannot save a administration module you
     * just can write their values to the backend but
     * thats done by the Default)Models_Configuration class
     *
     * @see Phprojekt_Model_Interface::save()
     *
     * @return void
     */
    public function save ()
    {
        $result = true;

        /* @todo: optimize */
        foreach ($this->_objects as $object) {
            $result = $result && $object->save();
        }

        return $result;
    }

    /**
     * Delete an entry
     *
     * @return int
     */
    public function delete()
    {
        $db    = Zend_Registry::get('db');
        $model = Phprojekt_Loader::getModel('Default', 'Configuration');

        $result = 0;
        if ($this->_moduleId) {
            $result += $db->delete($model->getTableName(), $db->quoteInto('moduleId = ?', $this->_moduleId));
        }

        return $result;
    }

    /**
     * Get the rigths
     *
     * @return string
     */
    public function getRights ($userId)
    {
        $permission = 'none';
        
        if (!empty($userId)) {
            $permission = 'write';
        }
        return $permission;
    }

    /**
     * Validate the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        $result = true;
        foreach ($this->_objects as $object) {
            $result = $object->recordValidate() && $result;
        }

        return $true;
    }

    /**
     * Needed for work as an activerecord
     *
     * @return string null
     */
    public function getTableName()
    {
        return;
    }
}