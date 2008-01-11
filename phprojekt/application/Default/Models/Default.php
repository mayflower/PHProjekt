<?php
/**
 * Default model class
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
 * Default model class
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Models_Default implements Phprojekt_Model_Interface
{
    /**
     * Construct class
     *
     */
    public function __construct()
    {
    }

    /**
     * Information about the fields
     *
     * @see Phprojekt_Item_Abstract
     *
     * @return void
     */
    public function getInformation()
    {
    }

    /**
     * Empty iterator implementation as a model must be iteratable
     *
     * @see Iterator::next()
     *
     * @return void
     */
    public function next()
    {
    }

    /**
     * Empty iterator implementation as a model must be iteratable
     *
     * @see Iterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
    }

    /**
     * Empty iterator implementation as a model must be iteratable
     *
     * @see Iterator::current()
     *
     * @return void
     */
    public function current()
    {
    }

    /**
     * Empty iterator implementation as a model must be iteratable
     *
     * @see Iterator::valid()
     *
     * @return void
     */
    public function valid()
    {
        return false;
    }

    /**
     * Empty iterator implementation as a model must be iteratable
     *
     * @see Iterator::key()
     *
     * @return void
     */
    public function key ()
    {
    }

    /**
     * Default fetchall - needs to be implemented
     *
     * @return void
     */
    public function fetchAll()
    {
    }

    /**
     * Default find - needs to be implemented
     *
     * @return void
     */
    public function find()
    {
    }

    /**
     * Some magic. The index controller code always has an instance of
     * an model. If no other model is specified, the index controller uses
     * this default model. As the index controller expect the model to be an
     * active record and this default model cannot be used as an active record,
     * as no database table exists for this model, all the calls to the
     * active record provided methods will fail.
     * To avoid this, we just suck all the calls and don't spit warnings
     *
     * @return void
     */
    public function save()
    {
    }
    
    /**
     * Get rights.
     *
     * @return string
     */
    public function getRights()
    {
        return 'read';
    }
    
    public function getFieldsForFilter()
    {
        return array();
    }
}