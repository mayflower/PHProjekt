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
        setlocale(LC_ALL, 'de_DE.utf8');
        $pdf = new Zend_Pdf();

        $page = new Phprojekt_Pdf_Page(Zend_Pdf_Page::SIZE_A4);

        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA),10);
        $page->addTable(array(
            'type' =>'table',
            'startX'=> 35,
            'startY'=> 60,
            'rows' => array(
                 array(array('text' => 'first column, first row','width' => 150),
                       array('text' => 'Die voluminöse Expansion der subterralen Knollengewächse ist reziprog proportional zum Intelligenzquotienten des Agrarökonoms.', 
                             'width' => 100),
                 ),
                 array(array('text' => 'first column, second row','width' => 150),
                       array('text' => 'second column, second row','width' => 100),
                 ),
             )
        ), $page);
        $pdf->pages[] = $page;
        return $pdf->render();
    }
}
