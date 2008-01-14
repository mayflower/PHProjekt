<?php
/**
 * Administration overview module for PHProjekt 6.0
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
 * Administration overview module for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Administration_IndexController extends IndexController
{
    /**
     * Return the list view
     *
     * @return void
     */
    protected function _generateOutput()
    {
        $this->view->treeView  = $this->getTreeView()->render();
        $this->view->adminView = $this->view->render('list.tpl');
        $this->render('adminindex');
    }

    /**
     * List the administration modules
     *
     * @return void
     */
    public function listAction()
    {
        $modules = Phprojekt_Loader::getModel('Administration', 'AdminModels');

        $this->view->adminModules = $modules->fetchAll();
    }
}