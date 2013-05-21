<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2013 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

class Timecard_Models_Vacation {
    public static function getByPeriod(DateTime $start = null, DateTime $end = null)
    {
        $select = Phprojekt::getInstance()->getDb()->select()
            ->from('vacation')
            ->where('user_id = ?', Phprojekt_Auth_Proxy::getEffectiveUserId());

        if (!is_null($start)) {
            $select->where('end >= ? OR end is NULL', $start->format('Y-m-d'));
        }

        if (!is_null($end)) {
            $select->where('start < ?', $end->format('Y-m-d'));
        }

        return array_map(function($e) {
            $e['start'] = new DateTime($e['start']);
            $e['start']->setTime(0, 0, 0);
            $e['end'] = new DateTime($e['end']);
            $e['end']->setTime(0, 0, 0);
            return $e;
        }, $select->query()->fetchAll());
    }
}