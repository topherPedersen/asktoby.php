<?php
    $startTime = time();
	echo "UNIX EPOCH START TIME: $startTime \n";
	function scrapeTitle($url) {
	    //echo "SCRAPETITLE FUNCTION CALLED";
        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 15); //timeout in seconds
        $result = curl_exec ($curl);
        curl_close ($curl);
		preg_match('/<title>(.*)<\/title>/i', $result, $title);
        $title_out = $title[1];
		return $title_out;
	}

    function scrapeLinks($url) {
	    //echo "SCRAPELINKS FUNCTION CALLED";
        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 15); //timeout in seconds
        $result = curl_exec ($curl);
        curl_close ($curl);
	    
		// scrape links
        // returns $hyperlink[0][0], $hyperlink[0][1], $hyperlink[0][2]...
        preg_match_all('/([a-zA-Z0-9\-]*?)(\.com|\.net|\.org|\.gov)/', $result, $hyperlink);
	
	    for ($i = 0; $i < count($hyperlink[0]); $i++) {
		    $domain[$i] = $hyperlink[0][$i];
        }
    
        for ($j = 1; $j < count($hyperlink[0]); $j++) {
		    for ($k = 0; $k < $j; $k++) {
			    if ($domain[$j] == $domain[$k]) {
				    $domain[$j] = "DUPLICATE!";
		        }
	        }
        }
    
        $n = 0;
        for ($m = 0; $m < count($hyperlink[0]); $m++) {
		    if ($domain[$m] != "DUPLICATE!") {
			    $uniqueDomain[$n] = $domain[$m];
			    $n++;
	        }
        }
		return $uniqueDomain; // $uniqueDomain is an array of unique domain names scraped from a url
	} // end scrapeLinks() function


    //$www = $_POST['www'];
function SUBROUTINE() {
    //echo "SUBROUTINE FUNCTION CALLED";
	$MySQL = new mysqli('localhost', 'USERNAMEGOESHERE', 'PASSWORDGOESHERE', 'DATABASENAMEGOESHERE');
	$SQL = "SELECT * FROM locationTable;";
	$output = $MySQL->query($SQL);
	while ($record = $output->fetch_assoc()){
        $lastIdScraped = $record['lastIdScraped'];
		//echo "LAST ID SCRAPED: $lastIdScraped <br>";
    }
	$MySQL->close();
	
	$scrapeNext = $lastIdScraped + 1;
	
	$MySQL = new mysqli('localhost', 'USERNAMEGOESHERE', 'PASSWORDGOESHERE', 'DATABASENAMEGOESHERE');
	$SQL = "SELECT domain FROM domainTable WHERE id = $scrapeNext;";
	$output = $MySQL->query($SQL);
	while ($record = $output->fetch_assoc()){
        $nextDomain = $record['domain'];
		//echo "LAST ID SCRAPED: $lastIdScraped <br>";
    }
	$MySQL->close();

        
	$scrapedTitleVar[0] = scrapeTitle("http://" . "$nextDomain");
	$scrapedLinksVar[0] = scrapeLinks("http://" . "$nextDomain");
	
	$scrapedTitleVar[1] = scrapeTitle("https://" . "$nextDomain");
	$scrapedLinksVar[1] = scrapeLinks("https://" . "$nextDomain");
	
	$scrapedTitleVar[2] = scrapeTitle("http://www." . "$nextDomain");
	$scrapedLinksVar[2] = scrapeLinks("http://www." . "$nextDomain");
	
	$scrapedTitleVar[3] = scrapeTitle("https://www." . "$nextDomain");
	$scrapedLinksVar[3] = scrapeLinks("https://www." . "$nextDomain");
	
	$scrapedTitleVar[4] = scrapeTitle("www." . "$nextDomain");
	$scrapedLinksVar[4] = scrapeLinks("www." . "$nextDomain");
	
	$scrapedTitleVar[5] = scrapeTitle($nextDomain);
	$scrapedLinksVar[5] = scrapeLinks($nextDomain);
	
	$linkCount[0] = count($scrapedLinksVar[0]);
	$linkCount[1] = count($scrapedLinksVar[1]);
	$linkCount[2] = count($scrapedLinksVar[2]);
	$linkCount[3] = count($scrapedLinksVar[3]);
	$linkCount[4] = count($scrapedLinksVar[4]);
	$linkCount[5] = count($scrapedLinksVar[5]);
	
	$urlZero = $linkCount[0];
	$urlOne = $linkCount[1];
	$urlTwo = $linkCount[2];
	$urlThree = $linkCount[3];
	$urlFour = $linkCount[4];
	$urlFive = $linkCount[5];
	
	// Bubble Sort Urls By Number of Links Per URL
	
	$linkSortArray[0] = $urlZero;
	$linkSortArray[1] = $urlOne;
	$linkSortArray[2] = $urlTwo;
	$linkSortArray[3] = $urlThree;
	$linkSortArray[4] = $urlFour;
	$linkSortArray[5] = $urlFive;
	
	
	$keepSorting = true;
	while ($keepSorting == true) {
	    $keepSorting = false;
		for ($x = 0; $x <= 4; $x++) {
		    if ($linkSortArray[$x] > $linkSortArray[$x + 1]) {
			    $keepSorting = true;
		        $placeHolder = $linkSortArray[1];
			    $linkSortArray[$x + 1] = $linkSortArray[$x];
			    $linkSortArray[$x] = $placeHolder;
		    }
		}
	}
	
	if ($linkSortArray[5] == $urlZero) {
	    $bestScrapedLinksVar = $scrapedLinksVar[0];
		$bestScrapedTitleVar = $scrapedTitleVar[0];
	}
	
	if ($linkSortArray[5] == $urlOne) {
	    $bestScrapedLinksVar = $scrapedLinksVar[1];
		$bestScrapedTitleVar = $scrapedTitleVar[1];
	}
	
	if ($linkSortArray[5] == $urlTwo) {
	    $bestScrapedLinksVar = $scrapedLinksVar[2];
		$bestScrapedTitleVar = $scrapedTitleVar[2];
	}
	
	if ($linkSortArray[5] == $urlThree) {
	    $bestScrapedLinksVar = $scrapedLinksVar[3];
		$bestScrapedTitleVar = $scrapedTitleVar[3];
	}
	
	if ($linkSortArray[5] == $urlFour) {
	    $bestScrapedLinksVar = $scrapedLinksVar[4];
		$bestScrapedTitleVar = $scrapedTitleVar[4];
	}
	
	if ($linkSortArray[5] == $urlFive) {
	    $bestScrapedLinksVar = $scrapedLinksVar[5];
		$bestScrapedTitleVar = $scrapedTitleVar[5];
	}
	
	
    
	//echo "$bestScrapedTitleVar <br>";
	//echo "$bestScrapedLink";
	
	$MySQL = new mysqli('localhost', 'USERNAMEGOESHERE', 'PASSWORDGOESHERE', 'DATABASENAMEGOESHERE');
	$SQL = "UPDATE locationTable SET lastIdScraped = \"$scrapeNext\";";
	$MySQL->query($SQL);
	$MySQL->close();
	
	
	$MySQL = new mysqli('localhost', 'USERNAMEGOESHERE', 'PASSWORDGOESHERE', 'DATABASENAMEGOESHERE');
	$SQL = "SELECT MAX(id) AS maxid FROM domainTable;";
	$output = $MySQL->query($SQL);
	while ($record = $output->fetch_assoc()){
        $zId = $record['maxid'];
    }
	$MySQL->close();
	
	for ($z = 0; $z < count($bestScrapedLinksVar); $z++) {
	    $MySQL = new mysqli('localhost', 'USERNAMEGOESHERE', 'PASSWORDGOESHERE', 'DATABASENAMEGOESHERE');
	    $SQL = "SELECT * FROM domainTable WHERE domain = \"{$bestScrapedLinksVar[$z]}\";";
	    $output = $MySQL->query($SQL);
	    while ($record = $output->fetch_assoc()){
		    continue 2; // if an entry already exists for this domain, break out of outerloop, then continue
		}
		$zId = $zId + 1;
	    $MySQL = new mysqli('localhost', 'USERNAMEGOESHERE', 'PASSWORDGOESHERE', 'DATABASENAMEGOESHERE');
	    $SQL = "INSERT INTO domainTable VALUES ($zId, \"{$bestScrapedLinksVar[$z]}\", \"false\", \"false\");";
	    $MySQL->query($SQL);
	    $MySQL->close();
	}
	
	
	$MySQL = new mysqli('localhost', 'USERNAMEGOESHERE', 'PASSWORDGOESHERE', 'DATABASENAMEGOESHERE');
	$SQL = "UPDATE domainTable SET scraped=\"true\" WHERE domain = \"$nextDomain\";";
	$MySQL->query($SQL);
	$MySQL->close();
	
	if ($bestScrapedTitleVar) {
	    $MySQL = new mysqli('localhost', 'USERNAMEGOESHERE', 'PASSWORDGOESHERE', 'DATABASENAMEGOESHERE');
	    $SQL = "UPDATE domainTable SET valid=\"true\" WHERE domain = \"$nextDomain\"; ";
	    $MySQL->query($SQL);
	    $MySQL->close();
	}
	
	
	
    $titleArray = explode(" ",$bestScrapedTitleVar);
	
    $MySQL = new mysqli('localhost', 'USERNAMEGOESHERE', 'PASSWORDGOESHERE', 'DATABASENAMEGOESHERE');
	$SQL = "INSERT INTO keywordTable VALUES (\"$nextDomain\", \"$bestScrapedTitleVar\", \"{$titleArray[0]}\", \"{$titleArray[1]}\", \"{$titleArray[2]}\", \"{$titleArray[3]}\", \"{$titleArray[4]}\", \"{$titleArray[5]}\", \"{$titleArray[6]}\", \"{$titleArray[7]}\", \"{$titleArray[8]}\", \"{$titleArray[9]}\", \"{$titleArray[10]}\", \"{$titleArray[11]}\", \"{$titleArray[12]}\");";
	$MySQL->query($SQL);
	$MySQL->close();
} // end bracket for function SUBROUTINE()

// In Theory, Running SUBROUTINE 695 times every minute should equal 1 million sites a day
// If tobycrawler is able to crawl 1 million sites a day, it can crawl every domain on the internet in 1 year

echo "RUN SUBROUTINE() \n";
SUBROUTINE();
	
$endTime = time();
$elapsedTime = $endTime - $startTime;
echo "SECONDS TO RUN 1 TIME: $elapsedTime \n";
echo "TOBYCRAWLER27 SCRIPT RUN FINISHED \n";
?>
