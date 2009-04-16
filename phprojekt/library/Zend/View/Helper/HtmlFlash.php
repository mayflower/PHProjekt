<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: HtmlFlash.php 10192 2008-07-18 20:14:57Z matthew $
 */

/**
 * @see Zend_View_Helper_HtmlObject
 */
require_once 'Zend/View/Helper/HtmlObject.php';

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HtmlFlash extends Zend_View_Helper_HtmlObject
{
    /**
     * Default file type for a flash applet
     *
     */
    const TYPE = 'application/x-shockwave-flash';

    /**
     * Object classid
     *
     */
    const ATTRIB_CLASSID  = 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000';

    /**
     * Object Codebase
     *
     */
    const ATTRIB_CODEBASE = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab';

    /**
     * Default attributes
     *
     * @var array
     */
    protected $_attribs = array('classid'  => self::ATTRIB_CLASSID,
                                'codebase' => self::ATTRIB_CODEBASE);
    /**
     * Output a flash movie object tag
     *
     * @param string $data The flash file
     * @param array  $attribs Attribs for the object tag
     * @param array  $params Params for in the object tag
     * @param string $content Alternative content
     * @return string
     */
    public function htmlFlash($data, array $attribs = array(), array $params = array(), $content = null)
    {
        // Attrs
        $attribs = array_merge($this->_attribs, $attribs);

        // Params
        $params = array_merge(array('movie' => $data), $params);

        return $this->htmlObject($data, self::TYPE, $attribs, $params, $content);
    }
}
