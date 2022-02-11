<?php
require_once("config.php");
$per_page = 25;
$page = 0;
if(!empty($_GET['page']) && $page > 0){
	$page = intval(@$_GET['page']) - 1;
}
if(!empty($_GET['per_page']) && $per_page > 0 && $per_page < 50){
	$per_page = intval(@$_GET['per_page']);
}
usleep(1600000);
$limit = $per_page;
$offset = $per_page * $page;
$query = "SELECT tid, author, text, sign, ptime, nonce FROM twits ORDER by tid DESC LIMIT $limit OFFSET $offset;";
$result = $mysqli->query($query);


$twits = array();
if($result->num_rows!=0){
	while($row = $result->fetch_assoc()){
		$twits[] = [
			"tid" => $row['tid'],
			"text" => $row['text'],
			"ptime" => $row['ptime'],
			"author" => $row['author'],
			"approved" => (!empty($row['nonce']))
			];
	}
}
echo json_encode($twits);
?>
