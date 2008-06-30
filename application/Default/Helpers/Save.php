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
     * @return boolean
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

        /* Set the owner */
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $node->ownerId = $authNamespace->userId;
        if ($node->getActiveRecord()->recordValidate()) {
            if ((int)$node->projectId !== $parentId) {
                $newNode = $node->setParentNode($parentNode);
            } else {
                $newNode = $node->getActiveRecord()->save();
            }
            if (isset($params['userIdAccess'])) {
                $adminUsers = array($authNamespace->userId);
                $writeUsers = array($authNamespace->userId);
                $readUsers  = array($authNamespace->userId);
                foreach ($params['userIdAccess'] as $accessUserId => $userName) {
                    if (isset($params['checkAdminAccess'][$accessUserId])) {
                        array_push($adminUsers, $accessUserId);
                    }
                    if (isset($params['checkWriteAccess'][$accessUserId])) {
                        array_push($writeUsers, $accessUserId);
                    }
                    if (isset($params['checkReadAccess'][$accessUserId])) {
                        array_push($readUsers, $accessUserId);
                    }
                }
                $node->getActiveRecord()->saveRights($adminUsers, $writeUsers, $readUsers);
            }
            return $newNode;
        } else {
            $error = array_pop($node->getActiveRecord()->getError());
            throw new Phprojekt_PublishedException($error['field'] . ' ' . $error['message']);
        }
    }

    /**
     * Help to save a model by setting the models properties.
     * Validation is based on the ModelInformation implementation
     *
     * @param Phprojekt_Model_Interface $model  The model
     * @param array                     $params The parameters used to feed the model
     *
     * @throws Exception
     *
     * @return boolean
     */
    protected static function _saveModel(Phprojekt_Model_Interface $model, array $params)
    {
        foreach ($params as $k => $v) {
            if (isset($model->$k)) {
                /* dont allow to set the id on save, since is doit by the activerecord */
                if (!in_array($k, array('id'))) {
                    $model->$k = $v;
                }
            }
        }

        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        /* Set the owner */
        if (isset($model->ownerId)) {
            $model->ownerId = $authNamespace->userId;
        }
        
        if ($model->recordValidate()) {
            $model->save();
            
            // creating rights for owner user
            $adminUsers = array($authNamespace->userId);
            $writeUsers = array($authNamespace->userId);
            $readUsers  = array($authNamespace->userId);
                
            // checking permission sent as parameter
            if (isset($params['userIdAccess'])) {
                foreach ($params['userIdAccess'] as $accessUserId) {
                    if (isset($params['checkAdminAccess'][$accessUserId])) {
                        array_push($adminUsers, $accessUserId);
                    }
                    if (isset($params['checkWriteAccess'][$accessUserId])) {
                        array_push($writeUsers, $accessUserId);
                    }
                    if (isset($params['checkReadAccess'][$accessUserId])) {
                        array_push($readUsers, $accessUserId);
                    }
                }
            }
            
            // saving rights
            $model->saveRights($adminUsers, $writeUsers, $readUsers);
            
            return $model;
        } else {
            $error = array_pop($model->getError());
            throw new Phprojekt_PublishedException($error['field'] . ' ' . $error['message']);
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
        $arguments = func_get_args();
      	$model     = $arguments[0];
      	$params    = $arguments[1];

        if (func_num_args() < 2) {
            throw new Phprojekt_PublishedException('Expect two arguments');
        }

        if (!is_array($params)) {
            throw new Phprojekt_PublishedException('Second parameter needs to be an array');
        }

        if ($model instanceof Phprojekt_Tree_Node_Database) {
            if (func_num_args() == 3) {
                $parentId = $arguments[2];
            } else if (array_key_exists('projectId', $params)) {
                $parentId = $params['projectId'];
            } else {
                throw new Phprojekt_PublishedException('No parent id found in parameters or passed');
            }

            return self::_saveTree($model, $params, $parentId);
        }

        if ($model instanceof Phprojekt_Model_Interface) {
            return self::_saveModel($model, $params);
        }
    }
}