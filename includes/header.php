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

<header class="clearfix">
	<a id="upload">	<i class="icon-upload"></i> Upload </a>
	
	<div class="form_holder">
	<?php include(INC."upload-form.php"); ?>
	</div>
	


	<ul>
		<li><a href="#">My stuff</a></li>
		<li><a href="#">Browse</a></li>
		<li><a href="#">Meh</a></li>
	</ul>
</header>
<div id="content">