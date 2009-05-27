<?php
/**
 * Helper for creating a PDF document for a Minutes entry
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
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id: Right.php 1553 2009-04-02 14:28:43Z svenrtbg $
 * @link       http://www.phprojekt.com
 * @author     Markus Wolff <markus.wolff@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Helper for set the rights of the user in one item
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Markus Wolff <markus.wolff@mayflower.de>
 */
final class Minutes_Helpers_Pdf
{
    /**
     * Creates a PDF report for the Minutes entry having
     * the given ID. Returns the PDF as a string that can
     * either be saved to disk or streamed back to the
     * browser.
     * 
     * @param int $minutesId ID of the Minutes entry
     * @return string The resulting PDF document
     */
    public function getPdf($minutesId)
    {
        // Create new PDF document.
        $pdf = new Zend_Pdf();
        
        $page = new Phprojekt_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);
        
        $page->addFreetext(array(
                           'lines'  => array('Title as Headline'),
                           'startX' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM,
                           'startY' => 2.0 * Phprojekt_Pdf_Page::PT_PER_CM,
                           'fontSize' => 20,
                           'textWidth' => 16.7 * Phprojekt_Pdf_Page::PT_PER_CM));
        
        $page->addFreetext(array(
                           'lines'=>array('Lorem ipsum whatever foobar.',
                                          'Die voluminöse Expansion der subterralen '
                                        . 'Knollengewächse ist reziprog proportional zum '
                                        . 'Intelligenzquotienten des Agrarökonoms. Die voluminöse Expansion der '
                                        . 'subterralen Knollengewächse ist reziprog proportional zum '
                                        . 'Intelligenzquotienten des Agrarökonoms.',
                                          'Date of meeting: ' . date('Y-m-d')
                                        . ' Start time: ' . date('H:i:s')
                                        . ', End time: ' . date('H:i:s'),
                                          'Location: ORTSANGABE',
                                          'Moderator: NAMENSANGABE'),
                            'startX'    => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM,
                            'fontSize'  => 12));
        
        $page->addTable(array(
                        'startX'=> 3.0 * Phprojekt_Pdf_Page::PT_PER_CM,
                        'fontSize' => 12,
                        'rows' => array(
                                      array(
                                          array('text' => 'INVITED', 'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => "NAME 1\nNAME 2\nNAME3", 'width' => 12.2 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                      array(
                                          array('text' => 'PARTICIPANTS', 'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => "NAME 1\nNAME 2", 'width' => 12.2 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                      array(
                                          array('text' => 'EXCUSED', 'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => "NAME 3\nNAME 4", 'width' => 12.2 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                      array(
                                          array('text' => 'RECIPIENTS', 'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => "NAME 1\nNAME 2\nNAME 3\nNAME 4", 'width' => 12.2 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                        )));
        
        $page->addTable(array(
                        'startX'=> 3.0 * Phprojekt_Pdf_Page::PT_PER_CM,
                        'fontSize' => 12,
                        'rows' => array(
                                      array('isHeader' => true,
                                          array('text' => 'No.', 'width' => 1.3 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => 'TYPE', 'width' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => "ITEM", 'width' => 12.6 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                      array(
                                          array('text' => '1', 'width' => 1.3 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => 'TOPIC', 'width' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => "TITLE OF ITEM\nCOMMENT OF ITEM IN MULTILINE", 'width' => 12.6 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                      array(
                                          array('text' => '1.1', 'width' => 1.3 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => 'STATEMENT', 'width' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => "TITLE OF ITEM\nCOMMENT OF ITEM IN MULTILINE", 'width' => 12.6 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                      array(
                                          array('text' => '1.2', 'width' => 1.3 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => 'TODO', 'width' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => "TITLE OF ITEM\nCOMMENT OF ITEM IN MULTILINE\nWHO: name\nDATE: YYYY-MM-DD", 'width' => 12.6 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                      array(
                                          array('text' => '2', 'width' => 1.3 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => 'TOPIC', 'width' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text' => "TITLE OF ITEM\nCOMMENT OF ITEM IN MULTILINE\nWHO: name\nDATE: YYYY-MM-DD", 'width' => 12.6 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                        )));
        
        $pdf->pages[] = $page;
        return $pdf->render();
    }
}
