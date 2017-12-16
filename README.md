# QueryBuilder

## Features ##
Querybuilder is a small library to quickly and easily build MySQL queries with PHP. These queries use **prepared statements** to prevent against SQL injection.

### Supported Queries ###
#### SELECT queries: ####

```php
$query = new Query($mysqli);
$myData = $query->select('users', 'username', 'password')->where('name', '=', 'Austin Bailey', 's')->get();
```

The corresponding MySQL statement that the above code builds is:
```mysql
SELECT username, password FROM users where name = 'Austin Bailey';
```
select(): takes parameter 1 as the table name, and any following parameters as the columns the user whishes to select. 
where(): takes parameter 1 as column name, parameter 2 as condition, parameter 3 as the value to compare to, and parameter 4 as the datatype of parameter 3. 'i' for integer, 's' for string, 'd' for double, and 'b' for blob. Additionally, parameter 5 can be used when chaining WHERE conditions together. By default, it is "AND", or it is ignored when there is only one WHERE statement:
get(): returns the query built in the previous functions


```php
$query = new Query($mysqli);
$query->select('users', 'username', 'password')->where('name', '=', 'Austin Bailey', 's')->where('age', '>', 21, 'i', 'OR');
$myData = $query->get();
```

The corresponding MySQL statement that the above code builds is:
```mysql
SELECT username, password FROM users where name = 'Austin Bailey OR age > 21';
```

#### INSERT queries: ####

```php
$query = new Query($mysqli);
$query->insert('users', 'name', 'age')->values('si', 'Austin Bailey', 21)->exec_insert();
```

The corresponding MySQL statement that this above code builds is:
```mysql
INSERT INTO users (name, age) VALUES ('Austin Bailey', 21);
```
insert(): takes parameter 1 as the parameter name, and any folloing parameters as the columns in which to insert. 
values(): parameter 1 is a string where each letter represents the data type of it's corresponding value in the following parameters. In this case, 's' pairs with 'Austin Bailey' and 'i' pairs with 21.
exec_insert(): returns boolean value representing whether of not the query was successful.

## In Development ##

- [X] Select queries
- [X] Insert queries
- [ ] Delete queries
- [ ] Update queries

And more to come.