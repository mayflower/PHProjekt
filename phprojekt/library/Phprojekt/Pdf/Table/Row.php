<?php
/**
 * Phprojekt Class for creation of row in PDF table
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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    $Id$
 * @author     Alesia Khizhko <alesia.khizhko@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Phprojekt Class for creation of row in PDF table
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    LGPL v3 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Alesia Khizhko <alesia.khizhko@mayflower.de>
 */
class Phprojekt_Pdf_Table_Row
{
    /**
     * Width of row
     *
     * @var int
     */
    protected $_width;

    /**
     * Height of row
     *
     * @var int
     */
    protected $_height;

    /**
     * Array of columns
     *
     * @var array
     */
    protected $_cols = array();

    /**
     * Show if current row is header or not
     *
     * @var bool
     */
    protected $_isHeader = false;

    /**
     * Add column to the list
     *
     * @param Phprojekt_Pdf_Table_Column $col
     *
     * @return void
     */
    public function addColumn(Phprojekt_Pdf_Table_Column $col)
    {
        $this->_cols[] = $col;
    }

    /**
     * Render row
     *
     * @param Phprojekt_Pdf_Page $page
     * @param int                $x    Start position x
     * @param int                $y    Start position y
     *
     * @return void
     */
    public function render($page, $x, $y)
    {
        $tmpX      = $x;
        $maxHeight = 0;
        $this->renderBorder($page, $tmpX, $y);
        foreach ($this->_cols as $col) {
            $col->render($page, $x, $y);
            $height = $col->getHeight();
            if ($height > $maxHeight) {
                $maxHeight = $height;
            }
            $x += $col->getWidth();
        }
        $this->_height = $maxHeight;
    }

    /**
     * Test function
     * @todo remove or rename
     *
     * @param Phprojekt_Pdf_Page $page
     * @param int                $x
     * @param int                $y
     *
     * @return int
     */
    public function testRender($page, $x, $y)
    {
        $this->_height = 0;
        foreach ($this->_cols as $col) {
            $col->testRender($page, $x, $y);
            $height = $col->getHeight();
            if ($height > $this->_height) {
                $this->_height = $height;
            }
            $x += $col->getWidth();
        }

        return $this->_height;
    }

    /**
     * Add border to the row
     *
     * @param Phprojekt_Pdf_Page $page
     * @param int                $x
     * @param int                $y
     *
     * @return void
     */
    public function renderBorder($page, $x, $y)
    {
        foreach ($this->_cols as $col) {
            $col->renderBorder($page, $x, $y, $this->testRender($page, $x, $y), $this->_isHeader);
            $x += $col->getWidth();
        }
    }

    /**
     * Get protected value
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Set true value of the protected property for the current row
     *
     * @return void
     */
    public function setHeader()
    {
        $this->_isHeader = true;
    }
}
