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
 * Phprojekt Class for creation of column in PDF table row.
 */
class Phprojekt_Pdf_Table_Column
{
    /**
     * Height of the column.
     *
     * @var int
     */
    protected $_height;

    /**
     * Width of the column.
     *
     * @var int
     */
    protected $_width;

    /**
     * Text in the column.
     *
     * @var unknown_type
     */
    protected $_text;

    /**
     * Encoding of the text.
     *
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Align for the text in cell.
     *
     * @var string
     */
    protected $_align = 'left';

    /**
     * Set text for the column.
     *
     * @param string $text     Text to use.
     * @param string $encoding Encoding of the text string $str, UTF-8 by default.
     *
     * @return Phprojekt_Pdf_Table_Column An instance of Phprojekt_Pdf_Table_Column.
     */
    public function setText($text, $encoding = 'UTF-8')
    {
        $this->_text     = $text;
        $this->_encoding = $encoding;

        return $this;
    }

    /**
     * Get protected value.
     *
     * @return integer Current value.
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Set protected value.
     *
     * @param integer $width Width to set.
     *
     * @return Phprojekt_Pdf_Table_Column An instance of Phprojekt_Pdf_Table_Column.
     */
    public function setWidth($width)
    {
        $this->_width = $width;

        return $this;
    }

    /**
     * Get protected value.
     *
     * @return integer Current value.
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Set protected value.
     *
     * @param string $align Value to use.
     *
     * @return void
     */
    public function setAlignment($align)
    {
        if (!is_null($align)) {
            $this->_align = $align;
        }
    }

    /**
     * Insert column in the page.
     *
     * @param Phprojekt_Pdf_Page $page Current page.
     * @param integer            $x    Start position x.
     * @param integer            $y    Start position y.
     *
     * @return void
     */
    public function render($page, $x, $y)
    {
        $fontSize = $page->getFontSize();
        $size     = $page->drawVariableText($this->_text, $x, $page->getHeight() - $y + $fontSize, $this->_width,
            $this->_align, $this->_encoding);
        $this->_height = $size['height'] + $page->tablePadding;
        $this->_width  = $this->_width;
    }

    /**
     * Test function.
     *
     * @param Phprojekt_Pdf_Page $page Current page.
     * @param integer            $x    Start position x.
     * @param integer            $y    Start position y.
     *
     * @return void
     */
    public function testRender($page, $x, $y)
    {
        $fontSize = $page->getFontSize();
        $size     = $page->getVariableText($this->_text, $x, $page->getHeight() - $y + $fontSize,
            $this->_width, $this->_encoding);
        $this->_height = $size['height'] + $page->tablePadding;
        $this->_width  = $this->_width;
    }

    /**
     * Add border.
     *
     * @param Phprojekt_Pdf_Page $page     Current page.
     * @param integer            $x        Start position x.
     * @param integer            $y        Start position y.
     * @param integer            $height   Current height.
     * @param boolean            $isHeader True if is a header.
     *
     * @return void
     */
    public function renderBorder($page, $x, $y, $height, $isHeader = false)
    {
        if ($isHeader) {
            $grayColor  = new Zend_Pdf_Color_GrayScale(Phprojekt_Pdf_Page::HEADER_GRAY_LEVEL);
            $blackColor = new Zend_Pdf_Color_GrayScale(0);
            $page->setFillColor($grayColor);
            $page->drawRectangle($x, $y, $x + $this->_width, $y - $height, Zend_Pdf_Page::SHAPE_DRAW_FILL);
            $page->setFillColor($blackColor);
        }
        $page->drawRectangle($x, $y, $x + $this->_width, $y - $height, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
    }
}
