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

/** Zend_Pdf */
require_once 'Zend/Pdf.php';

/**
 * Phprojekt Class for PFD creation.
 */
class Phprojekt_Pdf_Page extends Zend_Pdf_Page
{
    /**
     * pt/cm equals 72 pt/in / 2.54 cm/in.
     */
    const PT_PER_CM = 28.346456692913385826771653543307;

    const DEFAULT_FONT_SIZE   = 10;
    const HEADER_GRAY_LEVEL   = 0.9;

    /**
     * Default border values.
     *
     * @var float
     */
    public $borderLeft   = 30;
    public $borderRight  = 30;
    public $borderTop    = 30;
    public $borderBottom = 30;

    /**
     * Default position X of the next element in the page.
     *
     * @var float
     */
    public $freeLineX = 30;

     /**
     * Default position Y of the next element in the page.
     *
     * @var float
     */
    public $freeLineY = 30;

    /**
     * Default text width for freetext in point.
     *
     * NULL acts as a default indicator.
     *
     * @var float
     */
    public $textWidth = NULL;

     /**
      * Height of the line in text, in relation to font size.
      *
      * @var float
      */
    public $lineHeight = 1.4;

    /**
     * Padding in the table in point.
     *
     * @var float
     */
    public $tablePadding = 5;

    /**
     * Padding down for paragraphs in point.
     *
     * @var float
     */
    public $paragraphPadding = 5;

    /**
     * Constructor.
     *
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     *
     * @throws Zend_Pdf_Exception
     *
     * @return void
     */
    public function __construct($param1, $param2 = NULL, $param3 = NULL)
    {
        parent::__construct($param1, $param2, $param3);

        if ($param1 instanceof Phprojekt_Pdf_Page && $param2 === null && $param3 === null) {
            // Clone additional properties
            $this->setBorder($param1->borderTop, $param1->borderRight, $param1->borderBottom, $param1->borderLeft);
        }
    }

    /**
     * Define the used border space.
     *
     * @param float $top    Top value.
     * @param float $right  Right value.
     * @param float $bottom Borrom value.
     * @param float $left   Left value.
     *
     * @return void
     */
    public function setBorder($top, $right = NULL, $bottom = NULL, $left = NULL)
    {
        if (is_null($left)) {
            $left = $right;
        }
        if (is_null($bottom)) {
            $bottom = $top;
            $left   = $right;
        }
        if (is_null($right)) {
            $right  = $top;
            $bottom = $top;
            $left   = $top;
        }

        $this->borderTop    = $top;
        $this->borderRight  = $right;
        $this->borderBottom = $bottom;
        $this->borderLeft   = $left;
        $this->freeLineX    = $this->borderLeft;
        $this->freeLineY    = $this->borderTop;
    }

    /**
     * Function inserts table with given rows to pdf pages.
     *
     * @param array              $tableInfo   specifies table for the PDF.
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
     * @param Phprojekt_Pdf_Page $currentPage Current page.
     * @param string             $encoding    Encoding of the text string $str, UTF-8 by default.
     *
     * @return array Array of Phprojekt_Pdf_Page.
     */
    public function addTable($tableInfo, $currentPage = NULL, $encoding = 'UTF-8')
    {
        if (is_null($currentPage)) {
            $currentPage = $this;
        }

        if (!isset($tableInfo['rows'])) {
            throw new Exception("Missing data");
        }

        if (isset($tableInfo['fontSize'])) {
            $fontSizeChangeRate      = $tableInfo['fontSize'] - $currentPage->getFontSize();
            $currentPage->freeLineY += $fontSizeChangeRate * $this->lineHeight;

            $currentPage->setFont($this->getFont(), $tableInfo['fontSize']);
        }

        $startX = isset($tableInfo['startX']) ? $tableInfo['startX'] : $this->borderLeft;
        $startY = (isset($tableInfo['startY']) ? $tableInfo['startY'] : ($this->freeLineY != $this->borderTop ?
            $this->freeLineY : $this->borderTop));

        if (empty($tableInfo['rows']) || !is_array($tableInfo['rows'])) {
            throw new Exception("Rows are empty");
        }

        if (isset($tableInfo['lineWidth'])) {
            $currentPage->setLineWidth($tableInfo['lineWidth']);
        }

        // Data is correct
        $table = new Phprojekt_Pdf_Table($currentPage, $startX, $startY);
        foreach ($tableInfo['rows'] as $row) {
            $tableRow = new Phprojekt_Pdf_Table_Row();
            if (isset($row['isHeader']) && $row['isHeader']) {
                $tableRow->setHeader();
            }
            foreach ($row as $cell) {
                if (!is_array($cell)) {
                    continue;
                }
                // TODO: add validation for column data
                $column    = new Phprojekt_Pdf_Table_Column();
                $alignment = !empty($cell['align']) ? $cell['align'] : null;
                $column->setWidth($cell['width'])->setText($cell['text'], $encoding)->setAlignment($alignment);
                $tableRow->addColumn($column);
            }
            $table->addRow($tableRow);
        }

        $result          = array($currentPage);
        $additionalPages = $table->render();
        if (!empty($additionalPages)) {
            foreach ($additionalPages as $page) {
                $result[] = $page;
            }
        }

        return $result;
    }

    /**
     * Function inserts freetext into page.
     *
     * @param array              $freetextInfo specifies free text for the PDF.
     *                 E.g. array(
     *                         'type' =>'freetext',
     *                         'startX'=> 35,
     *                         'startY'=> 60,
     *                         'lines' => array(
     *                              'first line',
     *                              'second line',
     *                          ),
     *                          'fontSize' => 10,
     *                      )
     * @param Phprojekt_Pdf_Page $currentPage Current page.
     * @param string             $encoding    Encoding of the text string $str, UTF-8 by default.
     *
     * @return Phprojekt_Pdf_Page An instance of Phprojekt_Pdf_Page.
     */
    public function addFreetext($freetextInfo, $currentPage = NULL, $encoding = 'UTF-8')
    {
        if (is_null($currentPage)) {
            $currentPage = $this;
        }

        if (!isset($freetextInfo['lines'])) {
            throw new Exception("Missing data");
        }

        // Change font size
        if (isset($freetextInfo['fontSize'])) {
            $fontSizeChangeRate      = $freetextInfo['fontSize'] - $currentPage->getFontSize();
            $currentPage->freeLineY += $fontSizeChangeRate * $this->lineHeight;
            $currentPage->setFont($this->getFont(), $freetextInfo['fontSize']);
        }

        $startX = isset($freetextInfo['startX']) ? $freetextInfo['startX'] : $this->borderLeft;
        $startY = (isset($freetextInfo['startY']) ? $freetextInfo['startY'] : ($this->freeLineY != $this->borderTop ?
            $this->freeLineY :$this->borderTop)) + $currentPage->getFontSize();

        if (isset($freetextInfo['lineWidth'])) {
            $currentPage->setLineWidth($freetextInfo['lineWidth']);
        }

        if (isset($freetextInfo['width'])) {
            $this->textWidth = $freetextInfo['width'];
        } elseif (is_null($this->textWidth)) {
            $this->textWidth = $this->getWidth() - ($this->borderLeft + $this->borderRight);
        }

        $currentPage->drawMultilineText($freetextInfo['lines'], $startX, $startY, $this->textWidth, $encoding);

        return $currentPage;
    }

    /**
     * Function apply prototype of page (list of pdf tables and text lines).
     *
     * @param array  $prototype Specifies form of elements in the PDF page.
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
     * @param string $encoding Encoding of the text string $str, UTF-8 by default.
     *
     * @return array Array of Phprojekt_Pdf_Page.
     */
    public function parseTemplate($prototype, $encoding = 'UTF-8')
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
                    $pages      += $this->addTable($element, $currentPage, $encoding);
                    $currentPage = end($pages);
                    break;
                case 'freetext':
                    $this->addFreetext($element, $currentPage, $encoding);
                    break;
            }
        }

        return $pages;
    }

    /**
     * Add info box to the current page.
     *
     * @param string  $header
     * @param array   $lines    Array of string.
     * @param integer $x
     * @param integer $y
     * @param integer $width
     * @param integer $height
     * @param string  $encoding Encoding of the text string $str, UTF-8 by default.
     *
     * @return void
     */
    public function drawInfoBox($header, $lines, $x, $y, $width, $height, $encoding = 'UTF-8')
    {
        $fontSize = $this->getFontSize();
        // Draw the box
        $this->drawRectangle($x, $this->getHeight() - $y, $x + $width, $this->getHeight() - $y - $height,
            Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        // Draw the header bottom
        $this->drawLine($x, $this->getHeight() - $y - ($fontSize * 2), $x + $width,
            $this->getHeight() - $y - ($fontSize * 2));
        // Draw the header text
        $this->drawText($header, $x + ($fontSize / 2), $this->getHeight() - $y - $fontSize - ($fontSize / 4),
            $encoding);
        $this->drawMultilineText($lines, $x + 3, $y + ($fontSize * 3));
    }

    /**
     * Add free text to the current page.
     *
     * @param array   $lines    Array of strings.
     * @param integer $x
     * @param integer $y
     * @param string  $encoding Encoding of the text string $str, UTF-8 by default.
     *
     * @return void
     */
    public function drawMultilineText($lines, $x, $y, $width, $encoding = 'UTF-8')
    {
        $y             = $this->getHeight() - $y;
        $fontSize      = $this->getFontSize();
        $realLineCount = 0;

        if (!is_array($lines)) {
            $lines = array($lines);
        }
        $padding = 0;
        foreach ($lines as $line) {
            $parsedText = $this->getVariableText($line, $x, $y, $width);
            foreach ($parsedText['lines'] as $parsedLine) {
                list($str, $xTmp, $yTmp) = $parsedLine;
                $this->drawText(implode(' ', $str), $x + 2,
                    $y - ($fontSize * $this->lineHeight * $realLineCount) - $padding, $encoding);
                $realLineCount++;
            }
            $padding += $this->paragraphPadding;
        }
        $this->freeLineY = $this->getHeight() - $y + ($fontSize * $this->lineHeight * $realLineCount) + $padding;
    }

    /**
     * Get size of cell in given font and font size.
     *
     * @param text    $str
     * @param integer $x        Start position X of the text.
     * @param integer $y        Start position Y of the text.
     * @param integer $maxWidth Width of the column.
     * @param string  $encoding Encoding of the text string $str, UTF-8 by default.
     *
     * @return array Amount of lines, width and height of the cell.
     */
    public function getVariableText($str, $x, $y, $maxWidth, $encoding = 'UTF-8')
    {
        $y        = $this->getHeight() - $y;
        $font     = $this->getFont();
        $fontSize = $this->getFontSize();

        // Find out each word's width
        $words         = explode(' ', $str);
        $wordsLength   = array();
        $em            = $font->getUnitsPerEm();
        $spaceSize     = array_sum($font->widthsForGlyphs(array(ord(' ')))) / $em * $fontSize;
        foreach ($words as $i => $word) {
            $word         .= ' ';
            $wordsLength[] = $this->_calculateTextWidth($word, $encoding);
        }
        // Push words onto lines to be drawn.
        $yInc      = $y;
        $xInc      = 0;
        $lines     = array();
        $line      = array();
        $i         = 0;
        $maxLength = count($words);
        $paragraph = 0;
        while ($i < $maxLength) {
            // 10 - code of new line
            $wordsInside = explode(chr(10), $words[$i]);
            if (count($wordsInside) > 1) {
                // Add first word
                $firstWord           = array_shift($wordsInside);
                $currentWordLength   = $wordsLength[$i] * strlen($firstWord) / strlen($words[$i]);
                if (($xInc + $currentWordLength) > $maxWidth - 2 * $this->tablePadding) {
                    $lines[] = array($line, $x, $yInc);
                    $yInc   -= $fontSize + $this->paragraphPadding;
                    $xInc    = $currentWordLength;
                    $line    = array();
                    $paragraph++;
                }
                $line[] = $firstWord;
                // Add other, each from new line
                foreach ($wordsInside as $word) {
                    $currentWordLength = $wordsLength[$i] * strlen($word) / strlen($words[$i]);
                    $lines[]           = array($line, $x, $yInc);
                    $yInc             -= $fontSize;
                    $xInc              = $currentWordLength + $spaceSize;
                    $line              = array();
                    $line[]            = $word;
                }
                $yInc -= $this->paragraphPadding;
                $paragraph++;
            } else {
                if (($xInc + $wordsLength[$i]) < $maxWidth - 2 * $this->tablePadding) {
                    $xInc += $wordsLength[$i];
                } else {
                    $lines[] = array($line, $x, $yInc);
                    $yInc   -= $fontSize;
                    $xInc    = $wordsLength[$i];
                    $line    = array();
                }
                $line[] = $words[$i];
            }
            $i++;
        }

        unset($wordsLength);
        $lines[] = array($line, $x, $yInc);

        return array('width'  => $maxWidth,
                     'height' => ($fontSize * count($lines)) + $paragraph * $this->paragraphPadding,
                     'lines'  => $lines);
    }

    /**
     * Add text in cell to the page.
     *
     * @param string  $str
     * @param integer $x
     * @param integer $y
     * @param integer $maxWidth
     * @param string  $align    left, right or center.
     * @param string  $encoding Encoding of the text string $str, UTF-8 by default.
     *
     * @return array
     */
    public function drawVariableText($str, $x, $y, $maxWidth, $align = 'left', $encoding = 'UTF-8')
    {
        $text = $this->getVariableText($str, $x, $y, $maxWidth);
        foreach ($text['lines'] as $line) {
            list($str, $x, $y) = $line;
            $xPos              = $x + $this->tablePadding;
            if ($align == 'right') {
                $length = $this->_calculateTextWidth(implode(' ', $str));
                $xPos = $x + $maxWidth - $length - $this->tablePadding;
            } else if ($align == 'center') {
                $length = $this->_calculateTextWidth(implode(' ', $str));
                $xPos = $x + ($maxWidth - $length) / 2;
            }
            $this->drawText(implode(' ', $str), $xPos, $y, $encoding);
        }

        return array('width'  => $maxWidth,
                     'height' => $text['height']);
    }

    /**
     * Get text length in point.
     *
     * @param string $str
     * @param string $encoding Encoding of the text string $str, UTF-8 by default.
     *
     * @return integer
     */
    protected final function _calculateTextWidth($str, $encoding = 'UTF-8')
    {
        $font     = $this->getFont();
        $fontSize = $this->getFontSize();

        // Find out each word's width
        $em            = $font->getUnitsPerEm();
        $characters    = array();
        $drawingString = iconv($encoding, 'UTF-16BE', $str);
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);

        return array_sum($font->widthsForGlyphs($glyphs)) / $em * $fontSize;
    }

    /**
     * Set paragraph padding down value.
     *
     * @param integer paragraphPadding value.
     *
     * @return void
     */
    public function setParagraphPadding($value)
    {
        $this->paragraphPadding = $value;
    }

    /**
     * Set padding in table.
     *
     * @param integer $value tablePadding value.
     *
     * @return void
     */
    public function setTablePadding($value)
    {
        $this->tablePadding = $value;
    }

    /**
     * Line height.
     *
     * @param integer $value lineHeight value.
     *
     * @return void
     */
    public function setLineHeight($value)
    {
        $this->lineHeight = $value;
    }

    /**
     * Set free position coordinates.
     *
     * @param integer $x Position in pt.
     * @param integer $y Position in pt.
     *
     * @return void
     */
    public function setStartPosition($x = null, $y = null)
    {
        if (!is_null($x)) {
            $this->freeLineX = $x;
        } else {
            $this->freeLineX = $this->borderLeft;
        }
        if (!is_null($y)) {
            $this->freeLineY = $y;
        } else {
            $this->freeLineY = $this->borderTop;
        }
    }
}
