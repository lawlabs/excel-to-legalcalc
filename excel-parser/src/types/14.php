<?php

function myConv( $s ){
return mb_convert_encoding(mb_convert_encoding($s, 'ISO-8859-1', 'utf-8'), 'utf-8', 'windows-1251');
}

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Выписка по лицевому счету';

	$H = $sheet->getHighestRow();
	
	$encoded = false;
	
	$A2 = mb_strtolower($sheet->getCell("A2")->getValue());
	
	if (strpos($A2, "âûïèñêà ïî ëèöåâîìó ñ÷åòó çà äåêàáðü") !== false) $encoded = true; // сбитая кодировка...
	
	// метаданные...

	$bBalanceSet = false; // задаем баланс единожды, он только в первой строке может быть.
	
	// ФИО...
	$person = trim_it($sheet->getCell("Q8")->getValue());
	if ($encoded) $person = myConv($person);
	
	$doc->person = $person;

	$address = trim_it($sheet->getCell("D6")->getValue());
	if ($encoded) $address = myConv($address);
	
	$doc->address = $address;
	
	$organization = trim_it($sheet->getCell("L4")->getValue());
	if ($encoded) $organization = myConv($organization);
	$doc->organization = $organization;
	
	$period = trim_it($sheet->getCell("A2")->getValue());
	if ($encoded) $period = myConv($period);
	$doc->period = str_replace(["Выписка по лицевому счету за "], "", $period);
	
	$F = $H;

	// найти окончание данных - признак - ячейка со значением "Итого:" в столбце A...
	
	for ($i=10; $i<$H+1; $i++) // данные идут непонятно откуда идут...
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		
		if ($encoded) $A = myConv($A);
		
		if ($A == "Итого:") {
			$F = $i;
		}
	}
	
	
	// собственно разбор данных...
	
	for ($i=12; $i < $F + 1; $i++)
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		
		if ($encoded) $A = myConv($A);
		
		// проверка что строка содержит дату в нужном формате...
		if (strpos($A, " г.") !== false){
			
		// вытащить дату...
		$netDate = str_replace(" г.", "", $A);
		$newDate = GetFullDate($netDate);
		
		// дату нашли, ищем соответствующую итоговую строку...
			for ($k = $i + 1; $k <= $F + 1; $k++) {
				
				$A = trim_it($sheet->getCell("A".$k)->getValue());
				
				if ($encoded) $A = myConv($A);
				
				if ($A == "Итого:") {
					
					if (!$bBalanceSet) {
					$B = trim_it($sheet->getCell("I".$k)->getValue());
					$doc->setBalance(($B) ? $B : 0);
					$bBalanceSet = true;
					}
					
					$plus = floatval(trim_it($sheet->getCell("AA".$k)->getValue()));
					$minus = floatval(trim_it($sheet->getCell("AU".$k)->getValue()));
					$correction = floatval(trim_it($sheet->getCell("AP".$k)->getValue())); // перерасчет...
					
					//$plus = $correction * 1;

					if ($correction)
					$plus += $correction;

					// суммируем результат...
					$doc->addItem($plus, 1, $newDate);
					$doc->addItem($minus, -1, $newDate);
					break;
				}
			} 
		}
	}
}
?>
