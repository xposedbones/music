<?php 
	define('INC',$_SERVER['DOCUMENT_ROOT'].'/includes/');
	define('LIBS',$_SERVER['DOCUMENT_ROOT'].'/libs/');
	define('VIEWS',$_SERVER['DOCUMENT_ROOT'].'/views/');

	DEFINE("sBDD", "music");		//à remplir avec le nom de la base de données
	DEFINE("sHOST", "localhost");	// hostname
	DEFINE("sUSER", "root");		// username
	DEFINE("sPWD", "");

	include(LIBS.'functions.php');

	global $autoload;
	if($autoload==null || $autoload==true)
		include_once("autoload.php");

	$bd->connect();

	include_once("lang.php");

?>