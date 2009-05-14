<?php
/**
 * Phprojekt Class for PFD creation
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

/** Zend_Pdf */
require_once 'Zend/Pdf.php';

/**
 * Phprojekt Class for PFD creation
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
class Phprojekt_Pdf_Page extends Zend_Pdf_Page
{
    const RATE_FONT_IN_PIX = 1.2;

    const DEFAULT_FONT_SIZE = 10;

    /**
     * Default position X of the next element in the page
     *
     * @var float
     */
    public $freeLineX = 30;

    /**
     * Default position Y of the next element in the page
     *
     * @var float
     */
    public $freeLineY = 30;

    /**
     * Function inserts table with given rows to pdf pages
     *
     * @param array $tableInfo specifies table for the PDF
     *                 E.g. array(
     *                         'type' =>'table',
     *                         'startX'=> 35,
     *                         'startY'=> 60,
     *                         'rows' => array(
     *                              array(array('text' => 'first column', 'width' => 150),
     *                                    array('text' => 'second column', 'width' => 100),
     *                              ),
     *                              array(...),
     *                          )
     *                      ),
     *
     * @return array of Phprojekt_Pdf_Page
     */
    public function addTable($tableInfo, $currentPage)
    {
        if (!isset($tableInfo['rows'])) {
            throw new Exception("Missing data");
        }

        if (isset($tableInfo['fontSize'])) {
            $tmp = $tableInfo['fontSize'] - $currentPage->getFontSize();

            $currentPage->freeLineY += $tmp * self::RATE_FONT_IN_PIX;
            $currentPage->setFont($this->getFont(), $tableInfo['fontSize']);
        }

        $startX = isset($tableInfo['startX']) ? $tableInfo['startX'] : $this->freeLineX;
        $startY = isset($tableInfo['startY']) ? $tableInfo['startY'] : $this->freeLineY;

        if (empty($tableInfo['rows']) || !is_array($tableInfo['rows'])) {
            throw new Exception("Rows are empty");
        }

        if (isset($tableInfo['lineWidth'])) {
            $currentPage->setLineWidth($tableInfo['lineWidth']);
        }

        // Data is correct
        $table = new PHProjekt_Pdf_Table($currentPage, $startX, $startY);
        foreach ($tableInfo['rows'] as $row) {
            $tableRow = new Phprojekt_Pdf_Table_Row();
            foreach ($row as $cell) {
                // TODO: add validation for column data
                $column    = new PHProjekt_Pdf_Table_Column();
                $alignment = !empty($cell['align']) ? $cell['align'] : null;
                $column->setWidth($cell['width'])->setText($cell['text'])->setAlignment($alignment);
                $tableRow->addColumn($column);
            }
            $table->addRow($tableRow);
        }

        $result          = array($currentPage);
        $additionalPages = $table->render();
        return !empty($additionalPages) ? $result + $additionalPages : $result;
    }

    /**
     * Function inserts freetext into page
     *
     * @param array $freetextInfo specifies free text for the PDF
     *                 E.g. array(
     *                         'type' =>'freetext',
     *                         'startX'=> 35,
     *                         'startY'=> 60,
     *                         'lines' => array(
     *                              'first line',
     *                              'second line',
     *                          ),
     *                          'fontSize' => 10,
     *                      ),
     *
     * @return Phprojekt_Pdf_Page
     */
    public function addFreetext($freetextInfo, $currentPage)
    {
        if (!isset($freetextInfo['lines'])) {
            throw new Exception("Missing data");
        }

        // Change font size
        if (isset($freetextInfo['fontSize'])) {
            $tmp = $freetextInfo['fontSize'] - $currentPage->getFontSize();

            $currentPage->freeLineY += $tmp * self::RATE_FONT_IN_PIX;
            $currentPage->setFont($this->getFont(), $freetextInfo['fontSize']);
        }

        $startX = isset($freetextInfo['startX']) ? $freetextInfo['startX'] : $this->freeLineX;
        $startY = isset($freetextInfo['startY']) ? $freetextInfo['startY'] : $this->freeLineY;

        if (isset($freetextInfo['lineWidth'])) {
            $currentPage->setLineWidth($freetextInfo['lineWidth']);
        }

        $currentPage->drawMultilineText($freetextInfo['lines'], $startX, $startY);

        return $currentPage;
    }

    /**
     * Function inserts info box into page
     *
     * @param array $infoboxInfo specifies info box for the PDF
     *            E.g. array( 'type' =>'infoBox',
     *                         'startX'=> 35,
     *                         'startY'=> 100,
     *                         'lines' => array('line1', 'line2'),
     *                         'width' => 100,
     *                         'height' => 100,
     *                         'lineWidth' => (optional) 0.5,
     *                         'fontSize' => 10,
     *                         'header' => 'Header'),
     *
     * @return Phprojekt_Pdf_Page
     */
    public function addInfoBox($infoboxInfo, $currentPage)
    {
        if (!isset($infoboxInfo['lines'])) {
            throw new Exception("Missing data");
        }

        // Change font size
        if (isset($infoboxInfo['fontSize'])) {
            $tmp = $infoboxInfo['fontSize'] - $currentPage->getFontSize();

            $currentPage->freeLineY += $tmp * self::RATE_FONT_IN_PIX;
            $currentPage->setFont($this->getFont(), $infoboxInfo['fontSize']);
        }

        $startX = isset($infoboxInfo['startX']) ? $infoboxInfo['startX'] : $this->freeLineX;
        $startY = isset($infoboxInfo['startY']) ? $infoboxInfo['startY'] : $this->freeLineY;

        if (isset($infoboxInfo['lineWidth'])) {
            $currentPage->setLineWidth($infoboxInfo['lineWidth']);
        }

        $currentPage->drawInfoBox($infoboxInfo['header'], $infoboxInfo['lines'], $startX, $startY,
            $infoboxInfo['width'], $infoboxInfo['height']);

        return $currentPage;
    }

    /**
     * Function apply prototype of page (list of pdf tables and text lines)
     *
     * @param array $prototype specifies form of elements in the PDF page
     *              E.g. array(
     *                      'font' => 'Helvetica',
     *                      'fontSize' => 10,
     *                      0 => array(
     *                         'type' =>'table',
     *                         'startX'=> 35,
     *                         'startY'=> 60,
     *                         'rows' => array(...)
     *                      ),
     *                      1 => array(
     *                         'type' =>'freetext',
     *                         'startX'=> 35,
     *                         'startY'=> 100,
     *                         'lines' => array('line1', 'line2')
     *                      ),
     *                      2 => array(
     *                         'type' =>'infoBox',
     *                         'startX'=> 35,
     *                         'startY'=> 100,
     *                         'lines' => array('line1', 'line2'),
     *                         'width' => 100,
     *                         'height' => 100,
     *                         'header' => 'Header'
     *                      ),
     *                  )
     *
     * @return array of Phprojekt_Pdf_Page
     */
    public function applyPrototype($prototype)
    {
        $pages       = array($this);
        $currentPage = $this;
        $fontName    = isset($prototype['font']) ? $prototype['font'] : Zend_Pdf_Font::FONT_HELVETICA;
        $fontSize    = isset($prototype['fontSize']) ? $prototype['fontSize'] : self::DEFAULT_FONT_SIZE;
        $font        = Zend_Pdf_Font::fontWithName($fontName);

        foreach ($prototype as $element) {
            if (!isset($element['type'])) {
                continue;
            }

            $currentPage->setFont($font, $fontSize);

            switch ($element['type']) {
                case 'table':
                    $pages += $this->addTable($element, $currentPage);$currentPage = end($pages);
                    break;
                case 'freetext':
                    $this->addFreetext($element, $currentPage);
                    break;
                case 'infobox':
                    $this->addInfoBox($element, $currentPage);
                    break;
            }
        }

        return $pages;
    }

    /**
     * Add info box to the current page
     *
     * @param string $header
     * @param array  $lines
     * @param int    $x
     * @param int    $y
     * @param int    $width
     * @param int    $height
     *
     * @return void
     */
    public function drawInfoBox($header, $lines, $x, $y, $width, $height)
    {
        $fontSize = $this->getFontSize();
        // Draw the box
        $this->drawRectangle($x, $this->getHeight() - $y, $x + $width, $this->getHeight() - $y - $height,
            Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        // Draw the header bottom
        $this->drawLine($x, $this->getHeight() - $y - ($fontSize * 2), $x + $width,
            $this->getHeight() - $y - ($fontSize * 2));
        // Draw the header text
        $this->drawText($header, $x + ($fontSize / 2), $this->getHeight() - $y - $fontSize - ($fontSize / 4));
        $this->drawMultilineText($lines, $x + 3, $y + ($fontSize * 3));
    }

    /**
     * Add free text to the current page
     *
     * @param array $lines
     * @param int   $x
     * @param int   $y
     *
     * @return void
     */
    public function drawMultilineText($lines, $x, $y)
    {
        $y        = $this->getHeight() - $y;
        $fontSize = $this->getFontSize();
        foreach($lines as $i => $line) {
            $this->drawText($line, $x + 2, $y - ($fontSize * self::RATE_FONT_IN_PIX * $i));
        }
        $this->freeLineY = $this->getHeight() - $y + ($fontSize * self::RATE_FONT_IN_PIX * count($lines));
    }

    /**
     * Get size of cell in given font and font size
     *
     * @param text $str
     * @param int  $x        Start position X of the text
     * @param int  $y        Start position Y of the text
     * @param int  $maxWidth Width of the column
     *
     * @return array amount of lines, width and height of the cell
     */
    public function getVariableText($str, $x, $y, $maxWidth)
    {
        $y        = $this->getHeight() - $y;
        $font     = $this->getFont();
        $fontSize = $this->getFontSize();

        // Find out each word's width
        $words       = explode(' ', $str);
        $wordsLength = array();
        $em          = $font->getUnitsPerEm();
        $spaceSize   = array_sum($font->widthsForGlyphs(array(ord(' ')))) / $em * $fontSize;
        foreach($words as $i => $word) {
            $word  .= ' ';
            $glyphs = array();
            foreach(range(0, strlen($word)-1) as $i) {
                if (isset($str[$i])) {
                    $glyphs[] = ord($str[$i]);
                }
            }
            $wordsLength[] = array_sum($font->widthsForGlyphs($glyphs)) / $em * $fontSize + $spaceSize;
        }

        // Push words onto lines to be drawn.
        $yInc      = $y;
        $xInc      = 0;
        $lines     = array();
        $line      = array();
        $i         = 0;
        $maxLength = count($words);
        while($i < $maxLength) {
            // 10 - code of new line
            $wordsInside = explode(chr(10),$words[$i]);
            if (count($wordsInside) > 1) {
                // Add first word
                $firstWord        = array_shift($wordsInside);
                $currentWordLenth = $wordsLength[$i] * strlen($firstWord) / strlen($words[$i]);
                if(($xInc + $currentWordLenth) > $maxWidth) {
                    $lines[] = array($line, $x, $yInc);
                    $yInc   -= $fontSize;
                    $xInc    = $currentWordLenth + $spaceSize;
                    $line    = array();
                }
                $line[] = $firstWord;
                // Add other, each from new line
                foreach ($wordsInside as $word) {
                    $currentWordLenth = $wordsLength[$i] * strlen($word) / strlen($words[$i]);
                    $lines[] = array($line, $x, $yInc);
                    $xInc    = $currentWordLenth + $spaceSize;
                    $yInc   -= $fontSize;
                    $line    = array();
                    $line[]  = $word;
                }
            } else {
                if (($xInc + $wordsLength[$i] - $spaceSize) < $maxWidth) {
                    $xInc += $wordsLength[$i] + $spaceSize;
                } else {
                    $lines[] = array($line, $x, $yInc);
                    $yInc   -= $fontSize;
                    $xInc    = $wordsLength[$i] + $spaceSize;
                    $line    = array();
                }
                $line[] = $words[$i];
            }
            $i++;
        }
        unset($wordsLength);
        $lines[] = array($line, $x, $yInc);

        return array('width'  => $maxWidth,
                     'height' =>($fontSize * count($lines)),
                     'lines'  => $lines);
    }

    /**
     * Add text in cell to the page
     *
     * @param string $str
     * @param int    $x
     * @param int    $y
     * @param int    $maxWidth
     * @param string $align    Left, right or center
     *
     * @return array
     */
    public function drawVariableText($str, $x, $y, $maxWidth, $align = 'left')
    {
        $text = $this->getVariableText($str, $x, $y, $maxWidth);
        foreach($text['lines'] as $line) {
            list($str, $x, $y) = $line;
            $xPos              = $x;
            if($align == 'right') {
                $length = $this->_calculateTextWidth(implode(' ', $str));
                $xPos  += $maxWidth - $length - $this->getFontSize() / 2;
            } else if ($align == 'center') {
                $length = $this->_calculateTextWidth(implode(' ', $str));
                $xPos  += ($maxWidth - $length - $this->getFontSize() / 2) / 2;
            }
            $this->drawText(implode(' ', $str), $xPos, $y);
        }

        return array('width'  => $maxWidth,
                     'height' => $text['height']);
    }

    /**
     * Get text length in px
     *
     * @param string $str
     *
     * @return int
     */
    private function _calculateTextWidth($str)
    {
        $font     = $this->getFont();
        $fontSize = $this->getFontSize();

        // Find out each word's width
        $em     = $font->getUnitsPerEm();
        $glyphs = array();
        foreach(range(0, strlen($str)-1) as $i) {
            $glyphs[] = ord($str[$i]);
        }

        return array_sum($font->widthsForGlyphs($glyphs)) / $em * $fontSize;
    }
}
