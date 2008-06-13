<?php
/**
 * Calendar Module Controller for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Default Calendar Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Calendar_IndexController extends IndexController
{
    /**
     * Saves the current item
     * Save if you are add one or edit one.
     * Use the model module for get the data
     *
     * If there is an error, the save will return a Phprojekt_PublishedException
     * If not, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $translate     = Zend_Registry::get('translate');
        $userId        = Phprojekt_Auth::getUserId();
        $participants  = array();
        $relatedEvents = array();
        $message       = $translate->translate('The Item was added correctly');
        
        $id         = (int) $this->getRequest()->getParam('id');
        $invite     = $this->getRequest()->getParam('invite');
        $moduleName = $this->getRequest()->getModuleName();
        
        
        // getting the main row if the group if an id is provided
        if (!empty($id)) {
            $message = $translate->translate('The Item was edited correctly');
            
            $rootEvent = Phprojekt_Loader::getModel($moduleName, $moduleName);
            
            //$rootEvent = new Calendar_Models_Calendar(); 
            $rootEvent->find($id);
            while (!empty($rootEvent->parentId)) {
                $rootEvent->find($rootEvent->parentId);
            }
            
            $rootEventId = (int)$rootEvent->id;
            
            // the main event is related to himself
            $relatedEvents[$rootEvent->participantId] = $rootEventId;
            
            // getting the event list -all related events-
            $eventList = $rootEvent->fetchAll("parentId = ".$rootEventId);
            if (is_array($eventList)) {
                foreach ($eventList as $oneEvent) {
                    $tmp = (int)$oneEvent->participantId;
                    $relatedEvents[$tmp] = (int)$oneEvent->id;
                }
            }
        
        }
        else {
            $rootEventId = 0;
        }
        
        // getting the participant list from request
        if (is_array($invite)) {
            
            // we will put the owner id first, just to make it clear
            if (in_array($userId, $invite)) {
                $participants[] = $userId;
            }
            foreach ($invite as $oneParticipant) {
                if (!in_array((int)$oneParticipant, $participants)) {
                    $participants[] = (int)$oneParticipant;
                }
            }
        }
        elseif ((is_numeric($invite) && ($userId <> (int)$invite))) {
            $participants[] = (int)$userId;
            $participants[] = (int)$invite;
        }
        else {
            $participants[] = $userId;
        }
        
        // now the insertion or edition for each invited user
        foreach ($participants as $oneParticipant) {
            
            $this->getRequest()->setParam('participantId',$oneParticipant);
            $model  = Phprojekt_Loader::getModel($moduleName, $moduleName);
            if (isset($relatedEvents[$oneParticipant])) {
                
                if ($relatedEvents[$oneParticipant] <> $rootEventId) {
                    $this->getRequest()->setParam('parentId',$rootEventId);
                }
                else {
                    $this->getRequest()->setParam('parentId',0);
                }
                $model->find($relatedEvents[$oneParticipant]);
                unset($relatedEvents[$oneParticipant]);
            }
            
            Default_Helpers_Save::save($model, $this->getRequest()->getParams());
            
            if ($rootEventId == 0) {
                $this->getRequest()->setParam('parentId',$model->id);
            }
            unset($model);
        }
        
        // now, I'll delete the other participants (uninvited?)
        if (is_array($relatedEvents) && count($relatedEvents) > 0) {
            foreach ($relatedEvents as $oneParticipant => $oneId) {
                $model  = Phprojekt_Loader::getModel($moduleName, $moduleName);
                $model->find($relatedEvents[$oneParticipant]);
                $model->delete();
                unset($model);
            }
        }
        
        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $model->id);

        echo Phprojekt_Converter_Json::convert($return);
    }
    
}