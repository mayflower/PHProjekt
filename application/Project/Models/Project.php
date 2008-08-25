<?php
/**
 * Project model class
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * Project model class
 *
 * The class of each model return the data for show
 * on the list and the form view
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_Models_Project extends Phprojekt_Item_Abstract
{
    /**
     * Validate function for the projectId field
     *
     * @param integer $value Value of the projectId to check
     *
     * @return string Error msg
     */
    public function validateProjectId($value)
    {
        if (null !== $this->id && $this->id > 0) {
            $node = Phprojekt_Loader::getModel('Project', 'Project')->find($this->id);
            $tree = new Phprojekt_Tree_Node_Database($node, $this->id);
            $tree->setup();
            if ($tree->getActiveRecord()->id == $value) {
                return 'The project can not saved under itself';
            } else if ($this->_isInTheProject($value, $tree)) {
                return 'The project can not saved under his children';
            }
        }

        return null;
    }

    /**
     * Check if the projectId is under the same project or a subproject of him
     *
     * @param integer                      $projectId The projectId to check
     * @param Phprojekt_Tree_Node_Database $node      The node of the current project
     *
     * @return boolean
     */
    private function _isInTheProject($projectId, $node)
    {
        $allow = false;
        if ($node->hasChildren()) {
            $childrens = $node->getChildren();
            foreach ($childrens as $childrenNode) {
                if ($projectId == $childrenNode->id) {
                    $allow = true;
                    break;
                } else {
                    if ($this->_isInTheProject($projectId, $childrenNode)) {
                        $allow = true;
                        break;
                    }
                }
            }
        }
        return $allow;
    }
}