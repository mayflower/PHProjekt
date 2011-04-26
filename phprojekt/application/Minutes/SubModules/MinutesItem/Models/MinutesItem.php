<?php
/**
 * MinutesItem model class.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */

/**
 * MinutesItem model class.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */
class Minutes_SubModules_MinutesItem_Models_MinutesItem extends Phprojekt_Item_Abstract
{
    /**
     * Field for display in the search results.
     *
     * @var string
     */
    public $searchSecondDisplayField = 'comment';

    /**
     * Configuration to use or not the history class.
     *
     * @var boolean
     */
    public $useHistory = true;

    /**
     * Configuration to use or not the search class.
     *
     * @var boolean
     */
    public $useSearch = false;

    /**
     * Configuration to use or not the right class.
     *
     * @var boolean
     */
    public $useRights = true;

    /**
     * The Minutes object this item is related to.
     *
     * @var Phprojekt_Item_Abstract
     */
    protected $_minutes = null;

    /**
     * Initial state of the data after find().
     *
     * @var array
     */
    protected $_lastSortOrder = null;

    /**
     * Returns the Model information manager.
     *
     * @return Phprojekt_ModelInformation_Interface An instance of a Phprojekt_ModelInformation_Interface.
     */
    public function getInformation()
    {
        if (null == $this->_informationManager) {
            $this->_informationManager = Phprojekt_Loader::getModel('Minutes_SubModules_MinutesItem', 'Information',
                $this, $this->_dbConfig);
        }

        return $this->_informationManager;
    }

    /**
     * Extension of getRights() for use the parent minute getRights();
     *
     * @return array Array with rights.
     */
    public function getRights()
    {
        return $this->getParent()->getRights();
    }

    /**
     * Extension of getMultipleRights() for use the parent minute getMultipleRights();
     *
     * @param array $ids Array with various item IDs.
     *
     * @return array Array with rights.
     */
    public function getMultipleRights($ids)
    {
        return $this->getParent()->getMultipleRights($ids);
    }

    /**
     * Extension of getUsersRights() for use the parent minute getUsersRights();
     *
     * @return array Array with rights.
     */
    public function getUsersRights()
    {
        return $this->getParent()->getUsersRights();
    }

    /**
     * Set the parent related minutes object.
     *
     * @param integer $minutesId Parent minute ID.
     *
     * @return void
     */
    public function setParent($minutesId)
    {
        $this->_minutes = Phprojekt_Loader::getModel('Minutes', 'Minutes')->find($minutesId);
    }

    /**
     * Return the parent.
     *
     * @return Minutes_Models_MinutesItem An instance of Minutes_Models_MinutesItem.
     */
    public function getParent()
    {
        return $this->_minutes;
    }

    /**
     * Finds a record with current criteria key and populates the object with its data.
     *
     * Save the last sortOrder for detect changes made after calling find().
     *
     * @param mixed Optional criteria. Can be primary key value or array of field=>value pairs.
     *
     * @return Minutes_Models_MinutesItem An instance of Minutes_Models_MinutesItem.
     */
    public function find($criteria = null)
    {
        $res = parent::find($criteria);

        // Define the parent if is not set
        if (null === $this->getParent()) {
            $res->setParent($res->projectId);
        }

        // Make a backup of the initial data to compare against in save() method
        $this->_lastSortOrder = $this->_data['sortOrder'];

        return $res;
    }

    /**
     * Customized version to calculate the status of a minutes item regardless of its saved database entry.
     *
     * @param string|array $where  Where clause.
     * @param string|array $order  Order by.
     * @param string|array $count  Limit query.
     * @param string|array $offset Query offset.
     * @param string       $select The comma-separated columns of the joined columns.
     * @param string       $join   The join statements.
     *
     * @return Zend_Db_Table_Rowset The rowset with the results.
     */
    public function fetchAll($where = null, $order = array('sort_order ASC', 'id DESC'), $count = null, $offset = null,
        $select = null, $join = null)
    {
        $result = parent::fetchAll($where, $order, $count, $offset, $select, $join);

        // Integrate numbering
        $topicCount    = 0;
        $topicSubCount = 0;
        foreach ($result as $item) {
            if (1 == $item->topicType) {
                $topicCount++;
                $topicSubCount = -1;
            }
            $topicSubCount++;
            $item->topicId = (1 == $item->topicType ? sprintf('%d', $topicCount)
                : sprintf('%d.%d', $topicCount, $topicSubCount));
        }

        return $result;
    }

    /**
     * Save is handled by parent.
     *
     * @return boolean True for a sucessful save.
     */
    public function save()
    {
        $db = $this->getAdapter();

        if (trim($this->sortOrder) == '' || is_null($this->sortOrder) || !$this->sortOrder) {
            // We don't have a sort order yet, most probably a brand-new record.
            // Detect highest available sort order up until now and use next-higher number.
            $sql = 'SELECT MAX(' . $db->quoteIdentifier('sort_order') . ') FROM '
                . $db->quoteIdentifier($this->getTableName()) . ' WHERE '.$db->quoteIdentifier('minutes_id').' = ?';
            $result  = $db->fetchCol($sql, $this->getParent()->id);
            $maxSort = $result[0];

            if (!$maxSort || $maxSort < 0) {
                $maxSort = 0;
            }
            $this->sortOrder = $maxSort + 1;
        } else if (is_numeric($this->sortOrder) && ($this->sortOrder > 0)) {
            if ((!isset($this->_lastSortOrder))
                || (isset($this->_lastSortOrder) && ($this->_lastSortOrder != $this->sortOrder))) {
                // A sort order was given and differs from the initial value. We need to increment
                // all sort order values equal or above the new value by one, and then update this
                // record with the new value. That should ensure order value consistency.
                $data  = array('sort_order' => new Zend_Db_Expr($this->_db->quoteIdentifier('sort_order') . ' + 1'));
                $where = sprintf('%s = %d and %s >= %d', $this->_db->quoteIdentifier('minutes_id'),
                    $this->getParent()->id, $this->_db->quoteIdentifier('sort_order'), $this->sortOrder);
                $this->update($data, $where);
            }
        }

        return parent::save();
    }

    /**
     * Define a getter for the "display" field.
     *
     * @return integer Always 0.
     */
    public function getTopicId()
    {
        return 0;
    }

    /**
     * Define a setter for the "display" field.
     *
     * @param integer $value The value.
     *
     * @return void
     */
    public function setTopicId($value)
    {
        $this->topicId = $value;
    }

    /**
     * Return the display depend on the topicType.
     *
     * @return string String with data.
     */
    public function getDisplay()
    {
        $translate = Phprojekt::getInstance()->getTranslate();

        switch ($this->topicType) {
            case 1: // Topic
            case 2: // Statement
            case 4: // Decision
                $form = "%1\$s\n%2\$s";
                break;
            case 3: // Todo
                $form = "%1\$s\n%2\$s\n" . $translate->translate('Who') . ": %4\$s\n"
                    . $translate->translate('Date') . ": %3\$s";
                break;
            case 5: // Date
                $form = "%1\$s\n%2\$s\n" . $translate->translate('Date') . ": %3\$s";
                break;
            default:
                $form = $translate->translate('Undefined topicType');
                break;
        }

        return sprintf($form, $this->title, $this->comment, $this->topicDate,
            $this->information->getUserName($this->userId));
    }
}
