<?php
/**
 * Project Module Controller for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

require_once (PHPR_CORE_PATH . '/Default/Controllers/IndexController.php');

/**
 * Default Project Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Project_IndexController extends IndexController
{
    public function getListData()
    {
        /* Stuff for list View */
        $listData = array(
         '0' => array('Name','Description'),
         '1' => array('Projecto 1','Test<br />a e i o u'),
         '2' => array('Projecto 2','Test2'),
         '3' => array('Projecto 3','Test<br />k a ñ')
        );

        return $listData;
    }

    public function getFormData()
    {
        /* Stuff for form View */
        $formData = array(
            'title' => array(
                'type'      => 'text',
                'showName'  => 'Title',
                'value'     => ''
            ),
            'description' => array(
                'type'      => 'text',
                'showName'  => 'Description',
                'value'     => ''
            ),
            'description2' => array(
                'type'      => 'text',
                'showName'  => 'Description',
                'value'     => ''
            ),
            'description4' => array(
                'type'      => 'text',
                'showName'  => 'Description',
                'value'     => ''
            )

        );
        return $formData;
    }
}
