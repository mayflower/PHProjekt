<?php

/**
 * Mail notification class for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     Mariano La Penna <mariano.la.penna@gmail.com>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Mail notification class for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Mariano La Penna <mariano.la.penna@gmail.com>
 */


class Phprojekt_Mail_Notification extends Zend_Mail
{
 
    const MODE_HTML		  = 'Html'; //Internal
    const MODE_TEXT		  = 'Text'; //Internal
	const MAIL_LINEEND_RN = 0;		//External use (configuration.ini)
	const MAIL_LINEEND_N  = 1;		//External use (configuration.ini)
    
	private $_tableName;
	private $_customFrom;
	private $_customTo;
	private $_customSubject;
	private $_bodyMode;
	private $_view;
	private $_model;
	private $_customBody;

		
	
	/**
	* Sends an email notification in HTML mode, with the contents according to a
	* specific module and a specific event.
	*
	* The sender, recipients, subject and body are generated dynamically depending
	* on the module received in the $model parameter.
	* For sending a notification in Text mode, use function sendNotificationText()
	*
	* @param Phprojekt_Model_Interface	$model E.g.: A object of the type 
	*										   Todo_Models_Todo
	*
	* @uses	$mailNotif = new Phprojekt_Mail_Notification();
	* 		$mailNotif->sendNotificationHtml($model);
	*
	* @see _sendNotification()
	*
	* @return void
	*/
	
    public function sendNotificationHtml(Phprojekt_Model_Interface $model)
    {
      	$this->_model = $model;
      	$this->_bodyMode = self::MODE_HTML;
      	$this->_sendNotification();
    }

	
	/**
	* Sends an email notification in Text mode, with the contents according to a
	* specific module and a specific event.
	*
	* The sender, recipients, subject and body are generated dynamically depending
	* on the module received in the $model parameter.
	* For sending a notification in Html mode, use function sendNotificationHtml()
	*
	* @param Phprojekt_Model_Interface	$model E.g.: A object of the type 
	*										   Todo_Models_Todo
	*
	* @uses	$mailNotif = new Phprojekt_Mail_Notification();
	* 		$mailNotif->sendNotificationText($model);
	*
	* @see _sendNotification()
	*
	* @return void
	*/

	public function sendNotificationText(Phprojekt_Model_Interface $model)
	{
      	$this->_model 	 = $model;
      	$this->_bodyMode = self::MODE_TEXT;
		$this->_sendNotification();
	}  


	/**
	* Private function that sends an email notification in Html/Text mode, with
	* the contents according to a specific module and a specific event.
	*
	* The function is called by both sendNotificationHtml() and sendNotificationText()
	* It calls several functions to set the sender, the recipients, the subject
	* and the body. Then calls _mailNotifSend() to send the email.
	*
	* @see _sendNotification()
	*
	* @return void
	*/

	private function _sendNotification()
	{
		$this->_tableName = trim($this->_model->getTableName());
    	if (!isset($this->_customFrom)) $this->_setFromUserLogued();
		$this->_setTo();
		$this->_setCustomSubject();
		$this->_setCustomBody();
        $this->_mailNotifSend();
	}    

	/**
	* Public function for setting the sender name and address. If not called,
	* then, when sending the email through sendNotificationHtml/Text(), it is
	* automatically called _setFromUserLogued() that sets the sender to the 
	* logued user.
	*
	* @param array		$from	An array with two positions: the first value contains
	*							the name and the second one, the email address.
	*
	* @uses		$mailNotif = new Phprojekt_Mail_Notification();
	*			$from 	   = array("Mariano", "mariano.la.penna@gmail.com");
	* 			$mailNotif->setCustomFrom($from);
	*			$mailNotif->sendNotificationHtml($model);
	*
	* @return void
	*/
	    
    public function setCustomFrom(array $from)
    {
    	$this->_customFrom[0] = $from[0];	//Email
    	
    	//Has the name been set?
    	if (sizeof($from) == 2) {
    		$this->_customFrom[1] = $from[1];	//Name
    	}
    }
    
    
    /**
    * Fills the _customFrom property with the name and email of the logued user.
    * This function is called by default if an email is tryed to be sent with
    * not sender data specified. It means that if the sender data has not
    * been manually set via setCustomFrom, this function is called.
    *
    * @see Phprojekt_User_User()
    *
    * @return void
    */
    
    private function _setFromUserLogued()
    {
    	
        $phpUser = new Phprojekt_User_User();
		$phpUser->find(Phprojekt_Auth::getUserId());
		
		//Email assignment
		$this->_customFrom[0] = $phpUser->getSetting('email');
		
		//Name assignment
		$fullname = trim($phpUser->firstname . ' ' . $phpUser->lastname);
		if (!empty($fullname)) {
			$this->_customFrom[1] = $fullname . ' (' . $phpUser->username . ')';
		} else {
			$this->_customFrom[1] = $phpUser->username;
		}

	}
	
	
	/**
	* Private function that fills the recipients variable $_customTo with the
	* recipients obtained from $this->_model and the class Phprojekt_Item_Rights()
	*
	* @return void
	*/
	
	private function _setTo()
	{
		
		$rights  = $this->_model->getRights();
		$i		 = 0;
		$phpUser = new Phprojekt_User_User();
		foreach ($rights as $userId => $userRights) {
			
			if ($userRights['read']) {
				$i++;
				if ((int)$userId) {
					$phpUser->find($userId);
				} else {
					$phpUser->find(Phprojekt_Auth::getUserId());
				}
				$setting = new Setting_Models_Setting();
				$email 	 = $setting->getSetting('email', (int)$userId);
				$this->_customTo[$i] = array();
				$this->_customTo[$i][0] = $email;
				
				$fullname = trim($phpUser->firstname . ' ' . $phpUser->lastname);
				if (!empty($fullname)) {
					$this->_customTo[$i][1] = $fullname . ' (' . $phpUser->username . ')';
				} else {
					$this->_customTo[$i][1] = $phpUser->username;
				}
			}
		}
	}	
	

	/**
	* Private function that sets the subject of the email according to the
	* current module, stored in $this->_model.
	*
	* @return void
	*/

	private function _setCustomSubject()
	{
		$mailTitle="";
		if (isset($this->_model->searchFirstDisplayField)) {
			$mailTitle = $this->_model->{$this->_model->searchFirstDisplayField};
		}
		$this->_customSubject = trim('[' . $this->_tableName . ' #'
							   . $this->_model->id . '] '
							   . $mailTitle);
							   
	}
	

	/**
	* Private function for setting the body of the email according to the
	* current module and the event we are informing to the users.
	* It obtains all the data dinamically from the $this->_model object.
	*
	* @return void
	*/

	private function _setCustomBody()
	{
		$this->_view = Zend_Registry::get('view');
		
		$translate = Zend_Registry::get('translate');
		
		if ($this->_model->lastAction == Phprojekt_Item_Abstract::LAST_CHANGE_ADD) {
			$actionLabel = "created";
		} else if ($this->_model->lastAction == Phprojekt_Item_Abstract::LAST_CHANGE_EDIT) {
			$actionLabel = "modified";
		}

		$this->_view->title = $translate->translate('A ') . $this->_tableName . $translate->translate(' item has been ')
							  . $translate->translate($actionLabel);
		
		$this->_view->translate = $translate;
		
		$fieldDefinition = $this->_model->getInformation()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
		
		foreach ($fieldDefinition as $key => $field) {
			switch ($field['type']) {
				case 'text':
				case 'textarea':
				case 'date':
				case 'time':
				case 'percentage':
				//case 'password' and 'upload' ??
				default:
					$value = $this->_model->$field['key'];
					break;
				
				case 'selectbox':
				case 'multipleselectbox':
					//Search the value
					foreach ($field['range'] as $range) {
						if ($range['id'] == $this->_model->$field['key']) {
							$value = $range['name'];
						}
					}
					break;
			}
			
			$fieldsView[] = array('label' => $field['label'],
								  'value' => $value);
		}

	
		$this->_view->mainFields = $fieldsView;

		$history = new Phprojekt_History();
		$changes = $history->getLastHistoryData($this->_model);
		
		
		/* The following algorithm looks into $changes, searching Integer values
		that should be converted into Strings. It depends on the type of the field */
		
		//Loop in every change done
		for ($i=0; $i < count($changes); $i++) {
 			foreach ($fieldDefinition as $field) {
 				//Find the field definition for the field that has been modified
 				if ($field['key'] == $changes[$i]['field']) {
 					/* Is the field of a type that has an Integer that should be
 					converted into a string? */
 					if ($field['type'] == 'selectbox') {
 						//Yes, so translate it into the appropriate meaning
 						foreach ($field['range'] as $range) {
							//Try to replace oldValue Integer with the String
							if ($range['id'] == $changes[$i]['oldValue']) {
								$changes[$i]['oldValue'] = trim($range['name']);
							}	
							
							//Try to replace newValue Integer with the String
							if ($range['id'] == $changes[$i]['newValue']) {
								$changes[$i]['newValue'] = trim($range['name']);
							}
						}
 					}
 				}
 			}
 		}
		
		$this->_view->changes = $changes;

		if ($this->_bodyMode == self::MODE_TEXT) {

			switch (Zend_Registry::get('config')->mailEndOfLine) {
				case self::MAIL_LINEEND_N:
					$this->_view->endOfLine = "\n";
					break;
				case self::MAIL_LINEEND_RN:
				default:
					$this->_view->endOfLine = "\r\n";
					break;
			}

		}
		
		
		$this->_customBody = $this->_view->render('mail' . $this->_bodyMode . '.phtml');
		
	}
	

		
	/**
	* Private function that sends an email notification using the inherited
	* method send().
	*
	* The function sends an email to the users listed in the $_customTo array.
	* There are many private properties that must have been defined previously:
	* _customFrom, _customTo, _customSubject, _bodyMode and _customBody.
	*
	* @return void
	*/
	
    private function _mailNotifSend() {
        
        //Has the name been set?
    	if (sizeof($this->_customFrom) == 2) {
    		//Yes
    		$this->setFrom($this->_customFrom[0],	//Address
			   			   $this->_customFrom[1]);	//Name
    	} else {
    		//No
    		$this->setFrom($this->_customFrom[0]);	//Address
    	}
        
        
        //Iterates on the array to fill every recipient
        foreach ($this->_customTo as $recipient) {
        	//Has the name been set?
    		if (sizeof($recipient) == 2) {
    			//Yes
    			$this->addTo($recipient[0],		//Address
			   			     $recipient[1]);	//Name
    		} else {
    			//No
    			$this->addTo($recipient[0]);	//Address
    		}

 		}
 		
        $this->setSubject($this->_customSubject);
        
        switch ($this->_bodyMode) {
            case self::MODE_TEXT:
                $this->setBodyText($this->_customBody);
                break;
            case self::MODE_HTML:
            default:
                $this->setBodyHtml($this->_customBody);
                break;
        }
        
       //Creates the Zend_Mail_Transport_Smtp object
       $smtpTransport= $this->_setTransport();
        
       $this->send($smtpTransport);
                
    }
    
	/**
	* Private function used for setting a the SMTP server. The data is obtained from
	* the configuration.ini file.
	*
	* @param $host		string	The SMTP server
	* @param $user		string  Optional. User name for the server.
	* @param $password	string  Optional. Password for the server.
	*
	* @uses	$mailNotif = new Phprojekt_Mail_Notification();
	*		$mailNotif->setSmtp('smtp.myServer.com');
	* 		$mailNotif->sendNotificationHtml($model);
	*
	* @uses	$mailNotif = new Phprojekt_Mail_Notification();
	*		$mailNotif->setSmtp('smtp.myServer.com', 'myUser', 'myPassword');
	* 		$mailNotif->sendNotificationHtml($model);
	*
	* @return Zend_Mail_Transport_Smtp object
	*/
	
	private function _setTransport() {
		
		$smtpServer	  = Zend_Registry::get('config')->smtpServer;
		$smtpUser	  = Zend_Registry::get('config')->smtpUser;
		$smtpPassword = Zend_Registry::get('config')->smtpPassword;
		
		if (empty($smtpServer)) {
			$smtpServer = 'localhost';
		}

        if (empty($smtpUser)) {
        	$smtpTransport = new Zend_Mail_Transport_Smtp($smtpServer);
        } else {
        	$smtpTransport  = new Zend_Mail_Transport_Smtp($smtpServer,
        												   array('auth'		=> 'login',
        												   		 'username' => $smtpUser,
        												   		 'password' => $smtpPassword));
        }

		return $smtpTransport;
		
	}	
 	
    
}