<?php
//error_reporting(0);
$mysql_host	= "omctf-mysql";
$mysql_user	= "feed";
$mysql_pass	= "1";
$mysql_db 	= "feed";

$mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
if($mysqli->connect_errno){
	printf("Unable to connect to Database: (%d) %s", $mysqli->connect_errno, $mysqli->connect_error);
	exit();
}

$mysqli->set_charset("utf8");

$PREFERENCES=array();
$result = $mysqli->query("SELECT key, value FROM preferences'");
if(($result) && $result->num_rows!=0){
	while($row = $result->fetch_assoc()){
		$PREFERENCES[$row['key']] = $row['value'];
	}
}
if(isset($_COOKIE['token']) && !empty($_COOKIE['token'])){
	$result = $mysqli->query(sprintf("SELECT uid FROM tokens WHERE token = '%s' LIMIT 1;", $_COOKIE['token']));
	if($result->num_rows!=0){
		$row = $result->fetch_assoc();
		$USER_UID = $row['uid'];
		if(!$mysqli->query(sprintf("UPDATE tokens SET time = %d WHERE token = '%s';", time(), $_COOKIE['token']))){
				echo $mysqli->error;
		}
		
		$result = $mysqli->query(sprintf("SELECT login, role FROM users WHERE uid='%s' LIMIT 1;",intval($USER_UID)));
		if($result->num_rows!=0){
			$row = $result->fetch_assoc();
			$USER_NAME = $row['login'];

		}
	}
}
?>
