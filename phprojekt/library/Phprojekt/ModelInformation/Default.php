<?php
/**
 * A default module that can be feed with a array and provides
 * all necessary methods from that
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * A default module that can be feed with a array and provides
 * all necessary methods from that
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_ModelInformation_Default implements Phprojekt_ModelInformation_Interface
{
    /**
     * The fields given by the ModelInformation interface
     * are used by a list view and therefore ordered in that way
     */
    const ORDERING_LIST = 1;

    /**
     * The fields given by the ModelInformation interface
     * are used by a form and therefore ordered in that way
     */
    const ORDERING_FORM = 2;

    /**
     * The fields given by the ModelInformation interface
     * are used by a filter and therefore ordered in that way
     */
    const ORDERING_FILTER = 3;

    /**
     * The fields given by the ModelInformation interface
     * are used by something undeclared, therefore we must use a
     * default value.
     */
    const ORDERING_DEFAULT = Phprojekt_ModelInformation_Default::ORDERING_LIST;

    /**
     * Array that contains the form field values
     *
     * @var array
     */
    protected $_formFields;

    /**
     * Array that contains the list field values
     *
     * @var array
     */
    protected $_listFields;

    /**
     * An array that defines the default values used when
     * setting the form or list fields if they are not given
     *
     * @var array
     */
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
    public function getFormFields()
    {
        return $this->_formFields;
    }

    /**
     * Return a sorted array that should be used
     * to display the list view
     *
     * @return array
     */
    public function getListFields()
    {
        return $this->_listFields;
    }

    /**
     * Sets a fields definitions for the form view
     *
     * @param array $formFields All the field's data for the form
     *
     * @return void
     */
    public function setFormFields(array $formFields)
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
     * @param array $listFields All the field's data for the list
     *
     * @return void
     */
    public function setListFields(array $listFields)
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
    public function getFieldDefinition($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $definition = null;
        switch ($ordering) {
            case Phprojekt_ModelInformation_Default::ORDERING_FILTER:
            case Phprojekt_ModelInformation_Default::ORDERING_LIST:
                $definition = $this->_listFields;
                break;
            case Phprojekt_ModelInformation_Default::ORDERING_FORM:
                $definition = $this->_formFields;
                break;
        }
        return $definition;
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
    public function getTitles($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        switch ($ordering) {
            case Phprojekt_ModelInformation_Default::ORDERING_FILTER:
            case Phprojekt_ModelInformation_Default::ORDERING_LIST:
                $list = $this->_listFields;
                break;
            case Phprojekt_ModelInformation_Default::ORDERING_FORM:
                $list = $this->_formFields;
                break;
        }

        $result = array();
        foreach ($list as $definition) {
            $result[] = $definition['label'];
        }
        return $result;
    }
}
