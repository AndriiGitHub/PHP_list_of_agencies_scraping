<?php
/*************************************************************
Script to crawle a website and populate a db with links that will be parsed in the future
Written by Andrii Nebylovych
*************************************************************/
// crawling library with changed line 75: Had a problem with "file_get_contents(): stream does not support seeking", so changed line 75 in simple_php_dom, according to answer here https://stackoverflow.com/questions/42685814/file-get-contents-stream-does-not-support-seeking-when-was-php-behavior-abo

// passing const to queries https://stackoverflow.com/questions/8883911/how-do-you-insert-a-php-constant-into-a-sql-query
// have some issues with passing const, so use normal variable

// had a problem when execution time was too big. https://stackoverflow.com/questions/7533485/stop-running-php-function-if-exceed-a-given-time

require_once('simple_html_dom.php');
// function for checking if the page is a product page

// define start url
$start_url = 'https://clutch.co/developers?page=0';
/*************************************************************
    End of user defined settings.
*************************************************************/
$dbHost = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "clutch";

//// Create connection
$conn = mysqli_connect($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully\n";

function insertRow($company, $urlOnSource, $website){
    global $conn;
    $sql = "INSERT INTO arkansas (company,urlOnSource,website) VALUES ('".$company."','".$urlOnSource."','".$website."')";
    if(mysqli_query($conn, $sql)){
        echo "Records inserted successfully.\n";
    } else{
        echo "ERROR: Could not able to execute $sql.  ". mysqli_error($conn);
    }
}

function returnRowUrl($company){
    global $conn;
    $sql = "SELECT company FROM arkansas WHERE company = '$company'";
    $result = mysqli_query($conn, $sql);
    return $result;
}
/*************************************************************
    End of helpers functions
*************************************************************/
// takes argument $html object from CURL
// inserts into DB
// this one is hardcoded for Clutch
function insertDataintoDB($html) {
    foreach($html->find('.provider-row') as $div){
        // company
        $company = $div->find('a', 1)->href;
        // urlOnSource
        $urlOnSource = $div->find('a', 0)->href;
        // website
        $website = $div->find('a', 7)->href;

        // insert into DB
        // check if the company is already there
        if(returnRowUrl($company)->num_rows === 0){
            insertRow($company, $urlOnSource, $website);
        }
        else {
            echo "company is there already\n";
        }
   }
}
/*************************************************************
    Specific for Clutch
*************************************************************/
// takes url as an argument
function workWithData($url) {
    $html = getContentElumatingBrowser($url);
    // putting data into DB
    insertDataintoDB($html);
}

/*************************************************************
    working with pages
*************************************************************/
function getContentElumatingBrowser($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch,CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17)');
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
    $curl_scraped_page = curl_exec($ch);

    $html = new simple_html_dom();
    $html->load($curl_scraped_page);
    return $html;
}

/*************************************************************
    Partly emulate a browser
*************************************************************/
// INITIALIZATION
workWithData($start_url);

mysqli_close($conn);
/*************************************************************
    End of INITIALIZATION
*************************************************************/





























