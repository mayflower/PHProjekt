<?php
/**
 * Test framework to test databases using DBUnit.
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
 * @package    UnitTests
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */

/**
 * A DBUnit test case framework.
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
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

