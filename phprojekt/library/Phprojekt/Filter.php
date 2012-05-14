<?php
/**
 * Manage the where clause for filter records.
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
 * @package    Phprojekt
 * @subpackage Filter
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
* Manage the where clause for filter records.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Filter
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Filter
{
    /**
     * The where clause after apply the filters.
     *
     * @var string
     */
    protected $_userWhere = null;

    /**
     * The internal where clause used by the Model.
     *
     * @var string
     */
    protected $_where = null;

    /**
     * Metadata info of the fields.
     *
     * @var array
     */
    protected $_info = null;

    /**
     * Metadata info of the fields.
     *
     * @var array
     */
    protected $_cols = null;

    /**
     * An active record for work with the filters.
     *
     * @var Phprojekt_ActiveRecord_Abstract
     */
    protected $_record = null;

    /**
     * Initialize a new user filter on an active record.
     *
     * @param Phprojekt_ActiveRecord_Abstract $record An active record.
     * @param string                          $where  The internal where clause.
     *
     * @return void
     */
    public function __construct(Phprojekt_ActiveRecord_Abstract $record, $where = null)
    {
        $this->_info   = $record->info();
        $this->_cols   = $this->_info['cols'];
        $this->_where  = $where;
        $this->_record = $record;
    }

    /**
     * Make a where clause.
     *
     * @param string $field    Field for filter.
     * @param string $rule     Rule for apply the filter.
     * @param string $value    Value used for filter.
     * @param string $operator AND/OR operator for concatenate the where clause.
     *
     * @return void
     */
    public function addFilter($field, $rule, $value, $operator = 'AND')
    {
        $identifier = Phprojekt_ActiveRecord_Abstract::convertVarToSql($field);

        if (in_array($identifier, $this->_cols)) {
            $rule = $this->_convertRule($field, $identifier, $rule, $value);

            if (null !== $this->_userWhere) {
                $this->_userWhere .= $operator . " ";
            }
            $this->_userWhere .= sprintf('(%s) ', $rule);
        }
    }

    /**
     * Return the where clause for use in the fetchAll.
     *
     * @return string Where clause.
     */
    public function getWhere()
    {
        $return = null;

        if (null !== $this->_where) {
            $return .= "(" . $this->_where . ")";
        }

        if (null !== $this->_userWhere) {
            if (null === $return) {
                $return .= "( " . $this->_userWhere . " )";
            } else {
                $return .= " AND ( " . $this->_userWhere . " )";
            }
        }

        return $return;
    }

    /**
     * Convert the rule and value into a real where clause.
     *
     * @param string $field      Field for filter.
     * @param string $identifier Converted field for filter.
     * @param string $rule       Rule for apply the filter.
     * @param string $keyword    Value used for filter.
     *
     * @return string Where clause.
     */
    private function _convertRule($field, $identifier, $rule, $keyword)
    {
        // Sanitize values
        if ($this->_info['metadata'][$identifier]['DATA_TYPE'] == 'time') {
            // Moving the value to UTC
            $identifier = $this->_record->getTableName() . '.' . $identifier;
            $identifier = Phprojekt::getInstance()->getDb()->quoteIdentifier($identifier);
            $value      = Cleaner::sanitize('time', $keyword);
            $k          = date("H:i:s", Phprojekt_Converter_Time::userToUtc($value));
            //$identifier = 'TIME(' . $identifier . ')';
        } else if ($this->_info['metadata'][$identifier]['DATA_TYPE'] == 'datetime') {
            $identifier = $this->_record->getTableName() . '.' . $identifier;
            $identifier = Phprojekt::getInstance()->getDb()->quoteIdentifier($identifier);
            if (strstr($keyword, '-')) {
                // Use it as date
                $k          = Cleaner::sanitize('date', $keyword);
                $identifier = 'DATE(' . $identifier . ')';
            } else if (strstr($keyword, ':')) {
                // Use it as time
                $value      = Cleaner::sanitize('time', $keyword);
                $k          = date("H:i:s", Phprojekt_Converter_Time::userToUtc($value));
                $identifier = 'TIME(' . $identifier . ')';
            } else {
                // Use it as datetime
                $value = Cleaner::sanitize('timestamp', $keyword);
                $k     = date("Y-m-d H:i:s", Phprojekt_Converter_Time::userToUtc($value));
            }
        } else {
            $keyword    = mb_strtolower($keyword, 'UTF-8');
            $k          = $keyword;
            $identifier = $this->_record->getTableName() . '.' . $identifier;
            $identifier = Phprojekt::getInstance()->getDb()->quoteIdentifier($identifier);
        }

        switch ($rule) {
            case 'equal':
                $w = $identifier . ' = ? ';
                break;
            case 'notEqual':
                $w = $identifier . ' != ? ';
                break;
            case 'major':
                $w = $identifier . ' > ? ';
                break;
            case 'majorEqual':
                $w = $identifier . ' >= ? ';
                break;
            case 'minor':
                $w = $identifier . ' < ? ';
                break;
            case 'minorEqual':
                $w = $identifier . ' <= ? ';
                break;
            case 'begins':
                $w = $identifier . ' LIKE ? ';
                $k = $keyword . '%';
                break;
            case 'ends':
                $w = $identifier . ' LIKE ? ';
                $k = '%' . $keyword;
                break;
            case 'notLike':
                $w = $identifier . ' NOT LIKE ? ';
                $k = '%' . $keyword . '%';
                break;
            case 'like':
            default:
                $w = $identifier . ' LIKE ? ';
                $k = '%' . $keyword . '%';
        }

        return Phprojekt::getInstance()->getDb()->quoteInto($w, $k);
    }
}
