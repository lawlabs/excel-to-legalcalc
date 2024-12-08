<?php

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Карточка расчетов за период v2';

	// идем по столбцу B...
	
	$H = $sheet->getHighestRow();
	
	$F = $H;

	// найти окончание данных - признак - ячейка со значением "Обороты за период" в столбце В...
	
	for ($i=10; $i<$H+1; $i++) // данные идут с 10 строки...
	{
		$A = trim_it($sheet->getCell("B".$i)->getValue());
		if ($A=="Обороты за период") {
			$F=$i-1;
			break;
		}
	}
	
	
	for ($i=10; $i<$F+1; $i++) // данные идут с 10 строки... по вычисленную строчку с номером F включительно...
	{
		$D = trim_it($sheet->getCell("D".$i)->getValue());
		
		// вытащить дату...
		$date = only_digits_dots($D);
		
		// переписать год...
		$EX = explode(".", $date);
		
		$year = $EX[2];
			
		$newDate = $EX[0].".".$EX[1].".".fullYear($year);
			
		// заменить точку на запятую...
		$plus = trim_it(kill_spaces($sheet->getCell("G".$i)->getValue()));
		$minus = trim_it(kill_spaces($sheet->getCell("H".$i)->getValue()));

		// суммируем результат...
		$doc->addItem($plus, 1, $newDate);
		$doc->addItem($minus, -1, $newDate);
	}
	
		// метаданные...
	$A = $sheet->getCell("A2")->getValue();
	$B = $sheet->getCell("B4")->getValue();

	$EX = explode("\n", $A);

	$doc->period = str_replace(["за период "], "", $EX[1]);
	$doc->person = $B;
	
	$B = trim_it($sheet->getCell("B5")->getValue());
	$doc->address = $B;
	
	$B = trim_it($sheet->getCell("I7")->getValue());

	$doc->setBalance(($B) ? $B : 0);
	
}






?>
