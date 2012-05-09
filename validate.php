<?php
include_once("funcs.php");

session_start();

$coderName=$_POST['coder_name'];
$password=$_POST['password'];

db_connect();

$valid_coder = validate_login($coderName,$password);

if($valid_coder) {
	// login in successful
	$_SESSION['coder_id']=$valid_coder['coder_id'];
	$_SESSION['coder_name']=$valid_coder['coder_name'];
	header("Location: ./index.php");
}
else {
	// login did not work
	session_unset();
	header("Location: ./login.php?error=true");
}