<?php
require_once("config.php");
if(isset($_POST['text']) && !empty($_POST['text']) && isset($_POST['author']) && !empty($_POST['author'])){
	if(!isset($USER_NAME)){
		echo json_encode( array("error" => 1, "errors" => ["you must authorize to publish twits"]));
		exit();
	}
	$text = mysql_escape_string($_POST['text']);
	if(strlen($text)>255){
		echo json_encode( array("error" => 1, "errors" => ["text is too long"]));
		exit();
	}
	$author = $_POST['author'];
	//if(!preg_match('/^[A-Z0-9]{10,32}=$/',$author)){
	if(!preg_match('/^[A-Z0-9]{1,31}$/',$author)){
		echo json_encode( array("error" => 1, "errors" => ["wrong author format"]));
		exit();
	}
	$sign="";
	if(isset($_POST['sign']) && !empty($_POST['sign'])){
		$sign = $_POST['sign'];
		if(!preg_match('/^[a-zA-Z0-9]{16,64}$/',$sign)){
			echo json_encode( array("error" => 1, "errors" => ["wrong sign format"]));
			exit();
		}
	}
	$ptime=time();
	if($result = $mysqli->query("INSERT INTO twits (author, text, sign, ptime) VALUES ('$author', '$text', '$sign', '$ptime');")!= false){
					//"Received";
					//echo json_encode( array("error" => 0, "errors" => []));
					echo json_encode(  array("error" => 0, "message" => "Twit added"));
				} else {
					echo json_encode( array("error" => 1, "errors" => ["Internal error", $mysqli->error] ));
				}
}else{
	if(isset($_GET['sign']) && !empty($_GET['sign'])){

		$sign = $_GET['sign'];
		if(!preg_match('/^[a-zA-Z0-9]{1,64}$/',$sign)){
			echo json_encode( array("error" => 1, "errors" => ["wrong sign format"]));
			exit();
		}
		
	$query = "SELECT tid, author, text, nonce FROM twits WHERE sign = '$sign' LIMIT 1";
	
	}elseif(isset($_GET['tid']) && !empty($_GET['tid'])){
		if(!is_numeric($_GET['tid'])){
			echo json_encode( array("error" => 1, "errors" => ["wrong tid format"]));
			exit();
		}
		$tid = $_GET['tid'];
		$query = "SELECT tid, author, text FROM twits WHERE tid = '$tid' LIMIT 1";
	}
	$result = $mysqli->query($query);
	if($result->num_rows!=0){
		$row = $result->fetch_assoc();
		echo json_encode( array("error" => 0, "text" => $row['text'], "author" => $row['author'], "nonce" => $row['nonce']));
		exit();
	} else {
		echo json_encode( array("error" => 1, "errors" => ["nothing"]));
		exit();
	}
}
?>