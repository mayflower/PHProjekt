<?php
/**
 * Mail notification class for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
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
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Mail_Notification extends Zend_Mail
{
    /**
     * Smarty Object for draw the html of the form
     *
     * @var Smarty
     */
    protected $_smarty = null;

    /**
     * Public constructor
     *
     * @param Zend_View $smarty Smarty Object for draw the templates
     *
     * @return void
     */
    public function __construct($smarty)
    {
        parent::__construct();
        $this->_smarty = $smarty;
    }

    /**
     * Send a notification email in text mode only
     *
     * @param string $subject Subject text
     * @param array  $users   Array with all the id of the users to send the mail
     * @param string $text    Text of the email
     *
     * @see sendNotification
     *
     * @return void
     */
    public function sendNotificationText($subject, $users, $text)
    {
        $this->sendNotification($subject, $users, null, $text);
    }

    /**
     * Send a notification email in text and/or html mode
     * The function will send a email with all the users in the $users array.
     * For do that, the array contain all the ids
     * and the class will found each email of each user.
     * The email is the email field on the User table
     *
     * The html body is generated with the getHtmlFromTemplate function
     * by the module or the controller.
     * You also can give to the function directly the HTML string.
     *
     * If the text string is empty, the HTML will be used as text
     * without the html tags.
     * If not, the text string is using for text mode
     * and the html string is using for html mode
     *
     * @param string $subject Subject text
     * @param array  $users   Array with all the id of the users to send the mail
     * @param string $html    Html of the body
     * @param string $text    Text of the body
     *
     * @uses $mail = new Phprojekt_Mail_Notification($smartyObject);
     *       $html = $mail->getHtmlFromTemplate(array('var' => 'value'));
     *       $mail->sendNotification('subject', array(1,2,..), $html);
     *
     *       $mail = new Phprojekt_Mail_Notification($smartyObject);
     *       $data = array('var1' => 'value1',
     *                     'var2' => 'value2);
     *       $html = $mail->getHtmlFromTemplate($data, 'othertemplate');
     *       $mail->sendNotification('subject', array(1,2,..), $html, 'Other Text');
     *

     * @see getHtmlFromTemplate
     *
     * @return void
     */
    public function sendNotification($subject, $users, $html, $text = null)
    {
        $db         = Zend_Registry::get('db');
        $userObject = new Phprojekt_User_User($db);

        if ($text != null) {
            $this->setBodyText($text);
        } else {
            $this->setBodyText(strip_tags($html));
        }

        if ($html != null) {
            $this->setBodyHtml($html);
        }
        $userName = $this->setUserNames($userObject);
        $this->setFrom($userObject->email, $userName);
        foreach ($users as $userId) {
            $user     = $userObject->findUserById($userId);
            $userName = $this->setUserNames($user);
            $this->addTo($user->email, $userName);
        }
        $this->setSubject($subject);
        $this->send();
    }

    /**
     * Return the way to display the user name and lastname
     *
     * @param Phprojekt_User_User $object The user object
     *
     * @return string
     */
    public function setUserNames($object)
    {
        return $object->lastname . ' ' . $object->firstname;
    }

    /**
     * Return the HTML from a template using smarty
     *
     * The default template is mail.tpl of the module
     * You can use other template with the var $template
     *
     * The data is an asosiative array with key and values
     * for remplace in the smarty template
     *
     * @param array  $data     Array with all the variables for use in smarty
     * @param string $template Template for draw the HTML
     *
     * @return string
     */
    public function getHtmlFromTemplate($data = array() , $template = 'mail')
    {
        foreach ($data as $key => $value) {
            $this->_smarty->$key = $value;
        }
        $html   = $this->_smarty->render($template . '.tpl');
        return $html;
    }
}