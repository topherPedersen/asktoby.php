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

	
	
	$MySQL = new mysqli('localhost', 'ADMIN', 'PASSWORD', 'DATABASE');
	$queryText = $_POST['queryText'];
	$queryTextClean = preg_replace("/[^a-zA-Z0-9\s]/", "", $queryText);
	$queryTextArray = explode(" ", $queryTextClean);
	$numberOfKeywords = count($queryTextArray);
	$alphabet = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
	
	for ($xyz = 0; $xyz <= 25; $xyz++) {
	    for ($i = 0; $i < $numberOfKeywords; $i++) {
	        if ($queryTextArray[$i] == "") {
	            continue;
	        }
	        $SQL = "SELECT domain FROM " . $alphabet[$xyz] . "KeywordTable WHERE (keywordOne = '{$queryTextArray[$i]}' OR keywordTwo = '{$queryTextArray[$i]}' OR keywordThree = '{$queryTextArray[$i]}' OR keywordFour = '{$queryTextArray[$i]}' OR keywordFive = '{$queryTextArray[$i]}' OR keywordSix = '{$queryTextArray[$i]}' OR keywordSeven = '{$queryTextArray[$i]}' OR keywordEight = '{$queryTextArray[$i]}' OR keywordNine = '{$queryTextArray[$i]}' OR keywordTen = '{$queryTextArray[$i]}' OR keywordEleven = '{$queryTextArray[$i]}' OR keywordTwelve = '{$queryTextArray[$i]}' OR keywordThirteen = '{$queryTextArray[$i]}');";
	        $output = $MySQL->query($SQL);
			if ($output == false) { // THIS IF STATEMENT FIXES A BUG 
			    // echo "<p style=\"color: white;\">{$alphabet[$xyz]} is false</p>";
			    continue;
			}
		    $j = 0;
	        while ($record = $output->fetch_assoc()){
                $keywordMatch[$xyz][$i][$j] = $record['domain'];
			    $j++;
            }
	    }
	}
	$MySQL->close();
	
	
	
	echo "<center><a href=\"index.html\"><h2>Ask Toby!</h2></a></center>";
	echo "<div id=\"searchResultsDiv\">";
	
	for ($def = 0; $def <= 25; $def++) {
	    for ($ghi = 0; $ghi < $numberOfKeywords; $ghi++) {
		    for ($jkl = 0; $jkl < count($keywordMatch[$def][$ghi]); $jkl++) {
			    echo "<center><a class=\"searchResults\" href=\"http://www.{$keywordMatch[$def][$ghi][$jkl]}\">{$keywordMatch[$def][$ghi][$jkl]}</a></center><br>";
			}
		}
	}
	/*
	$k = count($keywordMatch);
	for ($l = 0; $l < $k; $l++) {
	    $m = count($keywordMatch[$l]);
		for ($n = 0; $n < $m; $n++) {
	        echo "<center><a class=\"searchResults\" href=\"http://www.{$keywordMatch[$l][$n]}\">{$keywordMatch[$l][$n]}</a></center><br>";
		}
	}
	*/
	
    echo "</div>";
	
?>
</body>
</html>
