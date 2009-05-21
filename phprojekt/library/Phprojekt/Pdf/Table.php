<?php
/**
  * Phprojekt Class for PFD table creation
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
 * @author     Alesia Khizhko <alesia.khizhko@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
  * Phprojekt Class for PFD table creation
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Alesia Khizhko <alesia.khizhko@mayflower.de>
 */
class Phprojekt_Pdf_Table
{
    /**
     * Start position X of the table in the page
     *
     * @var int
     */
    public $x;

    /**
     * Start position Y of the table in the page
     *
     * @var int
     */
    public $y;

    /**
     * Table width
     *
     * @var int
     */
    public $width;

    /**
     * Border site of the table
     *
     * @var float
     */
    public $border = 0.5;

    /**
     * PDF page object
     *
     * @var Phprojekt_Pdf_Page
     */
    public $page;

    /**
     * Array of PDF pages in case table couldn't fit to one page
     *
     * @var array
     */
    protected $_pages = array();

    /**
     * List of row in the table
     *
     * @var array
     */
    protected $_rows = array();

    /**
     * Contructor
     *
     * @param Phprojekt_Pdf_Page $page
     * @param int                $x    Position in the page
     * @param int                $y    Position in the page
     */
    function __construct($page, $x, $y)
    {
        $this->page = $page;
        $this->x    = $x;
        $this->y    = $y;
    }

    /**
     * Add row to the table
     *
     * @param Phprojekt_Pdf_Table_Row $row
     *
     * @return void
     */
    public function addRow(Phprojekt_Pdf_Table_Row $row)
    {
        $result = $this->checkAndSplitRow($row);
        foreach ($result as $item) {
            $this->_rows[] = $item;
        }
    }

    /**
     * Parse and draw table on the page
     *
     * @return array List of pages with table
     */
    public function render()
    {
        $y = $this->page->getHeight() - $this->y;
        foreach ($this->_rows as $row) {
            if ($y - $row->testRender($this->page, $this->x, $y) < 0) {
                $font     = $this->page->getFont();
                $fontSize = $this->page->getFontSize();

                $this->page = new Phprojekt_Pdf_Page($this->page);
                $this->page->setFont($font, $fontSize);
                $this->page->setLineWidth($this->border);
                $this->_pages[] = $this->page;
                $y              = $this->page->getHeight();
            }

            $row->render($this->page, $this->x, $y);
            $y -= $row->getHeight();
        }

        $tmpHeight             = $this->page->getHeight() - $y;
        $this->page->freeLineY = $tmpHeight + $this->page->getFontSize() * Phprojekt_Pdf_Page::RATE_FONT_IN_PIX;

        return $this->_pages;
    }

    /**
     * Enter description here...
     *
     * @param Phprojekt_Pdf_Table_Row $row
     *
     * @return array
     */
    public function checkAndSplitRow(Phprojekt_Pdf_Table_Row $row)
    {
        $availablePlace = $this->page->getHeight() - $this->page->freeLineY;
        if ($row->getHeight() < $availablePlace) {
            return array($row);
        } else {
            return array();
        }
    }
}
