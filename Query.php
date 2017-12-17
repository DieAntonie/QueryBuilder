<?php

class Query {

	private $mysqli = null;
	private $sql = "";
	private $datatypes = array();
	private $data = array();
	private $fields = array();
	private $table = "";

	public function __construct($mysqli = null) {
		// construct with mysqli object
		$this->mysqli = $mysqli;
	}

	public function set_mysqli($mysqli) {
		$this->mysqli = $mysqli;
	}

	public function clear() {
		//clears all Query object data
		$this->sql_command = '';
		$this->sql_info = '';
		$this->datatypes = '';
		$this->data = array();
		$this->table = '';
	}

	public function table($table) {
		$this->table = $table;

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

	/*
		****SELECT****
	*/

	public function select() {
		
	}

	public function where($column, $condition, $value) {
		// Set $sql to SELECT * FROM $this->table WHERE $column $condition $value
		$this->sql = "SELECT * FROM $this->table WHERE $column $condition ?";

		$this->data[] = $value;
		$this->fields[] = $column;

		return $this;
	}

	public function or_where() {
		// Append an additional WHERE clause with OR connection
		$this->sql = $this->sql . " OR WHERE $column $condition ?";

		$this->data[] = $value;
		$this->fields[] = $column;

		return $this;
	}

	public function and_where() {
		// Append an additional WHERE clause with AND connection
		$this->sql = $this->sql . " AND WHERE $column $condition ?";

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

	public function get() {
		// Returns array of selected rows
		

		$sql = $this->sql;
		var_dump($sql);
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

	/*
		****INSERT****
	*/

	public function insert($table, ...$columns) {
		$sql_command = "INSERT INTO $table ($columns[0]";
		for ($i=1; $i < count($columns); $i++) { 
			$sql_command = $sql_command . ", " . $columns[$i];
		}

		$sql_command = $sql_command . ")";

		$this->sql_command = $sql_command;

		return $this;
	}

	/*function values($datatypes, ...$values) {
		$sql_info = "VALUES (?";
		//reset data array in case it has been used
		$this->data = array();
		$this->data[] = $values[0];
		//set datatypes to user defined types
		$this->datatypes = $datatypes;

		for ($i=1; $i < count($values); $i++) { 
			$this->data[] = $values[$i];
			$sql_info = $sql_info . ", ?";
		}

		$sql_info = $sql_info . ")";

		$this->sql_info = $sql_info;

		return $this;

	}*/

	public function exec_insert() {
		$sql = "$this->sql_command $this->sql_info";
		$stmt = $this->mysqli->prepare($sql);
		$stmt->bind_param($this->datatypes, ...$this->data);

		return $stmt->execute();
	}

}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "liftapp";

$mysqli = new mysqli($servername, $username, $password, $dbname);

$query = new Query($mysqli);

/*$myData = $query->select('users', '*')
				->where('name', '=', 'Austin Bailey', 's')
				->where('id', '=', "egag", 's', 'OR')
				->order_by('id', 'DESC')
				->limit(5)
				->get();*/

$data = $query->table('lifts')->where('reps', '>', 5)->get();

var_dump($data);


























