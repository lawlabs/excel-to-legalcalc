<?php
function recognize($sheet, &$doc)
{
	
	$doc->documentType = 'Акт сверки v13';
	
	// идем по столбцу B...
	
	$H = $sheet->getHighestRow();
	
	$F = $H;
	$S = 1;
	
	// найти начало данных...
	
	for ($i=$S; $i<$H+1; $i++) 
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		if (strpos(strtolower($A), "на начало периода:") !== false) {
			$S = $i + 1;
			
			// тут же и баланс...
			$B = trim_it($sheet->getCell("H".$i)->getValue());
			$doc->setBalance(($B) ? $B : 0);
			
			break;
		}
	}

	// найти окончание данных
	
	for ($i=$S; $i<$H+1; $i++) // начало данных в ряду $S
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		
		if (strpos(strtolower($A), "Итого за период:") !== false) {
			$F = $i;
			break;
		}
	}
	
	
	for ($i=$S; $i<$F+1; $i++) // данные идут с $S строки... по вычисленную строчку с номером F включительно...
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
	
		if (strpos($A, "Начислено за") !== false) {

		// данная строка указывает на начисление...

		// вытащить дату...
		$netDate = str_replace([" г.", "Начислено за "], "", $A);

		$newDate = GetFullDate($netDate);		
		
		$plus = trim_it(kill_spaces($sheet->getCell("F".$i)->getValue()));
		$doc->addItem($plus, 1, $newDate);
		continue;
		}
		
		if (strpos($A, "Оплата (") !== false) {
			
		// вытащить дату...
		$date = only_digits_dots($A);
		
		$EX = explode(".", $date);
		
		$year = $EX[2];
		$newDate = $EX[0].".".$EX[1].".".fullYear($year);
		
		$minus = trim_it(kill_spaces($sheet->getCell("G".$i)->getValue()));
		$doc->addItem($minus, -1, $newDate);
		}
	}
	
	// метаданные...
	$A = trim_it($sheet->getCell("A3")->getValue());
	$B = trim_it($sheet->getCell("A4")->getValue());
	$C = trim_it($sheet->getCell("A2")->getValue());
	
	$A = str_replace(['между '], "", $A);
	$B = substr($B, 3);
	$C = str_replace(['взаимных расчетов '], "", $C);
	
	$doc->organization = $A;
	$doc->person = $B;
	$doc->period = $C;
}











?>
