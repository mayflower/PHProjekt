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
     * @param Phprojekt_Interface_Model|array $models The model to convert
     * @param int                             $order  A MODELINFO_ const that defines the ordering for the convert
     *
     * @return string
     */
    public static function convert ($models, $order = MODELINFO_ORD_DEFAULT)
    {
        if (null === $models) {
            return '/* */';
        }

        if (!is_array($models) && $models instanceof Phprojekt_Model_Interface) {
            $model = $models;
        } else if (is_array($models) && !empty($models)) {
            $model = current((array) $models);
        } else {
            return '/* */';
        }

        if (!$model instanceof Phprojekt_Model_Interface) {
            throw new InvalidArgumentException();
        }

        $information = $model->getInformation();
        $tag         = Phprojekt_Tags_Default::getInstance();

        /* we can check the returned array, but at the moment we just pass it */
        $datas   = array();
        $data    = array();
        $numRows = 0;

        /*
         * we have to do this ugly convert, because Zend_Json_Encoder doesnot check
         * if a value in an array is an object
         */
        if (!is_array($models) && $models instanceof Phprojekt_Model_Interface) {
            foreach ($information->getFieldDefinition() as $field) {
               $key   = $field['key'];
               $value = $models->$key;
               if (is_scalar($value)) {
                   $data[$key] = $value;
               } else {
                   $data[$key] = (string) $value;
               }
            }
            $datas[] = $data;
            $tags = $tag->getTagsByModule($models->getTableName(), $models->id);
        } else {
            foreach ($models as $cmodel) {
                foreach ($information->getFieldDefinition() as $field) {
                    $key   = $field['key'];
                    $value = $cmodel->$key;
                    if (is_scalar($value)) {
                        $data[$key] = $value;
                    } else {
                        $data[$key] = (string) $value;
                    }
                }
                $datas[] = $data;
            }
            $tags = $tag->getTags();
        }

        $numRows = count($datas);
        $data = array('metadata' => $information->getFieldDefinition($order),
                      'data'     => $datas,
                      'tags'     => $tags,
                      'numRows'  => (int)$numRows);

        // Enclose the json result in comments for security reasons, see "json-comment-filtered dojo"
        // the content-type dojo expects is: json-comment-filtered
        return '/* '.Zend_Json_Encoder::encode($data).' */';
    }

    /**
     * Convert a model or a model information into a json stream
     *
     * @param Phprojekt_Interface_Model $tree Tree instance to convert
     *
     * @return string
     */
    public static function convertTree (Phprojekt_Tree_Node_Database $tree)
    {
        $treeNodes = array();
        foreach ($tree as $node) {
            $references = array();
            foreach ($node->getChildren() as $child) {
                $references[] = array('_reference' => $child->id);
            }
            $treeNodes[] = array('name'     => $node->title,
                                 'id'       => $node->id,
                                 'parent'   => $node->projectId,
                                 'children' => $references);
        }

        $data               = array();
        $data['identifier'] = 'id';
        $data['label']      = 'name';
        $data['items']      = $treeNodes;

        return '/* '.Zend_Json_Encoder::encode($data).' */';
    }

    /**
     * Just convert a normal value
     * And return it with the json-comment-filtered
     *
     * @param mix $data Some value to convert
     *
     * @return string
     */
    public function covertValue($data)
    {
        return '/* '.Zend_Json_Encoder::encode($data).' */';
    }
}
