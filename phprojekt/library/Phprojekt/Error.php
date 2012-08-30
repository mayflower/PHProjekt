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
 * The class collect a number of errors in an array and the return it all together.
 *
 * For use with templates, you can assign array like:
 * addError(array('field'   => 'title',
 *                'label'   => 'Label'),
 *                'message' => 'Hello');
 * So the template can get the array and use the fields "field" and "message".
 * You can use the class with the array that you want.
 *
 * When you get the errors, the array that contain it is deleted.
 * So you can add errors the times that you want,
 * but only get the errors one time.
 */
class Phprojekt_Error
{
    /**
     * Containt all the errors.
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Collect all the errors in an array for show it later.
     *
     * @uses
     * addError(array('field'   => 'title',
     *                'label'   => 'Label',
     *                'message' => 'Hello');
     * So the template can get the array and use each field of them.
     *
     * @param array $data Array contain the fields for show.
     *
     * @return void
     */
    public function addError(array $data = array())
    {
        $this->_errors[] = $data;
    }

    /**
     * Return the error data and delete it.
     *
     * @return array Array with errors.
     */
    public function getError()
    {
        // TODO: Fix, what if I want to have retrieve it twice.
        // this is totally dangerous.
        $error         = $this->_errors;
        $this->_errors = array();

        return $error;
    }
}
