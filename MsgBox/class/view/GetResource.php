<?php

namespace view;

/**
 * Get HTML code and fill with the variables
 */
class GetResource
{

	public static function groupChat($nomeChat, $nick, $userCount, $form, $content, $keys = "")
	{
		$template = file_get_contents(\Config::resourceDIR . "/template/template.html");
		$layout = file_get_contents(\Config::resourceDIR . "/template/msgInfoBar/group.html");
		$template = str_replace("%msgInfoBar%", $layout, $template);

		$template = str_replace("%titolo%", $nomeChat, $template);
		$template = str_replace("%content%", $content, $template);
		$template = str_replace("%keys%", $keys, $template);
		$template = str_replace("%form%", $form, $template);

		$template = str_replace("%nome%", $nomeChat, $template);
		$template = str_replace("%nick%", $nick, $template);
		$template = str_replace("%user%", $userCount, $template);

		return $template;
	}

	public static function botChat($nomeBot, $form, $content, $keys = "<span id='keyboard'></span>")
	{
		$template = file_get_contents(\Config::resourceDIR . "/template/template.html");
		$layout = file_get_contents(\Config::resourceDIR . "/template/msgInfoBar/bot.html");
		$template = str_replace("%msgInfoBar%", $layout, $template);

		$template = str_replace("%nomeBot%", $nomeBot, $template);

		$template = str_replace("%titolo%", $nomeBot, $template);
		$template = str_replace("%content%", $content, $template);
		$template = str_replace("%keys%", $keys, $template);
		$template = str_replace("%form%", $form, $template);

		return $template;
	}

	/**
	 * Support up to 4 key per row
	 * @return mixed HTML code if it's <= 4 keys, false if it's > 4 keys
	 */
	public static function botKey($key)
	{
		if (!is_array($key)) {
			$template = file_get_contents(\Config::resourceDIR . "/template/botKeys/x1.html");
			$template = str_replace("%key0%", addslashes($key), $template);
			$template = str_replace("%text0%", htmlspecialchars($key), $template);
			return $template;
		}

		if (count($key) <= 4) {
			$template = file_get_contents(\Config::resourceDIR . "/template/botKeys/x" . count($key) . ".html");
			for ($i = 0; $i < count($key); $i++) {
				$template = str_replace("%key$i%", addslashes($key[$i]), $template);
				$template = str_replace("%text$i%", htmlspecialchars($key[$i]), $template);
			}
			return $template;
		}
		return false;
	}

	public static function msgSend($text, $info)
	{
		$info = htmlspecialchars($info);
		$template = file_get_contents(\Config::resourceDIR . "/template/msgBox/msgSend.html");
		$template = str_replace("%text%", $text, $template);
		$template = str_replace("%info%", $info, $template);
		return $template;
	}

	public static function msgReceive($text, $info)
	{
		$info = htmlspecialchars($info);
		$template = file_get_contents(\Config::resourceDIR . "/template/msgBox/msgReceive.html");
		$template = str_replace("%text%", $text, $template);
		$template = str_replace("%info%", $info, $template);
		return $template;
	}

	public static function msgBox_newGroup($action, $csrfValue)
	{
		$template = file_get_contents(\Config::resourceDIR . "/template/msgBox/newGroup.html");
		$template = str_replace("%action%", $action, $template);
		$template = str_replace("%csrf%", $csrfValue, $template);
		return $template;
	}

	public static function msgBox_newBot($action, $csrfValue)
	{
		$template = file_get_contents(\Config::resourceDIR . "/template/msgBox/newBot.html");
		$template = str_replace("%action%", $action, $template);
		$template = str_replace("%csrf%", $csrfValue, $template);
		return $template;
	}

	public static function msgBox_deleteGroup($action, $selection, $csrfValue)
	{
		$template = file_get_contents(\Config::resourceDIR . "/template/msgBox/deleteGroup.html");
		$template = str_replace("%action%", $action, $template);
		$template = str_replace("%selection%", $selection, $template);
		$template = str_replace("%csrf%", $csrfValue, $template);
		return $template;
	}

	public static function msgBox_deleteBot($action, $selection, $csrfValue)
	{
		$template = file_get_contents(\Config::resourceDIR . "/template/msgBox/deleteBot.html");
		$template = str_replace("%action%", $action, $template);
		$template = str_replace("%selection%", $selection, $template);
		$template = str_replace("%csrf%", $csrfValue, $template);
		return $template;
	}

	public static function authPage()
	{
		return file_get_contents(\Config::resourceDIR . "/template/formOverlay/authForm.html");
	}

	public static function adminLogin()
	{
		return file_get_contents(\Config::resourceDIR . "/template/formOverlay/adminLogin.html");;
	}

	/**
	 * @param string $text some text
	 * @return string HTML $text text but centered and with 24px font
	 */
	public static function centerFormText($text)
	{
		//Text that are shown on bot creation, on error like not valid link and so on
		return "<div style='text-align: center;font-size: 24px'>" . $text . "</div>";
	}

	public static function formImgSend()
	{
		$template = file_get_contents(\Config::resourceDIR . "/template/formOverlay/imgSend.html");
		return str_replace("%querySring%", $_SERVER['QUERY_STRING'], $template);
	}

	public static function loadTextJS()
	{
		return "<script>" . file_get_contents(\Config::resourceDIR . "/js/loadText.js") . "</script>";
	}

	public static function loadBotTextJS()
	{
		return "<script>" . file_get_contents(\Config::resourceDIR . "/js/loadBotText.js") . "</script>";
	}
}
