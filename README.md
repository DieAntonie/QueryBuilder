# QueryBuilder

## Features ##
Querybuilder is a small library to quickly and easily build MySQL queries with PHP. These queries use **prepared statements** to prevent against SQL injection.

### Supported Queries ###
#### SELECT queries: ####

```php
$query = new Query($mysqli);
$myData = $query->table('users')->execute();
```

The corresponding MySQL statement that the above code builds is:
```mysql
SELECT * FROM users;
```
The table() function sets the table to the parameter, and a table function followed by an execute selects all data in the table. The execute() function returns the array of data from the MySQL statement. Users can also append WHERE conditions using where(), or_where(), and and_where(). It is important that where() is the first appended, like below:

```php
$query = new Query($mysqli);
$myData = $query->table('users')->where('name', '=', 'Austin')->or_where('age', '>', 20)->execute();
```

The corresponding MySQL statement that the above code builds is:
```mysql
SELECT * FROM users WHERE name = 'Austin' OR age > 20;
```

where(), or_where(), and and_where() each take 3 parameters: column, condition, value. where() appends to the statement 'WHERE column condition value'. or_where and and_where append an OR or AND condition respectively.

SELECT queries can order be set with order_by() and/or limit() functions which will append an ORDER BY or LIMIT statement to the query:
```php
$query = new Query($mysqli);
$myData = $query->table('users')->where('age', '>', 20)->order_by('age', 'ASC')->limit(5)->execute();
```
The corresponding MySQL statement that the above code builds is:
```mysql
SELECT * FROM users WHERE age > 20 ORDER BY age ASC LIMIT 5
```
order_by() accepts 2 arguments: column name, and order. The column name is the column by which the data will be sorted, and the order is either 'ASC' for ascending, or 'DESC' for descending.

#### INSERT queries: ####

```php
$query = new Query($mysqli);
$query->table('users')->insert(array('name', 'age'), array('Austin', 21))->execute();
```

The corresponding MySQL statement that this above code builds is:
```mysql
INSERT INTO users (name, age) VALUES ('Austin', 21);
```
insert() takes parameter 1 as the array of columns which will have data inserted into them, and parameter 2 as the values that will go into their respective columns. It is important that the column and value indexes match. 
execute() will return a boolean true or false on success or failure of the query.

#### DELETE queries: ####
```php
$query->table('users')->delete('id', '=', 1)->execute();
```
The corresponding MySQL statement that this above code builds is:
```mysql
DELETE FROM users WHERE id = 1;
```
delete() is similar to the where() function in that it accepts 3 arguments: $column, $condition, and $value, and sets the MySQL statement to "DELETE FROM table WHERE $column $condition $value". Users can also append WHERE statements using the or_where() and and_where() functions just like SELECT queries:
 ```php
$query->table('users')->delete('id', '=', 1)->and_where('name', '=', 'Austin')->execute();
```
The corresponding MySQL statement that this above code builds is:
```mysql
DELETE FROM users WHERE id = 1 AND name = 'Austin';
```
The order_by() and limit() functions described in the SELECT queries section also apply to delete() queries.
## In Development ##

- [X] Select queries
- [X] Insert queries
- [X] Delete queries
- [ ] Update queries
- [ ] Error reporting
- [ ] Nested WHERE conditions

And more to come.