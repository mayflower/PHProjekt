<?php
/**
 * Paging helper class
 *
 * This class is for draw the paging of a list
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * The class calculate the number of pages to show in a list view
 * and return the nessesary variables for the render it.
 *
 * The render will show:
 * - A link to the first page.
 * - A link to the previous page.
 * - 10 links from the current page to the next 10 pages.
 * - A link to the next page.
 * - A link to the last page.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Helpers_Paging
{
    /**
     * Calculate the number of pages to show and assign the nessesary variables
     * to the smarty object for render it.
     *
     * @param int    $howManyRows         How many rows have the list view
     * @param int    $howManyItemsPerPage How many itemes will be showed in each page
     * @param int    $page                Actual page
     *
     * @return void
     */
    public function calculatePages($howManyRows, $howManyItemsPerPage, $page)
    {
        $pages        = array();
        $previousPage = $page - $howManyItemsPerPage;
        $nextPage     = $page + $howManyItemsPerPage;
        $total        = ceil($howManyRows/$howManyItemsPerPage);
        $howManyPages = ($page/$howManyItemsPerPage)+1;

        /* First page */
        if ($page != 0) {
            $currentPage = ceil($page/$howManyItemsPerPage);
        } else {
            $currentPage = 0;
        }

        /* Last page (only show 10) */
        $show = $currentPage + 10;
        if ($show > $total) {
            $show = $total;
        }

        for ($index = $currentPage; $index < $show ; $index++) {
            $position = $index * $howManyItemsPerPage;
            $page     = $index + 1;
            $pages[]  = array('number'   => $page,
                              'position' => $position);
        }

        return array('nextPage'     => $nextPage,
                     'howManyRows'  => $howManyRows,
                     'previousPage' => $previousPage,
                     'pages'        => $pages,
                     'lastPage'     => ($total - 1) * $howManyItemsPerPage,
                     'howManyPages' => $howManyPages);
    }
}