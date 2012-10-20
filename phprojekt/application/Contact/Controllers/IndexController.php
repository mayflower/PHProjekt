<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Contact Module Controller.
 */
class Contact_IndexController extends IndexController
{
    /**
     * Saves a contact item
     *
     * Contacts, unlike other items, don't have a role or item rights.
     * The save method doesn't save any rights whatsoever.
     */
    protected function _saveModel(Phprojekt_Model_Interface $model, $params, $newItem)
    {
        if (false === $newItem
         && true === $model->private
         && $model->ownerId !== Phprojekt_Auth_Proxy::getEffectiveUserId()
         && !Phprojekt_Auth::isAdminUser()) {
            throw new Zend_Controller_Action_Exception("You are not the owner of this contact", 403);
        }

        $model = Default_Helpers_Save::parameterToModel($model, $params, $newItem);
        /* contacts are always saved under the root project */
        $model->projectId = 1;

        if (!$model->recordValidate()) {
            $errors = $model->getError();
            $error  = array_pop($errors);
            throw new Zend_Controller_Action_Exception($error['label'] . ': ' . $error['message'], 400);
        }

        $model->save();
    }
}
