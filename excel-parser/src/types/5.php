<?php

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Отчет по начислениям и долгам';

	$H = $sheet->getHighestRow();
	
	$F = $H;

	// найти окончание данных - признак - ячейка со значением "Итого" в столбце A...
	
	for ($i=10; $i<$H+1; $i++) // данные идут с 10 строки...
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		if ($A=="Итого") {
			$F=$i-1;
			break;
		}
	}
	
	
	for ($i=10; $i<$F+1; $i++) // данные идут с 10 строки... по вычисленную строчку с номером F включительно...
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		
		// проверка что строка содержит дату в нужном формате...
		if (strpos($A, " г.")===false) continue;

		// вытащить дату...
		$netDate = str_replace(" г.","",$A);

		$newDate = GetFullDate($netDate);		

		$plus = trim_it(kill_spaces($sheet->getCell("C".$i)->getValue()));
		$minus = trim_it(kill_spaces($sheet->getCell("E".$i)->getValue()));
		$correction = trim_it(kill_spaces($sheet->getCell("D".$i)->getValue()));
		
		$plus = floatval($plus) + floatval($correction);
		
		// в отдельных редчайших случаях плюс будет - минусом из-за коррекции...
		
		// суммируем результат...
		if ($plus < 0) $doc->addItem(abs($plus), -1, $newDate);
		else $doc->addItem($plus, 1, $newDate);
		$doc->addItem($minus, -1, $newDate);
	}
	
	// метаданные...
	$A = $sheet->getCell("A2")->getValue();
	$B = $sheet->getCell("A8")->getValue();

	$EX = explode("\n", $A);

	$doc->period = str_replace(["Период: "], "", $EX[0]);
	$doc->organization = $B;
	
	// собственник...
	foreach ($EX as $ex)
	{
		$owner = mb_strtolower($ex);
		if (strpos($owner, "собственник")!==false) {
			$doc->person = trim_it(str_replace(['Собственник',':','', ';'], '', $ex));
			break;
		}
	}
	
	// адрес...
	foreach ($EX as $ex)
	{
		$address = mb_strtolower($ex);
		if (strpos($address, "адрес")!==false) {
			$doc->address = str_replace(['Адрес:'], '', $ex);
			break;
		}
	}
	
	
	// баланс...
	$B = trim_it($sheet->getCell("B10")->getValue());
	$doc->setBalance(($B) ? $B : 0);
	
}











?>
