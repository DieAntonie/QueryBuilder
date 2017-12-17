<?php

class Query {

	private $mysqli = null;
	private $sql = '';
	private $datatypes = array();
	private $data = array();
	private $fields = array();
	private $table = '';
	private $command = '';

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

	public function where($column, $condition, $value) {
		// Set $sql to SELECT * FROM $this->table WHERE $column $condition $value

		$this->sql = "SELECT * FROM $this->table WHERE $column $condition ?";

		$this->data[] = $value;
		$this->fields[] = $column;
		$this->command = "s";

		return $this;
	}

	/* or_where and and_where can both be used to append conditions to DELETE queries */

	public function or_where($column, $condition, $value) {
		// Append an additional WHERE clause with OR connection
		$this->sql = $this->sql . " OR $column $condition ?";

		$this->data[] = $value;
		$this->fields[] = $column;

		return $this;
	}

	public function and_where($column, $condition, $value) {
		// Append an additional WHERE clause with AND connection
		$this->sql = $this->sql . " AND $column $condition ?";

		$this->data[] = $value;
		$this->fields[] = $column;

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

	private function exec_select() {
		// Returns array of selected rows
		
		$sql = $this->sql;

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

$data = $query->table('lifts')
			->delete('reps', '=', 1)
			->or_where('weight', '<', 100.0)
			->execute();

var_dump($data);



























