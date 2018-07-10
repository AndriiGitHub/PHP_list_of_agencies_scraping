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

// define domain to make site map. Should be without '/' in the end for now // TODO check for it in the end later.
define('HOMEPAGE', 'https://www.agencyspotter.com');

// define start url
$START_URL = 'https://www.agencyspotter.com/search?location=New+York%2C+%D0%9D%D1%8C%D1%8E-%D0%99%D0%BE%D1%80%D0%BA%2C+%D0%A1%D0%BF%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D1%96+%D0%A8%D1%82%D0%B0%D1%82%D0%B8+%D0%90%D0%BC%D0%B5%D1%80%D0%B8%D0%BA%D0%B8&budget_min=0&budget_mid=infinity&optradio=project&q=';
// define('START_URL', 'http://www.pozitiff.site');
// define ID to look for
define('SPECIAL_ID', 'product-topinfo');

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
    $sql = "SELECT url FROM arkansas WHERE url = '$company'";
    $result = mysqli_query($conn, $sql);
    return $result;
}



/*************************************************************
    End of helpers functions
*************************************************************/
function workWithData($linkToDive) {
    $html = getContentElumatingBrowser($linkToDive);
//    var_dump($html->find('.provider-base-info', 0)->find('h3'));
    foreach($html->find('.provider-row') as $div){
        // urlOnSource
        echo $div->find('a', 0)->href."\n";
        // company
        echo $div->find('a', 1)->plaintext."\n";
        // website
        echo $div->find('a', 7)->href."\n";
        echo "   \n";
   }
}

/*************************************************************
    Specific for Clutch
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
workWithData('https://clutch.co/developers');

mysqli_close($conn);
/*************************************************************
    End of INITIALIZATION
*************************************************************/





























