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
     * @param int                             $order  A Phprojekt_ModelInformation_Default::ORDERING_*
     *                                                const that defines the ordering for the convert
     *
     * @return string
     */
    public static function convert($models, $order = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        if (null === $models) {
            return '{}&&{"metadata":[]}';
        }

        if (!is_array($models) && $models instanceof Phprojekt_Model_Interface) {
            $model = $models;
        } else if (is_array($models) && !empty($models)) {
            $model = current((array) $models);
        } else {
            return '{}&&{"metadata":[]}';
        }

        if (!$model instanceof Phprojekt_Model_Interface) {
            throw new InvalidArgumentException();
        }

        $information = $model->getInformation($order);

        /* we can check the returned array, but at the moment we just pass it */
        $datas   = array();
        $data    = array();
        $numRows = 0;

        /*
         * we have to do this ugly convert, because Zend_Json_Encoder doesnot check
         * if a value in an array is an object
         */
        if (!is_array($models) && $models instanceof Phprojekt_Model_Interface) {
            foreach ($information->getFieldDefinition($order) as $field) {
               $data['id'] = $models->id;

               $key   = $field['key'];
               $value = $models->$key;
               if (is_scalar($value)) {
                   $data[$key] = $value;
               } else {
                   $data[$key] = (string) $value;
               }
               $data['rights'] = $model->getRights(Phprojekt_Auth::getUserId());

            }
            $datas[] = $data;
        } else {
            foreach ($models as $cmodel) {
                $data['id'] = $cmodel->id;
                foreach ($information->getFieldDefinition($order) as $field) {
                    $key   = $field['key'];
                    $value = $cmodel->$key;
                    if (is_scalar($value)) {
                        $data[$key] = $value;
                    } else {
                        $data[$key] = (string) $value;
                    }

                    $data['rights'] = $model->getRights(Phprojekt_Auth::getUserId());
                }
                $datas[] = $data;
            }
        }

        $numRows = count($datas);
        $data = array('metadata' => $information->getFieldDefinition($order),
                      'data'     => $datas,
                      'numRows'  => (int)$numRows);

        // Enclose the json result in comments for security reasons, see "json-comment-filtered dojo"
        // the content-type dojo expects is: json-comment-filtered
        // header('Content-Type','application/json');
        return '{}&&'.Zend_Json_Encoder::encode($data);
    }

    /**
     * Convert a model or a model information into a json stream
     *
     * @param Phprojekt_Interface_Model $tree Tree instance to convert
     *
     * @return string
     */
    public static function convertTree(Phprojekt_Tree_Node_Database $tree)
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

        return Zend_Json_Encoder::encode($data);
    }

    /**
     * Just convert a normal value
     * And return it with the json-comment-filtered
     *
     * @param mix $data Some value to convert
     *
     * @return string
     */
    public function convertValue($data)
    {
        return Zend_Json_Encoder::encode($data);
    }

    /**
     * Convert the tag data to json-format
     *
     * @param array $data            The tags values
     * @param array $fieldDefinition The definition of each field
     *
     * @return string
     */
    public function convertTag($data, $fieldDefinition)
    {
        $numRows = count($data);
        $data = array('metadata' => $fieldDefinition,
                      'data'     => $data,
                      'numRows'  => (int)$numRows);

        return Zend_Json_Encoder::encode($data);
    }
}
