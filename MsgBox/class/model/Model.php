<?php

namespace model;

/**
 * Parent class where contains things that are used in every model class
 */
class Model
{

	protected $db;

	protected function __construct()
	{

		$this->db = new \database\GroupDB(\Config::nameDB, \Config::user, \Config::pass);
	}

	/**
	 * @param int $len lenght of the base62 string
	 * @return string return base62 string of len $len
	 */
	protected static function getRandomString($len)
	{
		//Generate secure random base62 string of lenght $len
		$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$string = '';
		$max = strlen($characters) - 1;
		for ($i = 0; $i < $len; $i++) {
			$string .= $characters[random_int(0, $max)];
		}
		return $string;
	}

	/**
	 * Check if website is protected by cloudflare and return user IP
	 * @return string user IP address
	 */
	protected static function getIP()
	{
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			return $_SERVER["HTTP_CF_CONNECTING_IP"];
		} else {
			return $_SERVER["REMOTE_ADDR"];
		}
	}
}
