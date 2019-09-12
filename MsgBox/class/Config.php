<?php

/**
 * Config file that contain the most used variables
 */
class Config
{

	//DB login info
	public const nameDB = "db";
	public const user = "user";
	public const pass = "";

	//Admin passoword
	public const adminUser = "erli";
	//Sha1 is not secure anymore, this uses SHA256 default "admin"
	public const adminPass = "8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918";

	//How many messages to trasmit per requests
	public const maxMessagePerRequest = 50;

	public const tokenHTTPS = false;
	public const imgDIR = 'userContent/';
	public const resourceDIR = "resource";
	public const msgBoxCsrfName = "msgBox_csrf";

	public const tokenLastTime = 2; //Hours

	//public const serverURL = "https://erlipan.dev";
	public const serverURL = "http://10.0.0.107";
}
