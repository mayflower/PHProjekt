<?php
/**
 * Simple ActiveRecord implementation based on Zend_Db_Table
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * Simple ActiveRecord implementation based on Zend_Db_Table
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
abstract class Phprojekt_ActiveRecord_Abstract extends Zend_Db_Table_Abstract
{
    /**
     * The format for the foreign key.
     * Available formatters are
     *  - :tableName
     *
     * Default is :tableName_id
     *
     */
    const FOREIGN_KEY_FORMAT = ':tableName_id';

    /**
     * Define the set of allowed characters for classes
     *
     */
    const CLASS_PATTERN = '[A-Za-z0-9_]';

    /**
     * models where this element does belong to,
     * for example owner, creator, mandator or similar
     *
     * @var array $belongsTo
     */
    public $belongsTo = array();

    /**
     * Other models that are connected to this model, for example relations
     *
     * @var array $hasMany
     */
    public $hasMany = array();

    /**
     * n to m relationship, like user <-> projects
     *
     * @var array $hasManyAndBelongsToMany
     */
    public $hasManyAndBelongsToMany = array();

    /**
     * models that this model just got one of each
     *
     * @var array $hasOne
     */
    public $hasOne = array();

    /**
     * Data array
     *
     * @var array $_data
     */
    protected $_data = array();

    /**
     * Relationship where clause
     * Filled with a simple where clause for belongsTo and hasMany relations
     * $this->_relations['simple'] and complex descriptions for
     * hasManyAndBelongsToMany in $this->_relations['hasManyAndBelongsToMany']
     *
     * @var string $_relations
     */
    protected $_relations = array();

    /**
     * Logging object from the global scope
     *
     * @var Zend_Log
     */
    protected $_log = null;

    /**
     * Defines if the id for the entry changed. We have to update
     * the relations then
     *
     * @var integer
     */
    protected $_storedId = null;

    /**
     * Initialize new object
     *
     * @param array  $config        Configuration for Zend_Db_Table
     */
    public function __construct($config)
    {
        if (Zend_Registry::isRegistered('log')) {
            $this->_log = Zend_Registry::get('log');
        }

        if (!array_key_exists('db', $config)
         || !is_a($config['db'], 'Zend_Db_Adapter_Abstract')) {
             throw new
               Phprojekt_ActiveRecord_Exception('ActiveRecord class must '
                                              . 'be initialized using a valid '
                                              . 'Zend_Db_Adapter_Abstract');

        }

        parent::__construct($config);

        $this->_initDataArray();
    }

    /**
     * Destructor
     *
     */
    function __destruct()
    {
    }

    protected function _initDataArray()
    {
        /*
         * We have to fill our data array with the colum names, as
         * __set makes a lookup on the _data keys to validate if
         * a column exists on the activerecord
         */
        $information = $this->info();
        foreach ($information['cols'] as $col) {
            $this->_data[$col] = null;
        }
    }

    /**
     * Translate our class name to a table name and setup it
     *
     * @return void
     */
    protected function _setupTableName()
    {
        $this->_name = $this->_translateClassNameToTable(get_class($this));
        parent::_setupTableName();
    }

    /**
     * 1) look if we got a method getVarname, if it's there, use it
     * 2) get value for varname from data array
     * 3) throw exception, if neither of them exists
     *
     * @param string $varname Name of the property to be set
     *
     * @return mixed
     */
    public function __get($varname)
    {
        $getter = 'get' . strtoupper($varname{0}) . substr($varname, 1);
        if (in_array($getter, get_class_methods(get_class()))) {
            return call_user_method($getter, $this);
        } elseif (array_key_exists($varname, $this->hasMany)
               && array_key_exists('id', $this->_data)) {
            return $this->_hasMany($varname);
        } elseif (array_key_exists($varname, $this->belongsTo)
               && array_key_exists('id', $this->_data)) {
            return $this->_belongsTo($varname);
        } elseif (array_key_exists($varname, $this->hasManyAndBelongsToMany)
               && array_key_exists('id', $this->_data)) {
            return $this->_hasManyAndBelongsToMany($varname);
        } elseif (array_key_exists($varname, get_object_vars($this))) {
            return $this->$varname;
        } elseif (array_key_exists($varname, $this->_data)) {
            return $this->_data[$varname];
        } elseif (array_key_exists('hasManyAndBelongsToMany', $this->_relations)
               && get_class($this) == $this->_relations['hasManyAndBelongsToMany']['refclass']) {
            return $this->_relations['hasManyAndBelongsToMany']['id'];
        } else {
            throw new Exception("{$varname} doesnot exist");
        }
    }

    /**
     * 1) look for method setVarname(value), use it
     * 2) if the attribute varname exists, set it's value
     *
     * @param string $varname Name of the property to be set
     * @param mixed  $value   Value of the property to be set
     *
     * @return void
     */
    public function __set($varname, $value)
    {
        $setter = 'set' . strtoupper($varname{0}) . substr($varname, 1);

        if (in_array($setter, get_class_methods(get_class()))) {
            call_user_method($setter, $this, $value);
        } elseif (array_key_exists($varname, get_object_vars($this))) {
            $this->$varname = $value;
        } elseif (array_key_exists($varname, $this->_data)) {
            $this->_data[$varname] = $value;
        } else {
            throw new Exception("{$varname} doesnot exist");
        }
    }

    /**
     *
     * @param string $key The name of the hasManyAndBelongsToMany relation
     *
     * @return Phprojekt_ActiveRecord
     */
    protected function _hasManyAndBelongsToMany($key)
    {
        if (!array_key_exists($key, $this->_data)) {
            $className = $this->_getClassNameForRelationship($key,
                                                $this->hasManyAndBelongsToMany);

            $instance = new $className(array('db' => $this->getAdapter()));
            $instance->_relations['hasManyAndBelongsToMany'] =
                    array('id'       => $this->id,
                         'classname' => get_class($this),
                         'refclass'  => $className); // needed for __get

            $this->_data[$key] = $instance;
        }

        return $this->_data[$key];
    }

    /**
     * Overwrite the fetch method if we have a hasManyAndBelongsToMany relation.
     * This is needed as relations are handles using table objects on the ZF
     * but as we want to keep things simple we don't want to have a
     * e.g.: RoleUserRel object. We also cannot create this object on runtime
     * as the Zend_Db_Table Relationships needs class names, and cannot handle
     * just objects.
     *
     * @see _fetch
     *
     * @return unknown
     */
    protected function _fetchHasManyAndBelongsToMany($where = null)
    {
        $className = $this->_relations['hasManyAndBelongsToMany']['classname'];
        $classId   = $this->_relations['hasManyAndBelongsToMany']['id'];

        $tableNames   = array();

        $foreignKeyName = $this->_translateKeyFormat(get_class($this));
        $myKeyName      = $this->_translateKeyFormat($className);

        $foreignTable = $this->_translateClassNameToTable(get_class($this));
        $myTable      = $this->_translateClassNameToTable($className);
        $tableNames[] = $myTable;
        $tableNames[] = $foreignTable;

        sort($tableNames);
        reset($tableNames);

        $tableName = sprintf('%s_rel', implode('_', $tableNames));

        $select = $this->getAdapter()->select();
        $select->from(array('rel'     => $tableName), array())
               ->from(array('foreign' => $foreignTable))
               ->from(array('my'      => $myTable), array());

        $select->where(sprintf("my.id = rel.%s", $myKeyName));
        $select->where(sprintf("foreign.id = rel.%s", $foreignKeyName));
        $select->where(sprintf("rel.%s = ?", $myKeyName), $classId);

        /*
         * somewhat special, we might have a better solution here once.
         * At the moment we asume that the where clause contains the id string,
         * as it is called from find()
         */
        if (null !== $where)
            $select->where(str_replace('`id`', 'foreign.id', $where));

        if (null !== $this->_log) {
            $this->_log->log($select->__toString(), 'DEBUG');
        }

        $stmt = $this->getAdapter()->query($select);
        return $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
    }

    /**
     * Overrwite fetch method to support hasManyAndBelongsToMany relationship
     * For more information about that.
     *
     * @param string|array $where  Where clause
     * @param string|array $order  Order clause
     * @param string|array $count  Limit
     * @param string|array $offset Offset
     *
     * @return array
     */
    protected function _fetch($where = null, $order = null, $count = null, $offset = null)
    {
        if (array_key_exists('hasManyAndBelongsToMany', $this->_relations)
         && is_array($this->_relations['hasManyAndBelongsToMany'])) {
            return $this->_fetchHasManyAndBelongsToMany($where);
         } else {
            return parent::_fetch($where, $order, $count, $offset);
         }
    }

    /**
     * Enter description here...
     *
     * @param string $key The name of the belongsTo relation
     *
     * @return Phprojekt_ActiveRecord
     */
    protected function _belongsTo($key)
    {
        if (!array_key_exists($key, $this->belongsTo)) {
            throw new
             Phprojekt_ActiveRecord_Exception('BelongsTo {$key} doesnot exist');
        }

        if (!array_key_exists($key, $this->_data)) {
            $className = $this->_getClassNameForRelationship($key,
                                                             $this->belongsTo);

            $instance = new $className(array('db' => $this->getAdapter()));

            $foreignKeyName = $this->_translateKeyFormat($className);

            if (array_key_exists($foreignKeyName, $this->_data)) {
                $row = $instance->find($this->_data[$foreignKeyName]);
                foreach ($row as $k => $v) {
                    $instance->_data[$k] = $v;
                }
            }

            $instance->_storedId = $instance->_data['id'];

            $this->_data[$key] = $instance;
        }

        return $this->_data[$key];
    }

    /**
     * Enter description here...
     *
     * @param string $key The name of the hasMany relation
     *
     * @return Phprojekt_ActiveRecord
     */
    protected function _hasMany($key)
    {
        if (!array_key_exists($key, $this->hasMany)) {
            throw new
               Phprojekt_ActiveRecord_Exception('HasMany {$key} doesnot exist');
        }

        if (!array_key_exists($key, $this->_data)) {
            /*
             * There is no object in the data array yet, so we have to
             * create a new instance with all the necessary relation information
             * This is done by passing a simple where clause to the object.
             * We also do a guess on the real class name. Either there is a
             * 'classname' key in the hasMany array or we use the provided key
             * itself.
             */
            $className = $this->_getClassNameForRelationship($key,
                                                             $this->hasMany);

            /* @var Phprojekt_ActiveRecord $instance */
            $instance = new $className(array('db' => $this->getAdapter()));
            /*
             * $instance->_relations['simple'] = );
             */
            $instance->_relations['hasMany'] =
                                        array('id'       => $this->id,
                                             'classname' => get_class($this),
                                             'refclass'  => $className);

            $instance->_storedId = $instance->_data['id'];

            $this->_data[$key] = $instance;
        }

        return $this->_data[$key];
    }


    /**
     * Fetches all rows according to the where, order, count, offset rules
     *
     * @param string|array $where  Where clause
     * @param string|array $order  Order by
     * @param string|array $count  Limit query
     * @param string|array $offset Query offset
     *
     * @return Zend_Db_Table_Rowset
     */
    public function fetchAll($where = null, $order = null,
                             $count = null, $offset = null)
    {
        $wheres = array();
        if (array_key_exists('hasMany', $this->_relations)) {
            $wheres[] = $this->getAdapter()->quoteInto
                            (sprintf('%s = ?',
                                $this->_translateKeyFormat(
                                    $this->_relations['hasMany']['classname'])),
                                $this->_relations['hasMany']['id']);
        }
        if (null !== $where) {
            $wheres[] = $where;
        }

        $where = (count($wheres) > 0) ? implode(' AND ', $wheres) : null;
        $rows = parent::fetchAll($where, $order,
                                 $count, $offset);

        $result = array();
        foreach ($rows as $row) {
            $instance          = clone $this;
            $instance->_data   = array();

            foreach ($row->toArray() as $k => $v) {
                $instance->_data[$k] = $v;
            }

            $instance->_storedId = $instance->_data['id'];

            $result[] = $instance;
        }

        return $result;
    }

    /**
     * Creates a new instance and preserve relation information.
     * Use this method to create a new object to save a relation
     *
     * @return Phprojekt_ActiveRecord_Abstract
     */
    public function create()
    {
        $instance = clone $this;
        $instance->_relations = $this->_relations;
        return $instance;
    }

    /**
     * Overwrite the clone id, to reset _storeId and reinit the data array
     *
     * @return void
     */
    public function __clone()
    {
        $this->_data = array();
        $this->_storedId = null;
        $this->_initDataArray();
    }

    /**
     * Overwrite the find method to get relations too
     *
     * @return Phprojekt_ActiveRecord
     */
    public function find()
    {
        $args = func_get_args();

        $find = parent::find($args[0]);
        $find = $find[0];

        /*
         * reset data as all our relatios, etc stuff has to
         * deal with a new id
         */
        $this->_data      = array();
        $this->_storedId  = null;

        $this->_data = (array) $find->_data;
        unset($find);

        $this->_storedId = $this->_data['id'];

        return $this;
    }

    /**
     * Save an entry. We either trigger update or create here.
     *
     * @return void
     */
    public function save()
    {
        $data = array();

        foreach ($this->_data as $k => $v) {
            if (is_scalar($v)) {
                $data[$k] = $v;
            }
        }

        /*
         * If we have a storedId, the item was received from the databas
         * and therefore should exist on the database, so we trigger an update.
         * Otherwise we create the entry.
         */
        if (null !== $this->_storedId) {
            $this->update($data,
                $this->getAdapter()->quoteInto('id = ?', $this->_storedId));

            if ($this->id !== $this->_storedId
             && count($this->hasMany) > 0) {
                 $this->_updateHasMany($this->_storedId, $this->id);
             }

             if ($this->id !== $this->_storedId
              && count($this->hasManyAndBelongsToMany) > 0) {
                 $this->_updateHasManyAndBelongsToMany($this->_storedId,
                                                        $this->id);
             }
        } else {
            /*
             * We have to insert before we update the relations, as we
             * need the new id for the relations (e.g.: n:m relations).
             */
            $this->insert($data);
            $this->_data['id'] = $this->getAdapter()->lastInsertId();
            $this->_storedId   = $this->_data['id'];

            if (array_key_exists('hasMany', $this->_relations)) {
                $foreignKeyName = $this->_translateKeyFormat(
                                    $this->_relations['hasMany']['classname']);
                $data[$foreignKeyName] = $this->_relations['hasMany']['id'];
            }

            if (array_key_exists('hasManyAndBelongsToMany', $this->_relations)) {
                $this->_insertHasManyAndBelongsToMany();
            }
        }
    }

    /**
     * Insert a n:m relation into the relation table
     *
     * @return void
     */
    protected function _insertHasManyAndBelongsToMany()
    {
        $className = $this->_relations['hasManyAndBelongsToMany']['classname'];
        $foreignId = $this->_relations['hasManyAndBelongsToMany']['id'];

        $foreignKeyName = $this->_translateKeyFormat($className);
        $myKeyName      = $this->_translateKeyFormat(get_class($this));

        $tableName = $this->_translateIntoRelationTableName(get_class($this),
                                                           $className);
        $query = sprintf ("INSERT INTO %s (%s, %s) VALUES (?, ?)",
                          $tableName, $myKeyName, $foreignKeyName);
        $stmt = $this->getAdapter()->prepare($query);
        $stmt->execute(array($this->id, $foreignId));
    }

    /**
     * Count
     *
     * @return integer
     */
    public function count()
    {
        return parent::fetchAll()->count();
    }

    /**
     * Enter description here...
     *
     * @param integer $oldId
     * @param integer $newId
     */
    protected function _updateHasMany($oldId, $newId)
    {
        foreach ($this->hasMany as $key => $relationInfo) {
            $className = $this->_getClassNameForRelationship($key,
                                                    $this->hasMany);
            $tableName  = $this->_translateClassNameToTable($className);
            $columnName = $this->_translateKeyFormat(get_class($this));

            $query = sprintf("UPDATE %s SET %s = ? WHERE %s = ?",
                         $tableName, $columnName, $columnName);

            /* @var Zend_Db_Statement $stmt */
            $stmt = $this->getAdapter()->prepare($query);
            $stmt->execute(array($newId, $oldId));

            /*
             * Manually update. Not nice, but effective.
             */
            if (array_key_exists($key, $this->_data)) {
                foreach ($this->_data[$key] as $instance) {
                    $instance->_data[$columnName] = $newId;
                }
            }
        }
    }


    /**
     * Enter description here...
     *
     * @param integer $oldId
     * @param integer $newId
     */
    protected function _updateHasManyAndBelongsToMany($oldId, $newId)
    {
        foreach ($this->hasManyAndBelongsToMany as $key => $relationInfo) {
            $className = $relationInfo['classname'];

            $myKeyName = $this->_translateKeyFormat(get_class($this));
            $tableName = $this->_translateIntoRelationTableName(
                                get_class($this), $className);


            $select = $this->getAdapter()->select();

            $query = sprintf("UPDATE %s SET %s = ? WHERE %s = ?",
                            $tableName, $myKeyName, $myKeyName);

            /* @var Zend_Db_Statement $stmt */
            $stmt = $this->getAdapter()->prepare($query);
            $stmt->execute(array($newId, $oldId));

            /*
             * Manually update. Not nice, but effective.
             */
            if (array_key_exists($key, $this->_data)) {
                foreach ($this->_data[$key] as $instance) {
                    $instance->_data[$columnName] = $newId;
                }
            }
        }
    }

    /**
     * Tries to figure out the classname for a relationship
     *
     * @param string $key   Name of the requested attribute
     * @param array  $array Array to search
     *
     * @return string
     */
    protected function _getClassNameForRelationship($key, $array)
    {
        if (is_array($array)
         && is_array($array[$key])
         && array_key_exists('classname', $array[$key])) {
            $className = $array[$key]['classname'];
        } else {
            $className = $key;
        }

        if (!class_exists($className, true)) {
            throw new
                Phprojekt_ActiveRecord_Exception('Cannot instantiate'
                                               . '{$className}');
        }

        return $className;
    }

    /**
     * Translates the class name to a table name.
     *
     * @param string $className Name of the class
     *
     * @example
     *  Class name: 'Phprojekt_Project'
     *  will be translated to
     *  Table name: 'projects'
     * @throws Phprojekt_ActiveRecord_Exception Thrown if an class name
     *  contains illegal chars
     * @return string
     */
    protected function _translateClassNameToTable($className)
    {
        if (preg_match('@' . self::CLASS_PATTERN . '@', $className)) {
            if (preg_match('@(?:.*?_)?([A-Za-z0-9]+)$@', $className, $match)) {

                $name = preg_replace('@([A-Z])@', '_\\1', $match[1]);

                if ($name{0} == '_') $name = substr($name, 1);

                return strtolower($name);
            }
        }
        throw new Phprojekt_ActiveRecord_Exception('Classname contains '
                                                  .'illegal characters');

    }

    /**
     * Translate a class name into a foreign key name
     *
     * @param string $className Name of the class
     *
     * @return string
     */
    protected function _translateKeyFormat($className)
    {
        $tableName = $this->_translateClassNameToTable($className, false);

        return str_replace(':tableName', $tableName, self::FOREIGN_KEY_FORMAT);
    }

    /**
     * We translate the names of two classes into a relation table
     * Its always {CLASS1}_{CLASS2}_rel while the classes are sorted
     * in alphabetic order.
     */
    protected function _translateIntoRelationTableName($myClassName, $foreignClassName)
    {
        $tableNames   = array();
        $myTable      = $this->_translateClassNameToTable($myClassName);
        $foreignTable = $this->_translateClassNameToTable($foreignClassName);
        $tableNames[] = $myTable;
        $tableNames[] = $foreignTable;

        sort($tableNames);
        reset($tableNames);

        return sprintf('%s_rel', implode('_', $tableNames));
    }
}