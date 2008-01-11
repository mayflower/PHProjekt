<?php

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
     * @return unknown
     */
    public function getFormFields ()
    {
        return $this->_formFields;
    }
    
    /**
     * @return unknown
     */
    public function getListFields ()
    {
        return $this->_listFields;
    }
    
    /**
     * @param unknown_type $_formFields
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
     * @param unknown_type $_listFields
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
     * @see Phprojekt_ModelInformation_Interface::getTitles()
     *
     * @param integer $ordering
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
        
        $results = array();
        foreach ($list as $definition) {
            $result = $results['hint'];
        }
        return $results;
    }

}