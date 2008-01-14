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
    protected $_niceName;

    /**
     * The module name
     *
     * @var string
     */
    protected $_module;

    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * A list of directories that are not included in the search.
     * Usually Default and PHProjekt
     *
     * @var array
     */
    protected static $_excludePatterns = array('Default', 'Phprojekt', 'Administration');

    /**
     * Initialize a new object and setup a the model default modelinformation
     *
     * @param array $configuration The configuration of the admin module that should be rendered
     */
    public function __construct($configuration = null)
    {
        $this->_informationManager = new Phprojekt_ModelInformation_Default($configuration);
    }

    /**
     * Returns the merged configuration between
     *
     * @return void
     */
    public function getConfiguration()
    {

    }

    /**
     * Add a module to the ignore list.
     * A ignored module is not received using
     * fetchAll() but it can be received using find().
     * Returns TRUE on success otherwise FALSE;
     *
     * @param string $name Name of the module to be ignored
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
     * @param string $name Name of the module to be removed from the ignore list
     *
     * @return boolean
     */
    public function unignoreModule($name)
    {
        if (($key = array_search($name, self::$_excludePattern)) !== false) {
            unset(self::$_excludePattern[$key]);
            return true;
        }

        return false;
    }

    /**
     * Re-writed function
     *
     * @see Phprojekt_Model_Interface::fetchAll()
     *
     * @return array
     */
    public function fetchAll()
    {
        $results = array();

        foreach (scandir(PHPR_CORE_PATH) as $dir) {
            $path = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $dir;
            if ($dir == '.' || $dir == '..' || in_array($dir, self::$_excludePatterns)) {
                continue;
            }

            if (is_dir($path)) {
                /* @todo Optimize */
                $instance  = clone $this;
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
     * @param string $name Name of the var to get
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        $name = '_' . $name;
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    /**
     * Re-writed function
     *
     * @see Phprojekt_Model_Interface::find()
     *
     * @return Phprojekt_Model_Interface
     */
    public function find()
    {
        $module      = ucfirst(func_get_arg(0));
        $moduleClass = sprintf('%s_AdminController', $module);

        try {
            Phprojekt_Loader::loadClass($moduleClass);

            /* workaround as php 5.2 doesnot support late static bindings */
            $vars = get_class_vars($moduleClass);
            $this->_niceName = (empty($vars['name'])) ? $module : $vars['name'];
            $this->_module   = $module;

            return $this;
        } catch (Zend_Exception $ze) {
            // .. file not found
            return false;
        }
    }

    /**
     * Re-writed function
     *
     * @see Phprojekt_Model_Interface::getInformation()
     *
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation()
    {
        return $this->_informationManager;
    }

    /**
     * Re-writed function
     *
     * @see Phprojekt_Model_Interface::save()
     *
     * @return void
     */
    public function save()
    {
        // we don't save admin modules
    }

    /**
     * Get the rigths
     *
     * @return string
     */
    public function getRights()
    {
        return 'write';
    }
}