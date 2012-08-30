<?php
/**
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
 */

/**
 * Phprojekt Class for PFD table creation.
 */
class Phprojekt_Pdf_Table
{
    /**
     * Start position X of the table in the page.
     *
     * @var integer
     */
    public $x;

    /**
     * Start position Y of the table in the page.
     *
     * @var integer
     */
    public $y;

    /**
     * Table width.
     *
     * @var integer
     */
    public $width;

    /**
     * Border site of the table.
     *
     * @var float
     */
    public $border = 0.5;

    /**
     * PDF page object.
     *
     * @var Phprojekt_Pdf_Page
     */
    public $page;

    /**
     * Array of PDF pages in case table couldn't fit to one page.
     *
     * @var array
     */
    protected $_pages = array();

    /**
     * List of row in the table.
     *
     * @var array
     */
    protected $_rows = array();

    /**
     * Contructor.
     *
     * @param Phprojekt_Pdf_Page $page
     * @param integer            $x    Position in the page.
     * @param integer            $y    Position in the page.
     *
     * @return void
     */
    function __construct($page, $x, $y)
    {
        $this->page = $page;
        $this->x    = $x;
        $this->y    = $y;
    }

    /**
     * Add row to the table.
     *
     * @param Phprojekt_Pdf_Table_Row $row The row to add.
     *
     * @return void
     */
    public function addRow(Phprojekt_Pdf_Table_Row $row)
    {
        $this->_rows[] = $row;
    }

    /**
     * Parse and draw table on the page.
     *
     * @return array List of pages with table.
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

                $this->_pages[] = $this->page;
                $y              = $this->page->getHeight() - $this->page->borderTop;
            }
            $row->render($this->page, $this->x, $y);
            $y -= $row->getHeight();
        }
        $positionOfTheLastRow  = $this->page->getHeight() - $y;
        $rowHeight             = $this->page->getFontSize() * $this->page->lineHeight;
        $this->page->freeLineY = $positionOfTheLastRow + $rowHeight;

        return $this->_pages;
    }
}
