<?php $MySQL = new mysqli('localhost', 'ADMIN', 'PASSWORD', 'DATABASE');
    $startTime = time();
	$pages = 20; // Number of Pages to Crawl
    function curlMultiRequest($urls, $options) {
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
        }
        while ($running > 0);
        // Get content and remove handles.
        foreach ($ch as $key => $val) {
            $results[$key] = curl_multi_getcontent($val);
            curl_multi_remove_handle($mh, $val);
        }
        curl_multi_close($mh);
        return $results;
    }
    $SQL = "SELECT * FROM locationTable;";
    $output = $MySQL->query($SQL);
    while ($record = $output->fetch_assoc()){
        $lastIdScraped = $record['lastIdScraped'];
    }
    $scrapeNextId = $lastIdScraped;
	    
    for ($a = 0; $a < $pages; $a++) {
        $scrapeNextId++;
        $SQL = "SELECT domain FROM domainTable WHERE id = $scrapeNextId;";
        $output = $MySQL->query($SQL);
        while ($record = $output->fetch_assoc()){
			$nextDomain[0][$a] = "http://" . "{$record['domain']}";
			$nextDomain[1][$a] = "https://" . "{$record['domain']}";
			$nextDomain[2][$a] = "http://www." . "{$record['domain']}";
			$nextDomain[3][$a] = "https://www." . "{$record['domain']}";
			$domainNoPrefix[$a] = $record['domain'];
        }
    }
 
    for ($i = 0; $i <= 3; $i++) {
        $domainData[$i] = curlMultiRequest($nextDomain[$i], $options = array(CURLOPT_TIMEOUT => 15, CURLOPT_RETURNTRANSFER => true));
    }
	
    /*
     *    $titles[domain prefixes 0 - 3][scraped titles]
     *    $links[domain prefixes][scraped urls][havested links]
     *
     */
    for ($z = 0; $z <= 3; $z++) {
        for ($c = 0; $c < $pages; $c++) {
            preg_match('/<title>(.*)<\/title>/i', $domainData[$z][$c], $title);
            $titles[$z][$c] = $title[1];
        }
        for ($e = 0; $e < count($domainData[$z]); $e++) {
            preg_match_all('/([a-zA-Z0-9\-]*?)(\.com|\.net|\.org|\.gov)/', $domainData[$z][$e], $hyperlink);
            for ($f = 0; $f < count($hyperlink[0]); $f++) {
                $links[$z][$e][$f] = $hyperlink[0][$f];
            }
        }
	}
	
    $endTime = time();
    $elapsedTime = $endTime - $startTime;
    echo "TOTAL TIME ELAPSED: $elapsedTime SECONDS <br>";
    $fractionalTime = $elapsedTime / $pages;
    echo "CRAWL TIME PER URL: $fractionalTime SECONDS <br>";
	

for ($i = 0; $i < $pages; $i++) {
    $prefixZero = count($links[0][$i]);
    $prefixOne = count($links[1][$i]);
    $prefixTwo = count($links[2][$i]);
    $prefixThree = count($links[3][$i]);
    $prefixSortArray[0] = $prefixZero;
    $prefixSortArray[1] = $prefixOne;
    $prefixSortArray[2] = $prefixTwo;
    $prefixSortArray[3] = $prefixThree;
    // BUBBLE SORT $prefixSortArray IN ORDER TO DETERMINE
    // WHICH PREFIX+DOMAIN RETURNS THE MOST LINKS WHEN SCRAPED.
    // THIS SHOULD SERVE AS A RESONABLE WAY TO DETERMINE WHICH
    // PREFIX (http://, https://, http://www., or https://www.)
    // A URL USES. NOTE, USING AN INCORRECT PREFIX+DOMAIN COMBINATION
    // WILL RESULT IN USELESS RETURN OF DATA FROM SUBSEQUENT WEB CRAWLS
    
    $keepSorting = true;
    while ($keepSorting == true) {
        $keepSorting = false;
        if ($prefixSortArray[0] > $prefixSortArray[1]) {
            $placeholder = $prefixSortArray[1];
            $prefixSortArray[1] = $prefixSortArray[0];
            $prefixSortArray[0] = $placeholder;
            $keepSorting = true;
        }
        if ($prefixSortArray[1] > $prefixSortArray[2]) {
            $placeholder = $prefixSortArray[2];
            $prefixSortArray[2] = $prefixSortArray[1];
            $prefixSortArray[1] = $placeholder;
            $keepSorting = true;
        }
        if ($prefixSortArray[2] > $prefixSortArray[3]) {
            $placeholder = $prefixSortArray[3];
            $prefixSortArray[3] = $prefixSortArray[2];
            $prefixSortArray[2] = $placeholder;
            $keepSorting = true;
        }
    }
    $mostLinks = $prefixSortArray[3];
    switch ($mostLinks) {
        case $prefixZero:
            $bestLinks[$i] = $links[0][$i];
            $bestTitles[$i] = $titles[0][$i];
            break;
        case $prefixOne:
            $bestLinks[$i] = $links[1][$i];
            $bestTitles[$i] = $titles[1][$i];
            break;
        case $prefixTwo:
            $bestLinks[$i] = $links[2][$i];
            $bestTitles[$i] = $titles[2][$i];
            break;
        case $prefixThree:
            $bestLinks[$i] = $links[3][$i];
            $bestTitles[$i] = $titles[3][$i];
            break;
    }
}

    // $bestLinks[0-19][variableAmount]
    for ($i = 0; $i < $pages; $i++) {
        for ($j = 1; $j < count($bestLinks[$i]); $j++) {
            for ($k = 0; $k < $j; $k++) {
                if ($bestLinks[$i][$j] == $bestLinks[$i][$k]) {
                    $bestLinks[$i][$j] = "DUPLICATE";
                }
            }
        }
    }
	
	// MARK DOMAINS AS 'SCRAPED' ON DATABASE
	for ($i = 0; $i < $pages; $i++) {
	    $SQL = "UPDATE domainTable SET scraped=\"true\" WHERE domain = \"{$domainNoPrefix[$i]}\";";
	    $MySQL->query($SQL);
	}
	
	// ADD NEW DOMAINS TO THE domainTable TO BE SCRAPED IN THE FUTURE
	$SQL = "SELECT MAX(id) AS maxid FROM domainTable;";
	$output = $MySQL->query($SQL);
	while ($record = $output->fetch_assoc()){
        $zId = $record['maxid'];
    }
	$zId++;
	for ($i = 0; $i < $pages; $i++) {
	    for ($j = 0; $j < count($bestLinks[$i]); $j++) {
	        $SQL = "SELECT * FROM domainTable WHERE domain = \"{$bestLinks[$i][$j]}\";";
	        $output = $MySQL->query($SQL);
	        while ($record = $output->fetch_assoc()){
		        continue 2; // if an entry already exists for this domain, break out of outerloop, then continue
            }
	        $SQL = "INSERT INTO domainTable VALUES ($zId, \"{$bestLinks[$i][$j]}\", \"false\", \"false\");";
	        $MySQL->query($SQL);
			$zId++;
		}
	}
	
	
	// MARK CRAWLED DOMAINS AS 'SCRAPED' ON THE domainTable
	for ($i = 0; $i < $pages; $i++) {
	    $SQL = "UPDATE domainTable SET scraped=\"true\" WHERE domain = \"{$domainNoPrefix[$i]}\";";
	    $MySQL->query($SQL);
	}
	
	for ($i = 0; $i < $pages; $i++) {
	    $titleArray = explode(" ", $bestTitles[$i]);
		$SQL = "INSERT INTO keywordTable VALUES (\"{$domainNoPrefix[$i]}\", \"{$bestTitles[$i]}\", \"{$titleArray[0]}\", \"{$titleArray[1]}\", \"{$titleArray[2]}\", \"{$titleArray[3]}\", \"{$titleArray[4]}\", \"{$titleArray[5]}\", \"{$titleArray[6]}\", \"{$titleArray[7]}\", \"{$titleArray[8]}\", \"{$titleArray[9]}\", \"{$titleArray[10]}\", \"{$titleArray[11]}\", \"{$titleArray[12]}\");";
	    $MySQL->query($SQL);
	}
	
	$newLastIdScraped = $lastIdScraped + $pages;
	$SQL = "UPDATE locationTable SET lastIdScraped = \"$newLastIdScraped\";";
	$MySQL->query($SQL);
	
$MySQL->close(); ?>