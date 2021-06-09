<?php

class DB {
	public $connect = null;

	function __construct() {
		$this->connect = new mysqli(
			getenv('DB_HOST'),
			getenv('DB_USER'),
			getenv('DB_PASS'),
			getenv('DB_NAME')
		);

		if($this->connect->connect_error)
			$this->connect = null;
	}

	function __destruct() {
		$this->connect->close();
	}

	public function do_select($query) {
		$return = null;

		if($this->connect) {
			$result = $this->connect->query($query);

			if($result != null && $result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$return[] = $row;
				}
			}
		}

		return $return;
	}

	public function do_query($query) {
		$flag = false;

		if($this->connect)
			$flag = $this->connect->query($query);

		return $flag;
	}
}
?>