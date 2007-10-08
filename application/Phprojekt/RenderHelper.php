<?php
/**
 * Interface definition for render helpers
 *
 * Render helper like e.g. the ListView helper are classes that
 * take an ActiveRecord and render it. They hide render and receiving
 * mechanisms to help developers to create easy and fast CRUD modules.
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
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
 * Interface definition for render helpers
 *
 * Render helper like e.g. the ListView helper are classes that
 * take an ActiveRecord and render it. They hide render and receiving
 * mechanisms to help developers to create easy and fast CRUD modules.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <solt@mayflower.de>
 */
interface Phprojekt_RenderHelper
{
    /**
     * Set a model that should be rendered.
     *
     * @param Phprojekt_Abstract_Item $model The activerecord
     */
    public function setModel(Phprojekt_Item_Abstract $model);

    /**
     * Render the part.
     *
     */
    public function render();

    /**
     * Return the model that is rendered
     *
     * @return Phprojekt_Item_Abstract
     */
    public function &getModel();
}