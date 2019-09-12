<?php

namespace model;

/**
 * Model class that manages group chats
 * 
 * Deleting and creating chats are static
 * 
 * @param string $groupLink the link of the group that the user uses for accessing it
 */
class Group extends Model
{

	private $groupID;
	private $name;
	private $link;
	private $creation;
	private $userCount;

	private $exists;

	public function __construct($groupLink)
	{
		parent::__construct();

		$this->link = $groupLink;

		if (!$this->db->groupExists($this->link)) {
			$this->exists = false;
		} else {
			$this->exists = true;
			$r = $this->db->getGroupInfo($this->link);

			$this->groupID = $r["idGroup"];
			$this->name = $r["name"];
			$this->creation = $r["creation"];
			$this->userCount = $r["userCount"];
		}
	}

	/* 
	 * Just some getter 
	 */
	public function getGroupID()
	{
		return $this->groupID;
	}
	public function getLink()
	{
		return $this->link;
	}
	public function getName()
	{
		return $this->name;
	}
	public function getUserCount()
	{
		return $this->userCount;
	}
	public function getExists()
	{
		return $this->exists;
	}

	/**
	 * Create new chat group
	 * @param string $name the group name
	 * @param bool $public if the group is public 
	 */
	public static function newGroup($name, $public)
	{
		$db = new \database\GroupDB(\Config::nameDB, \Config::user, \Config::pass);
		if ($public) {
			$link = $name;
		} else {
			$link = self::getRandomString(8);
		}
		$time = time();

		if ($db->groupExists($link)) {
			return false;
		} else {
			$db->addGroup($name, $link, $time);
			return $link;
		}
	}

	/**
	 * Delete chat group
	 * @param string $id
	 */
	public static function delGroup($id)
	{
		$db = new \database\GroupDB(\Config::nameDB, \Config::user, \Config::pass);
		$db->deleteGroup($id);
	}

	/**
	 * Get all public chat list in HTML
	 * @return string HTML content
	 */
	public static function getPublicChatList()
	{
		$db = new \database\GroupDB(\Config::nameDB, \Config::user, \Config::pass);
		$r = $db->getPublicChat();

		$content = empty($r) ? $content = "No chats<br>" : $content = "";

		foreach ($r as $key => $value) {
			//Public chat have as link their name
			$content .= "Chat: " . $value["link"] .
				" utenti: " . $value["users"] .
				"<a href = '?groupLink=" . $value["link"] . "'> ACCEDI </a><br />";
		}
		$content .= "<br><a href = '?manage'>Pannello di controllo</a>";

		return $content;
	}

	/**
	 * @return string HTML <option> all the chat group list
	 */
	public static function deleteGroupSelection()
	{
		$db = new \database\GroupDB(\Config::nameDB, \Config::user, \Config::pass);
		$r = $db->getGroupList();
		$result = "";
		foreach ($r as $key => $value) {
			$groupID = $value["idGroup"];
			$name = $value["name"];
			$link = $value["link"];
			$userCount = $value["userCount"];

			$result .= "<option value='$groupID'>nome: $name link: $link utenti: $userCount</option>";
		}
		if ($result == "") {
			return "<option> --- No group --- </option>";
		}
		return $result;
	}
}
