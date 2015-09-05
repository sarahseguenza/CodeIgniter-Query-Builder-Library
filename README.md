# Codeigniter Query Builder
A small lib that builds and execute query using codeigniter's active records / query builder class

## Table of Contents

- [Introduction](#introduction)
- [Database Configuration and Connecting to your Database](#database-configuration-and-connecting-to-your-database)
- [Connection Options](#connection-options)
- [Queries](#queries)
- [Select Statement](#select-statement)
- [Update Statement](#update-statement)
- [Delete Statement](#delete-statement)
- [Insert Statement](#insert-statement)
- [Native Query](#native-query)
- [Set Field Clause](#set-field-clause)
- [Build a Condition Clause](#build-a-condition-clause)
- [Build a Join Clause](#build-a-join-clause)
- [View the Generated Query](#view-the-generated-query)
- [Error Handling](#error-handling)

## Introduction

This is a small query builder library using codeigniter's active records or query builder class.

Lets turn 

```php
<?php 
	$this->db->select('*');
	$this->db->from('blogs');
	$this->db->join('comments', 'comments.id = blogs.id');
	$this->db->where('name', $name);
	$this->db->where('title', $title);
	$this->db->where('status', $status);
	$query = $this->db->get();
?>
```

into

```php
<?php 
	$this->query->select(
		array(
			'table' => 'blogs',
			'joins' => array(
				'comments' => array(
					'comments.id' => 'blogs.id'
				)
			),
			'conditions' => array(
				'name' => $name,
				'title' => $title,
				'status' => $status
			)
		)
	);
?>
```

CI Versions compatible
- 3.0.1
- 2.2.4

Sample Usage:

```php
<?php 
	$this->load->library('query');
	
	$this->query->select(
		array(
			'table' => 'table_name_here',
			'conditions' => array(
				'field1' => 'value1',
				'field2' => 'value2'
			)
		)
	);
?>
```

By this, we can generate a query something like this:

```sql
SELECT `field1`, `field2` FROM `table_name` WHERE `field1` = 'value1' AND `field2` = 'value2'
```

## Database Configuration and Connecting to your Database

Please see codeigniter's Database Configuration userguide.

If you are using Codeigniter version 3.0.1, please set $query_builder to TRUE.

Connect in other database

Using database group configuration, we can connect to other database schema.

```php
<?php 
	$this->load->library( 'query', array('dbase' => 'group2') );
?>
```

Or set database on the fly

```php
<?php
	$this->set_dbase( $this->load->database('default', TRUE) );
?>
```

## Queries

To execute a query, use the following functions.

```js
$this->query->select( $details, $to_view_query, $count_rec, $escape_query );
$this->query->update( $table_name, $where_conditions, $data );
$this->query->delete( $table_name, $where_conditions );
$this->query->insert( $table_name, $data_to_insert, $to_return_id, $print_query );
$this->query->insert_batch( $table_name, $data );
```

This will automatically generate, escape and execute a query.

## Select Statement

Options for Select Statement

* `table` : The table name that you want to select. Required.
* `fields` : An array of table fields that you want to show. OPTIONAL
* `conditions` : This is for `WHERE` clause. Please see `Build a Condition Parameters`.
* `joins` : This is for `JOIN` portion of your query. Please see `Build a Join Parameters`.
* `order` : Set an `ORDER BY` clause.
* `group` : Permits you to create a `GROUP BY` clause.
* `limit` : Limit the number of rows you would like returned by the query.
* `start` : Set a result offset. Commonly used on pagination functionality. Set to '0' by default.
* `count` : Determine the number of rows in a particular table. A boolean type. Set to `false` by default. Optional.
* `show_query` : View the generated query. Please see `View the Generated Query`. Set to `false` by default. Optional.
* `escape` : Escape query to build. Set to `true` by default. Optional.

```php
<?php 
	$this->query->select(
		array(
			'table' => 'table',
			'fields' => 'field1, field2',
			'conditions' => array(
				'field1' => 'value1',
				'field2' => 'value2'
			),
			'order' => 'field_name ASC',
			'group' => 'field_name, ...',
			'limit' => 10,
			'start' => 0
		),
		$show_query,
		$count_rec,
		$escape_query
	);
?>
```

## Update Statement

Options for Update Statement

* `table` : The table name where you want to update a particular record. Required.
* `conditions` : This is for `WHERE` clause. Please see `Build a Condition Parameters`.
* `details` : This is where we set the fields and its new values. Please see build `Set Fields Parameters`.
* `show_query` : View the generated query. Please see `View the Generated Query`. Optional.

```php
<?php 
	$this->query->update(
		'table',
		array(
			'id' => 1
		),
		array(
			'field' => 'This is edited'
		)
		$to_show_query
	);
?>
```

## Delete Statement

Options for Delete Statement

* `table` : The table name where you want to delete a particular record. Required.
* `conditions` : This is for `WHERE` clause. Please see `Build a Condition Parameters`.
* `show_query` : View the generated query. Please see `View the Generated Query`. Optional.

```php
<?php 
	$this->query->delete(
		'table',
		array(
			'id' => 1
		)
		$to_show_query
	);
?>
```

## Insert Statement

Options for Insert Statement

* `table` : The table name where you want to insert a particular record. Required.
* `details` : This is where we set the fields and its values. Please see build `Set Fields Parameters`.
* `show_query` : View the generated query. Please see `View the Generated Query`. Optional.

```php
<?php 
	$this->query->insert(
		'users',
		array(
			'username' => $username,
			'password' => md5( $password )
		)
		$to_show_query
	);
?>
```


## Native Query

Options for executing a query

* `query` : Your query statement to execute.

```php
<?php 
	$this->query->native_query( 'SELECT * FROM `users`' );
?>
```

## Set Field Clause

This is for select, delete, update and insert queries.

To build on what fields to insert and/or update, please create an array of field names and values

```php
<?php 
	array(
		'field1' => 'value1',
		'field2' => 'value2',
		'field3' => 'value3'
	)
?>
```

This will generate

```sql
UPDATE `table` SET `field1` = 'value', `field2` = 'value';
INSERT INTO `table` SET `field1` = 'value', `field2` = 'value';
```

## Build a Condition Clause

This is where we build the `WHERE` clause.

For simple condition, this will generate an AND operator by default

```php
<?php
	array(
		'field1' => 'value',
		'field2' => 'value'
	)
?>
```
```sql
	`field1` = 'value' AND `field2` = 'value'
```

Joined by OR
```php
<?php
	'or' => array(
		'field1' => 'value',
		'field2' => 'value'
	)
?>
```
```sql
	`field1` = 'value' OR `field2` = 'value'
```

NOT clauses
```php
<?php
	array(
		'field1 !=' => NULL, // version 3.0.1
		'field1 IS NOT NULL' => NULL, // version 2.4.4 and escape parameter must set to FALSE
		
		'field2 !=' => 'value'
	)
?>
```
```sql
	`field1` IS NOT NULL
	
	`field1` != 'value'
```

LIKE clause, by default, this will joined by AND
```php
<?php
	'conditions' => array(
		'like' => array(
			'field' => '%value%'
		),
		'or_like' => array(
			'field' => 'value'
		),
		'not_like' => array(
			'field' => 'value'
		),
		'or_not_like' => array(
			'field' => 'value'
		)
	)
?>
```
```sql
	`field1` LIKE '%value%' OR `field` LIKE 'value%' AND `field` NOT LIKE '%value%' OR `field` NOT LIKE 'value'
```

AND clause
```php
<?php
	'conditions' => array(
		'and' => array(
			'field' => 'value',
			'field2' => 'value'
		)
	)
?>
```
```sql
	`field` = 'value' AND `field2` => 'value'
```

OR clause
```php
<?php
	'conditions' => array(
		'or' => array(
			'field' => 'value',
			'field2' => 'value'
		)
	)
?>
```
```sql
	`field` = 'value' OR `field2` => 'value'
```

Other operators
```php
<?php
	array(
		'field1 !=' => 1,
		'field2 <=' => 1,
		'field3 >=' => 1
	)
?>
```
```sql
	field1 != 1 AND field2 <= 1 AND field3 >= 1
```

## Build a Join Clause

Permits you to write the JOIN portion of your query.

Options are as follows:

* `joins` : An object of table details to join.
* `type` : Optional and this is `JOIN` by default. Join types are `left`, `right` and `inner`.
* `ON clause` : This is where the ON condition resides. Similar to `Condition` parameter format.

```php
<?php 
	$this->query->select(
		array(
			'table' => 'blogs',
			'joins' => array(
				'comments' => array(
					'type' => 'left',
					'comments.id' => 'blogs.id'
				)
			),
			'conditions' => array(
				'name' => $name,
				'title' => $title,
				'status' => $status
			)
		)
	);
?>
```
```sql
	SELECT * FROM `blogs` LEFT OUTER JOIN `comments` ON `comments`.`id` = `blogs`.`id` WHERE `name` = 'name' AND `title` => 'title' AND `status` = 'status'
```

## View the Generated Query

This will log the generated query using `console.log()`.

Setting is as simple as assigning a boolean value to `show_query`. This is set `false` by default.

## Error Handling

While problem occurs when executing the query, library will returns an error code and an error message.

```
Array
(
    [code] => 1054
    [message] => Unknown column 'field_name' in 'where clause'
)
```