<?php
/**
 * Convert a model into a json structure.
 * This is usually done by a controller to send data to the
 * client
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id: Interface.php 635 2008-04-02 19:32:05Z david $
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Convert a model into a json structure.
 * This is usally done by a controller to send data to the client.
 * The Phprojekt_Convert_Json takes care that a apporpriate structure
 * is made from the given model.
 *
 * The fields are hardcore.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Phprojekt_Module_Information extends EmptyIterator implements Phprojekt_ModelInformation_Interface
{
    /**
     * Return an array of field information.
     *
     * @param integer $ordering An ordering constant
     *
     * @return array
     */
    public function getFieldDefinition($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $converted = array();
        $translate = Zend_Registry::get('translate');

        // name
        $data = array();
        $data['key']      = 'name';
        $data['label']    = $translate->translate('name');
        $data['type']     = 'text';
        $data['hint']     = $translate->translate('name');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $converted[] = $data;

        // Internal name
        $data = array();
        $data['key']      = 'internalName';
        $data['label']    = $translate->translate('internalName');
        $data['type']     = 'text';
        $data['hint']     = $translate->translate('internalName');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        if ($ordering == Phprojekt_ModelInformation_Default::ORDERING_FORM) {
            $data['readOnly'] = false;
        } else {
            $data['readOnly'] = true;
        }
        $converted[] = $data;

        // active
        $data = array();
        $data['key']      = 'active';
        $data['label']    = $translate->translate('active');
        $data['type']     = 'selectbox';
        $data['hint']     = $translate->translate('active');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        $data['range'][]    = array('id'   => '0',
                                    'name' => $translate->translate('no'));
        $data['range'][]    = array('id'   => '1',
                                    'name' => $translate->translate('yes'));
        $data['required'] = false;
        $data['readOnly'] = false;

        $converted[] = $data;

        // tabs
        //$data = array();
        //$data['key']      = 'tabs';
        //$data['label']    = $translate->translate('tabs');
        //$data['type']     = 'multipleselectbox';
        //$data['hint']     = $translate->translate('tabs');
        //$data['order']    = 0;
        //$data['position'] = 4;
        //$data['fieldset'] = '';

        //foreach (Phprojekt_Tabs::getTabs() as $tab) {
        //    $data['range'][] = array('id'   => $tab['id'],
        //                             'name' => $tab['label']);
        //}
        //$data['required'] = true;
        //$data['readOnly'] = false;

        //$converted[] = $data;
        return $converted;
    }

    /**
     * Return an array with titles to simplify things
     *
     * @param integer $ordering An ordering constant (ORDERING_DEFAULT, etc)
     *
     * @return array
     */
    public function getTitles($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        return array();
    }
}