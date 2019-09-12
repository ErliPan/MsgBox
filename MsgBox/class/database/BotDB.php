<?php

namespace database;

use PDO;

/**
 * Database class for bots
 */
class BotDB extends DB
{

	function __construct($dataBase, $user, $pwd, $host = "127.0.0.1")
	{
		parent::__construct($dataBase, $user, $pwd, $host);
	}

	//BotChat SQL public functions
	/**
	 * @return bool if bot exists
	 */
	public function botExist($link)
	{

		$sql = "SELECT COUNT(1) as result
			FROM Bots
			WHERE link = :link";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':link', $link);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return ($app[0]["result"] == 1) ? true : false;
	}

	/**
	 * Add new bot
	 */
	public function addBot($name, $token, $webHook, $link)
	{

		$sql = "INSERT INTO Bots(token, webHook, link, name)
		VALUES(:token, :webHook, :link, :name)";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':token', $token);
		$stmt->bindParam(':webHook', $webHook);
		$stmt->bindParam(':name', $name);
		$stmt->bindParam(':link', $link);
		$stmt->execute();
		$stmt->closeCursor();
	}

	/**
	 * update token value
	 */
	public function updateToken($newToken, $oldToken)
	{

		$sql = "UPDATE Bots
			SET token = :new
			WHERE token = :old";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':new', $newToken);
		$stmt->bindParam(':old', $oldToken);
		$stmt->execute();
		$stmt->closeCursor();
	}

	/**
	 * Update webhook value
	 */
	public function updateWebHook($token, $webHook)
	{

		$sql = "UPDATE Bots
			SET webHook = :webHook
			WHERE token = :old";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':webHook', $webHook);
		$stmt->bindParam(':old', $token);
		$stmt->execute();
		$stmt->closeCursor();
	}

	/**
	 * update name value
	 */
	public function updateName($token, $name)
	{

		$sql = "UPDATE Bots
			SET name = :name
			WHERE token = :old";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':name', $name);
		$stmt->bindParam(':old', $token);
		$stmt->execute();
		$stmt->closeCursor();
	}

	/**
	 * SELECT * FROM Bots WHERE link = $link
	 * @return mixed result[0] (There should be only one bot per link)
	 */
	public function getBotInfo($link)
	{
		$sql = "SELECT * FROM Bots WHERE link = :link";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':link', $link);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		return $app[0];
	}
	/**
	 * Delete bot from token
	 */
	public function deleteBot($token)
	{
		$sql = "DELETE FROM Bots WHERE token = :token";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':token', $token);
		$stmt->execute();
		$stmt->closeCursor();
	}
	/**
	 * SELECT * FROM Bots
	 * @return mixed
	 */
	public function getBotsList()
	{
		$sql = "SELECT * FROM Bots";

		$stmt = $this->connessione->prepare($sql);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		return $app;
	}
}
