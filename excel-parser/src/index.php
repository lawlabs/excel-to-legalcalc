<?php

// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

header("Content-Type: application/json; charset=UTF-8");

require_once('vendor/autoload.php');
require_once("fun.php");
include "document.php";

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$excel = new Spreadsheet();
$excel->setActiveSheetIndex(0);
$excel->getActiveSheet()->setTitle('GOOGLE');
try {
    if (file_exists($_FILES['excelfile']['tmp_name'])) {
        $source = $_FILES['excelfile']['tmp_name'];
        $reader = IOFactory::createReaderForFile($source);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($source);
        $sheet = $spreadsheet->getActiveSheet();
        $H = $sheet->getHighestRow();
        $fileName = $_FILES['excelfile']['name'];
		$docType = GetDocType($sheet);
        if (!$docType) {
            http_response_code(400);
            echo('{"status": "error", "code": "invalid_file"}');
        }
        if ($docType == 555) {
            http_response_code(400);
            echo('{"status": "error", "code": "result_file"}');
        }
        require_once('types/' . $docType . '.php');
        $doc = new Document(date("d.m.Y"));
        recognize($sheet, $doc);
        $json = $doc->packToJson2();
        http_response_code(200);
        echo($json);
    } else {
        http_response_code(400);
        echo('{"status": "error"}');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo('{"status": "error"}');
}


function GetDocType($sheet)
{
	$B2 = trim_it($sheet->getCell("B2")->getValue());
	$B2 = mb_strtolower($B2);
	
	if (strpos($B2, "сальдо вх.") !== false) return "user01"; // пользовательский формат...
	
	// для простого формата, только 3 столбца...
	$maxColumn = $sheet->getHighestColumn();
	if ($maxColumn == "C") return "simple"; // простой формат...
	
	// если это файл с расчетами...
	$A2 = trim_it($sheet->getCell("A2")->getValue());
	$A2 = mb_strtolower($A2);
	
	// все равно пока как
	$code = explode(",", $A2);
	if (strpos($code[0], 'еф01') !== false) return 18; // "все равно пока как"
	
	if (strpos($A2, "расчёт пеней по задолженности") !== false) return 555; // файл с расчётами, не обрабатываем...

	//  Акт сверки v13
	$A1 = trim_it($sheet->getCell("A1")->getValue());
	$A12 = trim_it($sheet->getCell("A12")->getValue());
	if ($A1 == "Акт сверки" && strpos(mb_strtolower($A12), "на начало периода:") !== false) return 13; // Акт сверки v13 - присвоен индекс 13...
	
	//  Акт сверки v2
	$B2 = trim_it($sheet->getCell("B2")->getValue());
	if ($B2 == "Акт сверки") return 7; // Акт сверки v2 - присвоен индекс 7...
	
	//  Акт сверки v3
	$A2 = trim_it($sheet->getCell("A2")->getValue());
	$A3 = trim_it($sheet->getCell("A3")->getValue());
	if (strpos($A2, "Акт сверки") !== false && strpos($A3, "взаимных расчетов по состоянию") !== false) return 17; // Акт сверки v3 (взаимных расчетов по состоянию ...) - присвоен индекс 17...
	
	//  Акт сверки
	$A1 = trim_it($sheet->getCell("A1")->getValue());
	if ($A1 == "Акт сверки") return 1; // Акт сверки - присвоен индекс 1...
	

	//  Оборотно-сальдовая ведомость
	$A2 = trim_it($sheet->getCell("A2")->getValue());
	if (strpos($A2, "Оборотно-сальдовая ведомость")!==false) return 2; // Оборотно-сальдовая ведомость - присвоен индекс 2...
	
	//  Карточка расчетов за период (v1, v2 и v3)
	$A2 = trim_it($sheet->getCell("A2")->getValue());
	$A2 = mb_strtolower($A2);
	
	if ($A2 == "карточка расчетов") // тут точное совпадение
		return 16; // Карточка расчетов (v3) - присвоен индекс 16...
	
	if (strpos($A2, "карточка расчетов")!==false)
	{
		$A4 = trim_it($sheet->getCell("A4")->getValue());
		if ($A4=="Дата") return 3; // Карточка расчетов за период  (v1) - присвоен индекс 3...
		
		return 4; // Карточка расчетов за период (v2) - еще один вариант (нет ДАТА в нужной ячейке) - присвоен индекс 4...	
	}
	
	//  Отчет по начислениям и долгам
	$A1 = trim_it($sheet->getCell("A1")->getValue());
	$A1 = mb_strtolower($A1);
	if ($A1=="отчет по начислениям и долгам") return 5; // Отчет по начислениям и долгам - присвоен индекс 5
	
	//  Отчет по начислениям и долгам v.2 (27/07/2020)
	$A1 = trim_it($sheet->getCell("A1")->getValue());
	$A1 = mb_strtolower($A1);
	if (strpos($A1, "отчет по начислениям и долгам")!==false) return 11; // Отчет по начислениям и долгам верия 2 - присвоен индекс 11
	
	//  Отчет по начислениям и долгам v.12 (05/08/2020)
	$A2 = trim_it($sheet->getCell("A2")->getValue());
	$A2 = mb_strtolower($A2);
	if (strpos($A2, "отчет по начислениям и долгам")!==false) return 12; // Отчет по начислениям и долгам v.12 - присвоен индекс 12
	
	
	//  История начислений за период
	$A5 = trim_it($sheet->getCell("A5")->getValue());
	$A5 = mb_strtolower($A5);
	if (strpos($A5, "история начислений за период")!==false) return 6; // История начислений за период - присвоен индекс 6

	
    // Справка о задолженности по выставленному счету с учетом
    $A3 = trim_it($sheet->getCell("A3")->getValue());
    $A3 = mb_strtolower($A3);
    if (strpos($A3, "справка о задолженности по выставленному счету")!==false) return 8;

	// Карточка счета 76.87
    $A2 = trim_it($sheet->getCell("A2")->getValue());
    $A2 = mb_strtolower($A2);
    if (strpos($A2, "карточка счета 76.87")!==false) return 9;
	
	// РАСЧЕТ ЗАДОЛЖЕННОСТИ (06/07/2020) // "мудрый" файл со смещением, учитываем это сразу
    $B1 = trim_it($sheet->getCell("B1")->getValue());
	$A1 = trim_it($sheet->getCell("A1")->getValue());

    $B1 = mb_strtolower($B1);
	$A1 = mb_strtolower($A1);
    if (strpos($B1, "расчет задолженности")!==false) return 10; // РАСЧЕТ ЗАДОЛЖЕННОСТИ
	if (strpos($A1, "расчет задолженности")!==false) return 10; // РАСЧЕТ ЗАДОЛЖЕННОСТИ
	
	
	
	$A2 = trim_it($sheet->getCell("A2")->getValue());
	$A2 = mb_strtolower($A2);
	
	if (strpos($A2, "выписка по лицевому счету за") !== false){
			
		return 15; // Выписка по лицевому счету	(ну типа пробуем сделать универсальным - с поиском нужных столбцов - т.е. все будет 15-й формат.)
	}
	
	// сбитая кодировка ?
	if (strpos($A2, "âûïèñêà ïî ëèöåâîìó ñ÷åòó çà äåêàáðü") !== false) return 14; // Выписка по лицевому счету
	
	//  Отчет по начислениям и долгам 
	return false;
}


?>
