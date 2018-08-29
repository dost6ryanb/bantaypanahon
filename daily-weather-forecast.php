<?php

require_once ('lib/Scrape.php');

//echo 'Fetching Daily Weather Forecast...';
$success = false;
while (!$success) {
    try {
        $scraper = new Scrape('https://www1.pagasa.dost.gov.ph/index.php', 5);
        $scraper ->load('/general-weather/daily-weather-forecast');
        $success = true;

    } catch (RuntimeException  $e) {
        echo $e->getMessage();
    }
}

$xpathParent = '//*[@id="content"]/div/div[2]';
$scraper->setParentNode($xpathParent);

$xpathSynopsis = '//*[@id="content"]/div/div[2]/p[1]';

$title = $scraper->setNodeTitle($xpathSynopsis)->getTitle();

//dd($title);
//if title is short or not complete
if (strlen($title) <= 20) {
    return;
}
//echo $title;

$tmpStr = explode("\n", $title, 2);

if (sizeof($tmpStr)>1) {
    $issuedAtStr = substr($tmpStr[0], 10);
    $synopsisStr = substr($tmpStr[1], 10);
    $synopsisStr = str_replace("\xc2\xa0", '', $synopsisStr);
    $synopsisStr = str_replace("\x20\x20", ' ', $synopsisStr);
    $synopsisStr = trim($synopsisStr);
    $data = array("success"=>true, "issuedat"=>$issuedAtStr, "synopsis"=>$synopsisStr);
} else {
    $data = array("success"=>false);
}
header('Content-Type: application/json');
echo json_encode($data);
