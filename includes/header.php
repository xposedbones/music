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
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,400,300,700,600' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<<<<<<< HEAD
<header>
	<div><a href="#" class="icon-upload">Upload </a>
	<?php include(INC."upload-form.php"); ?>
	</div>
=======
<header class="clearfix">
	<a href="#"id="upload"><span class="icon-upload"></span>Upload</a>
>>>>>>> 7ca13b4299da162db35388538a182d73fef05340
	<ul>
		<li><a href="#">My stuff</a></li>
		<li><a href="#">Browse</a></li>
		<li><a href="#">Meh</a></li>
	</ul>
</header>
<div id="content">