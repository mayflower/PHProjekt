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
 * Tests Tab Model class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_TabModelTab_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test valid method
     *
     */
    public function testTabModelsTab()
    {

        $tabModel = Phprojekt_Loader::getModel('Tab','Tab');
        
        $expected = Phprojekt_Loader::getModel('Tab','Information');

        $this->assertEquals($tabModel->getInformation(), $expected);
        
        $this->assertEquals($tabModel->getRights(), array());
        
        $this->assertEquals($tabModel->saveRights(), null);
        
        $this->assertEquals($tabModel->recordValidate(), false);
        
        $this->assertEquals($tabModel->getError(), array(0 => array('field'=> 'label', 'message' => 'Is a required field')));
        
        $this->assertEquals($tabModel->__toString(), '');
        
    }

}