<?php

namespace controller;

/**
 * Controller class for the bot
 * Creation and deletion of a bot is static
 * 
 * @param string $link the link of the existing bot
 */
class BotController
{

	private $Bot;
	private $link;

	public function __construct($link)
	{
		$this->link = $link;
		$this->Bot = new \model\Bot($this->link);
	}

	/**
	 * @return bool if this bot exists
	 */
	public function botExist()
	{
		return $this->Bot->botExist();
	}

	/**
	 * @return string bot name
	 */
	public function getBotName()
	{
		return $this->Bot->getName();
	}

	/**
	 * Make a request to the bot via webhook and send "/start" as message
	 * @return string bot response to "/start" message
	 */
	public function startChat()
	{
		return $this->Bot->startChat();
	}
	/**
	 * @param string $msg send message to the bot
	 */
	public function sendMessage($msg)
	{
		return $this->Bot->sendMessage($msg);
	}

	/**
	 * Static function for creating a new bot
	 * @param string $name name of the bot
	 * @param string $webhook the link of the bot's webhook
	 * @param string $csrfValue the csrf token for added security
	 */
	public static function createBot($name, $webHook, $csrfValue)
	{
		if (\AuthClass::verify_csrf_token(\Config::msgBoxCsrfName, $csrfValue) | true) {
			return \model\Bot::addBot($name, $webHook);
		}
	}
	/**
	 * Static method for deleting a bot
	 * @param string $token the bot token
	 * @param string $csrfValue the csrf token for added security
	 */
	public static function deleteBot($token, $csrfValue)
	{
		if (\AuthClass::verify_csrf_token(\Config::msgBoxCsrfName, $csrfValue)) {
			\model\Bot::delBot($token);
		}
	}
}
