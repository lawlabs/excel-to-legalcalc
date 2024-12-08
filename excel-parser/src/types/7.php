<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Акт сверки v2';

	$H = $sheet->getHighestRow();
	$S = 0; // начало - признак салодо начальное
	
	$pl = array();
	$mn = array();
	
	// найти начало и окончание данных - признак - ячейка со значением "Обороты за период" в столбце В... 
	
	for ($i=7; $i<$H+1; $i++)
	{
		$A = mb_strtolower(trim_it($sheet->getCell("B".$i)->getValue()));
		if ($A=="обороты за период") {
			$F=$i-1;
			break;
		}
	}
	
	for ($i=7; $i<$H+1; $i++)
	{
		$A = mb_strtolower(trim_it($sheet->getCell("B".$i)->getValue()));
		if ($A=="сальдо начальное") {
			$S=$i;
			break;
		}
	}
	
	for ($i=$S+1; $i<$F+1; $i++) // данные идут с S строки... по вычисленную строчку с номером F включительно...
	{
		$A = trim_it($sheet->getCell("B".$i)->getValue());
		
		$B = trim_it(kill_spaces($A));
		
		// проверка что строка содержит хоть что-то...
		if (!$B) continue;

		// вытащить дату...
		$netDate = only_digits_dots($A);
		
		if (!$netDate) continue;
		
		$newDate = GetFullDateFullVersion($netDate);		
		
		$plus = trim_it(kill_spaces($sheet->getCell("E".$i)->getCalculatedValue()));
		$minus = trim_it(kill_spaces($sheet->getCell("G".$i)->getCalculatedValue()));
	
		// суммируем результат...
		$doc->addItem($plus, 1, $newDate);
		$doc->addItem($minus, -1, $newDate);
	}
	
	// метаданные...
	$debit = $sheet->getCell("E".$S)->getValue();
	$credit = $sheet->getCell("G".$S)->getValue();
	
	$B = intval($debit) - intval($credit);
	$doc->setBalance(($B) ? $B : 0);
	
	$B3 = $sheet->getCell("B3")->getValue();
	
	$EX = explode("\n", $B3);
	
	// ТСЖ...
	foreach ($EX as $ex)
	{
		$organization = mb_strtolower($ex);
		if (strpos($organization, "между")!==false) {
			$doc->organization = trim_it(str_replace(['между'], '', $ex));
			break;
		}
	}
	
	
	// собственник...
	foreach ($EX as $ex)
	{
		$owner = mb_strtolower($ex);
		$pos = strpos($owner, "и ");
		
		if ($pos!==false) {
			if ($pos==0) $doc->person = trim_it(str_replace(['и '], '', $ex));
			break;
		}
	}
	
	// период...
	foreach ($EX as $ex)
	{
		$period = mb_strtolower($ex);
		if (strpos($period, "взаимных расчетов за период")!==false) {
			$doc->period = trim_it(str_replace(['взаимных расчетов за период'], '', $ex));
			break;
		}
	}

	
}











?>
