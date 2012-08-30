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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * A DBUnit test case framework.
 */
abstract class DatabaseTest extends PHPUnit_Extensions_Database_TestCase {
    public function setUp () {
        parent::setUp();
        Phprojekt::getInstance();
        Zend_Db_Table_Abstract::getDefaultMetadataCache()->clean();
    }

    protected function getConnection() {
        /* @todo read from settings later */

        return $this->createDefaultDBConnection(Phprojekt::getInstance()->getDb()->getConnection());
    }
}

