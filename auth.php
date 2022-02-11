<?php
require_once("config.php");
if(isset($_POST['login']) && isset($_POST['pass'])){
	if(!preg_match('/^[a-zA-Z0-9_-]{1,32}$/', $_POST['login'])){
		echo json_encode( array("error" => 1, "message" => "only latin letter, digits, - and _ are allowed in login. Length in range between 1 and 32 inclusively"));
		exit();
	}
	if(!preg_match('/^[a-zA-Z0-9_-]{1,32}$/', $_POST['pass'])){
		echo json_encode( array("error" => 1, "message" => "only latin letter, digits, - and _ are allowed in pass. Length in range between 1 and 32 inclusively"));
		exit();
	}
	$LOGIN = $_POST['login'];
	$PASS = $_POST['pass'];
}
switch($_GET['action']){
	case "issign":
		if(!preg_match('/^[a-zA-Z0-9_-]{1,32}$/', $_POST['username'])){
			echo json_encode( array("error" => 1, "message" => "Wrong username format"));
			exit();
		}
		$username=$_POST['username'];
		if(($token=$_POST['token'])==$_COOKIE['token']){
			
			$result = $mysqli->query("SELECT login FROM users, tokens WHERE users.uid = tokens.uid AND tokens.token = '$token' AND users.login = '$username' LIMIT 1;");
			//echo "SELECT login FROM users, tokens WHERE users.uid = tokens.uid AND tokens.token = '$token' AND users.login = '$username' LIMIT 1;";
			if($result->num_rows==0){
					echo json_encode( array("error" => 1, "message" => "Invalid token"));
					exit();
			} else {
				$row = $result->fetch_assoc();
				echo json_encode( array("error" => 0, "username" => $row['login']));
				exit();
			}
		} else {
			echo json_encode( array("error" => 1, "message" => "token hack attemption"));
			exit();
		}
	break;
	
	case "signup":
		if(!isset($LOGIN)){
			echo json_encode( array("error" => 1, "message" => "No login"));
		} else {
			
			$result = $mysqli->query("SELECT 1 FROM users WHERE login = '$LOGIN' LIMIT 1;");
			if($result->num_rows!=0){
				echo json_encode( array("error" => 1, "message" => "We have user with such login"));
				exit();
			}
			if($result = $mysqli->query("INSERT INTO users (login, pass, role) VALUES ('$LOGIN', MD5('$PASS'), '0');") != false){
				echo json_encode( array("error" => 0, "message" => "You've completely registered"));
				exit();
			} else {
				http_response_code(500);
				exit();
			}
		}
		
	break;
	case "signin":
		if(!isset($LOGIN)){
			echo json_encode( array("error" => 1, "message" => "No login"));
			exit();
		} else {
			$result = $mysqli->query("SELECT uid, role FROM users WHERE login = '$LOGIN' AND pass = MD5('$PASS') LIMIT 1;");
			if($result->num_rows!=0){
				$token = md5(time().$LOGIN.$USER);
				$time = time();
				$row = $result->fetch_assoc();
				$uid = $row['uid'];
				if($result = $mysqli->query("INSERT INTO tokens (token, uid, time) VALUES ('$token', $uid, '$time');")!= false){
					setcookie('token', $token, time()+3600);
					echo json_encode( array("error" => 0, "message" => "You've completely entered", "token" => $token, "username" => $LOGIN));
					exit();
				} else {
					echo json_encode( array("error" => 1, "message" => "error creating token: ".$mysqli->error));
					exit();
				}
			} else {
				echo json_encode( array("error" => 1, "message" => "no such user"));
				exit();
			}
		}
	break;
	case "logout":
		if(!isset($_COOKIE['token'])) {
			echo json_encode( array("error" => 0, "message" => "No cookie. BTW your token is deactivated"));
			exit();
		}
			if(preg_match('/^[a-zA-Z0-9]{32}$/', $_COOKIE['token'])){
				$token = $_COOKIE['token'];
				$result = $mysqli->query("DELETE FROM `tokens` WHERE `token` = '$token';");
			}
			unset($_COOKIE['token']);
			setcookie('token', null, -1, '/');
			echo json_encode( array("error" => 0, "message" => "completely log off"));
		break;
	default:
		echo json_encode( array("error" => 1, "message" => "No action"));
		exit();
	break;
}
?>
