<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests Default Model class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_DefaultModelDefault_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test valid method
     *
     */
    public function testDefaultModelsDefault()
    {

        $defaultModel = Phprojekt_Loader::getModel('Default','Default');

        $this->assertEquals($defaultModel->valid(), false);
        
        $this->assertEquals($defaultModel->save(), false);
        
        $this->assertEquals($defaultModel->getRights(), array());
        
        $this->assertEquals($defaultModel->recordValidate(), true);
        
        $this->assertEquals($defaultModel->getFieldsForFilter(), array());
        
        $this->assertEquals($defaultModel->find(), null);
        
        $this->assertEquals($defaultModel->fetchAll(), null);
        
        $this->assertEquals($defaultModel->current(), null);
        
        $this->assertEquals($defaultModel->rewind(), null);
        
        $this->assertEquals($defaultModel->next(), null);
        
        $this->assertEquals($defaultModel->getInformation(), null);
        
        $this->assertEquals($defaultModel->key(), null);

    }

}