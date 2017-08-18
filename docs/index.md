# API Documentation

## Table of Contents

* [Adapter](#adapter)
    * [setNames](#setnames)
    * [enableLog](#enablelog)
    * [disableLog](#disablelog)
    * [logIsEnabled](#logisenabled)
    * [disableNestedTransactions](#disablenestedtransactions)
    * [enableNestedTransactions](#enablenestedtransactions)
    * [log](#log)
    * [getLastStatement](#getlaststatement)
    * [getLog](#getlog)
    * [getCountQueries](#getcountqueries)
    * [insert](#insert)
    * [replace](#replace)
    * [update](#update)
    * [fetchAll](#fetchall)
    * [fetch](#fetch)
    * [fetchOne](#fetchone)
    * [exec](#exec)
    * [query](#query)
    * [quote](#quote)
    * [placeholder](#placeholder)
    * [placeholderCallback](#placeholdercallback)
    * [lastInsertId](#lastinsertid)
    * [stamp](#stamp)
    * [getAdapter](#getadapter)
    * [setAdapter](#setadapter)
    * [beginTransaction](#begintransaction)
    * [commit](#commit)
    * [rollBack](#rollback)
    * [getInstance](#getinstance)
    * [setup](#setup)
* [Exception](#exception)
* [Record](#record)
    * [__construct](#__construct)
    * [model](#model)
    * [setTable](#settable)
    * [getTable](#gettable)
    * [offsetExists](#offsetexists)
    * [offsetGet](#offsetget)
    * [offsetSet](#offsetset)
    * [offsetUnset](#offsetunset)
    * [count](#count)
    * [getData](#getdata)
    * [__set](#__set)
    * [set](#set)
    * [__get](#__get)
    * [__isset](#__isset)
    * [__unset](#__unset)
    * [filtered](#filtered)
    * [findById](#findbyid)
    * [find](#find)
    * [findAll](#findall)
    * [save](#save)
    * [update](#update-1)
    * [delete](#delete)
    * [deleteAll](#deleteall)
    * [getCount](#getcount)

## Adapter





* Full name: \Devgkz\Db\Adapter


### setNames

Set connection charset

```php
Adapter::setNames(  $names = &#039;utf8&#039; )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$names` | **** |  |




---

### enableLog

Enable query logging.

```php
Adapter::enableLog(  ): boolean
```







---

### disableLog

Disable query logging.

```php
Adapter::disableLog(  ): boolean
```







---

### logIsEnabled

is log enabled ?

```php
Adapter::logIsEnabled(  )
```

return boolean





---

### disableNestedTransactions

Disable nested transactions

```php
Adapter::disableNestedTransactions(  )
```







---

### enableNestedTransactions

Enable nested transactions

```php
Adapter::enableNestedTransactions(  )
```







---

### log



```php
Adapter::log(  $sql )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **** |  |




---

### getLastStatement



```php
Adapter::getLastStatement(  )
```







---

### getLog

Get logged queries

```php
Adapter::getLog(  ): array
```







---

### getCountQueries



```php
Adapter::getCountQueries(  )
```







---

### insert

Performs insert query

```php
Adapter::insert(  $table, array $values,  $protect_names = null ): integer
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **** |  |
| `$values` | **array** |  |
| `$protect_names` | **** |  |




---

### replace

Performs replace query

```php
Adapter::replace(  $table, array $values,  $protect_names = null ): integer
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **** |  |
| `$values` | **array** |  |
| `$protect_names` | **** |  |




---

### update

Performs update query

```php
Adapter::update(  $table, array $values,  $where = &#039;&#039;,  $protect_names = null ): integer
```

You have to take care of escaping data in the where clause,
with the quote method:

<code>
$db->update($persons, $values, 'name=' . $db->quote($name));
</code>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **** |  |
| `$values` | **array** |  |
| `$where` | **** |  |
| `$protect_names` | **** |  |




---

### fetchAll

Performs SELECT query and return fetched results

```php
Adapter::fetchAll( string $query, array $quote = null ): array
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **string** | select query... without select :-) |
| `$quote` | **array** | values for place holders |


**Return Value:**

results (empty array if none)



---

### fetch

Fetch one row

```php
Adapter::fetch( string $query, array $quote = null ): array
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **string** | select query... without select :-) |
| `$quote` | **array** | values for place holders |


**Return Value:**

Row result as associative array (false if none)



---

### fetchOne

Fetch first value of first row

```php
Adapter::fetchOne( string $query, array $quote = null ): array
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **string** | select query... without select :-) |
| `$quote` | **array** | values for place holders |


**Return Value:**

Row result as associative array (NULL if none)



---

### exec



```php
Adapter::exec(  $query, array $quote = null )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **** |  |
| `$quote` | **array** |  |




---

### query

Convenience shortcut to PDO's query method

```php
Adapter::query(  $statement ): \PDOStatement
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statement` | **** |  |




---

### quote

Convenience shortcut to PDO's quote method

```php
Adapter::quote(  $string,  $type = \PDO::PARAM_STR ): string
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$string` | **** |  |
| `$type` | **** |  |




---

### placeholder



```php
Adapter::placeholder(  )
```







---

### placeholderCallback



```php
Adapter::placeholderCallback(  $m )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$m` | **** |  |




---

### lastInsertId

Convinience shortcut to PDO's lastInsertId method

```php
Adapter::lastInsertId(  ): string
```







---

### stamp

Return SQL stamp

```php
Adapter::stamp(  $time = &#039;&#039;,  $date_only = false ): string
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$time` | **** |  |
| `$date_only` | **** |  |




---

### getAdapter

For those special cases where you need all the power of PDO

```php
Adapter::getAdapter(  ): \PDO
```





**Return Value:**

database adapter



---

### setAdapter

Set PDO adapter

```php
Adapter::setAdapter( \PDO $adapter )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$adapter` | **\PDO** |  |




---

### beginTransaction

Begin transaction

```php
Adapter::beginTransaction(  ): boolean
```

Support nested transaction where available (pgsql, mysql)





---

### commit

Transaction commit

```php
Adapter::commit(  ): boolean
```

Support nested transaction where available (pgsql, mysql)





---

### rollBack

Transaction rollback

```php
Adapter::rollBack(  ): boolean
```

Support nested transaction where available (pgsql, mysql)





---

### getInstance

Get/set db instance from/to the connection pool

```php
Adapter::getInstance(  $handle_id = &#039;default&#039;,  $dsn = null,  $user = null,  $pass = null, array $options = null ): \Devgkz\Db\Adapter
```

<code>
$dbHandle = Adapter::getInstance($mydb, $mydsn, $myuser, $mypass);
</code>

* This method is **static**.
**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handle_id` | **** |  |
| `$dsn` | **** |  |
| `$user` | **** |  |
| `$pass` | **** |  |
| `$options` | **array** |  |




---

### setup

Register DB instance (DB::getInstance alias)

```php
Adapter::setup(  $handle_id = &#039;default&#039;,  $dsn = null,  $user = null,  $pass = null, array $options = null ): \Devgkz\Db\Adapter
```

<code>
$dbHandle = DB::setup($mydb, $mydsn, $myuser, $mypass);
</code>

* This method is **static**.
**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handle_id` | **** |  |
| `$dsn` | **** |  |
| `$user` | **** |  |
| `$pass` | **** |  |
| `$options` | **array** |  |




---

## Exception

Exception



* Full name: \Devgkz\Db\Exception
* Parent class: 


## Record





* Full name: \Devgkz\Db\Record
* This class implements: \ArrayAccess, \Countable


### __construct



```php
Record::__construct(  $db,  $table = null )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$db` | **** |  |
| `$table` | **** |  |




---

### model



```php
Record::model(  )
```



* This method is **static**.



---

### setTable



```php
Record::setTable(  $table )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **** |  |




---

### getTable



```php
Record::getTable(  )
```







---

### offsetExists

This method is required by the interface ArrayAccess.

```php
Record::offsetExists( mixed $offset ): boolean
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$offset` | **mixed** | the offset to check on |




---

### offsetGet

This method is required by the interface ArrayAccess.

```php
Record::offsetGet( integer $offset ): mixed
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$offset` | **integer** | the offset to retrieve element. |


**Return Value:**

the element at the offset, null if no element is found at the offset



---

### offsetSet

This method is required by the interface ArrayAccess.

```php
Record::offsetSet( integer $offset, mixed $item )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$offset` | **integer** | the offset to set element |
| `$item` | **mixed** | the element value |




---

### offsetUnset

This method is required by the interface ArrayAccess.

```php
Record::offsetUnset( mixed $offset )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$offset` | **mixed** | the offset to unset element |




---

### count



```php
Record::count(  )
```







---

### getData



```php
Record::getData(  )
```







---

### __set

Magic method, calls [View::set] with the same parameters.

```php
Record::__set(  $key,  $value ): void
```

$view->foo = 'something';


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **** |  |
| `$value` | **** |  |




---

### set

Assigns a variable by name. Assigned values will be available as a
variable within the view file:

```php
Record::set(  $key,  $value = null ): $this
```

// This value can be accessed as $foo within the view
    $view->set('foo', 'my value');

You can also use an array to set several values at once:

    // Create the values $food and $beverage in the view
    $view->set(array('food' => 'bread', 'beverage' => 'water'));


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **** |  |
| `$value` | **** |  |




---

### __get

Magic method, searches for the given variable and returns its value.

```php
Record::__get(  $key ): mixed
```

Local variables will be returned before global variables.

    $value = $view->foo;

[!!] If the variable has not yet been set, an exception will be thrown.


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **** |  |




---

### __isset

Magic method, determines if a variable is set.

```php
Record::__isset(  $key ): boolean
```

isset($view->foo);

[!!] `NULL` variables are not considered to be set by [isset](http://php.net/isset).


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **** |  |




---

### __unset

Magic method, unsets a given variable.

```php
Record::__unset(  $key ): void
```

unset($view->foo);


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **** |  |




---

### filtered

Отфильтрованные данные для сохранения

```php
Record::filtered(  )
```







---

### findById



```php
Record::findById(  $id )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **** |  |




---

### find



```php
Record::find(  $where,  $quote = array() )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$where` | **** |  |
| `$quote` | **** |  |




---

### findAll



```php
Record::findAll(  $where = &#039;&#039;,  $quote = array() )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$where` | **** |  |
| `$quote` | **** |  |




---

### save



```php
Record::save(  )
```







---

### update



```php
Record::update(  $id )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **** |  |




---

### delete



```php
Record::delete(  $id = &#039;&#039; )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **** |  |




---

### deleteAll



```php
Record::deleteAll(  $where,  $quote = array() )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$where` | **** |  |
| `$quote` | **** |  |




---

### getCount



```php
Record::getCount(  $where,  $quote = array() )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$where` | **** |  |
| `$quote` | **** |  |




---



--------
> This document was automatically generated from source code comments on 2017-08-18 using [phpDocumentor](http://www.phpdoc.org/) and [cvuorinen/phpdoc-markdown-public](https://github.com/cvuorinen/phpdoc-markdown-public)
