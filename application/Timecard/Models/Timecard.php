<?php
/**
 * Timecard model class
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id: Timecard.php 635 2008-04-02 19:32:05Z david $
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Timecard model class
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Timecard_Models_Timecard extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Validate object
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;

    /**
     * Initialize new user
     * If is seted the user id in the session,
     * the class will get all the values of these user
     *
     * @param array $db Configuration for Zend_Db_Table
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Zend_Registry::get('db');
        }
        parent::__construct($db);

        $this->_validate           = new Phprojekt_Model_Validate();
        $this->_informationManager = new Timecard_Models_Information();
    }

    /**
     * Get the information manager
     *
     * @see Phprojekt_Model_Interface::getInformation()
     *
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation()
    {
        return $this->_informationManager;
    }

    /**
     * Get the rigths
     *
     * @return array
     */
    public function getRights()
    {
        return array();
    }

    /**
     * Save the rigths
     *
     * @return void
     */
    public function saveRights()
    {
    }

    /**
     * Validate the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        $data      = $this->_data;
        $fields    = $this->_informationManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        $this->_validate = new Phprojekt_Model_Validate();
        return $this->_validate->recordValidate($this, $data, $fields);
    }

    /**
     * Return the error data
     *
     * @return array
     */
    public function getError()
    {
        return (array) $this->_validate->error->getError();
    }
    
    public function getRecords($view, $year, $month, $count, $offset)
    {
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $sortRecords   = array();
        
        if (strlen($month) == 1) {
            $month = '0'.$month;
        }        
    	switch ($view) {
    		case 'today':
    			$where   = sprintf('(ownerId = %d AND date = "%s")', $authNamespace->userId, date("Y-m-d"));
                $order   = ' date ASC';
                $records = $this->fetchAll($where, $order, $count, $offset);      
    			$data= $records;
    			break;
    		case 'month':    			
                $where = sprintf('(ownerId = %d AND date LIKE "%s")', $authNamespace->userId, $year.'-'.$month.'-%');
                $order = ' date ASC';

                $records       = $this->fetchAll($where, $order, $count, $offset);      
                $sortRecords   = array();
                $timeproj      = new Timecard_Models_Timeproj();
    			
    			$information     = $this->getInformation($order);
                $fieldDefinition = $information->getFieldDefinition($view);
    			
                $datas   = array();
                $data    = array();
                $numRows = 0;
                foreach ($records as $record) {
                    $sum = $this->getDiffTime($record->endTime, $record->startTime);
                    if (!isset($sortRecords[$record->date])) {
                        $sortRecords[$record->date] = array('sum'      => 0,
                                                            'bookings' => 0);
                        $where = sprintf('(ownerId = %d AND date = "%s")', $authNamespace->userId, $record->date);
                        $bookingsResults = $timeproj->fetchAll($where);
                        $bookings = 0;
                        foreach ($bookingsResults as $booking) {
                            $bookings += $this->getDiffTime($booking->amount, '00:00:00');	
                        }
                        $sortRecords[$record->date]['bookings'] += (int)$bookings;
                    }
                    if ($sum > 0) {
                        $sortRecords[$record->date]['sum'] += (int)$sum;
                    }
                }
                
                $endDayofTheMonth = date("t");
                for($i = 1; $i <= $endDayofTheMonth; $i++) {
                	$day = $i;
                    if (strlen($day) == 1) {
                        $day = '0'.$i;
                    }                	
                	$date = $year.'-'.$month.'-'.$day;
                	if (isset($sortRecords[$date])) {
                    	$data['date']     = $date;
                    	$data['sum']      = $this->convertTime($sortRecords[$date]['sum']);
                    	$data['bookings'] = $this->convertTime($sortRecords[$date]['bookings']);
                        $data['rights']   = array();
                        $datas[] = $data;
                	} else {
                        $data['date']     = $date;
                        $data['sum']      = 0;
                        $data['bookings'] = 0;
                        $data['rights']   = array();
                        $datas[] = $data;                		
                	}
                }

                $numRows = count($datas);
                $data = array('metadata' => $fieldDefinition,
                              'data'     => $datas,
                              'numRows'  => (int)$numRows);
                break;
    	}
    	
    	return $data;
    }
    
    public function getDiffTime($end, $start)
    {
    	$hoursEnd   = substr($end, 0, 2);
    	$minutesEnd = substr($end, 3, 2);

        $hoursStart   = substr($start, 0, 2);
        $minutesStart = substr($start, 3, 2);

        return (($hoursEnd - $hoursStart)*60) + ($minutesEnd - $minutesStart);
    }
    
    public function convertTime($time)
    {
        $hoursDiff = floor($time / 60);
        $minutesDiff = $time - ($hoursDiff * 60);
        if (strlen($hoursDiff) == 1) {
        	$hoursDiff = '0'.$hoursDiff;
        }
        if (strlen($minutesDiff) == 1) {
            $minutesDiff = '0'.$minutesDiff;
        }
        return $hoursDiff.':'.$minutesDiff;
    }
}