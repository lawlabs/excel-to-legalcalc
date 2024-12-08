<?php

function myConv( $s ){
return mb_convert_encoding(mb_convert_encoding($s, 'ISO-8859-1', 'utf-8'), 'utf-8', 'windows-1251');
}

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Выписка по лицевому счету v.2';

	$H = $sheet->getHighestRow();
	
	$A1 = $sheet->getCell("A1")->getValue();
	
	$bCapital = false; // по умолчанию считаем все кроме "Взносы на капитальный ремонт"

	if ($A1) // если что-то было в ячейке А1 - то считаем только "Взносы на капитальный ремонт"
		$bCapital = true;
	
	// метаданные...

	$bBalanceSet = false; // задаем баланс единожды, он только в первой строке может быть.
	
	// ФИО...
	$person = trim_it($sheet->getCell("Q8")->getValue());
	$doc->person = $person;

	$address = trim_it($sheet->getCell("D6")->getValue());
	$doc->address = $address;
	
	$organization = trim_it($sheet->getCell("L4")->getValue());
	$doc->organization = $organization;
	
	$period = trim_it($sheet->getCell("A2")->getValue());
	$doc->period = str_replace(["Выписка по лицевому счету за "], "", $period);
	
	$F = $H;

	// найти окончание данных - признак - ячейка со значением "Итого:" в столбце A...
	// шапка - опреляем по первому слову "Услуга"
	
	$headerIndex = 0;

	
	for ($i=10; $i<$H+1; $i++) { // данные идут непонятно откуда идут...

		$A = trim_it($sheet->getCell("A".$i)->getValue());
		if ($A == "Итого:") // поиск идет до конца ... 
			$F = $i;
			
		if ($A == 'Услуга' && !$headerIndex) // запись первого упоминания "Услуга"
			$headerIndex = $i; 
	}
	
	if (!$headerIndex)
		die('не возможно определить шапку...');
	
	// найти все 5 полей - Начальный остаток | Начислено | Льгота | Перерасчет | Оплата
	$headers = ['Начальный остаток', 'Начислено', 'Льгота', 'Перерасчет', 'Оплата'];
	
	$LettersByheaders = []; // ассоциативный массив который будет хранить соотвествие столбца и его буквенного представления... А, В, С и .д.
	
	$L = Coordinate::columnIndexFromString($sheet->getHighestColumn());
	
	for ($j = 1; $j < $L + 1; $j++) {
		
		$letter = Coordinate::stringFromColumnIndex($j);
		$A = trim_it($sheet->getCell($letter.$headerIndex)->getValue());

		if (in_array($A, $headers)) // есть такой заголовок из разрешенных... 
			$LettersByheaders[$A] = $letter;
	}
	
	// собственно разбор данных...
	
	for ($i=12; $i < $F + 1; $i++) {
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		
		// проверка что строка содержит дату в нужном формате...
		if (strpos($A, " г.") !== false){
			
		// вытащить дату...
		$netDate = str_replace(" г.", "", $A);
		$newDate = GetFullDate($netDate);
		
		$cap1 = 0; $cap2 = 0; $cap3 = 0; $cap4 = 0; $cap5 = 0;// все 5 составляющие которые будут только по "Взносы на капитальный ремонт"
		
		// дату нашли, ищем соответствующую итоговую строку...
			for ($k = $i + 1; $k <= $F + 1; $k++) {
				
				$A = trim_it($sheet->getCell("A".$k)->getValue());
				
				if (strpos(mb_strtolower($A), "взносы на капитальный ремонт" ) !== false){ // записали данные только по кап. взносам...
					$cap1 = floatval(trim_it($sheet->getCell($LettersByheaders["Начальный остаток"].$k)->getValue()));
					$cap2 = floatval(trim_it($sheet->getCell($LettersByheaders["Начислено"].$k)->getValue()));
					$cap3 = floatval(trim_it($sheet->getCell($LettersByheaders["Льгота"].$k)->getValue()));
					$cap4 = floatval(trim_it($sheet->getCell($LettersByheaders["Перерасчет"].$k)->getValue()));
					$cap5 = floatval(trim_it($sheet->getCell($LettersByheaders["Оплата"].$k)->getValue()));
					
				}
				
				if ($A == "Итого:") {
					
					if (!$bBalanceSet) { // задание начального баланса с учетом кап. ремонта...
						
						$B = floatval(trim_it($sheet->getCell($LettersByheaders["Начальный остаток"].$k)->getValue())) - $cap1;
						
						if ($bCapital)
							$B = $cap1;
						
						$doc->setBalance(($B) ? $B : 0);
						$bBalanceSet = true;
					}
						
					$plus = floatval(trim_it($sheet->getCell($LettersByheaders["Начислено"].$k)->getValue()));
					$minus = floatval(trim_it($sheet->getCell($LettersByheaders["Оплата"].$k)->getValue()));
					$exemption = floatval(trim_it($sheet->getCell($LettersByheaders["Льгота"].$k)->getValue())); // льгота...
					$correction = floatval(trim_it($sheet->getCell($LettersByheaders["Перерасчет"].$k)->getValue())); // перерасчет...
					
					if (!$bCapital){ // если не капитальный, то уменьшаем итого на сумму капитальных взносов...
						
						$plus -=  $cap2;
						$minus -= $cap5;
						$correction -= $cap4;
						$exemption -= $cap3;
					}
					
					else {
						
						$plus =  $cap2;
						$minus = $cap5;
						$correction = $cap4;
						$exemption = $cap3;
						
					}
						
					if ($correction)
						$plus += $correction;
					
					if ($exemption)
						$plus -= $exemption;
						
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
