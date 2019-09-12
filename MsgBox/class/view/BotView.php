<?php

namespace view;

/**
 * View class for bots
 */
class BotView
{

	public static function loadBotChat($nomeBot, $content)
	{
		if (!$content) {
			$content = "BOT attualmente offline, riprova tra poco";
		} else {
			$content = self::sendResponse($content);
		}
		return \view\GetResource::botChat($nomeBot, \view\GetResource::loadBotTextJS(), $content);
	}

	public static function sendResponse($message, $prevMsg = "")
	{
		$msg = $message[0];
		$kb  = $message[1];

		if ($prevMsg != "") {
			$prevMsg = urldecode(base64_decode($prevMsg));
			$content = \view\GetResource::msgSend($prevMsg, "Tu - " . date("Y-m-d H:i:s", time()));
		} else {
			$content = "";
		}

		if (is_array($msg)) {
			foreach ($msg as $key) {
				$content .= \view\GetResource::msgReceive($key, date("Y-m-d H:i:s", time()));
			}
		} else {
			$content .= \view\GetResource::msgReceive($msg, date("Y-m-d H:i:s", time()));
		}

		$keys = "";
		if ($kb != "") {
			if (is_array($kb)) {
				for ($i = 0; $i < count($kb); $i++) {
					$keys .= \view\GetResource::botKey($kb[$i]);
				}
			} else {
				$keys .= \view\GetResource::botKey($kb);
			}
		}

		return $content . "<span id='keys'>$keys</span>";
	}

	public static function botCreated($r)
	{
		if (!$r) {
			return \view\GetResource::botChat("Errore", "", \view\GetResource::centerFormText("CSRF Token mismatch riprova"));
		}
		$link = $r["link"];
		$token = $r["token"];
		return \view\GetResource::botChat("Successo", "", \view\GetResource::centerFormText("Il bot è stato creato<br>Link: " . \Config::serverURL . "/MsgBox/MsgBox.php?link=$link<br>Token: $token"));
	}

	public static function botDeleted()
	{
		return \view\GetResource::botChat("Errore", "", \view\GetResource::centerFormText("Il BOT non esiste più"));
	}
}
