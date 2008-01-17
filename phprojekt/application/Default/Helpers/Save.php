<?php
/**
 * Helper to save tree nodes and models
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * Helper to save tree nodes and models
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
final class Default_Helpers_Save 
{
    /**
     * Save a tree
     *
     * @todo optimize and use native queries to do
     * @param Phprojekt_Tree_Node_Database $node
     * @param array $params
     * 
     * @throws Exception If validation of parameters fails
     * 
     * @return void
     */
    protected static function _saveTree(Phprojekt_Tree_Node_Database $node, array $params, $parentId = null)
    {
        $node->setup();
        
        if (null === $parentId) {
            $parentId = $node->getParentNode()->id;
        }
        
        $parentNode = new Phprojekt_Tree_Node_Database($node->getActiveRecord(), $parentId);
        $parentNode->setup();

        /* Assign the values */
        foreach ($params as $k => $v) {
            if (isset($node->$k)) {
                $node->$k = $v;
            }
        }    
        
        if ($node->recordValidate()) {   
	        if ($node->parent !== $parentId) {
	            $node->setParentNode($parentNode);
	        } else {
	            $node->getActiveRecord()->save();
	        }
        } else {
            throw new Exception('Validation failed');
        }
    }
    
    /**
     * Overwrite call to support multiple save routines
     *
     * @param string $name
     * @param array  $arguments
     * @throws Exception If validation of parameters fails
     * 
     * @return void
     */
    public static function save()
    {
        if (func_num_args() < 2) {
            throw new InvalidArgumentException('Expect two arguments');
        }
        
        $arguments = func_get_args();
		$model     = $arguments[0];
		$params    = $arguments[1];
		
		if (!is_array($params)) {
			throw new InvalidArgumentException('Second parameter needs to be an array');
		}
		
        if ($model instanceof Phprojekt_Tree_Node_Database) {
            
            if (func_num_args() == 3) {
                $parentId = $arguments[2];
            } else if (array_key_exists('parent', $params)) {
                $parentId = $params['parent'];
            } else if (array_key_exists('projectId', $params)) {
                $parentId = $params['projectId'];
            } else {
                throw new InvalidArgumentException('No parent id found in parameters or passed');
            }
            
            return self::_saveTree($model, $params, $parentId);
            
        } 

        if ($model instanceof Phprojekt_Model_Interface) {
            return self::_saveModel($model, $params);
        }
    }
}

?>