<?php
session_start();
require_once __DIR__ . "/vendor/autoload.php";
//include_once __DIR__ . "/vendor/google/apiclient/examples/templates/base.php";
require_once __DIR__ . "/vendor/google/apiclient/src/Google/autoload.php";


// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//require_once __DIR__ . '/WorkWithFile.php';
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * @param $cellFeed
 * @param $i
 * @param $j
 * @param $batchRequest
 */
function addEntryToBatch($cellFeed, $i, $j, $batchRequest, $contetn)
{
    $input = $cellFeed->createInsertionCell($i, $j, $contetn);
    $batchRequest->addEntry($input);
}


$client_id = '731304622813-7dqu772cehhj2qufojg5s8sg5t32gg36.apps.googleusercontent.com';
$service_account_name = '731304622813-7dqu772cehhj2qufojg5s8sg5t32gg36@developer.gserviceaccount.com';  // email address
$key_file_location = __DIR__ . "/keys/P12/spreadsheet-data-updating-429d6c668fc1.p12"; //key.p12
$key = file_get_contents($key_file_location);
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//$myFile = new WorkWithFile();
function getFile($pathToFile){
    $headers = get_headers($pathToFile);
    if (strpos($headers[0], '200')){
        $fileContent = file($pathToFile);
        array_pop($fileContent);
        return $fileContent;
    } else {
        user_error("Invalid value: $pathToFile for path to file");
        exit;
    }
}
$pathToFile = 'http://sorting.local/test.csv';
$fileContent = getFile($pathToFile);
$numberOfRowsInNewWorksheet = count($fileContent);
$numberOfColumnsInNewWorksheet = '';
foreach($fileContent as $columns => $dataInColumns){
    $dataInRow = explode(';', trim($dataInColumns));
    $numberOfColumnsInNewWorksheet = count($dataInRow);
}
//var_dump($numberOfColumnsInNewWorksheet); die;
//var_dump(count($fileContent)); die;
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

/**
 * @param $cellFeed
 * @param $i
 * @param $j
 * @param $batchRequest
 */




/**
 * Build the client object
 */
$client = new Google_Client();
$client->setApplicationName("my-google-spreadsheet-data-updating");
//$client->setDeveloperKey("AIzaSyA4BK1O_pz9cVKk-uvDiHmZOp5mIC-Gbvw");

$credentials = new Google_Auth_AssertionCredentials(
    $service_account_name,
    array('https://spreadsheets.google.com/feeds'),
    $key
);

$client->setAssertionCredentials($credentials);
if ($client->getAuth()->isAccessTokenExpired()){
    $client->getAuth()->refreshTokenWithAssertion($credentials);
}

$_SESSION['service_token'] = $client->getAccessToken();
// Build the service object
$resultArray = json_decode($_SESSION['service_token']);
$accessToken = $resultArray->access_token;


/**
 * Calling an API
 */

// Bootstrapping (initialize the service request factory)
$serviceRequest = new DefaultServiceRequest($accessToken);
ServiceRequestFactory::setInstance($serviceRequest);


// Adding a list row
$spreadsheetService = new Google\Spreadsheet\SpreadsheetService();


$spreadsheetFeed = $spreadsheetService->getSpreadsheets();

$spreadsheet = $spreadsheetFeed->getByTitle('test');
$worksheetFeed = $spreadsheet->getWorksheets();
$worksheet = $worksheetFeed->getByTitle('list_2');

$headers = [
    "Offer_ID_Reporting",
    "Offer_ID_View",
    "Offer_ID_Cart",
    "Offer_Name",
    "Category_ID",
    "Category_Name",
    "Price",
    "Image_URL",
    "Exit_URL",
    "Default",
    "Active",
];

$batchRequest = new \Google\Spreadsheet\Batch\BatchRequest();

$cellFeed = $worksheet->getCellFeed();


for ($i = 0; $i < count($headers); $i++) {
    addEntryToBatch($cellFeed, 1, $i + 1, $batchRequest, $headers[$i]);
}


for ($i = 2; $i < 20000; $i++) {
    for ($j = 1; $j <= 11; $j++) {
        addEntryToBatch($cellFeed, $i, $j, $batchRequest, generateRandomString());
    }
    if($i % 1000 == 1){
        $cellFeed->insertBatch($batchRequest);
        $batchRequest = new \Google\Spreadsheet\Batch\BatchRequest();
    }
}


$cellFeed->insertBatch($batchRequest);

die;



/**
 * Handling the result
 */

