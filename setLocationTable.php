<?php $MySQL = new mysqli('localhost', 'ADMIN', 'PASSWORD', 'DATABASE');
    
	$alphabet = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
   
    for ($i = 0; $i <= 25; $i++) {
	    $SQL = "INSERT INTO " . $alphabet[$i] . "LocationTable VALUES (0);";
	    $MySQL->query($SQL);
	}
	
	echo "LocationTables Updated";
		
$MySQL->close(); ?>