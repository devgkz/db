<?php
/**
 * PDO Wrapper Adapter.
 *
 * @link      https://github.com/devgkz/db
 * @copyright Copyright (c) 2013-2017 Eugene Dementyev.
 * @license   https://opensource.org/licenses/BSD-3-Clause
 *
 * Adds a few helpers, lazy-connecting, nested transactions with pgsql and mysql
 * and query logging. Supports multiple databases. Defaults connection to utf-8.
 *
 * <code>
 * // typical usage
 * $db = Devgkz\Db\Adapter::getInstance('my_instance_name', 'mysql:host=localhost;dbname=mydatabase', $myuser, $mypass);
 * $rows = $db->fetch('* from mytable');
 * $db->insert('mytable', array('myfield' => 'myvalue'));
 * $db->update('mytable', array('myfield' => 'myvalue'), 'myfield IS NULL');
 * </code>
 */
namespace Devgkz\Db;

use PDO;
use PDOStatement;

class Adapter
{
    private static $pool;
    private $pdo;
    private $isConnected = false;
    private $useNestedTransactions = true;
    private $logEnabled = true;
    private $dsn;
    private $user;
    private $pass;
    private $lastStatement = '';
    private $options = array();
    private $names = 'utf8';
    private $attributes = array(
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    );
    private $protectNames = true;
    public $queryLog = array();
    // Database drivers that support SAVEPOINTs.
    private static $savepointTransactions = array(
        'pgsql',
        'mysql'
    );
    // The current transaction level.
    private $transLevel = 0;
    
    // args for placeholder
    private $phArgs;
    
    protected function nestable()
    {
        return in_array($this->getAdapter()->getAttribute(PDO::ATTR_DRIVER_NAME), self::$savepointTransactions);
    }
    
    /**
     * Construct new DB object
     *
     * @param string data source name in the PDO syntax
     * @param string user
     * @param string password
     * @param array PDO options
     */
    private function __construct($dsn = null, $user = null, $pass = null, array $options = null)
    {
        if (!is_null($dsn)) {
            $this->dsn = $dsn;
        }
        if (!is_null($user)) {
            $this->user = $user;
        }
        if (!is_null($pass)) {
            $this->pass = $pass;
        }
        if (!is_null($options)) {
            $this->options = $options;
        }
    }
    /**
     * Set connection charset
     *
     * @param string names values
     */
    public function setNames($names = 'utf8')
    {
        $this->names = $names;
    }
    /**
     * Enable query logging.
     *
     * @return boolean
     */
    public function enableLog()
    {
        $this->logEnabled = true;
        return $this->logEnabled;
    }
    /**
     * Disable query logging.
     *
     * @return boolean
     */
    public function disableLog()
    {
        $this->logEnabled = false;
        return $this->logEnabled;
    }
    /**
     * is log enabled ?
     *
     * return boolean
     */
    public function logIsEnabled()
    {
        return $this->logEnabled;
    }
    
    /**
     * Disable nested transactions
     */
    public function disableNestedTransactions()
    {
        if ($this->transLevel !== 0) {
            throw new Exception('Cant disable nested transactions while a transaction is started.');
        }
        $this->useNestedTransactions = false;
    }
    
    /**
     * Enable nested transactions
     */
    public function enableNestedTransactions()
    {
        if ($this->transLevel !== 0) {
            throw new Exception('Cant enable nested transactions while a transaction is started.');
        }
        $this->useNestedTransactions = true;
    }

    public function log($sql)
    {
        $this->lastStatement = $sql;
        if ($this->logEnabled) {
            $this->queryLog[] = $sql;
        }
    }

    public function getLastStatement()
    {
        return $this->lastStatement;
    }

    /**
     * Get logged queries
     *
     * @return array
     */
    public function getLog()
    {
        ksort($this->queryLog);
        return $this->queryLog;
    }
    
    public function getCountQueries()
    {
        return count($this->queryLog);
    }
    /**
     * Performs insert query
     *
     * @param string table name
     * @param array associative array with values to insert
     * @param boolean wether to protect names using "`"
     * @return integer
     */
    public function insert($table, array $values, $protect_names = null)
    {
        if (is_null($protect_names)) {
            $protect_names = $this->protectNames;
        }
        $table = trim($table);
        if (empty($table)) {
            throw new Exception('Table name can not be empty');
        }
        $values = $this->getColumnValues($values, $protect_names);
        if ($values['count'] < 1) {
            throw new Exception('Nothing to insert');
        }
        $sql = 'INSERT INTO ' . ($protect_names ? '`' . $table . '`' : $table) . '(' . implode(', ', $values['columns']) . ') VALUES(' . implode(', ', $values['data']) . ');';
        $this->log($sql);
        return $this->getAdapter()->exec($sql);
    }
    /**
     * Performs replace query
     *
     * @param string table name
     * @param array associative array with values to replace
     * @param boolean wether to protect names using "`"
     * @return integer
     */
    public function replace($table, array $values, $protect_names = null)
    {
        if (is_null($protect_names)) {
            $protect_names = $this->protectNames;
        }
        $table = trim($table);
        if (empty($table)) {
            throw new Exception('Table name can not be empty');
        }
        $values = $this->getColumnValues($values, $protect_names);
        if ($values['count'] < 1) {
            throw new Exception('Nothing to insert');
        }
        $sql = 'replace into ' . ($protect_names ? '`' . $table . '`' : $table) . '(' . implode(', ', $values['columns']) . ') values(' . implode(', ', $values['data']) . ');';
        $this->log($sql);
        return $this->getAdapter()->exec($sql);
    }
    /**
     * Performs update query
     *
     * You have to take care of escaping data in the where clause,
     * with the quote method:
     *
     * <code>
     * $db->update($persons, $values, 'name=' . $db->quote($name));
     * </code>
     *
     * @param string table name
     * @param array associative array with values to update
     * @param string where clause
     * @param boolean wether to protect names using "`"
     * @return integer
     */
    public function update($table, array $values, $where = '', $protect_names = null)
    {
        if (is_null($protect_names)) {
            $protect_names = $this->protectNames;
        }
        $table = trim($table);
        if (empty($table)) {
            throw new Exception('Table name can not be empty');
        }
        $values = $this->getColumnValues($values, $protect_names);
        if ($values['count'] < 1) {
            throw new Exception('Nothing to update');
        }
        $where = empty($where) ? '' : ' where ' . $where;
        $sql = 'update ' . ($protect_names ? '`' . $table . '`' : $table) . ' set ' . implode(', ', $values['updates']) . $where;
        $this->log($sql);
        return $this->getAdapter()->exec($sql);
    }
    /**
     * Performs SELECT query and return fetched results
     *
     * @param string $query select query... without select :-)
     * @param array $quote values for place holders
     * @return array results (empty array if none)
     */
    public function fetchAll($query, array $quote = null)
    {
        $sql = 'SELECT ' . $query;
        if (empty($quote)) {
            $this->log($sql);
            $results = $this->getAdapter()->query($sql)->fetchAll();
        } else {
            $this->log($sql);
            $stmt = $this->getAdapter()->prepare($sql);
            $stmt->execute($quote);
            
            $results = $stmt->fetchAll();
        }
        return $results;
    }
    /**
     * Fetch one row
     *
     * @param string $query select query... without select :-)
     * @param array $quote values for place holders
     * @return array Row result as associative array (false if none)
     */
    public function fetch($query, array $quote = null)
    {
        $sql = 'SELECT ' . $query . ' LIMIT 1';
        
        if (empty($quote)) {
            $this->log($sql);
            $result = $this->getAdapter()->query($sql)->fetch();
        } else {
            $this->log($sql);
            $stmt = $this->getAdapter()->prepare($sql);
            $stmt->execute($quote);
            $result = $stmt->fetch();
        }
        
        return $result;
    }
    /**
     * Fetch first value of first row
     *
     * @param string $query select query... without select :-)
     * @param array $quote values for place holders
     * @return array Row result as associative array (NULL if none)
     */
    public function fetchOne($query, array $quote = null)
    {
        $sql = 'SELECT ' . $query . ' LIMIT 1';
        
        if (empty($quote)) {
            $this->log($sql);
            $result = $this->getAdapter()->query($sql)->fetch(PDO::FETCH_NUM);
        } else {
            $this->log($sql);
            $stmt = $this->getAdapter()->prepare($sql);
            $stmt->execute($quote);
            $result = $stmt->fetch(PDO::FETCH_NUM);
        }
        if ($result) {
            return $result[0];
        }
    }
    
    public function exec($query, array $quote = null)
    {
        if (empty($quote)) {
            $this->log($query);
            return $this->getAdapter()->exec($query);
        } else {
            $this->log($query);
            $stmt = $this->getAdapter()->prepare($query);
            $stmt->execute($quote);
            return $stmt->rowCount();
        }
    }

    /**
     * Convenience shortcut to PDO's query method
     *
     * @param string Execution statement
     * @return PDOStatement
     */
    public function query($statement)
    {
        $this->log($statement);
        return $this->getAdapter()->query($statement);
    }

    /**
     * Convenience shortcut to PDO's quote method
     *
     * @param string the string to escape
     * @param integer Escape mode, defaults to PDO::PARAM_STR
     * @return string
     */
    public function quote($string, $type = PDO::PARAM_STR)
    {
        return $this->getAdapter()->quote($string, $type);
    }
    
    // простые плейсхолдеры SQL-запросов
    public function placeholder()
    {
        $this->phArgs = func_get_args();
        return preg_replace_callback('/(\?[akvp]{0,1})/', [$this, 'placeholderCallback'], array_shift($this->phArgs));///(\?[akv#]{0,1}(.*?))/
    }
    
    //SQL placeholder callback
    public function placeholderCallback($m)
    {
        $args = $this->phArgs;
        if (!sizeof($args)) {
            return;
        }
        $val = [];
        
        $value = array_shift($args);
        switch ($m[0]) {
        case '?': return $this->quote($value);  //todo escape_string
        case '?p': return $value;
        case '?a':
          if (!is_array($value)) {
              throw new Exception('Неверный плейсхолдер (аргумент не массив)');
          }
          foreach ($value as $k=>$v) {
              $val[] = "`".$k."`=".$this->quote($v);
          }
          return implode(', ', $val);
        case '?k':
          if (!is_array($value)) {
              throw new Exception('Неверный плейсхолдер (аргумент не массив)');
          }
          foreach ($value as $k=>$v) {
              $val[] = str_replace('"', '`', $this->quote($v));
          }
          return implode(', ', $val);
        case '?v':
          if (!is_array($value)) {
              throw new Exception('Неверный плейсхолдер (аргумент не массив)');
          }
          foreach ($value as $k=>$v) {
              $val[] = $this->quote($value);
          }
          return implode(', ', $val);
      }
    }
    
    /**
     * Convinience shortcut to PDO's lastInsertId method
     *
     * @return string
     */
    public function lastInsertId()
    {
        return $this->getAdapter()->lastInsertId();
    }
    /**
     * Return SQL stamp
     *
     * @param format stamp according to given unix timestamp
     * @param boolean return only date ?
     * @return string
     */
    public function stamp($time = '', $date_only = false)
    {
        return date($date_only ? 'Y-m-d' : 'Y-m-d H:i:s', empty($time) ? time() : (int)$time);
    }
    
    private function connect()
    {
        if (!$this->isConnected) {
            $this->setAdapter(new PDO($this->dsn, $this->user, $this->pass, $this->options));
            //$this->protectNames = false;
            foreach ($this->attributes as $attribute => $value) {
                $this->pdo->setAttribute($attribute, $value);
            }
            if (in_array(substr($this->dsn, 0, 5), array(
                'mysql',
                'pgsql'
            ))) {
                // devg см. DSN
                $q = 'SET NAMES ' . $this->names;
                $this->log($q);
                $this->pdo->exec($q);
            }
        }
    }
    private function getColumnValues(array $values, $protect_names = null)
    {
        if (is_null($protect_names)) {
            $protect_names = $this->protectNames;
        }
        $i = 0;
        $keys = array();
        $data = array();
        $updates = array();
        foreach ($values as $key => $value) {
            $i++;
            $key = $protect_names ? '`' . $key . '`' : $key;
            // проблема null ? см. MySQL design patterns
            //$value = is_null($value) ? 'NULL' : $this->quote($value);
            $value = $this->quote($value);
            $keys[] = $key;
            $data[] = $value;
            $updates[$key] = $key . '=' . $value;
        }
        return array(
            'count' => $i,
            'columns' => $keys,
            'data' => $data,
            'updates' => $updates
        );
    }
    /**
     * For those special cases where you need all the power of PDO
     *
     * @return PDO database adapter
     */
    public function getAdapter()
    {
        $this->connect();
        return $this->pdo;
    }
    /**
     * Set PDO adapter
     *
     * @param PDO $adapter
     */
    public function setAdapter(PDO $adapter)
    {
        $this->pdo = $adapter;
        $this->isConnected = true;
    }
    /**
     * Begin transaction
     *
     * Support nested transaction where available (pgsql, mysql)
     *
     * @return boolean
     */
    public function beginTransaction()
    {
        $return = false;
        if ($this->useNestedTransactions === false) {
            if ($this->transLevel === 0) {
                $this->log('START TRANSACTION;');
                $return = $this->getAdapter()->beginTransaction();
            }
        } else {
            if (!$this->nestable() || $this->transLevel === 0) {
                $this->log('START TRANSACTION;');
                $return = $this->getAdapter()->beginTransaction();
            } else {
                $return = $this->exec("SAVEPOINT LEVEL{$this->transLevel}");
            }
        }
        $this->transLevel++;
        return $return;
    }
    /**
     * Transaction commit
     *
     * Support nested transaction where available (pgsql, mysql)
     *
     * @return boolean
     */
    public function commit()
    {
        $return = false;
        $this->transLevel--;
        if ($this->useNestedTransactions === false) {
            if ($this->transLevel === 0) {
                $this->log('COMMIT;');
                $return = $this->getAdapter()->commit();
            }
        } else {
            if (!$this->nestable() || $this->transLevel === 0) {
                $this->log('COMMIT;');
                $return = $this->getAdapter()->commit();
            } else {
                $return = $this->exec("RELEASE SAVEPOINT LEVEL{$this->transLevel}");
            }
        }
        return $return;
    }
    /**
     * Transaction rollback
     *
     * Support nested transaction where available (pgsql, mysql)
     *
     * @return boolean
     */
    public function rollBack()
    {
        $return = false;
        $this->transLevel--;
        if ($this->useNestedTransactions === false) {
            if ($this->transLevel === 0) {
                $this->log('ROLLBACK;');
                $return = $this->getAdapter()->rollBack();
            }
        } else {
            if (!$this->nestable() || $this->transLevel === 0) {
                $this->log('ROLLBACK;');
                $return = $this->getAdapter()->rollBack();
            } else {
                $return = $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transLevel}");
            }
        }
        return $return;
    }
    /**
     * Get/set db instance from/to the connection pool
     *
     * <code>
     * $dbHandle = Adapter::getInstance($mydb, $mydsn, $myuser, $mypass);
     * </code>
     *
     * @param string pool id
     * @param string database dsn
     * @param string database user
     * @param string user password
     * @param array options
     * @return Adapter
     */
    public static function getInstance($handle_id = 'default', $dsn = null, $user = null, $pass = null, array $options = null)
    {
        if (!isset(self::$pool[$handle_id])) {
            self::$pool[$handle_id] = new static($dsn, $user, $pass, $options);
        }
        return self::$pool[$handle_id];
    }
    /**
     * Register DB instance (DB::getInstance alias)
     *
     * <code>
     * $dbHandle = DB::setup($mydb, $mydsn, $myuser, $mypass);
     * </code>
     *
     * @param string pool id
     * @param string database dsn
     * @param string database user
     * @param string user password
     * @param array options
     * @return Adapter
     */
    public static function setup($handle_id = 'default', $dsn = null, $user = null, $pass = null, array $options = null)
    {
        return self::getInstance($handle_id, $dsn, $user, $pass, $options);
    }
}
