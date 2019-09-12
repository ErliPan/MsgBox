document.addEventListener("keyup", function (event) {
	// Number 13 is the "Enter" key
	if (event.keyCode === 13) {
		sendMessage();
	}
});

//Load ASAP but first need to wait a bit for some reason
setTimeout(loop, 50);
//Then update every tot hundred milliseconds
//Not using socket but using repetitive get requests. Not good but it works
setInterval(loop, 700);

var updatedMsgOffset = 0;
var currentMsgOffset = 0;
var maxMsgToLoadAtStart = 100;
var apiPage = "MsgBox.php" + window.location.search + "&";
var loadingMessage = false;

function loop() {
	updateUserCount();
	updateOffset();
	updateImgUrl();
	loadMessage();
}

function loadMessage() {
	if (currentMsgOffset >= updatedMsgOffset) {
		if (currentMsgOffset > updatedMsgOffset) {
			currentMsgOffset = updatedMsgOffset; //Some rare bug i can't find may require this
		}
		return;
	} else if (updatedMsgOffset - maxMsgToLoadAtStart > currentMsgOffset) {
		currentMsgOffset = updatedMsgOffset - maxMsgToLoadAtStart;
	}
	if (loadingMessage == true) {
		console.log("return");
		return;
	}
	loadingMessage = true;
	var getUrl = apiPage + "getMessage=" + currentMsgOffset;

	ajax(function () {
		if (this.readyState == 4 && this.status == 200) {
			if (this.responseText == "<span style='display:none'>CSRF_mismatch</span>") {
				location.reload();
			}
			document.getElementById("content").innerHTML += this.responseText;
			currentMsgOffset += parseInt((this.responseText.match(/col-12 nickname/g) || []).length);
			//wait to load before going to bottom
			loadingMessage = false;
			//if a message was sent, wait until the message is updated before enabling the field
			document.getElementById("msg").disabled = false;
			document.getElementById("msg").value = "";
			setTimeout(goToBottom, 300);
		}
	}, getUrl);
}

function sendMessage() {
	if (document.getElementById("fileChooser").value != "") {
		document.getElementById("csrf").value = getCookie("msgBox_csrf");
		document.getElementById("sendBtn").click();
	} else {
		var text = document.getElementById("msg").value;
		if (text.replace(/\s/g, '') != "") {
			document.getElementById("msg").disabled = true;
			var getUrl = apiPage + "sendMessage=" + btoa(encodeURI(text));
			ajax(function () {
				loadMessage();
			}, getUrl);
		}
	}
}

function updateOffset() {
	var getUrl = apiPage + "action=getOffset";
	ajax(function () {
		if (this.readyState == 4 && this.status == 200) {
			updatedMsgOffset = parseInt(this.responseText);
		}
	}, getUrl);
}

function updateUserCount() {
	var getUrl = apiPage + "action=getUserCount";
	ajax(function () {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("userCount").innerHTML = this.responseText;
		}
	}, getUrl);
}

function ajax(funcName, url) {
	url += "&msgBox_csrf=" + getCookie("msgBox_csrf")
	//console.log(url); // #TODO remove
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = funcName;
	xhttp.open("GET", url, true);
	xhttp.send();
}

function goToBottom() {
	window.scrollTo(0, document.body.scrollHeight);
}

function logout() {
	window.location.replace(apiPage + "action=logout");
}

function updateImgUrl() {
	//If some image is choosed then put the image path on msg textfield
	if (document.getElementById("fileChooser").value != "") {
		document.getElementById("msg").value = document.getElementById("fileChooser").value;
	}
}

function getCookie(name) {
	var value = "; " + document.cookie;
	var parts = value.split("; " + name + "=");
	if (parts.length == 2) return parts.pop().split(";").shift();
}