<?php
/**
 * Tab model class.
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
 * @package    Phprojekt
 * @subpackage Tab
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Tab model class.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Tab
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Tab_Tab extends Phprojekt_Item_Abstract
{
    /**
     * Configuration to use or not the history class.
     *
     * @var boolean
     */
    public $useHistory = false;

    /**
     * Configuration to use or not the search class.
     *
     * @var boolean
     */
    public $useSearch = false;

    /**
     * Configuration to use or not the right class.
     *
     * @var boolean
     */
    public $useRights = false;

    /**
     * Returns the Model information manager.
     *
     * @return Phprojekt_ModelInformation_Interface An instance of a Phprojekt_ModelInformation_Interface.
     */
    public function getInformation()
    {
        if (null == $this->_informationManager) {
            $this->_informationManager = Phprojekt_Loader::getLibraryClass('Phprojekt_Tab_Information');
        }

        return $this->_informationManager;
    }

    /**
     * Delete a Tab.
     * It prevents deletion of Tab 1 -Basic Data-.
     *
     * @return void
     */
    public function delete()
    {
        if ($this->id > 1) {
            parent::delete();
        }
    }
}
