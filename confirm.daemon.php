<?php

require_once "sha3.php";
include_once 'TotalEnumeration.php';


$mysql_host     = "localhost";
$mysql_user     = "feed";
$mysql_pass     = "1";
$mysql_db       = "feed";

$log_path       = "/var/log/confirm.daemon.php.log";
function displayUsage(){
    global $log_path;
    echo "\n";
    echo "Process for demonstrating a PHP daemon.\n";
    echo "\n";
    echo "Usage:\n";
    echo "\tDaemon.php [options]\n";
    echo "\n";
    echo "toptions:\n";
    echo "\t\t--help display this help message\n";
    echo "\t\t--log=<filename> The location of the log file (default '$log_path')\n";
    echo "\n";
        echo str_hex2bin("000000000000f678", 16, 2);
        echo "\n";
}


function str_hex2bin($input){
        $chuncks = str_split($input,8);
        $output = "";
        foreach($chuncks as $chunck){
                $output .= str_pad(base_convert($chunck, 16, 2), 32, '0',STR_PAD_LEFT);
        }
        return $output;
}


function generate_flag($length = 31) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString.'=';
}


function iteration(){
        global $mysqli;
        global $diff;
		global $log_path;

        //$mysql_result = $mysqli->query("SELECT author, text, sign FROM twits WHERE author IS NOT NULL AND author LIKE '____________________________' AND sign IS NOT NULL LIMIT 1 OFFSET 1;");
        //$mysql_result = $mysqli->query("SELECT tid, author, text, sign FROM twits WHERE author IS NOT NULL AND sign IS NOT NULL AND nonce IS NULL LIMIT 1 OFFSET 1;");
        $mysql_result = $mysqli->query("SELECT tid, author, text, sign FROM twits WHERE author IS NOT NULL AND sign IS NOT NULL AND nonce IS NULL AND tid>'26098' LIMIT 1;");//AND tid='26055'
		//echo "SELECT tid, author, text, sign FROM twits WHERE author IS NOT NULL AND sign IS NOT NULL LIMIT 1 OFFSET 1;\n";
		if(!$mysql_result) {
			echo $mysqli->error;
			return;
		}
		if($mysql_result->num_rows==0){
			print("No official newz\n");
			exit();
		}
        $twit = $mysql_result->fetch_assoc();
        $solution = 0;
        $start_time = time();
        $end_time = 0;
		$tid = $twit['tid'];
		$author = $twit['author'];
		$sign = $twit['sign'];
		$diff = 31-strlen($author);
		var_dump($twit);
		
	
		printf("\nComplexity %d. Sign: %s. Starting RKN fact approving\n", $diff, $sign);
		$end_time = 0;
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$max = pow(strlen($characters), $diff) - 1;
		$nonce = "";
		//echo $nonce.$author.$twit['text'];
		//echo "\n";
		//$hash = sha3($nonce.$author.$twit['text'], 256);
		//echo "HASH: $hash \n";
		//var_dump($hash);
		//echo $max;
		//if($hash === $twit['sign']){
		//			$solution = $nonce;
        //                $end_time = time();
		//}
		$combo = [];
		for($i=0; $i<$diff; $i++){
			$combo[$i]=str_split($characters);
		}

		$enumgen = new TotalEnumeration($combo);
		$i=0; $step = $max / 10;
		foreach ($enumgen->generate() as $value) {
			//echo $nonce.$author.$twit['text'];
			//echo "\n";
			$nonce = implode("",$value);
			
			if(($i%$step)==0){
				//echo "Step ". round($i/$step).": $nonce \n";
				echo round($i/$step).": $nonce  \t";
			}
			
			//$hash = sha3($nonce.$author.$twit['text'],256);
			//$hash = sha1($nonce.$author.$twit['text'],256);
			$hash = md5($nonce.$author.$twit['text']);
			//printf("Nonce: %s -> Hash: %s \n", $nonce, $hash);
			//echo "---> $hash \n";
                //$hash = hash("sha256",$twit['text'].nonce_gen($nonce));
                if($hash === $twit['sign']){
                        $solution = $nonce;
                        $end_time = time();
                        break;
                }
			$i++;
		}
		
		print("\n");
        /*for($i = 0; $i < $max; $i++){
                //$hash = Sha3::hash($twit['text'].base64_encode($nonce), 256);
				$nonce = $characters[$i];
				
        }*/
		
        if($solution==''){
                $str = sprintf("Complexity %d. No solution was found. Time: %d sec\n", $diff, $end_time-$start_time);
        } else {
			//printf("\nComplexity %d. Solution nonce is %s. Hash: %s. Time: %d sec\n", $diff, $solution, Sha3::hash($twit['text'].base64_encode($solution), 256), $end_time-$start_time);
			$str = sprintf("Complexity %d. Solution nonce is %s. Hash: %s. Time: %d sec\n", $diff, $solution, sha3($twit['text'].base64_encode($nonce), 256), $end_time-$start_time);
			
			$result = $mysqli->query("UPDATE twits SET nonce = '$nonce', ptime = '$end_time' WHERE tid = '$tid'");
			if(!$result){
				echo $mysqli->error;
			}
			//printf("Finded solution with nonce %d is %s\n",  $solution, hash("sha256",$twit['text'].nonce_gen($nonce)));
		}
		print($str);
		file_put_contents($log_path, time().$str, FILE_APPEND);
}
//configure command line arguments
$SINGLE_ITERATION = false;
if($argc > 0){
    foreach($argv as $arg){
        $args = explode('=',$arg);
        switch($args[0]){
            case '--diff':
                $diff = intval($args[1]);
                break;
            case '--help':
                return displayUsage();
                break;
            case '--log':
                $log_path = $args[1];
                break;
            case '--iteration':
				//if(!isset($diff))
				//$diff = 12;
				if(!isset($hash_algo))
					$hash_algo = "sha3-256";
					$SINGLE_ITERATION=true;
                break;
            case '--env':
                echo print_r($GLOBALS,1);
                break;
        }
    }
}
//echo sha3("", 256);
//fork the process to work in a daemonized environment
file_put_contents($log_path, "Status: starting up.\n", FILE_APPEND);
$max=1;
$mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
if($mysqli->connect_errno){
	printf("Unable to connect to DB: (%d) %s", $mysqli->connect_errno, $mysqli->connect_error);
	//exit();
}
$mysqli->set_charset("utf8");
//file_put_contents($log_path, print_r($GLOBALS, 1), FILE_APPEND);
//file_put_contents($log_path, Sha3::hash('',256), FILE_APPEND);
//file_put_contents($log_path, $mysqli->host_info, FILE_APPEND);

for($i=0;$i<$max;$i++){
	iteration();
}
$mysqli->close();
?>
