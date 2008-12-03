<?php
/**
 * Convert a model into a json structure.
 * This is usually done by a controller to send data to the
 * client
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id: 
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
class Timecard_Models_TimeprojInformation extends EmptyIterator implements Phprojekt_ModelInformation_Interface
{
    /**
     * Return an array of field information.
     *
     * @param string $ordering Sort
     *
     * @return array
     */
    public function getFieldDefinition($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $converted = array();
        $translate = Zend_Registry::get('translate');

        // date
        $data = array();
        $data['key']      = 'date';
        $data['label']    = $translate->translate('date');
        $data['type']     = 'date';
        $data['hint']     = $translate->translate('date');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = true;
        $data['tab']      = 1;
                
        $converted[] = $data;
        
        // projectId
        $data = array();
        $data['key']      = 'projectId';
        $data['label']    = $translate->translate('project');
        $data['type']     = 'time';
        $data['hint']     = $translate->translate('project');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $data['range']    = array();
        $data['type']     = 'selectbox';        
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree->setup();
        foreach ($tree as $node) {
            $key   = $node->id;
            $value = str_repeat('....', $node->getDepth()) . $node->title;
            $data['range'][] = array('id'   => $key,
                                     'name' => $value);
        }
        $data['required'] = true;
        $data['readOnly'] = true;
        $data['tab']      = 1;
                
        $converted[] = $data;
                
        // notes
        $data = array();
        $data['key']      = 'notes';
        $data['label']    = $translate->translate('notes');
        $data['type']     = 'textarea';
        $data['hint']     = $translate->translate('notes');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = true;
        $data['tab']      = 1;
                
        $converted[] = $data;

        // amount 
        $data = array();
        $data['key']      = 'amount';
        $data['label']    = $translate->translate('amount');
        $data['type']     = 'time';
        $data['hint']     = $translate->translate('amount');
        $data['order']    = 0;
        $data['position'] = 4;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = true;
        $data['tab']      = 1;
                
        $converted[] = $data;
    
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
        $result = array();
        return $result;
    }
}