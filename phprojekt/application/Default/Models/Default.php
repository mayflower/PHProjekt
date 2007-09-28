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
class Default_Models_Default
{
    /**
     * Some magic. The index controller code always has an instance of
     * an model. If no other model is specified, the index controller uses
     * this default model. As the index controller expect the model to be an
     * active record and this default model cannot be used as an active record,
     * as no database table exists for this model, all the calls to the
     * active record provided methods will fail.
     * To avoid this, we just suck all the calls and don't spit warnings
     *
     * @param string $method Action method
     * @param array  $args   Arguments for the Action
     *
     * @return IndexController Action
     */
    public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            /* If the action method was not found,
            forward to the index action */
            return $this->_forward('index');
        }

        $arguments = null;
        if (false == empty($args)) {
            foreach ($args as $argument) {
                $arguments .= $argument;
            }
        }
        throw new Exception('Invalid method "'. $method . '" called'
                            . ' with arguments: ' . $arguments);
    }
}