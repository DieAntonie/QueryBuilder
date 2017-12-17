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

## In Development ##

- [X] Select queries
- [X] Insert queries
- [ ] Delete queries
- [ ] Update queries
- [ ] Error reporting

And more to come.