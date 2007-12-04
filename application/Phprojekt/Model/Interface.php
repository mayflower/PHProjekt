<?php
 /**
 * A generic interface to interact with models.
 *
 * @copyright 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @version   CVS: $Id$
 * @author    Eduardo Polidor <polidor@mayflower.de>
 * @package   PHProjekt
 * @subpackage Core
 * @link      http://www.phprojekt.com
 * @since     File available since Release 1.0
 */

/**
 * The model interface describes the smallest set of methods that must
 * be provided by a model. All core components that donnot deal with a specific
 * interface should use this interface to interact with an object.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
interface Phprojekt_Model_Interface extends Iterator
{
	/**
	 * Returns an object that implements the model information interface
	 * and that provides detailed information about the fields and their
	 * types. For database objects implementing Phprojekt_Item this
	 * ModelInformation implementation is usually the DatabaseManager
	 *
	 * @return Phprojekt_ModelInformation_Interface
	 */
	public function getInformation();

	/**
	 * Find a dataset, usually by an id. If the record is found
	 * the current object is filled with the data and returns itself.
	 *
	 * @retrn Phprojekt_Model_Interface
	 */
    public function find();

	/**
	 * Fetch a set of records. Depending on the implementation
	 * it might be possible to limit the fetch by e.g. providing a where clause.
	 * A model _neednot_ to implement a limiting mechanism.
	 * 
	 * @return array
	 */
    public function fetchAll();

	/**
	 * Save the current object to the backend
	 *
	 * @return void
	 */
    public function save();
}
