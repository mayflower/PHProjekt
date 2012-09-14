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
 * Helper for creating a PDF document for a Minutes entry.
 */
final class Minutes_Helpers_Pdf
{
    /**
     * Callback helper function for array_reduce to create string lists of user names.
     *
     * @param string $collect Var to collect all strings.
     * @param string $new     New array member to add.
     *
     * @return string Result of array_reduce call.
     */
    private static function _concat($collect, $new)
    {
        if (is_null($collect)) {
            return $new['display'];
        } else {
            return $collect . "\n" . $new['display'];
        }
    }

    /**
     * Creates a PDF report from the Minutes model given.
     * Returns the PDF as a string that can either be saved to disk
     * or streamed back to the browser.
     *
     * @param Phprojekt_Model_Interface $minutesModel The minutes model object to create the PDF from.
     *
     * @return string The resulting PDF document.
     */
    public static function getPdf(Phprojekt_Model_Interface $minutesModel)
    {
        $phpr  = Phprojekt::getInstance();
        $pdf   = new Zend_Pdf();
        $page  = new Phprojekt_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        $pages = array($page);

        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);

        $page->setBorder(2.0 * Phprojekt_Pdf_Page::PT_PER_CM, 2.0 * Phprojekt_Pdf_Page::PT_PER_CM,
            2.0 * Phprojekt_Pdf_Page::PT_PER_CM, 3.0 * Phprojekt_Pdf_Page::PT_PER_CM);

        $page->addFreetext(array(
                           'lines'    => $minutesModel->title,
                           'fontSize' => 20));

        $page->addFreetext(array(
                           'lines'    => array_merge(explode("\n\n", $minutesModel->description),
                                            array($phpr->translate('Start') . ': ' . $minutesModel->meetingDatetime,
                                                  $phpr->translate('End') . ': ' . $minutesModel->endTime,
                                                  $phpr->translate('Place') . ': ' . $minutesModel->place,
                                                  $phpr->translate('Moderator') . ': ' . $minutesModel->moderator)),
                           'fontSize' => 12));

        $invited   = Minutes_Helpers_Userlist::expandIdList($minutesModel->participantsInvited);
        $attending = Minutes_Helpers_Userlist::expandIdList($minutesModel->participantsAttending);
        $excused   = Minutes_Helpers_Userlist::expandIdList($minutesModel->participantsExcused);

        $pages += $page->addTable(array(
                        'fontSize' => 12,
                        'rows'     => array(
                                          array(
                                              array('text'  => $phpr->translate('Invited'),
                                                    'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                              array('text'  => array_reduce($invited, array('self', '_concat')),
                                                    'width' => 12.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                                ),
                                          array(
                                              array('text'  => $phpr->translate('Attending'),
                                                    'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                              array('text'  => array_reduce($attending, array('self', '_concat')),
                                                    'width' => 12.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                                ),
                                          array(
                                              array('text'  => $phpr->translate('Excused'),
                                                    'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                              array('text'  => array_reduce($excused, array('self', '_concat')),
                                                    'width' => 12.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                                ),
                                        )));
        $page = end($pages);

        $itemtable = array();
        $items     = $minutesModel->items->fetchAll();

        foreach ($items as $item) {
            $itemtable[]  = array(
                                array('text'  => $item->topicId,
                                      'width' => 1.3 * Phprojekt_Pdf_Page::PT_PER_CM),
                                array('text'  => $phpr->translate($item->information->getTopicType($item->topicType)),
                                      'width' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                array('text'  => $item->getDisplay(),
                                      'width' => 12.4 * Phprojekt_Pdf_Page::PT_PER_CM),
                            );
        }

        $pages += $page->addTable(array(
                        'fontSize' => 12,
                        'rows'     => array_merge(array(
                                        array('isHeader' => true,
                                            array('text'  => $phpr->translate('No.'),
                                                  'width' => 1.3 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            array('text'  => $phpr->translate('Type'),
                                                  'width' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            array('text'  => $phpr->translate('Item'),
                                                  'width' => 12.4 * Phprojekt_Pdf_Page::PT_PER_CM),
                                        )
                                      ), $itemtable)
                        ));
        $page = end($pages);

        $pdf->pages = $pages;

        $pdf->properties['Title']        = $minutesModel->title;
        $owner                           = Minutes_Helpers_Userlist::expandIdList($minutesModel->ownerId);
        $pdf->properties['Author']       = $owner[0]['display'];
        $pdf->properties['Producer']     = 'PHProjekt version ' . Phprojekt::getVersion();
        $pdf->properties['CreationDate'] = 'D:' . gmdate('YmdHis');
        $pdf->properties['Keywords'] = $minutesModel->description;

        return $pdf->render();
    }
}
