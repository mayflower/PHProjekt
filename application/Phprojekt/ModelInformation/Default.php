<?php
/**
 * A default module that can be feed with a array and provides
 * all necessary methods from that
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007, 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * A default module that can be feed with a array and provides
 * all necessary methods from that
 *
 * @copyright  2007, 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_ModelInformation_Default implements Phprojekt_ModelInformation_Interface
{
    protected $_formFields;
    protected $_listFields;
    protected $_defaultValues = array (
                                'key'      => '',
                                'label'    => '',
                                'type'     => 'string',
                                'hint'     => '',
                                'order'    => 0,
                                'position' => 0,
                                'fieldset' => null,
                                'range'    => '',
                                'required' => false,
                                'right'    => 'write',
                                'readOnly' => false);
    /**
     * Initialize
     *
     * @param array $listFields array with field definitions
     * @param array $formFields array with field definitions
     */
    public function __construct($listFields = null, $formFields = null)
    {
        if (!is_array($formFields) && !is_array($listFields)) {
            $this->setFormFields(array());
            $this->setListFields(array());
        } else if (null === $formFields) {
            $this->setFormFields($listFields);
            $this->setListFields($listFields);
        } else if (null === $listFields) {
            $this->setListFields($formFields);
            $this->setFormFields($formFields);
        } else {
            $this->setFormFields($formFields);
            $this->setListFields($listFields);
        }
    }

    /**
     * Return a sorted array that should be used
     * to display the form view
     *
     * @return array
     */
    public function getFormFields ()
    {
        return $this->_formFields;
    }

    /**
     * Return a sorted array that should be used
     * to display the list view
     *
     * @return array
     */
    public function getListFields ()
    {
        return $this->_listFields;
    }

    /**
     * Sets a fields definitions for the form view
     *
     * @param array $formFields All the field�s data for the form
     *
     * @return void
     */
    public function setFormFields (array $formFields)
    {
        $this->_formFields = array();

        if (!is_array(current($formFields))) {
            $formFields = array($formFields);
        }

        foreach ($formFields as $fields) {
            $this->_formFields[] = array_merge($this->_defaultValues, $fields);
        }
    }

    /**
     * Sets a fields definitions for the list view
     *
     * @param array $listFields All the field�s data for the list
     *
     * @return void
     */
    public function setListFields (array $listFields)
    {
        $this->_listFields = array();

        if (!is_array(current($listFields))) {
            $listFields = array($listFields);
        }

        foreach ($listFields as $fields) {
            $this->_listFields[] = array_merge($this->_defaultValues, $fields);
        }
    }

    /**
     * Returns a the necessary field definitions based on the ordering
     * const that's given
     *
     * @param integer $ordering Type of order
     *
     * @see Phprojekt_ModelInformation_Interface::getFieldDefinition()
     *
     * @return array
     */
    public function getFieldDefinition ($ordering = MODELINFO_ORD_DEFAULT)
    {
        switch ($ordering) {
            case MODELINFO_ORD_FILTER:
            case MODELINFO_ORD_LIST:
                return $this->_listFields;
                break;
            case MODELINFO_ORD_FORM:
                return $this->_formFields;
                break;
        }
    }

    /**
     * Return an array containing all titles
     *
     * @param integer $ordering Type of order
     *
     * @see Phprojekt_ModelInformation_Interface::getTitles()
     *
     * @return array
     */
    public function getTitles ($ordering = MODELINFO_ORD_DEFAULT)
    {
        switch ($ordering) {
            case MODELINFO_ORD_FILTER:
            case MODELINFO_ORD_LIST:
                $list = $this->_listFields;
                break;
            case MODELINFO_ORD_FORM:
                $list = $this->_formFields;
                break;
        }

        $result = array();
        foreach ($list as $definition) {
            $result = $definition['hint'];
        }
        return $result;
    }
}