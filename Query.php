<?php

class Query {

	public $mysqli = null;
	public $sql_command = "";
	public $sql_info = "";
	public $datatypes = "";

	function __construct($mysqli = null) {
		// construct with mysqli object
		$this->mysqli = $mysqli;
	}

	function set_mysqli($mysqli) {
		$this->mysqli = $mysqli;
	}

	function select(...$columns) {
		$sql_command = "SELECT ";
		var_dump($columns);
		if (count($columns) > 1) {
			$sql_command = $sql_command . '(' . $columns[0];
			for ($i=1; $i < count($columns); $i++) { 
				$sql_command = $sql_command . ", " . $columns[$i];
			}
			$sql_command = $sql_command . ')';
		}

		echo $sql_command;
	}

}

$query = new Query();
$query->select('user', 'column2', 'column3');


