<?php

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Отчет по начислениям и долгам v.12';

	$H = $sheet->getHighestRow();
	
	$F = $H;

	// найти окончание данных - признак - ячейка со значением "Итого" в столбце A...
	
	for ($i=13; $i<$H+1; $i++) // данные идут с 13 строки...
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		if ($A=="Итого") {
			$F=$i-1;
			break;
		}
	}
	
	
	for ($i=13; $i<$F+1; $i++)
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		
		// проверка что строка содержит дату в нужном формате...
		if (strpos($A, " г.") !== false){
			
		// вытащить дату...
		$netDate = str_replace(" г.", "", $A);
		$newDate = GetFullDate($netDate);	

		}
		else {
		
		$date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($A);		
		$date = $date->format('d.m.Y');
		$EX = explode(".", $date);
		$year = $EX[2];
		$newDate = $EX[0].".".$EX[1].".".fullYear($year);
		}

		$plus = trim_it(kill_spaces($sheet->getCell("D".$i)->getValue()));
		$correction = trim_it(kill_spaces($sheet->getCell("E".$i)->getValue()));
		$minus = trim_it(kill_spaces($sheet->getCell("F".$i)->getValue()));
		
		$plus = floatval($plus) + floatval($correction);
		
		// суммируем результат...
		$doc->addItem($plus, 1, $newDate);
		$doc->addItem($minus, -1, $newDate);
	}
	
	// метаданные...
	$A = $sheet->getCell("A2")->getValue();
	$doc->period = str_replace(["Отчет по начислениям и долгам за "], "", $A);
	
	$B = $sheet->getCell("A1")->getValue();
	$doc->organization = $B;
	
	// баланс...
	$B = trim_it($sheet->getCell("C13")->getValue());
	$doc->setBalance(($B) ? $B : 0);
	
}











?>
