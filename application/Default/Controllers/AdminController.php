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
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
abstract class AdminController extends IndexController
{
    /**
     * Configuration array that contains the 
     * definitions for the admin values that can be
     * saved for that module.
     * Take a look into the developer part of the
     * manual to see how this array should be defined
     *
     * @var array
     */ 
    public static $configuration = array();
    
    /**
     * Overwritten generateOutput method to render or own index file
     *
     * @return void
     */
    protected function _generateOutput ()
    {
        $this->view->module     = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action     = $this->getRequest()->getActionName();
        $this->view->treeView = $this->getTreeView()->render();
        $this->render('adminindex');
    }

    /**
     * Save the values into a given global
     * table that is maintained by the administration module
     *
     * @return void
     */
    public function saveAction ()
    {
        $model  = Phprojekt_Loader::getModel('Administration', 'AdminModels');
        $model->find($this->getRequest()->getModuleName());
        
        foreach ($model->configuration as $key => $config) {
            if ($this->getRequest()->getParam($key, false) !== false) {
                $model->$key = $this->getRequest()->getParam($key);
            }
        }
        
        $model->save();

        $this->forward('show');
    }

    /**
     * Setup the variables to render the overview over the 
     * administrateable modules
     *
     * @return voidvar_dump(func_get_args());exit;
     */
    public function showAction ()
    {
        /* @todo: sanitize? */
        $module = $this->getRequest()->getModuleName();
        $model  = Phprojekt_Loader::getModel('Administration', 'AdminModels');
        
        if (null === $module) {
            throw new Exception('Module not given');
        }
        
        $result = $model->find($module);
        if (false === $result) {
            throw new Exception('Module not found');
        }
        
        $renderer = new Default_Helpers_FormViewRenderer();
        $renderer->setModel($model);

        $this->view->adminView = $renderer->render('adminform.tpl');
    }
}