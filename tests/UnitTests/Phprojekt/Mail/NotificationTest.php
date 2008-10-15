<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests Notification class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_NotificationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test send notification
     *
     */
    public function testSendNotification()
    {
        /*  Initialization */
        $smarty = Zend_Registry::get('view');

        $mailNotification = new Phprojekt_Mail_Notification($smarty);

        /* Send an email without problems */
        $mailNotification->sendNotificationText('Testing subject', array(1), 'Testing body');

        /* Send an HTML email without problems */
        $mailNotification->sendNotification('Testing subject', array(1), 'mail');

        /* send an email to an invalid user */
        $error = 0;
        try {
            $mailNotification->sendNotificationText('Testing subject', array(3), 'Testing body');
        }
        catch (Exception $ae) {
            $error = $ae->getCode();
        }
        $this->assertFalse($error == 0);

        /* send an email with not recipient */
        $error = 0;
        try {
            $mailNotification->sendNotificationText('Testing subject', null, 'Testing body');
        }
        catch (Exception $ae) {
            $error = $ae->getCode();
        }
        $this->assertFalse($error == 0);

        /* send an email without subject */
        $error = 0;
        try {
            $mailNotification->sendNotificationText(null, array(1), 'Testing body');
        }
        catch (Exception $ae) {
            $error = $ae->getCode();
        }
        $this->assertFalse($error == 0);

        /* send an email without body */
        $error = 0;
        try {
            $mailNotification->sendNotificationText('Test subject' , array(1), null);
        }
        catch (Exception $ae) {
            $error = $ae->getCode();
        }
        $this->assertFalse($error == 0);


    }

    /**
     * Test getHtmlFromTemplate
     *
     */
    public function getHtmlFromTemplate()
    {
        /*  Initialization */
        $smarty = Zend_Registry::get('view');

        $mailNotification = new Phprojekt_Mail_Notification($smarty);

        /* Parsing an email */
        $html = $mailNotification->getHtmlFromTemplate(array(), 'mail');

        $this->assertEquals('<!DOCTYPE HTML',substr($html, 0, 14));

        /* try to parse an invalid template */
        $error = 0;
        try {
            $html = $mailNotification->getHtmlFromTemplate(array(), 'invalidTemplate');
        }
        catch (Exception $ae) {
            $error = $ae->getCode();
        }
        $this->assertFalse($error == 0);

    }

}