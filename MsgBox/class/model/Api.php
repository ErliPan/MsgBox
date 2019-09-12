<?php

namespace model;

/**
 * Class that manages the API for the AJAX requests
 * Everything is static because there will be only one call per request
 * 
 * I don't know if i did right so some suggestion is appreciated
 */
class Api extends Model
{	

	/**
	 * Api for send message
	 * @param string $msg
	 * @param string $userID
	 * @param string $groupID
	 */
	public static function sendMessage($msg, $userID, $groupID)
	{
		$db = new \database\GroupDB(\Config::nameDB, \Config::user, \Config::pass);
		$db->addMessage($msg, $userID, $groupID);
	}

	/**
	 * Get the current message offset
	 * @param string $groupID
	 */
	public static function currentMsgOffset($groupID)
	{
		$db = new \database\GroupDB(\Config::nameDB, \Config::user, \Config::pass);
		return $db->currentMsgOffset($groupID);
	}

	/**
	 * Get the messages after certain offset
	 * @param string $groupID
	 * @param string $offset return x number of messages after this offset. x is in the config file
	 * @param string $userID
	 */
	public static function getMessage($groupID, $offset, $userID)
	{
		$db = new \database\GroupDB(\Config::nameDB, \Config::user, \Config::pass);

		$currentOffset = $db->currentMsgOffset($groupID);
		if ($currentOffset <= $offset) {
			return false;
		}
		$maxOffset = $offset + \Config::maxMessagePerRequest;

		$msgs = $db->getMessage($groupID, $offset, $maxOffset);
		return \view\View::formMessage($msgs, $userID);
	}

	/**
	 * Function for saving a image
	 * @param string $imgUrl the temporary url that PHP stores in the TMP directory of the server
	 * @param string $groupLink
	 */
	public static function saveImage($imgUrl, $groupLink)
	{

		if (!file_exists(\Config::imgDIR . "$groupLink/")) {
			mkdir(\Config::imgDIR . "$groupLink/", 0700, true);
		}

		$uploadfile = \Config::imgDIR . "$groupLink/" . self::getRandomString(4) . ".jpg";

		$a = getimagesize($imgUrl);
		$image_type = $a[2];
		list($newWidth, $newHeight) = $a;
		list($width, $height) = $a;

		if ($image_type == IMAGETYPE_PNG) {
			$img = imagecreatefrompng($imgUrl);
		} else if ($image_type == IMAGETYPE_JPEG) {
			$img = imagecreatefromjpeg($imgUrl);
		}

		while ($newWidth * $newHeight > 1300000) {
			$newWidth /= 1.4;
			$newHeight /= 1.4;
		}

		$newWidth = (int) $newWidth;
		$newHeight = (int) $newHeight;

		$image_p = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($image_p, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		imagejpeg($image_p, $uploadfile, 35);

		return $uploadfile;
	}
}
