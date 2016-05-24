<!DOCTYPE html>
<html>
<head>
<style>
@font-face {
	    font-family: klavika;
		src: url(klavika.otf);
	}
    h2 {
        font-family: klavika;
        color: white;
        font-size: 9em;
    }
    body { 
        background-image: url('space.jpg');
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-position: center; 
		background-size: 100%;
    }
    .searchResults {
        font-family: klavika;
        color: white;
        font-size: 3em;
    }
    a {
        text-decoration: none;
    }
    #searchResultsDiv {
        margin-top: -7.5%;
    }
</style>
</head>
<body>
<?php
    $queryText = $_POST['queryText'];
	$queryTextArray = explode(" ", $queryText);
	$numberOfKeywords = count($queryTextArray);
	
	$MySQL = new mysqli('localhost', 'mdotchri_dba', 'Milf15milf', 'mdotchri_asktobyDatabase');
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
	
	echo "<center><a href=\"asktoby.html\"><h2>Ask Toby!</h2></a></center>";
	echo "<div id=\"searchResultsDiv\">";
	for ($l = 0; $l < $k; $l++) {
	    $m = count($keywordMatch[$l]);
		for ($n = 0; $n < $m; $n++) {
	        echo "<center><a class=\"searchResults\" href=\"http://www.{$keywordMatch[$l][$n]}\">{$keywordMatch[$l][$n]}</a></center><br>";
		}
	}
        echo "</div>";
	

?>
</body>
</html>
