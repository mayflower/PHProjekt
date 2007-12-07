<?php
/**
 * Convert a model into a json structure. 
 * This is usually done by a controller to send data to the
 * client
 *
 * @copyright 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @version   CVS: $Id$
 * @author    David Soria Parra <soria_parra@mayflower.de>
 * @package   PHProjekt
 * @subpackage Core
 * @link      http://www.phprojekt.com
 * @since     File available since Release 1.0
 */

/**
 * Convert a model into a json structure.
 * This is usally done by a controller to send data to the client.
 * The Phprojekt_Convert_Json takes care that a apporpriate structure
 * is made from the given model.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     David Soria PArra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Phprojekt_Converter_Json
{
	/**
	 * Convert a model or a model information into a json stream
	 * 
	 * @param Phprojekt_Interface_Model|Phprojekt_Interface_ModelInformation $model
	 * @return string 
	 */
	public static function convert($models, $order=MODELINFO_ORD_DEFAULT) 
	{
	    $model = current((array) $models);
	    if (! $model instanceof Phprojekt_Model_Interface) {
	        throw new InvalidArgumentException();
	    }
	    
	    $information = $model->getInformation();
	    /* we can check the returned array, but at the moment we just pass it */
	    $datas = array();
	    
	    /*
	     * we have to do this ugly convert, because Zend_Json_Encoder doesnot check
	     * if a value in an array is an object
	     */
	    foreach ($models as $cmodel) {
	        foreach ($cmodel as $key => $value) {
    	        $datas[$key] = (string) $value;
	        }    
	    }
	    
	    $data = array('metadata' =>  $information->getFieldDefinition($order),
	          		  'data'     =>  $datas);
	    
	    return Zend_Json_Encoder::encode($data);
	}
}
