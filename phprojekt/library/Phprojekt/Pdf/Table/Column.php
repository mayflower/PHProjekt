<?php
/**
 * Phprojekt Class for creation of column in PDF table row
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
 * Phprojekt Class for creation of column in PDF table row
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
class Phprojekt_Pdf_Table_Column
{
    /**
     * Height of the column
     *
     * @var int
     */
    protected $_height;

    /**
     * Width of the column
     *
     * @var int
     */
    protected $_width;

    /**
     * Text in the column
     *
     * @var unknown_type
     */
    protected $_text;
    
    /**
     * Encoding of the text
     * 
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Padding, dafault value is 5
     *
     * @var int
     */
    protected $_padding = 5;

    /**
     * Align for the text in cell
     *
     * @var string
     */
    protected $_align = 'left';

    /**
     * Set text for the column
     *
     * @param string $text
     * @param string $encoding Encoding of the text string $str, UTF-8 by default
     * 
     * @return Phprojekt_Pdf_Table_Column
     */
    public function setText($text, $encoding = 'UTF-8')
    {
        $this->_text = $text;
        $this->_encoding = $encoding;

        return $this;
    }

    /**
     * Get protected value
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Set protected value
     *
     * @param int $width
     *
     * @return int Current value
     */
    public function setWidth($width)
    {
        $this->_width = $width;

        return $this;
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
     * Set protected value
     *
     * @param int $width
     *
     * @return int Current value
     */
    public function setAlignment($align)
    {
        if (!is_null($align)) {
            $this->_align = $align;
        }
    }

    /**
     * Insert column in the page
     *
     * @param Phprojekt_Pdf_Page $page
     * @param int                $x    Start position x
     * @param int                $y    Start position y
     *
     * @return void
     */
    public function render($page, $x, $y)
    {
        $fontSize = $page->getFontSize();
        $size     = $page->drawVariableText($this->_text,
                                            $x + $this->_padding,
                                            $page->getHeight() - $y + $fontSize,
                                            $this->_width - $this->_padding,
                                            $this->_align,
                                            $this->_encoding);
        $this->_height = $size['height'] + $this->_padding;
        $this->_width  = $this->_width + $this->_padding;
    }

    /**
     * Test function
     * @todo remove
     *
     * @param Phprojekt_Pdf_Page $page
     * @param int                $x
     * @param int                $y
     *
     * @return void
     */
    public function testRender($page, $x, $y)
    {
        $fontSize = $page->getFontSize();
        $size     = $page->getVariableText($this->_text, $x + $this->_padding, $page->getHeight() - $y + $fontSize,
            $this->_width - $this->_padding, $this->_encoding);
        $this->_height = $size['height'] + $this->_padding;
        $this->_width  = $this->_width + $this->_padding;
    }

    /**
     * Add border
     *
     * @param Phprojekt_Pdf_Page $page
     * @param int                $x
     * @param int                $y
     * @param int                $height
     * @param boolean            $isHeader
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
