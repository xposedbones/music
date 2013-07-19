<?php 
if(!class_exists("Bd")){
	include('config.php');
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<link rel="stylesheet" href="css/styles.css">
	
</head>
<body>
<header>
	<div><a href="#" class="icon-upload">Upload </a>
	<?php include(INC."upload-form.php"); ?>
	</div>
	<ul>
		<li><a href="#">My stuff</a></li>
		<li><a href="#">Browse</a></li>
		<li><a href="#">Meh</a></li>
	</ul>
</header>
<div id="content">