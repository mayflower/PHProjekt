<?php
/**
 * Minutes Module Controller for PHProjekt 6.0
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Minutes Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */
class Minutes_IndexController extends IndexController
{
    /*
     * Get a user list in JSON
     * 
     * Produces a list of users that should be selectable in the frontend.
     * First implementation returns the list of users invited to the meeting.
     * 
     * @return void
     */
    public function jsonListUserAction () {
        $minutes = Phprojekt_Loader::getModel('Minutes', 'Minutes');
        $minutes->find($this->getRequest()->getParam('id'));
        
        if (!empty($minutes->id)) {
            $user = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $userList = $user->fetchAll(sprintf('id in (%s)', $minutes->participantsInvited));
            Phprojekt_Converter_Json::echoConvert($userList);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }
}
