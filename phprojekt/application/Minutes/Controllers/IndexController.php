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
    public function jsonListUserAction ()
    {
        $minutes = Phprojekt_Loader::getModel('Minutes', 'Minutes');
        $minutes->find($this->getRequest()->getParam('id'));

        if (!empty($minutes->id)) {
            $user = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $idList = array();
            $idList = array_merge($idList, 
                                  explode(',', $minutes->participantsInvited),
                                  explode(',', $minutes->participantsExcused),
                                  explode(',', $minutes->participantsAttending),
                                  explode(',', $minutes->recipients));
            $userList = $user->fetchAll(sprintf('id IN (%s)', implode(',', $idList)));

            $data    = array();
            $display = $user->getDisplay();
            foreach ($userList as $record) {
                $data['data'][] = array('id'      => $record->id,
                                        'display' => $record->applyDisplay($display, $record));
            }
            $data['numRows'] = count($userList);
            
            Phprojekt_Converter_Json::echoConvert($data);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }
    
    /*
     * Deleting minutes also deletes all minutes items belonging to this minutes.
     * 
     * @return void
     */
    public function jsonDeleteAction () 
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $minutes = Phprojekt_Loader::getModel('Minutes', 'Minutes')->find($id);
        $minutesItems = Phprojekt_Loader::getModel('Minutes', 'MinutesItem')->init($id)->fetchAll();
        
        $success = true;
        
        if ($minutes instanceof Phprojekt_Model_Interface) {
            foreach ($minutesItems as $item) {
                Phprojekt::getInstance()->getLog()->debug('Deleting minutesItem' . $item->id);
                $success = $success && (false !== Default_Helpers_Delete::delete($item));
                Phprojekt::getInstance()->getLog()->debug('Deletion was successful:' . ($success?'yes':'no'));
            }
            $success = $success && (false !== Default_Helpers_Delete::delete($minutes));
            Phprojekt::getInstance()->getLog()->debug('Main Deletion was successful:' . ($success?'yes':'no'));
            
            
            if ($success === false) {
                $message = Phprojekt::getInstance()->translate(self::DELETE_FALSE_TEXT);
            } else {
                $message = Phprojekt::getInstance()->translate(self::DELETE_TRUE_TEXT);
            }
            $return = array('type'    => 'success',
                            'message' => $message,
                            'code'    => 0,
                            'id'      => $id);
            
            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }
}
