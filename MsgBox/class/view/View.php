<?php

namespace view;

/**
 * View class for chat group
 */
class View
{
	/**
	 * @return string HTML page with public chat list
	 */
	public static function getPublicChatList()
	{
		$t = \model\Group::getPublicChatList();
		$t = \view\GetResource::centerFormText($t);
		return \view\GetResource::botChat("Gruppi pubblici", "", $t);
	}

	/**
	 * @return string HTML page for failed login
	 */
	public static function loginFailed()
	{
		$t = \view\GetResource::centerFormText("Password errato <br /><a href='MsgBox.php'>Torna indietro</a>");
		return \view\GetResource::botChat("Errore", "", $t);
	}

	/**
	 * @return string HTML page for failed creation of new chat group
	 */
	public static function createFailed()
	{
		$t = \view\GetResource::centerFormText("Chat esiste di già <br /><a href='MsgBox.php'>Torna indietro</a>");
		return \view\GetResource::botChat("Errore", "", $t);
	}

	/**
	 * @return string HTML page for auth before entering chat group
	 */
	public static function authPage($Group)
	{
		$nomeChat = $Group->getName();
		$userCount = $Group->getUserCount();
		$nome = "Tu";
		$form = \view\GetResource::authPage();
		return \view\GetResource::groupChat($nomeChat, $nome, $userCount, $form, "");
	}

	/**
	 * Login before accessing manage page
	 */
	public static function adminLogin() {

		$form = \view\GetResource::adminLogin();
		return \view\GetResource::botChat("AREA RISERVATA", "", $form);

	}

	/**
	 * @return string HTML page for chat created
	 */
	public static function chatCreated($link)
	{
		$t = \view\GetResource::centerFormText("Chat creato<br>" . \Config::serverURL .
			"/MsgBox/MsgBox.php?groupLink=$link" . "<br><a href = '?'>Lista gruppi pubblici</a><br><a href='?manage'>Pannello di controllo</a>");
		return \view\GetResource::botChat("Successo", "", $t);
	}

	/**
	 * @return string HTML group chat page
	 */
	public static function loadChat($User, $Group)
	{
		$nomeChat = $Group->getName();
		/*
		Create group is a private function.
		if you don't sanitize it you can easily inject tracking code
		*/
		//$nomeChat = htmlentities($Group->getName());
		$userCount = $Group->getUserCount();
		$nick = htmlentities($User->getUsername());
		$content = \view\GetResource::loadTextJS();
		$form = \view\GetResource::formImgSend();
		return \view\GetResource::groupChat($nomeChat, $nick, $userCount, $form, $content);
	}

	/**
	 * @return string HTML page for managing bot and chat group
	 */
	public static function manage($csrfValue)
	{
		$content = "";

		$content .= \view\GetResource::msgBox_newGroup("?", $csrfValue);
		$content .= \view\GetResource::msgBox_newBot("?", $csrfValue);
		$content .= \view\GetResource::msgBox_deleteGroup("?", \model\Group::deleteGroupSelection(), $csrfValue);
		$content .= \view\GetResource::msgBox_deleteBot("?", \model\Bot::deleteBotSelection(), $csrfValue);
		$content .= "<h1><a href='?'><b>>> LISTA GRUPPI PUBBLICI <<</b></a></h1>";
		$content .= "<br /><h1><a href='../index.php'><b><< BACK</b></a></h1>";

		return $content;
	}

	/**
	 * Generate messages
	 * @return string HTML of generated messages
	 */
	public static function formMessage($msgs, $userID)
	{
		$ret = "";
		foreach ($msgs as $msg) {
			if ($msg["idUser"] == $userID) { //Sended message
				$ret .= \view\GetResource::msgSend(
					self::sanitize($msg["msg"]),
					"Tu - " . date("Y-m-d H:i:s", $msg["sendTime"])
				);
			} else { //Received message
				$ret .= \view\GetResource::msgReceive(
					self::sanitize($msg["msg"]),
					$msg["nickname"] . " - " . date("Y-m-d H:i:s", $msg["sendTime"])
				);
			}
		}
		return $ret;
	}

	/**
	 * @param string $text html that need to be sanitized
	 * @return string sanitized html unless is a img
	 */
	public static function sanitize($text)
	{
		//Sanitize if it's not a image
		$dirRegex = str_replace("/", "\/", \Config::imgDIR); //Make image dir regex compatible
		$regex = '/' . $dirRegex . '([A-z,0-9]){0,16}\/....\.jpg/';
		if (preg_match($regex, $text)) {
			return "<a href='$text'><img class='img' src='$text'/></a>";
		} else {
			return htmlentities(urldecode($text));
		}
	}
}
