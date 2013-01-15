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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Represents a contract
 */
class Timecard_Models_Contract extends Phprojekt_ActiveRecord_Abstract
{

    /**
     * Returns an array of ['contract' => Timecard_Models_Contract, 'start' => DateTime, 'end' => DateTime] arrays,
     * sorted by start.
     */
    public static function fetchByUserAndPeriod(Phprojekt_User_User $user, DateTime $start, DateTime $end)
    {
        $db = Phprojekt::getInstance()->getDb();
        $userContractRels = $db->select()->from('user_contract_relation', array('contract_id', 'start', 'end'))
            ->where('user_id = ?', Phprojekt_Auth::getUserId())
            ->where('start < ?', $end->format('Y-m-d'))
            ->where('end >= ? OR end is NULL', $start->format('Y-m-d'))
            ->order('start')
            ->query()->fetchAll();

        $contractIds = array();
        foreach ($userContractRels as $r) {
            $contractIds[] = $r['contract_id'];
        }

        if (empty($contractIds)) {
            return array();
        }

        $contractsById = array();
        $contract = new self();
        foreach ($contract->fetchAll('id in (' . implode(',', $contractIds) . ')') as $c) {
            $contractsById[$c->id] = $c;
        }

        $ret = array();
        foreach ($userContractRels as $r) {
            $ret[] = array(
                'contract' => $contractsById[$r['contract_id']],
                'start'    => DateTime::createFromFormat('Y-m-d', $r['start']),
                'end'      => DateTime::createFromFormat('Y-m-d', $r['end'])
            );
        }

        return $ret;
    }
}
