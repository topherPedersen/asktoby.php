<?php
    $queryText = $_POST['queryText'];
	$queryTextArray = explode(" ", $queryText);
	$numberOfKeywords = count($queryTextArray);
	
	$MySQL = new mysqli('localhost', 'USERNAMEGOESHERE', 'PASSWORDGOESHERE', 'DATABASENAMEGOESHERE');
	for ($i = 0; $i < $numberOfKeywords; $i++) {
	    $SQL = "SELECT domain FROM keywordTable WHERE (keywordOne = '{$queryTextArray[$i]}' OR 
		                                               keywordTwo = '{$queryTextArray[$i]}' OR
													   keywordThree = '{$queryTextArray[$i]}' OR
													   keywordFour = '{$queryTextArray[$i]}' OR
													   keywordFive = '{$queryTextArray[$i]}' OR
													   keywordSix = '{$queryTextArray[$i]}' OR
													   keywordSeven = '{$queryTextArray[$i]}' OR
													   keywordEight = '{$queryTextArray[$i]}' OR
													   keywordNine = '{$queryTextArray[$i]}' OR
													   keywordTen = '{$queryTextArray[$i]}' OR
													   keywordEleven = '{$queryTextArray[$i]}' OR
													   keywordTwelve = '{$queryTextArray[$i]}' OR
													   keywordThirteen = '{$queryTextArray[$i]}');";
	    $output = $MySQL->query($SQL);
		$j = 0;
	    while ($record = $output->fetch_assoc()){
            $keywordMatch[$i][$j] = $record['domain'];
			$j++;
        }
	}
	$MySQL->close();
	
	$k = count($keywordMatch);
	
	echo "<h1>Ask Toby!</h1>";
	echo "<h2>Search Results:</h2>";
	
	for ($l = 0; $l < $k; $l++) {
	    $m = count($keywordMatch[$l]);
		for ($n = 0; $n < $m; $n++) {
	        echo "<a href=\"http://www.{$keywordMatch[$l][$n]}\">{$keywordMatch[$l][$n]}</a><br>";
		}
	}
	

?>