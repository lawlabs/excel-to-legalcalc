<?php

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Оборотно-сальдовая ведомость';
	
	// идем по столбцу B...
	
	$H = $sheet->getHighestRow();
	
	$F = $H-1;

	// найти окончание данных - признак - пустая ячейка в столбце А.
	
	for ($i=13; $i<$H+1; $i++) // данные идут с 13 строки...
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		if (strlen($A)<1) {
			$F=$i-2;
			break;
		}
	}
	
	$bBalanceSet = true; // задаем баланс единожды, он только в первой строке может быть.
	// в этих файлах нет данных о начальном балансе, пока везде нулевой.
	$doc->setBalance(0);

	for ($i=13; $i<$F+1; $i++) // данные идут с 13 строки... по вычисленную строчку с номером F включительно...
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		
		if (!$A) continue;
		if ( strpos( $A, "обслуживание л/с" ) !== false) continue;
		
		/// шаблон для даты...
		
		preg_match('/\d{2}\.\d{2}\.(\d{4}|\d{2})/', $A, $match);
		
		if ($match) {
			
		// переписать год...
		$EX = explode(".", $match[0]);
			
		//$newDate = GetFullDate($netDate); // дописать под редкий файл с месяцем как пропись...

		$year = $EX[2];
		$newDate = $EX[0].".".$EX[1].".".fullYear($year);
		
			if (!$bBalanceSet) {
				$B = trim_it($sheet->getCell("C".$i)->getValue());
				$doc->setBalance(($B) ? $B : 0);
				$bBalanceSet = true;
			}
			
		// получить данные из таблицы...
		$plus = trim_it(kill_spaces($sheet->getCell("E".$i)->getValue()));
		$minus = trim_it(kill_spaces($sheet->getCell("F".$i)->getValue()));

		// суммируем результат...
		$doc->addItem($plus, 1, $newDate);
		$doc->addItem($minus, -1, $newDate);
		}
	}
	
		// метаданные...
	$A = trim_it($sheet->getCell("A1")->getValue());
	$B = trim_it($sheet->getCell("A11")->getValue());
	$C = trim_it($sheet->getCell("A2")->getValue());
	
	$pos = strpos($C, " за ");

	if ($pos!==false) {
	$C = substr($C, $pos);
	$doc->period = $C;
	}
	
	$doc->organization = $A;
	$doc->person = $B;

}











?>
