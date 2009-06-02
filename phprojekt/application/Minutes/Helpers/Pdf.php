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
 * @version    $Id$
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
     * Helper function to create string lists of user names
     */
    private static function _concat($collect, $new)
    {
        if (is_null($collect)) {
            return $new['display'];
        }

        return $collect . "\n" . $new['display'];
    }

    /**
     * Creates a PDF report from the Minutes model given.
     * Returns the PDF as a string that can either be saved to disk
     * or streamed back to the browser.
     *
     * @param Phprojekt_Model_Interface $minutesModel The minutes model object to create the PDF from
     *
     * @return string The resulting PDF document
     */
    public static function getPdf(Phprojekt_Model_Interface $minutesModel)
    {
        $phpr = Phprojekt::getInstance();
        $pdf  = new Zend_Pdf();
        $page = new Phprojekt_Pdf_Page(Zend_Pdf_Page::SIZE_A4);

        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);
        $page->addFreetext(array(
                           'lines'  => $minutesModel->title,
                           'startX' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM,
                           'startY' => 2.0 * Phprojekt_Pdf_Page::PT_PER_CM,
                           'fontSize' => 20,
                           'textWidth' => 16.7 * Phprojekt_Pdf_Page::PT_PER_CM));
        $page->addFreetext(array(

                           'lines'    => array($minutesModel->description,
                                            $phpr->translate('Date of Meeting') . ': ' . $minutesModel->meetingDate
                                            . ' ' . $phpr->translate('Start time') . ': ' . $minutesModel->startTime
                                            . ', ' . $phpr->translate('End time') . ': ' . $minutesModel->endTime,
                                            $phpr->translate('Place') . ': ' . $minutesModel->place,
                                            $phpr->translate('Moderator') . ': ' . $minutesModel->moderator),
                           'startX'   => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM,
                           'fontSize' => 12));

        $invited    = Minutes_Helpers_Userlist::expandIdList($minutesModel->participantsInvited);
        $attending  = Minutes_Helpers_Userlist::expandIdList($minutesModel->participantsAttending);
        $excused    = Minutes_Helpers_Userlist::expandIdList($minutesModel->participantsExcused);
        $recipients = Minutes_Helpers_Userlist::expandIdList($minutesModel->recipients);

        $page->addTable(array(
                        'startX'=> 3.0 * Phprojekt_Pdf_Page::PT_PER_CM,
                        'fontSize' => 12,
                        'rows' => array(
                                      array(
                                          array('text'  => $phpr->translate('Invited'),
                                                'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text'  => array_reduce($invited, array('self', '_concat')),
                                                'width' => 12.2 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                      array(
                                          array('text'  => $phpr->translate('Attending'),
                                                'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text'  => array_reduce($attending, array('self', '_concat')),
                                                'width' => 12.2 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                      array(
                                          array('text'  => $phpr->translate('Excused'),
                                                'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text'  => array_reduce($excused, array('self', '_concat')),
                                                'width' => 12.2 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                      array(
                                          array('text'  => $phpr->translate('recipients'),
                                                'width' => 4.7 * Phprojekt_Pdf_Page::PT_PER_CM),
                                          array('text'  => array_reduce($recipients, array('self', '_concat')),
                                                'width' => 12.2 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            ),
                                        )));

        $itemtable     = array();
        $topicCount    = 0;
        $topicSubCount = 0;
        $items         = $minutesModel->items->fetchAll();

        foreach ($items as $item) {
            switch ($item->topicType) {
                case 1: // TOPIC
                    $topicCount++;
                    $topicSubCount = -1;
                case 2: // STATEMENT
                case 4: // DECISION
                    $topicSubCount++;
                    $form = $phpr->translate("%1\$s\n%2\$s");
                    break;
                case 3: // TODO
                    $topicSubCount++;
                    $form = $phpr->translate("%1\$s\n%2\$s\nWHO: %4\$s\nDATE: %3\$s");
                    break;
                case 5: // DATE
                    $topicSubCount++;
                    $form = $phpr->translate("%1\$s\n%2\$s\nDATE: %3\$s");
                    break;
                default:
                    $form = $phpr->translate("Undefined topicType");
                    break;
            }
            $itemtable[]  = array(
                                array('text'  => (1 == $item->topicType? sprintf('%d', $topicCount)
                                                    : sprintf('%d.%d', $topicCount, $topicSubCount)),
                                      'width' => 1.3 * Phprojekt_Pdf_Page::PT_PER_CM),
                                array('text'  => $phpr->translate($item->information->getTopicType($item->topicType)),
                                      'width' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                array('text'  => sprintf($form, $item->title, $item->comment, $item->topicDate,
                                                    $item->information->getUserName($item->userId)),
                                      'width' => 12.6 * Phprojekt_Pdf_Page::PT_PER_CM),
                            );
        }

        $page->addTable(array(
                        'startX'   => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM,
                        'fontSize' => 12,
                        'rows'     => array_merge(array(
                                        array('isHeader' => true,
                                            array('text'  => $phpr->translate('No.'),
                                                  'width' => 1.3 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            array('text'  => $phpr->translate('TYPE'),
                                                  'width' => 3.0 * Phprojekt_Pdf_Page::PT_PER_CM),
                                            array('text'  => $phpr->translate('ITEM'),
                                                  'width' => 12.6 * Phprojekt_Pdf_Page::PT_PER_CM),
                                        )
                                      ), $itemtable)
                        ));
        $pdf->pages[] = $page;

        $pdf->properties['Title']        = $minutesModel->title;
        $owner                           = Minutes_Helpers_Userlist::expandIdList($minutesModel->ownerId);
        $pdf->properties['Author']       = $owner[0]['display'];
        $pdf->properties['Producer']     = 'PHProjekt version ' . Phprojekt::getVersion();
        $pdf->properties['CreationDate'] = 'D:' . gmdate('YmdHis') . sprintf("%+02d'00'",
            (int) Phprojekt_User_User::getSetting("timeZone", 'UTC'));
        $pdf->properties['Keywords'] = $minutesModel->description;

        return $pdf->render();
    }
}
