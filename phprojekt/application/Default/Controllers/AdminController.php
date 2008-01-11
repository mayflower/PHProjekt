<?php
/**
 * Default Controller for PHProjekt 6
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Default Admin Controller for PHProjekt 6
 *
 * This controller gives you the possibility you creae easy 
 * crud-like admin modules by extendin this controller
 * 
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
abstract class AdminController extends IndexController
{
    /**
     * The configuration array contains a list of 
     * predefined renderable settings that are 
     * stored in global table by the admin module.
     *
     * @var array
     */
	public static $_configuration = array('type' => 'text', 'label' => 'On/Off');
	
	/**
	 * Initialize
	 *
	 */
	public function init()
	{
	    parent::init();
	    
	    // late static binding configuration
	    $class = get_class($this);
	    $vars  = get_class_vars($class);
	    
	    // merge the default configuration with the users configuration
        $this->_configuration = array_merge(self::$_configuration, $vars['_configuration']);    
	}
	
	/**
	 * Overwrite postDispatch, that calls generateOutput
	 * as the IndexController postDispatch does some additional
	 * checks we don't need and we don't satisfy
	 *
	 * @return void
	 */
	public function postDispatch()
	{
		$this->_generateOutput();	
	}
	
	/**
	 * Overwritten generateOutput method to render or own index file
	 *
	 * @return void
	 */
	protected function _generateOutput()
	{
		$this->view->treeView = $this->getTreeView()->render();
		$this->render('adminindex');
	}
	
	/**
	 * Save the values into a given global
	 * table that is maintained by the administration module
	 *
	 * @return void
	 */
	public function saveAction()
	{
	    $configuration = Phprojekt_Loader::getModel('Default', 'AdminModels');
	    foreach($this->_configuration as $config) {
	        
	    }
	}
	
	/**
	 * Setup the variables to render the overview over the 
	 * administrateable modules
	 *
	 * @return voidvar_dump(func_get_args());exit;
	 */
	public function showAction()
	{
	    /* @todo: sanitize? */
	    $module = $this->getRequest()->getModuleName();
	    $model  = Phprojekt_Loader::getModel('Administration', 'AdminModels', $this->_configuration);
	    
	    if (null === $module) {
	        throw new Exception('Module not given');
	    }
	    
	    if (false === $model->find($module)) {
	        throw new Exception('Module not found');
	    }
	    
	    $renderer = new Default_Helpers_FormViewRenderer();
	    $renderer->setModel($model);
	    
	    $this->view->adminView = $renderer->render();
	}
}