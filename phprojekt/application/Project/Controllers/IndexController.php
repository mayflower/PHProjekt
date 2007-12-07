<?php
/**
 * Project Module Controller for PHProjekt 6
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Default Project Module Controller for PHProjekt 6
 *
 * For make a indexController for your module
 * just extend it to the IndexController
 * and redefine the function getModelsObject
 * for return the object model that you want
 *
 * You can redefine too, the var FORM_COLUMNS
 * for make your module with other number than the default
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_IndexController extends IndexController
{
    /**
     * Define the amount of columns into which the form is rendered
     *
     * @var integer
     */
    const FORM_COLUMNS = 1;

    /**
     * Set various session data.
     *
     * @todo Check if we have to move this part of the code, as lastProjectId
     *       is used everywhere.
     *
     * @return void
     */
    public function init() {
        parent::init();
        $session = new Zend_Session_Namespace();
        $project = $this->getModelObject();
        if ($this->getRequest()->getParam('nodeId', 0) > 0) {
            $project->find($this->getRequest()->getParam('nodeId'));
            $session->currentProjectId   = $this->getRequest()->getParam('nodeId', 0);
            $session->currentProjectName = $project->title;
        }
    }

    /**
     * We store the id of the shown project in the session, as other modules
     * and the indexcontroller might depend on that to define the current active
     * object
     *
     * @return void
     */
    public function listAction()
    {
        $db = Zend_Registry::get('db');
        /* Save the last project id into the session */
        /* @todo: Sanitize ID / Request parameter */
        $session = new Zend_Session_Namespace();
        $project = $this->getModelObject();

        if (isset($session->currentProjectId)) {
            $this->addWhere($db->quoteInto('parent = ?', $session->currentProjectId));
        }

        $this->getListView()->setModel($project->fetchAll($this->getWhere()));
    }

    /**
     * Save Action
     *
     * The save is redefined for use with tree in the project module
     *
     * @return void
     */
    public function saveAction()
    {
        if ($this->getRequest()->getParam('id', 0) > 0) {
            $model = $this->getModelObject()->find($this->getRequest()->getParam('id'));
        } else {
            $model = $this->getModelObject();
        }

        $parent = (isset($this->_params['parent'])) ? (int) $this->_params['parent'] : 1;

        $parentNode = new Phprojekt_Tree_Node_Database($model, $parent);
        $newNode    = new Phprojekt_Tree_Node_Database($model, $this->getRequest()->getParam('id'));

        if (null !== $this->getRequest()->getParam('id')) {
            $newNode->setup();
        }
        $parentNode->setup();

        /* Assign the values */
        foreach ($this->_params as $k => $v) {
            if ($newNode->getActiveRecord()->keyExists($k)) {
                $newNode->$k = $v;
            }
        }

        /* Validate and save if is all ok */
        if ($newNode->getActiveRecord()->recordValidate()) {
            if (null === $this->getRequest()->getParam('id') || $newNode->parent !== $parentNode->id) {
                $parentNode->appendNode($newNode);
            } else {
                $newNode->getActiveRecord()->save();
            }
            $this->view->message = 'Saved';
        } else {

            $this->view->errors = $newNode->getActiveRecord()->getError();
        }

        $this->listAction();
    }
}