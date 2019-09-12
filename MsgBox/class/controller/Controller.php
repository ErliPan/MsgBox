<?php

namespace controller;

/**
 * Controller for interfacing with the model
 * Creation and deletion of chatGroup are static
 * 
 * @param string $groupLink link of the group
 */
class Controller
{

	private $User;
	private $Group;

	public function __construct($groupLink)
	{
		$this->Group = new \model\Group($groupLink);

		if ($this->Group->getExists()) {
			$this->User = new \model\User($this->Group->getGroupID(), $this->Group);
		} else {
			//Group doesn't exists anymore
			header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
		}
	}

	/*
	 * Getter
	 */
	public function getUser()
	{
		return $this->User;
	}

	public function getGroup()
	{
		return $this->Group;
	}

	/**
	 * Check if user is already logged in
	 * @return bool if user is logged in
	 */
	public function verifyToken()
	{
		return $this->User->isLogged();
	}

	/**
	 * Send message
	 * @param string $message the message
	 * @param string $csrfValue csrftoken for security reason
	 */
	public function sendMessage($message, $csrfValue)
	{
		if (\AuthClass::verify_csrf_token(\Config::msgBoxCsrfName, $csrfValue)) {
			$message = base64_decode($message);
			\model\Api::sendMessage($message, $this->User->getUserID(), $this->Group->getGroupID());
		}
	}

	/**
	 * API for getting msg offset, user count and logout
	 * @param string $action "getOffset" or "logout" or "getUserCount"
	 * @return mixed depends on which action you choosed
	 */
	public function action($action)
	{

		if ($action == "getOffset") {
			return \model\Api::currentMsgOffset($this->Group->getGroupID());
		} else if ($action == "logout") {
			$this->User->removeToken();
			header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
		} else if ($action == "getUserCount") {
			return $this->Group->getUserCount();
		}
	}

	/**
	 * Get message after certain point
	 * @param int $offset get all message after this offset
	 * @param string $csrfValue csrftoken for security reason
	 */
	public function getMessage($offset, $csrfValue)
	{
		if (\AuthClass::verify_csrf_token(\Config::msgBoxCsrfName, $csrfValue)) {
			$userID = $this->User->getUserID();
			if ($offset >= \model\Api::currentMsgOffset($this->Group->getGroupID())) {
				die();
			}
			return \model\Api::getMessage($this->Group->getGroupID(), $offset, $userID);
		} else {
			return "<span style='display:none'>CSRF_mismatch</span>";
		}
	}

	/**
	 * Send image to some chatgroup
	 * @param string $file directory and name of the temporary file usually eg "$_FILES['image']['tmp_name']"
	 * @param string $csrfValue csrftoken for security reason
	 */
	public function sendImage($file, $csrfValue)
	{
		if (\AuthClass::verify_csrf_token(\Config::msgBoxCsrfName, $csrfValue)) {
			sleep(1); //Spread server load
			$url = \model\Api::saveImage($file, $this->Group->getLink());
			\model\Api::sendMessage($url, $this->User->getUserID(), $this->Group->getGroupID());
			header("Location: " . $_SERVER['REQUEST_URI']);
		} else {
			echo "CSRF Token mismatch. Redirect tra 3 secondi...";
			echo '<script>setTimeout("window.location.href=\'MsgBox.php\'", 3000)</script>';
		}
	}

	/**
	 * Login or register, it choose it automatically
	 * @param string $user
	 * @param string $pass
	 * @param string $csrfValue csrftoken for security reason
	 * 
	 * @return bool false if csrf token is invalid or login is unsuccessful true if successful
	 */
	public function auth($user, $pass, $csrfValue)
	{
		if (\AuthClass::verify_csrf_token(\Config::msgBoxCsrfName, $csrfValue)) {
			return $this->User->auth($user, $pass);
		} else {
			return false;
		}
	}

	/**
	 * Check if user exists
	 * @param string $userName
	 * @return bool
	 */
	public function userExist($userName)
	{
		return $this->User->userExist($userName);
	}


	//Static functions
	/**
	 * Create chat group
	 * @param string $name
	 * @param string $visibility "pub" if public, anything else is private
	 * @param string $csrfValue csrftoken for security reason
	 */
	public static function createGroup($name, $visibility, $csrfValue)
	{
		if (\AuthClass::verify_csrf_token(\Config::msgBoxCsrfName, $csrfValue)) {
			$pubblico = ($visibility == "pub");
			return \model\Group::newGroup($name, $pubblico);
		} else {
			return false;
		}
	}

	/**
	 * Delete chat group
	 * @param int $id id of the group
	 * @param string $csrfValue csrftoken for security reason
	 */
	public static function deleteGroup($id, $csrfValue)
	{
		if (\AuthClass::verify_csrf_token(\Config::msgBoxCsrfName, $csrfValue)) {
			\model\Group::delGroup($id);
		}
	}
}
