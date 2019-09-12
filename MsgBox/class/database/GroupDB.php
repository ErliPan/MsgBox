<?php

namespace database;

use PDO;

/**
 * Database class for managing chat groups
 */
class GroupDB extends DB
{

	function __construct($dataBase, $user, $pwd = "", $host = "127.0.0.1")
	{
		parent::__construct($dataBase, $user, $pwd, $host);
	}

	//GoupChat SQL functions
	/**
	 * Add group
	 */
	public function addGroup($name, $link, $time)
	{

		$sql = 'INSERT INTO ChatGroup(name, link, creation, userCount)
    VALUES(:nome, :link, :creation, 0)';

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':nome', $name);
		$stmt->bindParam(':link', $link);
		$stmt->bindParam(':creation', $time);

		$stmt->execute();
		$stmt->closeCursor();
	}

	/**
	 * return all public chats
	 * @return mixed multidimensional array
	 */
	public function getPublicChat()
	{
		//Groups are public when link = name
		$sql = "SELECT link, userCount as users FROM ChatGroup WHERE link = name";

		$stmt = $this->connessione->prepare($sql);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		return $app;
	}

	/**
	 * Get all chats
	 * @return mixed multidimensional array
	 */
	public function getGroupList()
	{

		$sql = "SELECT idGroup, name, link, userCount FROM ChatGroup";

		$stmt = $this->connessione->prepare($sql);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		return $app;
	}

	/**
	 * SELECT * FROM Users WHERE nickname = :user AND idGroup = :id
	 * @return mixed only the first result
	 */
	public function getUserInfo($userName, $groupID)
	{

		$sql = "SELECT * FROM Users WHERE nickname = :user AND idGroup = :id";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':user', $userName);
		$stmt->bindParam(':id', $groupID);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		//Return only the first result because there should be ony one per id
		return $app[0];
	}

	/**
	 * @return bool if the user exist
	 */
	public function userExist($userName, $groupID)
	{

		$sql = "SELECT COUNT(1) as result
      FROM Users WHERE nickname = :user AND idGroup = :id";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':user', $userName);
		$stmt->bindParam(':id', $groupID);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return (isset($app[0]["result"]) && $app[0]["result"] == 1) ? true : false;
	}

	/**
	 * @return bool if group exists
	 */
	public function groupExists($url)
	{

		$sql = "SELECT COUNT(1) as result FROM ChatGroup WHERE link = :url";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':url', $url);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return (isset($app[0]["result"]) && $app[0]["result"] == 1) ? true : false;
	}

	/**
	 * SELECT * FROM ChatGroup WHERE link = $groupLink
	 */
	public function getGroupInfo($groupLink)
	{

		$sql = "SELECT * FROM ChatGroup WHERE link = :link";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':link', $groupLink);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		//Return only the first result because there should be ony one per id
		return $app[0];
	}

	/**
	 * add new user
	 */
	public function addUser($userName, $passowrd, $salt, $groupID, $time, $ip)
	{

		$sql = "INSERT INTO Users
    (
      lastLogin, firstLogin, nickname, lastIP, password, salt, idGroup
    ) VALUES (
        :lastLogin, :firtLogin, :nickname, :lastIP, :password, :salt, :idGroup
    )";

		$stmt = $this->connessione->prepare($sql);

		$stmt->bindParam(':lastLogin', $time);
		$stmt->bindParam(':firtLogin', $time);
		$stmt->bindParam(':nickname', $userName);
		$stmt->bindParam(':lastIP', $ip);
		$stmt->bindParam(':password', $passowrd);
		$stmt->bindParam(':salt', $salt);
		$stmt->bindParam(':idGroup', $groupID);

		$stmt->execute();
		$stmt->closeCursor();
	}

	/**
	 * Get the current message offset for that group
	 * @return int
	 */
	public function currentMsgOffset($groupID)
	{

		$sql = "SELECT IFNULL(MAX(idMsgChat), 0) AS result
      FROM Message WHERE idGroup = :id";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':id', $groupID);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return (int) $app[0]["result"];
	}

	/**
	 * Send message
	 */
	public function addMessage($msg, $userID, $groupID)
	{

		$sql = "INSERT INTO Message(idMsgChat, msg, sendTime, idUser, idGroup)
      VALUES (:idMsgChat, :msg, :sendTime, :idUser, :idGroup)";

		$offset = $this->currentMsgOffset($groupID) + 1;
		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':idMsgChat', $offset);
		$stmt->bindParam(':msg', $msg);
		$time = time();
		$stmt->bindParam(':sendTime', $time);
		$stmt->bindParam(':idUser', $userID);
		$stmt->bindParam(':idGroup', $groupID);

		$stmt->execute();
		$stmt->closeCursor();
	}

	/**
	 * Get all message of $groupID between $offset and $maxOffset
	 */
	public function getMessage($groupID, $offset, $maxOffset)
	{

		$sql = 'SELECT msg, sendTime, Message.idUser, nickname
      FROM Message INNER JOIN Users ON Message.idUser = Users.idUser
      WHERE idMsgChat > :offset AND idMsgChat <= :maxOffset
      AND Message.idGroup = :id ORDER BY sendTime';

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':offset', $offset);
		$stmt->bindParam(':maxOffset', $maxOffset);
		$stmt->bindParam(':id', $groupID);
		$stmt->execute();
		$app = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $app;
	}

	/**
	 * Delete the chat group
	 * this delete also all user and messages
	 */
	public function deleteGroup($groupID)
	{

		$sql = "DELETE FROM Message WHERE idGroup = :id";
		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':id', $groupID);
		$stmt->execute();
		$stmt->closeCursor();
		$sql = "DELETE FROM Users WHERE idGroup = :id";
		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':id', $groupID);
		$stmt->execute();
		$stmt->closeCursor();
		$sql = "DELETE FROM ChatGroup WHERE idGroup = :id";
		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':id', $groupID);
		$stmt->execute();
		$stmt->closeCursor();
	}

	/**
	 * Updates user tracking info
	 */
	public function updateUserInfo($userID, $time, $ipAdd)
	{

		$sql = "UPDATE Users SET lastLogin = :lastLogin,
    lastIP = :ip WHERE idUser = :id";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':lastLogin', $time);
		$stmt->bindParam(':id', $userID);
		$stmt->bindParam(':ip', $ipAdd);
		$stmt->execute();
		$stmt->closeCursor();
	}

	/**
	 * Increment chat group user counter
	 */
	public function groupUserCountIncrement($groupID, $value = 1)
	{

		$sql = "UPDATE ChatGroup SET userCount = userCount + :value
      WHERE idGroup = :id";

		$stmt = $this->connessione->prepare($sql);
		$stmt->bindParam(':id', $groupID);
		$stmt->bindParam(':value', $value);
		$stmt->execute();
		$stmt->closeCursor();
	}
}
