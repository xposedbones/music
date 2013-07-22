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
	<div class="span5 fileupload-progress fade">
        <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
            <div class="bar" style="width:0%;"></div>
        </div>
    </div>
	<div class="form_holder">
	<?php include(INC."upload-form.php"); ?>
	</div>
	


	<ul id="menu">
		<li><a href="test.php">My stuff</a></li>
		<li><a href="albums-listing.php">Browse</a></li>
		<li><a href="testapi.php">Test API</a></li>
	</ul>
</header>
<div id="content">