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

	/*function __construct($servername, $username, $password, $dbname) {
		$this->mysqli = new mysqli($servername, $username, $password, $dbname);
	}*/

	function set_mysqli($mysqli) {
		$this->mysqli = $mysqli;
	}

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

		$this->datatypes = $this->datatypes . $type;
		$this->data[] = $value;

		if ($this->sql_info == "") {
			$this->sql_info = "WHERE $column $condition ?"; 
		} else {
			$this->sql_info = $this->sql_info . " $logic $column $condition ?";
		}

		return $this;
	}

	function execute_query() {
		$sql = "$this->sql_command $this->sql_info";
		echo $sql;
		$stmt = $this->mysqli->prepare($sql);
		$stmt->bind_param($this->datatypes, ...$this->data);
		$stmt->execute();

		//build array with generic data
		$result = $stmt->get_result();
		$data = array();
		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}

		//return the array
		echo json_encode($data);


	}

}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "liftapp";

$mysqli = new mysqli($servername, $username, $password, $dbname);

$query = new Query($mysqli);
$query->select('lifts', 'user', 'weight', 'reps')->where('user', '=', 1, 'i')->where('id', '>', 0, 'i', 'OR')->execute_query();




























