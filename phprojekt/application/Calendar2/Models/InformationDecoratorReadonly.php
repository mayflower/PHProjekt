<?php
/**
 * Wrapper around Calendar2_Models_CalendarInformation that sets all
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
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Wrapper around Calendar2_Models_CalendarInformation that sets all
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Calendar2_Models_InformationDecoratorReadonly extends Phprojekt_ModelInformation_Default
{
    protected $_wrapee;

    public function __construct(Calendar2_Models_CalendarInformation $calendarInformation)
    {
        $this->_wrapee = $calendarInformation;
    }

    /**
     * Return an array of field information.
     *
     * @param integer $ordering An ordering constant.
     *
     * @return array Array with fields definitions.
     */
    public function getFieldDefinition(
            $ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $meta = $this->_wrapee->getFieldDefinition($ordering);

        // If ownerId != currentUser then set all fields except status readonly
        foreach (array_keys($meta) as $key) {
            if ('confirmationStatus' != $meta[$key]['key']) {
                $meta[$key]['readOnly'] = 1;
            }
        }

        return $meta;
    }
}
