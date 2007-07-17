<?php
/**
 * Default Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    see http://phprojekt.com/licence
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

require_once 'Zend/Controller/Action.php';

/**
 * Default Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class IndexController extends Zend_Controller_Action
{
	/**
	 * Return true if not have access 
     *
	 */
	public function accessDenied()
	{
        return false;
	}

    /**
     * Standard action
     *
     * @return void
     *
     */
    public function indexAction()
    {
        /* @var $renderer Zend_View_Abstract */
        $renderer       = Zend_Registry::get('view');
        $renderer->name = "David";
    }

    /**
     * If the Action don´t exists, call indexAction
     *
     * @param string method - Action method
     * @param array  args   - Arguments for the Action
     * @return Zend_Exception
     *
     */
    public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            // If the action method was not found,
            // forward to the index action            
            return $this->_forward('index');  
        }
        // all other methods throw an exception
        throw new Exception('Invalid method "' . $method . '" called');
    }
}
