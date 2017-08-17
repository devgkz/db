<?php

namespace Devgkz\Db;

/**
 *
 * Simple Active Record implementation.
 *
 * @link      https://github.com/devgkz/db
 * @copyright Copyright (c) 2013-2017 Eugene Dementyev.
 * @license   https://opensource.org/licenses/BSD-3-Clause
 */
 
use ArrayAccess;
use Countable;

class Record implements ArrayAccess, Countable
{
    protected $db; // указатель на экземпляр класса Db, который используется для выполнения запросов к БД, инициализируется в конструкторе класса
    protected $table; // указатель на экземпляр класса БД
    protected $data = []; // данные выбранной строки из таблицы, ассоц. массив
    protected $fields = []; // перечень полей для сохранения в БД
    
    public $id; // перечень полей для сохранения в БД

    // TODO:
    // сортировка findAll по умолчанию (id, title, sort_order)

    // конструктор
    public function __construct($db, $table = null)
    {
        $this->db = $db;

        if ($table !== null) {
            $this->setTable($table);
        }
    }

    // альтернативный конструктор
    public static function model()
    {
        //return new self($table);
    }

    // установить текущую таблицу
    public function setTable($table)
    {
        $this->table = $table;
    }
  
    public function getTable()
    {
        return $this->table;
    }
  
  /**
     * This method is required by the interface ArrayAccess.
     * @param mixed $offset the offset to check on
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to retrieve element.
     * @return mixed the element at the offset, null if no element is found at the offset
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to set element
     * @param mixed $item the element value
     */
    public function offsetSet($offset, $item)
    {
        $this->data[$offset]=$item;
    }

    /**
     * This method is required by the interface ArrayAccess.
     * @param mixed $offset the offset to unset element
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
  
    public function count()
    {
        return count($this->data);
    }
  
    public function getData()
    {
        return $this->data;
    }

  
  /**
     * Magic method, calls [View::set] with the same parameters.
     *
     *     $view->foo = 'something';
     *
     * @param   string  variable name
     * @param   mixed   value
     * @return  void
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }
  
  /**
     * Assigns a variable by name. Assigned values will be available as a
     * variable within the view file:
     *
     *     // This value can be accessed as $foo within the view
     *     $view->set('foo', 'my value');
     *
     * You can also use an array to set several values at once:
     *
     *     // Create the values $food and $beverage in the view
     *     $view->set(array('food' => 'bread', 'beverage' => 'water'));
     *
     * @param   string   variable name or an array of variables
     * @param   mixed    value
     * @return  $this
     */
    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $this->data[$name] = $value;
        /* if(!count($this->fields) || in_array($name, $this->fields)) { //todo filter-method
          $this->data[$name] = $value;
        } else {
          $this->$name = $value;
        } */
            }
        } else {
            $this->data[$key] = $value;
        }
            /* elseif(!count($this->fields) || in_array($key, $this->fields)) { //todo filter-method
                $this->data[$key] = $value;
            } else {
          $this->$key = $value;
        } */

        return $this;
    }
  
  /**
     * Magic method, searches for the given variable and returns its value.
     * Local variables will be returned before global variables.
     *
     *     $value = $view->foo;
     *
     * [!!] If the variable has not yet been set, an exception will be thrown.
     *
     * @param   string  variable name
     * @return  mixed
     * @throws  Exception
     */
    public function & __get($key)
    {
        return $this->data[$key];
    
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        } else {
            //throw new Exception('Variable is not set:'.$key);
        }
    }
  
  /**
     * Magic method, determines if a variable is set.
     *
     *     isset($view->foo);
     *
     * [!!] `NULL` variables are not considered to be set by [isset](http://php.net/isset).
     *
     * @param   string  variable name
     * @return  boolean
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Magic method, unsets a given variable.
     *
     *     unset($view->foo);
     *
     * @param   string  variable name
     * @return  void
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }
  
  /**
     * Отфильтрованные данные для сохранения
     *
     */
    public function filtered()
    {
        /* $o = array_intersect_key($this->data, array_flip($this->fields));
    print_r ($o); */
    if (count($this->fields)) {
        return array_intersect_key($this->data, array_flip($this->fields));
    } else {
        return $this->data;
    }
    }
  
  
    public function findById($id)
    {
        $this->data = $this->db->fetch('* FROM '.$this->table.' WHERE id=:id', array('id'=>$id));
        return $this;
    }
  
    public function find($where, $quote=array())
    {
        $this->data = $this->db->fetch('* FROM '.$this->table.' '.$where, $quote);//' WHERE '.
    return $this;
    }
  
    public function findAll($where='', $quote=array())
    {
        $rows = $this->db->fetchAll('* FROM '.$this->table.' '.$where, $quote);
        
        $result = [];
        
        if (count($rows)) {
            foreach ($rows as $row) {
                $o = new self($this->table);
                $result[$row['id']] = $o->set($row);///?
            }
        }
        return $result;
    }
  
    public function save()
    {
        if ($this->id && $this->db->fetchOne('COUNT(*) FROM '.$this->table.' WHERE id=:id', array('id'=>$this->id))) {
            return $this->db->update($this->table, $this->filtered(), 'id='.$this->db->quote($this->id));
        } else {
            if ($this->db->insert($this->table, $this->filtered())) {
                return $this->db->lastInsertId();
            }
        }
    }
  
    public function update($id)
    {
        return $this->db->update($this->table, $this->filtered(), 'id='.$this->db->quote($id));
    }
  
 
    public function delete($id='')
    {
        if (!$id) {
            return $this->db->exec('DELETE FROM '.$this->table.' WHERE id=:id LIMIT 1',
      array('id'=>$this->id));
        } else {
            return $this->db->exec('DELETE FROM '.$this->table.' WHERE id=:id LIMIT 1',
      array('id'=>$id));
        }
    }
  
    public function deleteAll($where, $quote=array())
    {
        return $this->db->exec('DELETE FROM '.$this->table.' '.$where, $quote);
    }
  
    public function getCount($where, $quote=array())
    {
        return $this->db->fetchOne('COUNT(*) FROM '.$this->table.' '.$where, $quote);
    }
}
