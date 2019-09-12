<?php

date_default_timezone_set("Europe/Rome");

if (!isset($_SESSION)) {
	session_start();
}
spl_autoload_register(function ($className) {
	$className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
	include_once __DIR__ . '/class/' . $className . '.php';
});


//Create bot API
if (isset($_GET["name"]) && isset($_GET["webHook"]) && isset($_GET[Config::msgBoxCsrfName])) {
	$r = \controller\BotController::createBot($_GET["name"], $_GET["webHook"], $_GET[Config::msgBoxCsrfName]);
	echo \view\BotView::botCreated($r);
	die();
}

//Delete Bot
if (isset($_GET["delBot"]) && isset($_GET[Config::msgBoxCsrfName])) {
	\controller\BotController::deleteBot($_GET["delBot"], $_GET[Config::msgBoxCsrfName]);
	header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?manage");
	die();
}

//Bots API always contain "link" parameter except creation and deletion
if (isset($_GET["link"])) {
	$Controller = new \controller\BotController($_GET["link"]);

	if ($Controller->botExist()) {
		//Send message and receive response
		if (isset($_GET["sendMessage"])) {
			$msgs = $Controller->sendMessage($_GET["sendMessage"]);
			echo \view\BotView::sendResponse($msgs, $_GET["sendMessage"]);
		} else {
			$content = $Controller->startChat();
			echo \view\BotView::loadBotChat($Controller->getBotName(), $content);
		}
	} else {
		echo \view\BotView::botDeleted();
	}
	die();
}

//Create Group
if (isset($_GET["nome"]) && isset($_GET["visibilita"]) && isset($_GET[Config::msgBoxCsrfName])) {
	$link = \controller\Controller::createGroup($_GET["nome"], $_GET["visibilita"], $_GET[Config::msgBoxCsrfName]);
	if (!$link) {
		echo view\View::createFailed();
	} else {
		echo \view\View::chatCreated($link);
	}
	die();
}

//Delete Group
if (isset($_GET["delGroup"]) && isset($_GET[Config::msgBoxCsrfName])) {
	\controller\Controller::deleteGroup($_GET["delGroup"], $_GET[Config::msgBoxCsrfName]);
	header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?manage");
	die();
}

//Manage page
if (isset($_GET["manage"])) {
	if (AuthClass::isLogged()) {
		require_once("../lib/Site.php");
		$csrf = AuthClass::set_csrf_token(Config::msgBoxCsrfName);
		$site = new Site(view\View::manage($csrf));
		$site->template2("MSGBOX");
	} else {
		if (isset($_POST["user"]) && isset($_POST["pass"]) && !AuthClass::auth($_POST["user"], $_POST["pass"])) {
			sleep(3);
			echo view\View::loginFailed();
		} else {
			echo view\View::adminLogin();
		}
	}
	die();
}

//Show list of public groups
if (!isset($_GET["groupLink"])) {
	echo view\View::getPublicChatList();
	die();
}

$Controller = new \controller\Controller($_GET["groupLink"]);

//Login/register API
if (isset($_POST["user"]) && isset($_POST["pass"]) && isset($_GET[Config::msgBoxCsrfName])) {
	if ($Controller->auth($_POST["user"], $_POST["pass"], $_GET[Config::msgBoxCsrfName])) {
		//Reload for removing POST data
		header("Location: " . $_SERVER['REQUEST_URI']);
	} else {
		echo view\View::loginFailed();
	}
	die();
}

//Check if user exists API
if (isset($_GET["user"])) {
	echo ($Controller->userExist($_GET["user"])) ? 'true' : 'false';
	die();
}

//Login page
if (!$Controller->verifyToken()) {
	AuthClass::set_csrf_token(Config::msgBoxCsrfName);
	echo view\View::authPage($Controller->getGroup());
	die();
}

//Sendmessage
if (isset($_GET["sendMessage"]) && isset($_GET[Config::msgBoxCsrfName])) {
	$Controller->sendMessage($_GET["sendMessage"], $_GET[Config::msgBoxCsrfName]);
	die();
}

//MessageOffset, Logout, getUserCount
if (isset($_GET["action"])) {
	echo $Controller->action($_GET["action"]);
	die();
}

//Get messages
if (isset($_GET["getMessage"]) && isset($_GET[Config::msgBoxCsrfName])) {
	echo $Controller->getMessage($_GET["getMessage"], $_GET[Config::msgBoxCsrfName]);
	die();
}

//User sended a image
if (isset($_FILES['image']['tmp_name']) && isset($_POST[Config::msgBoxCsrfName])) {
	$Controller->sendImage($_FILES['image']['tmp_name'], $_POST[Config::msgBoxCsrfName]);
	die();
}

//Load group chat. Set csrf token so every AJAX can be secured
AuthClass::set_csrf_token(Config::msgBoxCsrfName);
echo view\View::loadChat($Controller->getUser(), $Controller->getGroup());
