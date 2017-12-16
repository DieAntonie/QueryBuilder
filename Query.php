<?php

class Query {

	public $mysqli = null;
	public $sql_command = "";
	public $sql_info = "";
	public $datatypes = "";
	public $data = array();

	function __construct($mysqli = null) {
		// construct with mysqli object
		$this->mysqli = $mysqli;
	}

	function set_mysqli($mysqli) {
		$this->mysqli = $mysqli;
	}

	/*
		****SELECT****
	*/

	function select($table, ...$columns) {
		/*
		* Resets and sets the command to SELECT $columns FROM $table
		*/

		$sql_command = "SELECT ";
		if (count($columns) > 1) {
			$sql_command = $sql_command . $columns[0];
			for ($i=1; $i < count($columns); $i++) { 
				$sql_command = $sql_command . ", " . $columns[$i];
			}
		} else {
			$sql_command = $sql_command . $columns[0];
		}

		$sql_command = $sql_command . " FROM " . $table;

		$this->sql_command = $sql_command;

		return $this;

	}

	function where($column, $condition, $value, $type, $logic = "AND") {
		/*
		* Append conditions for WHERE statements.
		* Ex: WHERE count > 0
		* $type is to be either 'i' for int, 's', for string, 'd' for double, or 'b' for blob
		* By default, $logic is 'AND'. User can specify how they want to connect conditions. 
		* If it is the first condition, $logic is ignored 
		*/

		$this->datatypes = $this->datatypes . $type;
		$this->data[] = $value;

		if ($this->sql_info == "") {
			$this->sql_info = "WHERE $column $condition ?"; 
		} else {
			$this->sql_info = $this->sql_info . " $logic $column $condition ?";
		}

		return $this;
	}

	function get() {
		/*
		Returns array of selected rows
		*/

		$sql = "$this->sql_command $this->sql_info";
		$stmt = $this->mysqli->prepare($sql);
		$stmt->bind_param($this->datatypes, ...$this->data);
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

	function insert($table, ...$columns) {
		$sql_command = "INSERT INTO $table ($columns[0]";
		for ($i=1; $i < count($columns); $i++) { 
			$sql_command = $sql_command . ", " . $columns[$i];
		}

		$sql_command = $sql_command . ")";

		$this->sql_command = $sql_command;

		return $this;
	}

	function values($datatypes, ...$values) {
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

	}

	function exec_insert() {
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
$query->insert('lifts', 'weight', 'reps')->values('ii', 5, 5)->exec_insert();




























