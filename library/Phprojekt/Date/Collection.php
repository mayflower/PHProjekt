<?php
/**
 * Class to hold a collection of dates. These dates can also be automatically
 * calculated from a iCal-rrule.
 *
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Michel Hartmann <michel.hartmann@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

class Phprojekt_Date_Collection
{
    /**
     * Array holding the elements of the collection. Each Element
     * is stored as a Zend_Date object.
     *
     * @var array
     */
    private $elements = array();

    /**
     * The highest value that should be allowed. If a higher value is added
     * it will be dropped.
     *
     * @var Zend_Date
     */

    private $maxDate = null;

    /**
     * The lowest value that should be allowed. If a lower value is added
     * it will be dropped.
     *
     * @var Zend_Date
     */
    private $minDate = null;
    
    /**
     * Create a new collection of dates.
     *
     * @param Zend_Date $startDate      The lowsest allowed value
     * @param Zend_Date $endDate        The highest allowed valueed
     */
    public function __construct($minDate, $maxDate = null)
    {
        $this->minDate = $minDate;
        $this->maxDate = $maxDate;
    }
    
    /**
     * Adds a date to the Collection. If the date is higher/lower than
     * maxDate/minDate it will not be added.
     *
     * @param Zend_Date|Array $element      A(n array of) Zend_Date object(s)
     */
    public function add($element)
    {
        if (is_array($element)) {
            foreach ($element as $e) {
                $this->add($e);
            }
        } else {
            if ($element->compare($this->minDate) >= 0 && $element->compare($this->maxDate) <= 0) {
                $this->elements[] = $element;
            }
        }
    }
    
    /**
     * Fill the collection with all dates that can be calculated from rrule
     * starting with minDate.
     * If there already are elements in the collection they will be dropped. 
     *
     * @param String $rrule     The rrule that should be parsed
     * @return boolean          TRUE if parsing was successfull,
     *                          FALSE otherwise
     */
    public function applyRrule($rrule)
    {
        // Clear collection
        $this->elements = array();
        // Parse RRule
        $rules = self::parseRrule($rrule);
        // Detect mathod to use for increment
        switch ($rules['FREQ']) {
            case 'YEARLY':
               $method = 'addYear';
            case 'MONTHLY':
                $method = 'addMonth';
                break;
            case 'WEEKLY':
                $method = 'addWeek';
                break;
            case 'DAILY':
                $method = 'addDay';
                break;
            default:
                // Frequence is not supported
                return false;
        }
        $date = new Zend_Date($this->minDate);
        $method = array($date, $method);
        
        // Calculate the dates
        $offset = 0;
        while ($date < $rules['UNTIL']) {
            $date  = call_user_func_array($method, array($rules['INTERVAL']*($offset++)));
            $dates = $this->rruleByXXX($rules, $date);
            $this->add($dates);
        }
        return true;
    }
    
    /**
     * Parse the RRULE of an iCal-file 
     *
     * @param String $rrule     RRULE to parse
     * @return Array            Array containing the parsed rule
     */
    private static function parseRrule($rrule)
    {
        $rrule = explode(';', $rrule);
        $rules = array();
        
        // Needed to translate the Weekdays to a format compatible with Zend_Date
        $translateByDay = array(
            'MO' => 1,
            'TU' => 2,
            'WE' => 3,
            'TH' => 4,
            'FR' => 5,
            'SA' => 6,
            'SU' => 7
        );
        
        foreach ($rrule as $rule) {
            list($name, $value) = explode('=', $rule, 2);
            if ($value == '') {
                continue;
            }
            switch ($name) {
                case 'UNTIL':
                    $value = Phprojekt_Date_Converter::parseIsoDateTime($value);
                    $this->maxDate = $value;
                    break;
                case 'BYDAY':
                    $value = explode(',', $value);
                    for ($i = 0; $i < count($value); $i++) {
                        $value[$i] = $translateByDay[$value[$i]];
                    }
                    break;
                case 'BYMONTH':
                case 'BYHOUR':
                case 'BYMINUTE':
                    $value = explode(',', $value);
                    break;
                case 'INTERVAL':
                    $value = (int)$value;
                    break;
            }
            $rules[$name] = $value;
        }
        return $rules;
    }
        
    /**
     * Calculate all Dates generated by a 'BYXXX' rule.
     *
     * @param array $rules          rrule as generated by self::parseRrule
     * @param Zend_Date $date       The date to start from
     * @return array                Array with all generated Zend_Date objects
     */
    private function rruleByXXX($rules, $date)
    {
        $bys = array(
            'BYMONTH' => 'setMonth',
            'BYWEEKNO' => 'setWeek',
            'BYYEARDAY' => 'setDayOfYear',
            'BYMONTHDAY' => 'setDay',
            'BYDAY' => 'setWeekday',
            'BYHOUR' => 'setHour',
            'BYMINUTE' => 'setMinute',
            'BYSECOND' => 'setSecond'
        );
        $dates = array($date);
        foreach ($bys as $by => $setter) {
            if (isset($rules[$by])) {
                $res = array();
                foreach ($rules[$by] as $value) {
                    foreach ($dates as $date) {
                        $date  = call_user_func_array(array($date, $setter), array($value));
                        $res[] = new Zend_Date($date);
                    }
                }
                $dates = $res;
            }
        }
        
        return $dates;
    }
    
    /**
     * Get the elements of the collection
     *
     * @return Array    Returns all dates (Zend_Date) of the collection as an array
     */
    public function getValues()
    {
        return $this->elements;
    }
    
    /**
     * Removes a series of dates from the collection
     *
     * @param Array $exclude        Array with Zend_Dates that should be removed from
     *                              the collection
     */
    public function filter($exclude)
    {
        for ($dateIndex = 0; $dateIndex < count($this->elements); $dateIndex++) {
            foreach ($exclude as $exDate) {
                if ($exDate->compare($this->elements[$dateIndex]) == 0) {
                    unset($this->elements[$dateIndex]);
                    continue;
                }
            }
        }
    }

}