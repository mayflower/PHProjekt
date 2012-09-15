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
 * Search Controller.
 * The controller will get all the actions for return the search results.
 */
class SearchController extends IndexController
{
    /**
     * Search for words.
     *
     * Returns a list of items that have the word, sorted by module with:
     * <pre>
     *  - id            => id of the item found.
     *  - moduleId      => id of the module.
     *  - moduleName    => Name of the module.
     *  - moduleLabel   => Display for the module.
     *  - firstDisplay  => Firts display for the item (Ej. title).
     *  - secondDisplay => Second display for the item (Ej. notes).
     *  - projectId     => Parent project id of the item.
     * </pre>
     *
     * REQUIRES request parameters:
     * <pre>
     *  - string <b>words</b> An string of words (Will be separated by the spaces).
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>count</b> Number of results.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonSearchAction()
    {
        $words  = (string) $this->getRequest()->getParam('words');
        $count  = (int) $this->getRequest()->getParam('count', null);
        $offset = (int) $this->getRequest()->getParam('start', null);

        $search  = new Phprojekt_Search();
        $tags    = new Phprojekt_Tags();

        $searchresults = $search->search($words, $count);
        $tagresults    = $tags->search($words, $count);

        $results = array(
            'search' => $searchresults,
            'tags' => $tagresults
        );

        Phprojekt_Converter_Json::echoConvert($results);
    }
}
