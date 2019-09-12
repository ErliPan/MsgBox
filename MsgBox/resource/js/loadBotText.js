document.addEventListener("keyup", function (event) {
	// Number 13 is the "Enter" key
	if (event.keyCode === 13) {
		sendMessage();
	}
});

loadKeyboard();
document.getElementById("imgBtn").remove();

var apiPage = "MsgBox.php" + window.location.search + "&";

function loadKeyboard() {
	var x = document.getElementById("keys");
	var y = document.getElementById("keyboard");
	y.innerHTML = x.innerHTML;
	x.remove();
}

function sendMessage() {
	var text = document.getElementById("msg").value;
	document.getElementById("msg").value = "";
	if (text.replace(/\s/g, '') == "") {
		return;
	} else {
		ajaxRequest("sendMessage=" + btoa(encodeURI(text)));
	}
}

function ajaxRequest(text) {
	var text = apiPage + text;
	//console.log(text);
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function () {
		if (this.readyState == 4 && this.status == 200) {

			document.getElementById("content").innerHTML += this.responseText;
			loadKeyboard();
			window.scrollTo(0, document.body.scrollHeight);
		}
	};
	xhttp.open("GET", text, true);
	xhttp.send();
}

function buttonPress(text) {
	document.getElementById("msg").value = text;
	sendMessage();
}