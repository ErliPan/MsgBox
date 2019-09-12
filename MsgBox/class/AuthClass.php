<?php

/**
 * Class for auth user before it can access the manage page
 * manage password and user in Config class
 */
class AuthClass
{

	public static function auth($userInput, $passInput)
	{
		if ($userInput == Config::adminUser && hash("sha256", $passInput) == Config::adminPass) {

			$cookie = hash("sha256", Config::adminPass . date("Y-m-d"));
			setcookie("auth", $cookie, NULL, NULL, NULL, NULL, true);
			$_SESSION["cookie"] = $cookie;

			header("Location: " . $_SERVER["REQUEST_URI"]);
			return true;
		}
		return false;
	}
	public static function isLogged()
	{
		return isset($_SESSION["cookie"]) && isset($_COOKIE["auth"]) && $_SESSION["cookie"] == $_COOKIE["auth"];
	}
	public static function set_csrf_token($name)
	{
		$csrf_token = bin2hex(random_bytes(32));
		setcookie($name, $csrf_token);
		$_SESSION[$name] = $csrf_token;
		return $csrf_token;
	}
	public static function verify_csrf_token($name, $input)
	{
		//echo $_SESSION[$name] . " : " . $input;
		if (isset($_SESSION[$name]) && $_SESSION[$name] == $input) {
			self::set_csrf_token($name);
			return true;
		} else {
			self::set_csrf_token($name);
			return false;
		}
	}
}
