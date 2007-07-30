<?php
/**
 * Form View helper class
 *
 * This class is for help on the draw of the form
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
 * Form View helper class
 *
 * This class is for help on the draw of the form
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Helpers_FormView
{
    /**
     * Make all the input fields and return and arrar for
     * use in smarty.
     *
     * @param array $fields      Array with the data of each field
     * @param int   $formColumns Number of columns to show
     *
     * @return array             The data for show in the template
     */
    public function makeColumns($fields,$formColumns)
    {
        $countFields = count($fields);
        $modFields   = $countFields % $formColumns;
        if ($modFields != 0) {
            for ($index = $modFields; $index < $formColumns; $index++) {
                $fields[] = '&nbsp';
            }
        }

        return $fields;
    }
}