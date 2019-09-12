<?php

namespace database;

use PDO;

/**
 * Parent database class
 * contain only function that are needed for every db class
 */
class DB
{

	protected $connessione;

	protected function __construct($dataBase, $user, $pwd, $host)
	{
		try {
			$this->connessione = new PDO("mysql:host=$host;dbname=$dataBase", $user, $pwd);
		} catch (PDOException $e) {
			echo "Errore: " . $e->getMessage();
			die();
		}
	}
}
