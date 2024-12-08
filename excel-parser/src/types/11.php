<?php

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Отчет по начислениям и долгам v.2';
	$doc->setBalance(0);

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
	
	
	//print $F;
	
	//die();
	
	
	
	
	for ($i=10; $i<$F+1; $i++) // данные идут с 10 строки... по вычисленную строчку с номером F включительно...
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		
		// проверка что строка содержит дату в нужном формате...
		if (strpos($A, " г.")===false) continue;

		// вытащить дату...
		$netDate = str_replace(" г.","",$A);

		$newDate = GetFullDate($netDate);		

		$plus = trim_it(kill_spaces($sheet->getCell("H".$i)->getCalculatedValue()));
		$minus = trim_it(kill_spaces($sheet->getCell("G".$i)->getValue()));
		
		$plus = round(floatval($plus), 2);
		$minus = round(floatval($minus), 2);
		
		
		//print $plus." - ".$minus."</br>";
		//print $A."</br>";
		
		
		// суммируем результат...
		$doc->addItem($plus, 1, $newDate);
		$doc->addItem($minus, -1, $newDate);
	}
	
	// метаданные...
	$A = $sheet->getCell("A1")->getValue();

	$doc->address = str_replace(['Отчет по начислениям и долгам'], '', $A);
		
}











?>
