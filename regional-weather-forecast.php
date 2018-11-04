<?php

require_once ('lib/Scrape.php');

//echo 'Fetching Daily Weather Forecast...';
$success = false;
while (!$success) {
    try {
        $scraper = new Scrape('https://www1.pagasa.dost.gov.ph/index.php', 5);
        $scraper ->load('/vis-weather/local-weather-forecast');
        $success = true;

    } catch (RuntimeException  $e) {
        echo $e->getMessage();
    }
}

$xpathParent = '//*[@id="content"]/div/div[2]';
$scraper->setParentNode($xpathParent);

$xpathSynopsis = '//*[@id="content"]/div/div[2]';

$title = $scraper->setNodeTitle($xpathSynopsis)->getTitle();

$html = $scraper->getHtml();
$p_only = $scraper->strip_tags_content($html, "<p>");
$text_only = strip_tags($p_only);
$clean = preg_replace('/\s+/', ' ', $text_only);
//dd($title);
//if title is short or not complete
if (strlen($title) <= 20) {
    return;
}
//echo $title;

$tmpStr = explode("Issued at: ", $title, 2);
if (sizeof($tmpStr)>1) {
    $tmpStr = $tmpStr[1];
    $tmpStr = explode("Valid Beginning:", $tmpStr, 2);
    $issuedAtStr= $tmpStr[0];
    $tmpStr = $tmpStr[1];
    $tmpStr = explode("SYNOPSIS:", $tmpStr, 2);
    $validityStr = $tmpStr[0];
    $tmpStr = $tmpStr[1];
    $tmpStr = explode("FORECAST:", $tmpStr, 2);
    $synopsisStr = $tmpStr[0];
    $tmpStr = $tmpStr[1];
    $tmpStr = explode("PAGBANA-BANA:", $tmpStr, 2);
    $forecastStr = $tmpStr[0];
    $data = array("success"=>true, "issuedat"=>$issuedAtStr, "validity"=>$validityStr, "synopsis"=>$synopsisStr, "forecast"=>$forecastStr);
} else {
    $data = array("success"=>false);
}
header('Content-Type: application/json');
echo json_encode($data);
