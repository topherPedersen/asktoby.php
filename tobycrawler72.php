<?php 
$startTime = time();
$MySQL = new mysqli('localhost', 'ADMIN', 'PASSWORD', 'DATABASE');

function curlMultipleUrls($urls, $options) {
    $ch = array();
    $results = array();
    $mh = curl_multi_init();
    foreach($urls as $key => $val) {
        $ch[$key] = curl_init();
        if ($options) {
            curl_setopt_array($ch[$key], $options);
        }
        curl_setopt($ch[$key], CURLOPT_URL, $val);
        curl_multi_add_handle($mh, $ch[$key]);
    }
    $running = null;
    do {
        curl_multi_exec($mh, $running);
    } while ($running > 0);
    // Get content and remove handles.
    foreach ($ch as $key => $val) {
        $results[$key] = curl_multi_getcontent($val);
        curl_multi_remove_handle($mh, $val);
    }
    curl_multi_close($mh);
    return $results;
}

$alphabet = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");

// Retrieve the id # of the next domain to scrape for each table: aLocationTable - zLocationTable.
// This works by finding the highest index number of each table: aLocationTable - zLocationTable.
for ($i = 0; $i <= 25; $i++) {
    $SQL = "SELECT * FROM " . $alphabet[$i] . "LocationTable;";
    $output = $MySQL->query($SQL);
    while ($record = $output->fetch_assoc()){
        $lastIdScraped[$i] = $record['lastIdScraped'];
		$scrapeNextId[$i] = $lastIdScraped[$i] + 1;
    }
	$SQL = "UPDATE " . $alphabet[$i] . "LocationTable SET lastIdScraped = \"{$scrapeNextId[$i]}\";";
	$MySQL->query($SQL);
}

// Retrieve the domain name to be scraped from each table: aLocationTable - zLocationTable
for ($i = 0; $i <= 25; $i++) {
    $SQL = "SELECT domain FROM " . $alphabet[$i] . "DomainTable WHERE id = {$scrapeNextId[$i]};";
    $output = $MySQL->query($SQL);
    while ($record = $output->fetch_assoc()){
	    $domain[$i] = $record['domain'];
    }
	//echo "{$domain[$i]} <br>";
}

// Create a multidimensional array called $urlStr[]. We will need to curl multiple different versions of
// urls to fetch the correct address. For example, we will not curl the $domain[6], 'google.com',
// instead we will curl http://google.com, https://google.com, http://www.google.com, https://www.google.com.
// Often times curl requires a very accurate url, whereas your browser does not. Hope this explanation helps!
for ($i = 0; $i <= 25; $i++) {
    $urlStr[$i][0] = "http://" . $domain[$i];
	$urlStr[$i][1] = "https://" . $domain[$i];
	$urlStr[$i][2] = "http://www." . $domain[$i];
	$urlStr[$i][3] = "https://www." . $domain[$i];
	//echo "{$urlStr[$i][0]} <br>";
	//echo "{$urlStr[$i][1]} <br>";
	//echo "{$urlStr[$i][2]} <br>";
	//echo "{$urlStr[$i][3]} <br>";
}

for ($i = 0; $i <= 25; $i++) {
    $httpUrlStr[$i] = $urlStr[$i][0];
	$httpsUrlStr[$i] = $urlStr[$i][1];
	$httpWwwUrlStr[$i] = $urlStr[$i][2];
	$httpsWwwUrlStr[$i] = $urlStr[$i][3];
}

// $httpData
// $httpsData
// $httpWwwData
// $httpsWwwData

$httpData = curlMultipleUrls($httpUrlStr, $options = array(CURLOPT_TIMEOUT => 15, CURLOPT_RETURNTRANSFER => true));
$httpsData = curlMultipleUrls($httpsUrlStr, $options = array(CURLOPT_TIMEOUT => 15, CURLOPT_RETURNTRANSFER => true));
$httpWwwData = curlMultipleUrls($httpWwwUrlStr, $options = array(CURLOPT_TIMEOUT => 15, CURLOPT_RETURNTRANSFER => true));
$httpsWwwData = curlMultipleUrls($httpsWwwUrlStr, $options = array(CURLOPT_TIMEOUT => 15, CURLOPT_RETURNTRANSFER => true));
	
	//echo "{$httpsWwwData[0]}";
/*
for ($i = 0; $i <= 25; $i++) {
    echo "\$httpData[\$i] = " . strlen($httpData[$i]) . "<br>";
	echo "\$httpsData[\$i] = " . strlen($httpsData[$i]) . "<br>";
	echo "\$httpWwwData[\$i] = " . strlen($httpWwwData[$i]) . "<br>";
	echo "\$httpsWwwData[\$i] = " . strlen($httpsWwwData[$i]) . "<br>";
}
*/

// $bestData

for ($i = 0; $i <= 25; $i++) {
    // Bubble Sort the Scraped Data
	// The Longest String of Data Scraped Will Sink Down To Variable $d
	// The Longest String of Data Should be the Data (HTML) That we Want
    $a = strlen($httpData[$i]);
	$b = strlen($httpsData[$i]);
	$c = strlen($httpWwwData[$i]);
	$d = strlen($httpsWwwData[$i]);
	$keepSorting = true;
	while ($keepSorting == true) {
	    $keepSorting = false;
		if ($a > $b) {
		    $keepSorting = true;
			$placeholder = $b;
			$b = $a;
			$a = $placeholder;
		}
		if ($b > $c) {
		    $keepSorting = true;
			$placeholder = $c;
			$c = $b;
			$b = $placeholder;
		}
		if ($c > $d) {
		    $keepSorting = true;
			$placeholder = $d;
			$d = $c;
			$c = $placeholder;
		}
	}
	// The Switch Statement Below Assigns the HTML Data That we Want to the Array: $bestData
	// The Data that We Do Not Want is Likely HTML That Reads Something Like: 404 Not Found, etc. etc.
	switch ($d) {
	    case strlen($httpData[$i]):
		    $bestData[$i] = $httpData[$i];
			break;
		case strlen($httpsData[$i]):
		    $bestData[$i] = $httpsData[$i];
			break;
		case strlen($httpWwwData[$i]):
		    $bestData[$i] = $httpWwwData[$i];
			break;
		case strlen($httpsWwwData[$i]):
		    $bestData[$i] = $httpsWwwData[$i];
			break;
	}
}

//echo "{$bestData[1]}";

for ($i = 0; $i <= 25; $i++) {
    preg_match('/<title>(.*)<\/title>/i', $bestData[$i], $title);
    $bestTitles[$i] = $title[1];
	$bestTitles[$i] = preg_replace("/[^a-zA-Z0-9\s]/", " ", $bestTitles[$i]); // Strip Special Characters
}
		
for ($i = 0; $i <= 25; $i++) {
    preg_match_all('/([a-zA-Z0-9\-]*?)(\.com|\.net|\.org|\.gov)/', $bestData[$i], $hyperlink);
    for ($j = 0; $j < count($hyperlink[0]); $j++) {
        $bestLinks[$i][$j] = $hyperlink[0][$j];
    }
}

// make all domains lowercase
for ($i = 0; $i <= 25; $i++) {
    for ($j = 0; $j < count($bestLinks[$i]); $j++) {
	    $bestLinks[$i][$j] = strtolower($bestLinks[$i][$j]);
	}
}

for ($i = 0; $i <= 25; $i++) {
	for ($j = 1; $j < count($bestLinks[$i]); $j++) {
	    for ($k = 0; $k < $j ; $k++) {
		    if ($bestLinks[$i][$k] == $bestLinks[$i][$j]) {
			    $bestLinks[$i][$j] = "DUPLICATE";
			}
		}
	}
}

/*
for ($i = 0; $i <= 25; $i++) {
    echo "<h2>{$bestTitles[$i]}</h2>";
	for ($j = 0; $j < count($bestLinks[$i]); $j++) {
	    echo "<p>{$bestLinks[$i][$j]}</p>";
	}
}
*/

for ($i = 0; $i <= 25; $i++) {
    $SQL = "UPDATE " . $alphabet[$i] . "DomainTable SET scraped=\"true\" WHERE domain = \"{$domain[$i]}\";";
	$MySQL->query($SQL);
	$keywordArray = explode(" ", $bestTitles[$i]);
	if ($bestTitles[$i] == false) {
	    continue;
	}
	if ($bestTitles[$i][0] == "2" && $bestTitles[$i][1] == "F") {
	    continue;
	}
	if ($keywordArray[0] == "301") {
	    continue;
	}
	if ($keywordArray[0] == "302") {
	    continue;
	}
	if ($keywordArray[0] == "400") {
	    continue;
	}
	if ($keywordArray[0] == "403") {
	    continue;
	}
	if ($keywordArray[0] == "404") {
	    continue;
	}
	if ($keywordArray[0] == "Not" && $keywordArray[1] == "Found") {
	    continue;
	}
	$SQL = "UPDATE " . $alphabet[$i] . "DomainTable SET valid=\"true\" WHERE domain = \"{$domain[$i]}\";";
	$MySQL->query($SQL);
	$switchLetter = strtolower($domain[$i][0]);
	switch ($switchLetter) {
	    case "a":
		    $SQL = "INSERT INTO " . "aKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "b":
		    $SQL = "INSERT INTO " . "bKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "c":
		    $SQL = "INSERT INTO " . "cKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "d":
		    $SQL = "INSERT INTO " . "dKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "e":
		    $SQL = "INSERT INTO " . "eKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "f":
		    $SQL = "INSERT INTO " . "fKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "g":
		    $SQL = "INSERT INTO " . "gKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "h":
		    $SQL = "INSERT INTO " . "hKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "i":
		    $SQL = "INSERT INTO " . "iKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "j":
		    $SQL = "INSERT INTO " . "jKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "k":
		    $SQL = "INSERT INTO " . "kKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "l":
		    $SQL = "INSERT INTO " . "lKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "m":
		    $SQL = "INSERT INTO " . "mKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "n":
		    $SQL = "INSERT INTO " . "nKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "o":
		    $SQL = "INSERT INTO " . "oKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "p":
		    $SQL = "INSERT INTO " . "pKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "q":
		    $SQL = "INSERT INTO " . "qKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "r":
		    $SQL = "INSERT INTO " . "rKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "s":
		    $SQL = "INSERT INTO " . "sKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "t":
		    $SQL = "INSERT INTO " . "tKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "u":
		    $SQL = "INSERT INTO " . "uKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "v":
		    $SQL = "INSERT INTO " . "vKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "w":
		    $SQL = "INSERT INTO " . "wKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "x":
		    $SQL = "INSERT INTO " . "xKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "y":
		    $SQL = "INSERT INTO " . "yKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
		case "z":
		    $SQL = "INSERT INTO " . "zKeywordTable VALUES (\"{$domain[$i]}\", \"{$bestTitles[$i]}\", \"{$keywordArray[0]}\", \"{$keywordArray[1]}\", \"{$keywordArray[2]}\", \"{$keywordArray[3]}\", \"{$keywordArray[4]}\", \"{$keywordArray[5]}\", \"{$keywordArray[6]}\", \"{$keywordArray[7]}\", \"{$keywordArray[8]}\", \"{$keywordArray[9]}\", \"{$keywordArray[10]}\", \"{$keywordArray[11]}\", \"{$keywordArray[12]}\");";
			$MySQL->query($SQL);
			break;
	}
}

for ($i = 0; $i <= 25; $i++) {
	for ($j = 0; $j < count($bestLinks[$i]); $j++) {
	    $abc = $bestLinks[$i][$j][0];
		$abc = strtolower($abc);
	    $SQL = "SELECT MAX(id) AS maxid FROM " . $abc . "DomainTable;";
	    $output = $MySQL->query($SQL);
		if ($output == false) { // THIS IF STATEMENT FIXES A BUG THAT WAS CRASHING THE CRAWLER
		    continue;
		}
	    while ($record = $output->fetch_assoc()){
            $maxid = $record['maxid'];
        }
		if ($bestLinks[$i][$j] == "DUPLICATE") {
			continue; // if duplicate, skip entry
	    }
	    $SQL = "SELECT * FROM " . $abc . "DomainTable WHERE domain = \"{$bestLinks[$i][$j]}\";";
	    $output = $MySQL->query($SQL);
		if ($output == false) { // THIS IF STATEMENT FIXES A BUG THAT WAS CRASHING THE CRAWLER
		    continue;
		}
	    while ($record = $output->fetch_assoc()){
		    continue 2; // if an entry already exists for this domain, break out of outerloop, then continue
        }
		$maxid++;
	    $SQL = "INSERT INTO " . $abc . "DomainTable VALUES ($maxid, \"{$bestLinks[$i][$j]}\", \"false\", \"false\");";
	    $MySQL->query($SQL);
    }
}


$MySQL->close();

$endTime = time();
$totalTime = $endTime - $startTime;
$fractionalTime = $totalTime / 26;
echo "$totalTime Seconds Elapsed <br>";
echo "$fractionalTime Seconds Per Domain";

?>
