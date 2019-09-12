<?php

namespace model;

/**
 * The model class fot Bots
 * Creation and deletion of a bot is static
 * 
 * @param string $link the link of the existing bot
 */
class Bot extends Model
{

	private $idBot;
	private $token;
	private $webHook;
	private $link;
	private $name;

	private $exists;
	private $userID;

	public function __construct($link)
	{
		$this->db = new \database\BotDB(\Config::nameDB, \Config::user, \Config::pass);
		$this->exists = $this->db->botExist($link);

		if ($this->exists) {
			$r = $this->db->getBotInfo($link);

			$this->idBot = $r["idBot"];
			$this->token = $r["token"];
			$this->webHook = $r["webHook"];
			$this->link = $r["link"];
			$this->name = $r["name"];

			if (!$this->getToken($this->link)) {
				$this->userID = \model\Model::getRandomString(6);
				$this->setToken($this->userID);
			}
		}
	}

	/**
	 * @return bool if this bot exists
	 */
	public function botExist()
	{
		return $this->exists;
	}

	/**
	 * @return string bot name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $msg send message to the bot
	 */
	public function sendMessage($message)
	{
		return $this->getResponse("msg=" . $message);
	}

	/**
	 * Make a request to the bot via webhook and send "/start" as message
	 * @return string bot response to "/start" message
	 */
	public function startChat()
	{
		$start = base64_encode("/start");
		return $this->getResponse("msg=" . $start);
	}

	/**
	 * Private function that make the webhook call and decodes the response
	 * if it fails to decode return false
	 * @return mixed if failed returns false, else returns the response to the webhook call
	 */
	private function getResponse($queryString)
	{
		try {
			return json_decode(file_get_contents($this->webHook . "?user=" . $this->userID . "&" . $queryString));
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Private function that set a string as session cookie
	 * @param string $token value to store in the session cookie
	 */
	private function setToken($token)
	{
		setcookie("MsgBox_Bot" . $this->link, $token, NULL, NULL, NULL, \Config::tokenHTTPS, true);
	}

	/**
	 * Private function to get the data stored with setToken()
	 * @return bool false if nothing is found, the cookie value if found
	 */
	private function getToken()
	{
		if (isset($_COOKIE["MsgBox_Bot" . $this->link])) {
			return $_COOKIE["MsgBox_Bot" . $this->link];
		} else {
			return false;
		}
	}

	/**
	 * Static method for deleting a bot
	 * @param string $token the bot token
	 */
	public static function delBot($token)
	{
		$db = new \database\BotDB(\Config::nameDB, \Config::user, \Config::pass);
		$db->deleteBot($token);
	}

	/**
	 * Static function for creating a new bot
	 * @param string $name name of the bot
	 * @param string $webhook the link of the bot's webhook
	 */
	public static function addBot($name, $webHook)
	{
		$db = new \database\BotDB(\Config::nameDB, \Config::user, \Config::pass);
		$link = self::getRandomString(16);
		$token = self::getRandomString(32);
		$db->addBot($name, $token, $webHook, $link);
		return array("link" => $link, "token" => $token);
	}

	/**
	 * @return string HTML <option> of all existing bots
	 */
	public static function deleteBotSelection()
	{
		$db = new \database\BotDB(\Config::nameDB, \Config::user, \Config::pass);
		$r = $db->getBotsList();
		$result = "";
		foreach ($r as $key => $value) {
			$token = $value["token"];
			$name = $value["name"];
			$webhook = $value["webHook"];
			$link = $value["link"];

			$result .= "<option value='$token'>nome: $name webHook: $webhook link: $link</option>";
		}
		if ($result == "") {
			return "<option> --- No bot --- </option>";
		}
		return $result;
	}
}
