<?php

class Query {

	// mysqli object used to connect to DB
	private $mysqli = null;
	// sql statement string which will be executed
	private $sql = '';
	// the datatype of each column in the table gathered using getTypes(). This is used in prepared statements
	private $datatypes = array();
	// an array holding the values entered by the user, to be bound to the prepared statement before execution
	private $data = array();
	// the columns that correspond to the items in $data
	private $fields = array();
	// the name of the table 
	private $table = '';
	// s, i, d, u for select, insert, delete, update respectively. 
	private $command = '';
	// list of columns for select
	private $columns_for_select = '*';

	public function __construct($mysqli = null) {
		// construct with mysqli object
		$this->mysqli = $mysqli;
	}

	public function set_mysqli($mysqli) {
		$this->mysqli = $mysqli;
	}

	public function clear() {
		//clears all Query object data
		$this->table = '';
		$this->sql_command = '';
		$this->sql_info = '';
		$this->datatypes = array();
		$this->data = array();
		$this->fields = array();
		$this->command = '';
	}

	public function table($table) {

		$this->clear();

		$this->table = $table;
		//set command to select by default
		$this->command = "s";

		$this->getTypes($table);

		return $this;
	}

	private function getTypes($table) {

		// fetch describe array of this table
		$sql = "DESCRIBE $table";
		$stmt = $this->mysqli->prepare($sql);
		$stmt->execute();
		$result = $stmt->get_result();
		$data = array();
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}

		//build array to know type of each column in the table. this removes the need for user-input types
		foreach ($data as $row) {
			if (strpos($row['Type'], 'int') > -1) {
				$type = 'i';
			} else if (strpos($row['Type'], 'double') > -1 || strpos($row['Type'], 'float') > -1) {
				$type = 'd';
			} else if (strpos($row['Type'], 'varchar') > -1) {
				$type = 's';
			} else {
				$type = 'b';
			}

			$this->datatypes[$row['Field']] = $type;
		}
	}

	/* **** SELECT **** */


	public function select($columns) {
		$this->columns_for_select = "";
		
		for ($i=0; $i < count($columns) - 1; $i++) { 
			$this->columns_for_select = $this->columns_for_select . $columns[$i] . ", ";
		}

		$this->columns_for_select = $this->columns_for_select . $columns[count($columns)-1];

		return $this;
	}

	public function where($column, $condition, $value) {
		// Set $sql to SELECT * FROM $this->table WHERE $column $condition $value

		if (strpos($this->sql, "SELECT") > -1) {
			$this->sql = $this->sql . "$column $condition ?";
		} else {
			$this->sql = "SELECT $this->columns_for_select FROM $this->table WHERE $column $condition ?";
		}

		$this->data[] = $value;
		$this->fields[] = $column;
		$this->command = "s";

		return $this;
	}

	/* or_where and and_where can both be used to append conditions to DELETE queries */

	public function or_where($column, $condition, $value) {
		// Append an additional WHERE clause with OR connection

		if (func_num_args() > 1) {
			$this->sql = $this->sql . " OR $column $condition ?";

			$this->data[] = $value;
			$this->fields[] = $column;

		} else {
			$this->sql = $this->sql . " OR (";

			call_user_func($column);

			$this->sql = $this->sql . ")";
		}

		return $this;
	}

	public function and_where($column, $condition, $value) {
		// Append an additional WHERE clause with AND connection

		if (func_num_args() > 1) {
			$this->sql = $this->sql . " AND $column $condition ?";

			$this->data[] = $value;
			$this->fields[] = $column;

		} else {
			$this->sql = $this->sql . " AND (";

			call_user_func($column);

			$this->sql = $this->sql . ")";
		}

		return $this;

	}

	public function order_by($column, $order) {
		// Order query by $column in order of $order
		$this->sql = $this->sql . " ORDER BY $column $order"; 

		return $this;
	}

	public function limit($n) {
		// Limit query to $n results max
		$this->sql = $this->sql . " LIMIT $n";

		return $this;
	}

	public function group_by($column) {
		$this->sql = $this->sql + " GROUP BY $column";

		return $this;
	}

	private function exec_select() {
		// Returns array of selected rows
		
		$sql = $this->sql;

		echo "$sql";

		if ($sql == "") $sql = "SELECT * FROM $this->table";

		$stmt = $this->mysqli->prepare($sql);

		$datatypes = '';
		
		foreach ($this->fields as $field) {
			$datatypes = $datatypes . $this->datatypes[$field];
		}

		$stmt->bind_param($datatypes, ...$this->data);
		$stmt->execute();
		$result = $stmt->get_result();
		$data = array();
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}

		return $data;

	}

	/* **** INSERT **** */
	
	public function insert($columns, $values) {

		$this->command = 'i';

		$sql = "INSERT INTO $this->table ($columns[0]";

		$this->fields[] = $columns[0];

		for ($i=1; $i < count($columns); $i++) { 
			$sql = $sql . ", " . $columns[$i];
			$this->fields[] = $columns[$i];
		}

		$sql = $sql . ') VALUES (? ';

		$this->data[] = $values[0];

		for ($i=1; $i < count($values); $i++) { 
			$sql = $sql . ", ?";

			$this->data[] = $values[$i];
		}

		$sql = $sql . ')';
		$this->sql = $sql;

		return $this;
	}

	private function exec_insert() {

		$stmt = $this->mysqli->prepare($this->sql);

		$datatypes = '';
		
		foreach ($this->fields as $field) {
			$datatypes = $datatypes . $this->datatypes[$field];
		}

		$stmt->bind_param($datatypes, ...$this->data);

		return $stmt->execute();
	}

	/* **** DELETE **** */

	public function delete($column, $condition, $value) {

		$this->sql = "DELETE FROM $this->table WHERE $column $condition ?";

		$this->data[] = $value;
		$this->fields[] = $column;
		$this->command = "d";

		return $this;

	}

	private function exec_delete() {

		$stmt = $this->mysqli->prepare($this->sql);

		$datatypes = '';

		foreach ($this->fields as $field) {
			$datatypes = $datatypes . $this->datatypes[$field];
		}

		$stmt->bind_param($datatypes, ...$this->data);

		return $stmt->execute();
	}

	/* **** UPDATE **** */

	function update($column, $value) {

		$this->command = 'u';
		$this->sql = "UPDATE $column = ?";
		$this->data[] = $value;
		$this->fields[] = $column;

		//todo
	}
	/* **** EXECUTE **** */

	public function execute() {
		if ($this->command == 's')  return $this->exec_select();
		else if ($this->command == 'i') return $this->exec_insert();
		else if ($this->command == 'd') return $this->exec_delete();
	}

}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "liftapp";

$mysqli = new mysqli($servername, $username, $password, $dbname);

$query = new Query($mysqli);

$myData = $query->table('users')->where('age', '>', 20)
								->or_where(function() use($query) {
									$query->where('name', '=', 'Austin');
									$query->or_where('name', '=', 'Bailey');
								})
								->execute();

//var_dump($data);




























