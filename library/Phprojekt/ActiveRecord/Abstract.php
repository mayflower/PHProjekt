<?php
/**
 * Simple ActiveRecord implementation based on Zend_Db_Table
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * Simple ActiveRecord implementation based on Zend_Db_Table
 *
 * Every ActiveRecord object represents a row in a database table.
 * The name of the table is get by naming conventions based on the class name.
 * All fields of the table are accessable using the name of the column
 * as an attribute identifier (e.g. $myRecord->title returns the title column).
 * There are various ways to influence this behaviour. See __get for more
 * details.
 *
 * Every new instantiated active record is considered a new row and a call
 * to save() will cause an insert into the database unless a particular
 * record is received using the find() method or fetchAll().
 *
 * Furthermore the ActiveRecord allows you to map database relations using
 * classes. This is done by providing the $hasMany, $hasManyAndBelongsToMany
 * and $belongsTo array. Every of these arrays accept an array with an
 * identifier as key and an array of model, module keys to identify the
 * class the relation is mapped to. The identifier key is used to access
 * the relation using attributes. For a more detailed explainations how
 * Phprojekt implement active record, take a look at the development
 * documentation
 *
 * NOTE for developers:
 *   If you have an attribute defined in your class that has the same name
 *   like an column, the attribute is returned NOT the column. You will
 *   hide the column
 * NOTE for developers:
 *   If you define the relation attribute name make sure the name doesn't exists
 *   twice and is unique in ALL $hasMany, $belongsTo, $hasManyAndBelongsToMany.
 *   If a key exists more often they are exactly evaluted in the above order.
 *   e.g. if the key exists in hasMany and in belongsTo, you will get the
 *   hasMany relation.
 * Naming Convention:
 *   A class is mapped to the database using the last part (after the last _)
 *   of the class name
 *
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
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
    const FOREIGN_KEY_FORMAT = ':tableNameId';

    /**
     * Models where this element does belong to,
     * for example owner, creator, mandator or similar.
     *
     * @var array $belongsTo
     */
    public $belongsTo = array();

    /**
     * Other models that are connected to this model.
     * The entries of this array should have the form
     *  'IDENTIFIER' => array ('module' => 'MODULENAME',
     *                         'model'  => 'CLASSNAME');
     * MODULENAME is the name of the module where to search for the
     * related model. CLASSNAME is the model name while IDENTIFIER
     * is the name of the attribute of *this* class, that is used to
     * access the relation.
     * @example
     *  $hasMany = array('usersettings'=> array('module'=>'Users',
     *                                          'model' =>'UserModuleSetting');
     * @var array $hasMany
     */
    public $hasMany = array();

    /**
     * n to m relationship, like user <-> projects
     *
     * Works exactly like hasMany but uses an relation table based
     * on the names of the two involved classes.
     *
     * @see $hasMany
     *
     * @var array $hasManyAndBelongsToMany
     */
    public $hasManyAndBelongsToMany = array();

    /**
     * models that this model just got one of each
     *
     * @see $hasMany
     *
     * @var array $hasOne
     */
    public $hasOne = array();

    /**
     * Data array. This holds actually all our data from the database
     * and values are passed from there using __get
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
     * Logging object from the global scope.
     * Fetched in the constructor.
     *
     * @var Zend_Log
     */
    protected $_log;

    /**
     * Defines if the id for the entry changed. We have to update
     * the relations then. It might happend that the real id differes
     * from the storedId e.g. the programmer changed the id of the
     * record and not yet updated/saved it into the database
     *
     * @var integer
     */
    protected $_storedId;

    /**
     * A list of the actual columns accessable through this active record object.
     * This is not similar to the keys of the _data array, as this might hold
     * also belongs, etc stuff. Furthermore we need something with an internal
     * pointer as this is used by the iterator implementation.
     */
    protected $_colInfo;

    /**
     * Initialize new object
     *
     * @param array $config Configuration for Zend_Db_Table
     */
    public function __construct($config = null)
    {
        //if (Zend_Registry::isRegistered('log')) {
        //    $this->_log = Zend_Registry::get('log');
        //}

        if (null === $config) {
            $config = array('db' => Zend_Registry::get('db'));
        }

        if (!is_array($config)) {
            $config = array('db' => $config);
        }

        if (!array_key_exists('db', $config)
        || !($config['db'] instanceof Zend_Db_Adapter_Abstract)) {
            throw new
            Phprojekt_ActiveRecord_Exception("ActiveRecord class must "
            . "be initialized using a valid "
            . "Zend_Db_Adapter_Abstract");

        }

        parent::__construct($config);

        $info           = $this->info();
        $this->_colInfo = $info['cols'];

        $this->_initDataArray();
    }

    /**
     * Destructor
     *
     */
    function __destruct()
    {
    }

    /**
     * Iterator implementation.
     * Returns the current element from the data array.
     *
     * @see Iterator::current()
     *
     * @return mixed
     */
    public function current ()
    {
        return $this->_data[$this->key()];
    }

    /**
     * Returns the name of the current field
     *
     * @see Iterator::key()
     *
     * @return string
     */
    public function key()
    {
        return current($this->_colInfo);
    }

    /**
     * Moves the internal iterator pointer one forward, befor
     * recieving the element with current()
     *
     * @see Iterator::next()
     *
     * @return string
     */
    public function next ()
    {
        next($this->_colInfo);
    }

    /**
     * Reset the internal pointer. As our iterator is just a wrapper
     * over the _data array, we just reset the internal array pointer of
     * _data
     *
     * @see Iterator::rewind()
     *
     * @return string
     */
    public function rewind()
    {
        reset($this->_colInfo);
    }

    /**
     * Checks if there is a current element after a next or rewind call.
     * We just check if the internal array pointer is null or not.
     *
     * @see Iterator::valid()
     *
     * @return boolean
     */
    public function valid()
    {
        return false !== current($this->_colInfo);
    }

    /**
     * Initialize the data array. The data array has empty values
     * after that, but the keys exist!
     *
     * @return void
     */
    protected function _initDataArray()
    {
        /*
        * We have to fill our data array with the colum names, as
        * __set makes a lookup on the _data keys to validate if
        * a column exists on the activerecord
        */
        foreach ($this->_colInfo as $col) {
            $this->_data[$col] = null;
        }
    }

    /**
     * Translate our class name to a table name and setup it.
     * See Naming Conventions for more information.
     *
     * @return void
     */
    protected function _setupTableName()
    {
        $this->_name = $this->getTableName();
        parent::_setupTableName();
    }

    /**
     * Checks if a key exists in the system
     *
     * @param string $key Name of the key
     *
     * @return boolean
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->_data) || array_key_exists($key, get_object_vars($this));
    }

    /**
     * 1) look if we got a method getVarname, if it's there, use it
     * 2) Look if the attribtename is a defined relation,
     *    if this is true, the relation object will be initialized and returned
     * 3) Lookup if the attribute itself has this attribute and return it
     *    if exists
     * 4) Get value for varname from data array if exists
     * 5) throw exception, if neither of them exists
     *
     * @param string $varname Name of the property to be set
     *
     * @return mixed
     */
    public function __get($varname)
    {
        $varname = trim($varname);
        $getter  = 'get' . ucfirst($varname);
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
        } elseif (array_key_exists('hasManyAndBelongsToMany', $this->_relations) &&
                  get_class($this) == $this->_relations['hasManyAndBelongsToMany']['refclass']) {
            return $this->_relations['hasManyAndBelongsToMany']['id'];
        } else {
            throw new Exception("{$varname} does not exist");
        }
    }

    /**
     * 1) look for method setVarname(value), use it
     * 2) if the attribute varname exists, set it's value
     * 3) Set the value in the data array
     *
     * @param string $varname Name of the property to be set
     * @param mixed  $value   Value of the property to be set
     *
     * @return void
     */
    public function __set($varname, $value)
    {
        $setter = 'set' . ucfirst($varname);

        if (in_array($setter, get_class_methods(get_class()))) {
            call_user_method($setter, $this, $value);
        } elseif (array_key_exists($varname, get_object_vars($this))) {
            $this->$varname = $value;
        } elseif (array_key_exists($varname, $this->_data)) {
            $this->_data[$varname] = $value;
        } else {
            throw
            new Phprojekt_ActiveRecord_Exception("{$varname} doesnot exist");
        }
    }

    /**
     * Initialize the _relations array for 1..n relation and initialize
     * a new object of the relation class and put it into the data array.
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
     * @param string $where A query clause
     *
     * @see _fetch
     *
     * @return unknown
     */
    protected function _fetchHasManyAndBelongsToMany($where = null)
    {
        $adapter = $this->getAdapter();

        $className = $this->_relations['hasManyAndBelongsToMany']['classname'];
        $classId   = $this->_relations['hasManyAndBelongsToMany']['id'];

        $foreignKeyName = $this->_translateKeyFormat(get_class($this));
        $myKeyName      = $this->_translateKeyFormat($className);

        $foreignTable = $this->getTableName(); // $this->_translateClassNameToTable(get_class($this));
        $im           = new $className($this->getAdapter());
        $myTable      = $im->getTableName(); // $className::getClassName();

        $tableName = $this->_translateIntoRelationTableName($im, $this);

        $select = $adapter->select()->from(array('rel' => $tableName));

        $select->joinInner(array('my' => $myTable),
        sprintf("%s = %s",
        $adapter->quoteIdentifier("my.id"),
        $adapter->quoteIdentifier("rel." . $myKeyName)));

        $select->joinInner(array('foreign' => $foreignTable),
        sprintf("%s = %s",
        $adapter->quoteIdentifier("foreign.id"),
        $adapter->quoteIdentifier("rel." . $foreignKeyName)));
        if (isset($classId)) {
            $select->where(sprintf("%s = ?",
            $adapter->quoteIdentifier("rel." . $myKeyName)),
            $classId);
        }
        /*
        * somewhat special, we might have a better solution here once.
        * At the moment we asume that the where clause contains the id string,
        * as it is called from find()
        */
        if (null !== $where) {
            $select->where(str_replace($adapter->quoteIdentifier("id"),
            $adapter->quoteIdentifier("foreign.id"), $where));
        }

        if (null !== $this->_log) {
            $this->_log->debug((string) $select);
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
     * Receive a belongsTo relation
     *
     * @param string $key The name of the belongsTo relation
     *
     * @return Phprojekt_ActiveRecord
     */
    protected function _belongsTo($key)
    {
        if (!array_key_exists($key, $this->belongsTo)) {
            throw new
            Phprojekt_ActiveRecord_Exception("BelongsTo {$key} doesnot exist");
        }

        if (!array_key_exists($key, $this->_data)) {
            $className = $this->_getClassNameForRelationship($key, $this->belongsTo);

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
     * Receive a hasMany relation
     *
     * @param string $key The name of the hasMany relation
     *
     * @return Phprojekt_ActiveRecord
     */
    protected function _hasMany($key)
    {
        if (!array_key_exists($key, $this->hasMany)) {
            throw new
            Phprojekt_ActiveRecord_Exception("HasMany {$key} doesnot exist");
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

            $instance = new $className(array('db' => $this->getAdapter()));
            /* @var Phprojekt_ActiveRecord $instance */

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
     * Insert a n:m relation into the relation table
     *
     * @return boolean
     */
    protected function _insertHasManyAndBelongsToMany()
    {
        $className = $this->_relations['hasManyAndBelongsToMany']['classname'];
        $foreignId = $this->_relations['hasManyAndBelongsToMany']['id'];

        $foreignKeyName = $this->_translateKeyFormat($className);
        $myKeyName      = $this->_translateKeyFormat(get_class($this));

        $im        = new $className($this->getAdapter());
        $tableName = $this->_translateIntoRelationTableName($this, $im);

        $query = sprintf("INSERT INTO %s (%s, %s) VALUES (?, ?)",
                         $this->getAdapter()->quoteIdentifier($tableName),
                         $this->getAdapter()->quoteIdentifier($myKeyName),
                         $this->getAdapter()->quoteIdentifier($foreignKeyName));

        if (null !== $this->_log) {
            $this->_log->debug($query);
        }

        $stmt = $this->getAdapter()->prepare($query);
        return $stmt->execute(array($this->id, $foreignId));
    }

    /**
     * Update    an hasMany relation
     *
     * @param integer $oldId The old primary id of the record
     * @param integer $newId The new primary id of the record
     *
     * @return void
     */
    protected function _updateHasMany($oldId, $newId)
    {
        $result = true;
        foreach (array_keys($this->hasMany) as $key) {
            $className = $this->_getClassNameForRelationship($key, $this->hasMany);

            $im         = new $className($this->getAdapter());
            $tableName  = $im->getTableName(); // fassNameToTable($className);
            $columnName = $this->_translateKeyFormat(get_class($this));

            $query = sprintf("UPDATE %s SET %s = ? WHERE %s = ?",
            $this->getAdapter()->quoteIdentifier($tableName),
            $this->getAdapter()->quoteIdentifier($columnName),
            $this->getAdapter()->quoteIdentifier($columnName));

            if (null !== $this->_log) {
                $this->_log->debug($query);
            }

            /* @var Zend_Db_Statement $stmt */
            $stmt   = $this->getAdapter()->prepare($query);
            $result = $stmt->execute(array($newId, $oldId)) && $result;

            /*
            * Manually update. Not nice, but effective.
            */
            if (array_key_exists($key, $this->_data)) {
                foreach ($this->_data[$key] as $instance) {
                    $instance->_data[$columnName] = $newId;
                }
            }
        }

        return $result;
    }


    /**
     * Update an has many and belongs to many relation
     *
     * @param integer $oldId The old primary id of the record
     * @param integer $newId The new primary id of the record
     *
     * @return void
     */
    protected function _updateHasManyAndBelongsToMany($oldId, $newId)
    {
        $result = true;
        foreach ($this->hasManyAndBelongsToMany as $key => $relationInfo) {
            $className = $relationInfo['classname'];
            $im        = new $className($this->getAdapter());

            $myKeyName = $this->_translateKeyFormat(get_class($this));
            $tableName = $this->_translateIntoRelationTableName($this, $im);

            $query = sprintf("UPDATE %s SET %s = ? WHERE %s = ?",
            $this->getAdapter()->quoteIdentifier($tableName),
            $this->getAdapter()->quoteIdentifier($myKeyName),
            $this->getAdapter()->quoteIdentifier($myKeyName));

            if (null !== $this->_log) {
                $this->_log->debug($query);
            }

            /* @var Zend_Db_Statement $stmt */
            $stmt   = $this->getAdapter()->prepare($query);
            $result = $stmt->execute(array($newId, $oldId)) && $result;

            /*
            * Manually update. Not nice, but effective.
            */
            if (array_key_exists($key, $this->_data)) {
                foreach ($this->_data[$key] as $instance) {
                    $instance->_data[$myKeyName] = $newId;
                }
            }
        }

        return $result;
    }

    /**
     * Tries to figure out the classname for a relationship
     *
     * @param string $key   Name of the requested attribute
     * @param array  $array Array to search
     *
     * @return string
     */
    protected static function _getClassNameForRelationship($key, $array)
    {
        if (!is_array($array) || !is_array($array[$key])) {
            throw new
            Phprojekt_ActiveRecord_Exception("The second parameter"
            . "must be an array");
        }

        $definition = $array[$key];

        if (array_key_exists('classname', $definition)) {
            $className = $array[$key]['classname'];
        } elseif (array_key_exists('model', $definition)
        && array_key_exists('module', $definition)) {
            $className = Phprojekt_Loader::getModelClassName($definition['module'],
            $definition['model']);
        } else {
            $className = $key;
        }

        if (!class_exists($className, true)) {
            throw new
            Phprojekt_ActiveRecord_Exception("Cannot instantiate"
            . "{$className}");
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
    protected static function _translateClassNameToTable($className)
    {
        $preg = sprintf('(?:%s_)?(%s)$', Phprojekt_Loader::CLASS_PATTERN, Phprojekt_Loader::CLASS_PATTERN);

        $match = array();
        if (preg_match('@' . $preg . '@', $className, $match)) {
            return $match[1];
        }

        throw new Phprojekt_ActiveRecord_Exception("Classname contains "
        ."illegal characters");

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
        $im        = new $className($this->getAdapter());
        $tableName = $im->getTableName(); // $this->_translateClassNameToTable($className, false);
        $keyName   = str_replace(':tableName', $tableName, self::FOREIGN_KEY_FORMAT);
        $keyName{0}= strtolower($keyName{0});
        if (null !== $this->_log) {
            $this->_log->debug(sprintf("%s translated to %s",
            $className, $keyName));
        }

        unset ($im);

        return $keyName;
    }

    /**
     * We translate the names of two classes into a relation table
     * Its always {CLASS1}_{CLASS2}_rel while the classes are sorted
     * in alphabetic order.
     *
     * @param string $myObject      Own class
     * @param string $foreignObject Foreign class
     *
     * @return string
     */
    protected static function _translateIntoRelationTableName(Phprojekt_ActiveRecord_Abstract $myObject,
    Phprojekt_ActiveRecord_Abstract $foreignObject)
    {
        $tableNames   = array();
        $myTable      = $myObject->getTableName();
        $foreignTable = $foreignObject->getTableName();
        $tableNames[] = $myTable;
        $tableNames[] = $foreignTable;

        sort($tableNames);
        reset($tableNames);

        $tableName = sprintf('%sRelation', implode('', $tableNames));

        return $tableName;

    }

    /**
     * Overwrite the clone id, to reset _storeId and reinit the data array
     *
     * @return void
     */
    public function __clone()
    {
        $this->_data     = array();
        $this->_storedId = null;
        $this->_initDataArray();
    }

    /**
     * Creates a new instance and preserve relation information.
     * Use this method to create a new object to save a relation
     *
     * @return Phprojekt_ActiveRecord_Abstract
     */
    public function create()
    {
        $instance             = clone $this;
        $instance->_relations = $this->_relations;
        return $instance;
    }

    /**
     * Save an entry. We either trigger update or create here.
     *
     * @return boolean
     */
    public function save()
    {
        $data = array();

        foreach ($this->_data as $k => $v) {
            if (in_array($k, $this->_colInfo) && is_scalar($v)) {
                $data[$k] = $v;
            }
        }

        $result = true;
        /*
        * If we have a storedId, the item was received from the database
        * and therefore should exist on the database, so we trigger an update.
        * Otherwise we create the entry.
        */
        if (null !== $this->_storedId) {
            $result = ($this->update($data, $this->getAdapter()->quoteInto('id = ?', $this->_storedId)) > 0);

            if ($this->id !== $this->_storedId
            && count($this->hasMany) > 0) {
                $result = $this->_updateHasMany($this->_storedId, $this->id) && $result;
            }

            if ($this->id !== $this->_storedId
            && count($this->hasManyAndBelongsToMany) > 0) {
                $result = $this->_updateHasManyAndBelongsToMany($this->_storedId, $this->id) && $result;
            }
        } else {
            /*
            * We have to insert before we update the relations, as we
            * need the new id for the relations (e.g.: n:m relations).
            */
            if (array_key_exists('hasMany', $this->_relations)) {
                $foreignKeyName        = $this->_translateKeyFormat($this->_relations['hasMany']['classname']);
                $data[$foreignKeyName] = (int) $this->_relations['hasMany']['id'];

                $this->_data[$foreignKeyName] = $data[$foreignKeyName];
            }

            $result            = ($this->insert($data) !== null);
            $this->_data['id'] = $this->_db->lastInsertId();
            $this->_storedId   = $this->_data['id'];

            if (array_key_exists('hasManyAndBelongsToMany', $this->_relations)) {
                $result = $this->_insertHasManyAndBelongsToMany() && $result;
            }
        }

        return $result;
    }

    /**
     * Delete a record and all his relations
     *
     * @return void
     */
    public function delete()
    {
        if (array_key_exists('id', $this->_data)) {
            if (array_key_exists('hasMany', $this->_relations)
            || count($this->hasMany) > 0) {           	
                foreach (array_keys($this->hasMany) as $key) {
                    $className = $this->_getClassNameForRelationship($key,
                    $this->hasMany);
                    $im        = new $className($this->getAdapter());
                    $tableName  = $im->getTableName();
                    $columnName = $this->_translateKeyFormat(get_class($this));
                    $this->getAdapter()->delete($tableName,
                    $this->getAdapter()->quoteInto(sprintf('%s = ?', $columnName),
                    $this->id));
                }
            }

            if (array_key_exists('hasManyAndBelongsToMany', $this->_relations)
            || count($this->hasManyAndBelongsToMany) > 0) {
                /*
                * We just delete the data from the relations and do
                * not do an lookup for a cascade delete if there is no
                * relation anymore
                */
                foreach (array_keys($this->hasManyAndBelongsToMany) as $key) {
                    $className = $this->_getClassNameForRelationship($key,
                    $this->hasManyAndBelongsToMany);
                    $keyName   = $this->_translateKeyFormat(get_class($this));
                    $im        = new $className($this->getAdapter());
                    $tableName = $this->_translateIntoRelationTableName($this, $im);
                    $this->getAdapter()->delete($tableName,
                    $this->getAdapter()->quoteInto(sprintf('%s = ?', $keyName),
                    $this->id));
                }
            }

            parent::delete($this->getAdapter()->quoteInto('id = ?', $this->_data['id']));

            $this->_initDataArray();
            $this->_relations = array();
        }

        return $this;
    }

    /**
     * Fetches all rows according to the where, order, count, offset rules
     * @todo Example call
     *
     * @param string|array $where  Where clause
     * @param string|array $order  Order by
     * @param string|array $count  Limit query
     * @param string|array $offset Query offset
     * @param string       $select The comma-separated columns of the joined tables
     * @param string       $join   Join Statements
     *
     * @return Zend_Db_Table_Rowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null, $select = null , $join = null)
    {
        $wheres = array();
        if (array_key_exists('hasMany', $this->_relations)) {
            $sqlString = sprintf('%s = ?', $this->_translateKeyFormat($this->_relations['hasMany']['classname']));
            $wheres[]  = $this->getAdapter()->quoteInto($sqlString, $this->_relations['hasMany']['id']);
        }
        if (null !== $where) {
            $wheres[] = $where;
        }

        $where = (is_array($wheres) && count($wheres) > 0) ? implode(' AND ', $wheres) : null;

        if (null !== $this->_log) {
            $this->_log->debug($where);
        }

        /*
         * In case of join strings please note that the resultset is read only.
         */
        if (null !== $join) {
            $rows = $this->_fetchWithJoin($where, $order, $count, $offset, $select, $join);
        } else {
            $rows = parent::fetchAll($where, $order, $count, $offset);
        }

        $result = array();
        foreach ($rows as $row) {
            $instance        = clone $this;
            $instance->_data = array();

            foreach ($row->toArray() as $k => $v) {
                $instance->_data[$k] = $v;
            }

            $instance->_storedId = $instance->_data['id'];

            $result[] = $instance;
        }

        return $result;
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

        if (false === is_array($find) || count($find) === 0) {
            return $find;
        }

        $find = $find[0];

        /*
        * reset data as all our relatios, etc stuff has to
        * deal with a new id
        */
        $this->_data     = array();
        $this->_storedId = null;

        $this->_data = (array) $find->_data;
        unset($find);

        if (!array_key_exists('id', $this->_data)) {
            throw new Phprojekt_ActiveRecord_Exception('Table must have an id');
        }

        $this->_storedId = $this->_data['id'];

        return $this;
    }

    /**
     * Count.
     * ! NOTE we call the fetchAll of the parent here. So this might not
     * performe good at the moment
     *
     * @return integer
     */
    public function count()
    {
        return parent::fetchAll()->count();
    }

    /**
     * Returns the name of the table for the active record
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->_translateClassNameToTable(get_class($this));
    }

    /**
     * Overwrite the fetch method if we have a joinData stuff
     *
     * The joinData can have many different joins.
     *
     * @see _fetch
     *
     * @param string|array $where  Where clause
     * @param string|array $order  Order by
     * @param string|array $count  Limit query
     * @param string|array $offset Query offset
     * @param string       $select The columns of the joined tables
     * @param string       $join   Join statement
     *
     * @return Zend_Db_Table_Rowset
     */
    protected function _fetchWithJoin($where = null, $order = null, $count = null,
                                      $offset = null, $select = null, $join = null)
    {
        // selection tool
        $selectObj = $this->_db->select();

        // the FROM clause
        $selectObj->from($this->_name, $this->_cols, $this->_schema);

        // the WHERE clause
        $where = (array) $where;
        foreach ($where as $key => $val) {
            // is $key an int?
            if (is_int($key)) {
                // $val is the full condition
                $selectObj->where($val);
            } else {
                // $key is the condition with placeholder,
                // and $val is quoted into the condition
                $selectObj->where($key, $val);
            }
        }

        // the ORDER clause
        if (!is_array($order)) {
            $order = array($order);
        }
        foreach ($order as $val) {
            $selectObj->order($val);
        }

        // the LIMIT clause
        $selectObj->limit($count, $offset);

        $sqlStr    = $selectObj->__toString();
        $statement = explode("FROM", $sqlStr);

        if (null == $select) {
            $sqlStr = "SELECT * FROM " . $statement[1];

        } else {
            $selectStmt = $statement[0] . ", ";
            $columns    = explode(",", trim($select));

            foreach ($columns as $column) {
                $selectStmt .= " " . $column . " ";
            }

            $selectStmt .= " FROM ";
            $sqlStr      = $selectStmt . $statement[1];
        }

        if (preg_match('/WHERE/i', $sqlStr)) {
            $joinPart = ' ' . $join . ' WHERE ';
            $sqlStr   = preg_replace('/WHERE/i', $joinPart, $sqlStr);
        } else if (preg_match('/ORDER/i', $sqlStr)) {
            $joinPart = ' ' . $join . ' ORDER ';
            $sqlStr   = preg_replace('/ORDER/i', $joinPart, $sqlStr);
        } else {
            $sqlStr .= ' ' . $join;
        }

        // return the results
        $stmt      = $this->_db->query($sqlStr);
        $dataArray = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);

        $data  = array(
            'table'    => $this,
            'data'     => $dataArray,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);
    }
}