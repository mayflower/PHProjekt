<?php
/**
 * Search Controller for PHProjekt 6
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
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Search Controller for PHProjekt 6
 *
 * The controller will get all the actions for return the search results
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class SearchController extends IndexController
{
    /**
     * Search for words
     *
     * @requestparam string  $words    The words for seach
     * @requestparam integer $count    Number of results
     * @requestparam integer $start    Number of page
     *
     * @return void
     */
    public function jsonSearchAction()
    {
        $words    = $this->getRequest()->getParam('words');
        $count    = (int) $this->getRequest()->getParam('count', null);
        $offset   = (int) $this->getRequest()->getParam('start', null);

        $search  = new Phprojekt_Search_Default();
        $results = $search->search($words, $count, $offset);

        echo Phprojekt_Converter_Json::convert($results);
    }
}
