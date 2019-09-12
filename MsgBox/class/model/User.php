<?php

namespace model;

/**
 * Model class for user object
 * @param string $groupID
 * @param Group $group
 */
class User extends Model
{

	private $lastLogin;
	private $lastIP;

	private $userID;
	private $nickName;
	private $salt;
	private $groupID;
	private $password;

	private $logged;
	private $Group;


	public function __construct($groupID, $Group)
	{
		parent::__construct();

		$this->groupID = $groupID;
		$this->Group = $Group;
		$this->verifyToken($this->Group->getLink());

		if ($this->logged == false) {
			return;
		}

		$r = $this->db->getUserInfo($this->nickName, $this->groupID);

		$this->salt = $r["salt"];
		$this->userID = $r["idUser"];
		$this->password = $r["password"];

		$this->lastIP = self::getIP();
		$this->lastLogin = time();
		$this->db->updateUserInfo($this->userID, $this->lastLogin, $this->lastIP);
	}

	//Some getter
	public function getUsername()
	{
		return $this->nickName;
	}
	public function getUserID()
	{
		return $this->userID;
	}
	public function getGroupID()
	{
		return $this->groupID;
	}
	public function getGroupLink()
	{
		return $this->Group->getLink();
	}
	public function isLogged()
	{
		return $this->logged;
	}
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * check if user exists
	 * @param string $userName well the username...
	 * @return bool if user exists
	 */
	public function userExist($userName)
	{
		if ($this->db->userExist($userName, $this->groupID)) {
			$this->logged = false;
			return true;
		} else {
			$this->logged = true;
			return false;
		}
	}

	/**
	 * This thing automatically understand if is a login attempt or register
	 * well i think this can be called AI by today standards
	 * @param string $user username
	 * @param string $pass the passw0rd
	 * @return bool if login/register is successful
	 */
	public function auth($user, $pass)
	{
		if ($this->db->userExist($user, $this->groupID)) {
			//$ret is the salt of the password if the user exists, otherwise is FALSE
			//Salt is used as unique identifier that's inaccessible from the user and it isn't predictable
			//The token is not forgeable and has time in it
			$ret = self::checkPass($user, $pass);
			//Login
			if ($ret === false) {
				sleep(3);
				$this->logged = false;
				return false;
			} else {
				$token = self::generateToken($user, $ret);
				self::setToken($token, $this->Group->getLink());
				$this->logged = true;
				return true;
			}
		}
		//Register
		else {
			$ret = self::newUser($user, $pass);
			if ($ret === false) { //The group does not exists anymore or for whatever reason
				$this->logged = false;
				return false;
			} else {
				$token = self::generateToken($user, $ret);
				self::setToken($token, $this->Group->getLink());
				$this->logged = true;
				return true;
			}
		}
	}

	/**
	 * Create a new user
	 * @param string $user
	 * @param string $password
	 */
	private function newUser($user, $password)
	{
		if ($this->db->userExist($user, $this->groupID) == false) {

			$salt = self::getRandomString(16);
			$password = hash("sha256", $password . $salt);
			$ip = self::getIP();
			$time = time();
			$this->db->addUser($user, $password, $salt, $this->groupID, $time, $ip);
			$this->db->groupUserCountIncrement($this->groupID);
		}
		return $salt;
	}

	/**
	 * Verify if user is already logged in
	 * @return bool if is already logged in
	 */
	private function verifyToken()
	{
		if (isset($_COOKIE["MsgBox" . $this->Group->getLink()])) {
			$token = $_COOKIE["MsgBox" . $this->Group->getLink()];
		} else {
			$this->logged = false;
			return false;
		}

		$hash = substr($token, 0, 64);
		$time = substr($token, 64, 10);
		$user = substr($token, 74);

		if (time() - $time > \Config::tokenLastTime * 3600) {
			$this->logged = false;
			return false;
		}

		if (!$this->db->userExist($user, $this->groupID)) {
			$this->logged = false;
			return false;
		}
		$infos = $this->db->getUserInfo($user, $this->groupID);
		$salt = $infos["salt"];

		if ($token == self::generateToken($user, $salt, $time)) {
			$this->nickName = $user;
			$this->logged = true;
			return true;
		} else {
			$this->logged = false;
			return false;
		}
	}

	/**
	 * check if user/password match
	 * @return mixed password salt (Used as non predictable user identification) if login is successful else returns false
	 */
	private function checkPass($user, $password)
	{
		$infos = $this->db->getUserInfo($user, $this->groupID);
		$inPass = hash("sha256", $password . $infos["salt"]);

		if ($inPass == $infos["password"]) {
			return $infos["salt"];
		} else {
			return false;
		}
	}

	/**
	 * Generate a token for session session cookie
	 * @param string $user 
	 * @param string $salt umpredictable string for making this thing secure
	 * @param int $time default current time so this thing can expire
	 */
	private function generateToken($user, $salt, $time = 0)
	/* How this thing works
	
	$hash is the hash of: username (predictable) + password salt (not predictable) + time (predictable)
	hacker cannot recreate the hash because the won't know the salt but the server will
	then the full $token is made of the hash (non forgeable) + time + username
	because of the hash user cannot forge the time and username because they cant forge the hash
	
	I think i need to use more standardized things but this was cool to do. I probably just added a vulnerability to this shitty webapp...
	*/
	{
		if ($time == 0) {
			$time = time();
		}
		$hash = hash("sha256", $user . $salt . $time);
		$token = $hash . $time . $user;

		return $token;
	}

	/**
	 * remove token from cookie
	 * used in logouts
	 */
	public function removeToken()
	{
		setcookie("MsgBox" . $this->Group->getLink(), " ", NULL, NULL, NULL, \Config::tokenHTTPS, true);
	}

	/**
	 * set token
	 * @param string $token set cookie but it have unique name for every group
	 */
	private function setToken($token)
	{
		setcookie("MsgBox" . $this->Group->getLink(), $token, NULL, NULL, NULL, \Config::tokenHTTPS, true);
	}
}
