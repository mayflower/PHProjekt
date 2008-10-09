<?php
/**
 * Phprojekt own dispatcher to handle customized controller names
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Phprojekt own dispatcher to handle customized controller names.
 * Customized controllers have the name {MODULE}_Customized_{FOO}Controller
 *
 * @todo not supported yet
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Dispatcher extends Zend_Controller_Dispatcher_Standard
{

    /**
     * Format a string into a controller class name.
     *
     * @param string $unformatted current controller name
     *
     * @return string
     */
    public function formatControllerName($unformatted)
    {
        $module = $this->getFrontController()->getRequest()->getModuleName();
        $module = self::formatModuleName($module);

        $controller = parent::formatControllerName($unformatted);
        return $controller;
    }
    
    /**
     * Formats a string from a URI into a PHP-friendly name.
     *
     * By default, replaces words separated by the word separator character(s)
     * with camelCaps. If $isAction is false, it also preserves replaces words
     * separated by the path separation character with an underscore, making
     * the following word Title cased. All non-alphanumeric characters are
     * removed.
     *
     * @param string $unformatted
     * @param boolean $isAction Defaults to false
     * @return string
     */
    protected function _formatName($unformatted, $isAction = false)
    {
        // preserve directories
        if (!$isAction) {
            $segments = explode($this->getPathDelimiter(), $unformatted);
        } else {
            $segments = (array) $unformatted;
        }

        foreach ($segments as $segment) {
            $segment        = str_replace($this->getWordDelimiter(), ' ', $segment);
            $segment        = preg_replace('/[^a-z0-9 ]/', '', $segment);
        }

        return implode('_', $segments);
    }    
}