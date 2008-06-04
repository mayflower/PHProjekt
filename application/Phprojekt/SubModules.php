<?php
/**
 * Sub-Modules for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Get and return all the submodules availables
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Phprojekt_SubModules
{
    /**
     * Singleton instance
     *
     * @var Phprojekt_SubModules
     */
    protected static $_instance = null;

    /**
     * Array with all the modules found
     *
     * @var array
     */
    protected $_subModules = array();

    /**
     * Ommited system modules
     * And CVS files
     *
     * @var array
     */
    protected $_ommited = array('Administration', 'Groups',
                                'Phprojekt', 'Role', 'User',
                                '.','..','.cvsignore','CVS','.svn');

    /**
     * Return this class only one time
     *
     * @return Phprojekt_SubModules
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructs a Phprojekt_SubModules
     */
    private function __construct()
    {
        if ($handle = opendir(PHPR_CORE_PATH)) {
            while (false !== ($file = readdir($handle))) {
                if (!in_array($file, $this->_ommited)) {
                    $this->_subModules[Phprojekt_Module::getId($file)] = array('name'  => $file,
                                                                               'label' => $file);
                }
            }
        }
        closedir($handle);
        ksort($this->_subModules);
    }

    /**
     * Return all the submodules found
     *
     * @param void
     *
     * @return array
     */
    public function getSubModules()
    {
        return $this->_subModules;
    }
}
