<?php
    for ($i = 1; $i <= 2; $i++) {
        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_URL, "https://personalhomepage.xyz/asktoby/tobycrawler31.php");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec ($curl);
        curl_close ($curl);

        echo "tobycrawler31.php script executed: run# $i \n";
    }
?>