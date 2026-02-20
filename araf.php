<?php
$json = [];
	$json['strt']= "Data written successfully.";
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$rData=[];
		$rData[] = isset($_POST['tvsid'])?$_POST['tvsid']:' ';
		$rData[] = isset($_POST['tvid'])?$_POST['tvid']:' ';
		$rData[] = isset($_POST['cid'])?$_POST['cid']:' ';
		$rData[] = isset($_POST['quest'])?$_POST['quest']:' ';
		$rData[] = isset($_POST['ans'])?$_POST['ans']:' ';
		$rData[] = isset($_POST['tm'])?$_POST['tm']:' ';
				
		$filename = '/home/u7092310/trivia2.bugunnelerizledim.com/sess/araf.txt';
		$data = implode(',',$rData ).','. date("Y-m-d H:i:s") .PHP_EOL;// '\n';
		$maxRetries = 5;
		$retryDelay = 100000;
		$retryCount = 0;
		$success = false;

		while ($retryCount < $maxRetries && !$success) {
		    // Open the file for writing
		    $file = fopen($filename, 'a');

		    if ($file) {
		        // Try to lock the file
		        if (flock($file, LOCK_EX)) {
		            // Write the data to the file
		            fwrite($file, $data);
		            
		            // Unlock the file
		            flock($file, LOCK_UN);
		            
		            // Close the file
		            fclose($file);
		            
		            $success = true;
		        } else {
		            // Failed to lock the file, close it
		            fclose($file);
		            
		            // Wait for a short period before retrying
		            usleep($retryDelay);
		        }
		    } else {
		        echo "Could not open the file!";
		        break;
		    }

		    $retryCount++;
		}

		if (!$success) {
		    $json['success']= "Failed to write to the file after $maxRetries attempts.";
		} else {
		    $json['err']= "Data written successfully.";
		}
	}
	
	header('Content-type: application/json');
	echo json_encode($json); 
?>
