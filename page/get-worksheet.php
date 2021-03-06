<?php
session_start();
/**
 * Created by PhpStorm.
 * User: hungnguyen
 * Date: 02/03/16
 * Time: 4:06 PM
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../GoogleSheet.php';
require_once __DIR__ . '/../Config.php';

$feedTitle = $_GET['feedTitle'];

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
use Lib\Common;

$serviceRequest = new DefaultServiceRequest(Common::getGoogleTokenFromKeyFile());
ServiceRequestFactory::setInstance($serviceRequest);

$spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
$spreadsheetFeed = $spreadsheetService->getSpreadsheets();
$spreadsheet = $spreadsheetFeed->getByTitle($feedTitle);
$worksheetFeed = $spreadsheet->getWorksheets();

foreach ($worksheetFeed as $key => $value)
{
    $sheet[] = $worksheetFeed[$key]->getTitle();
}

echo json_encode($sheet);
