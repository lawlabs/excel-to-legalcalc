<?php
function recognize($sheet, &$doc)
{
	
	$doc->documentType = 'Акт сверки';
	
	// идем по столбцу B...
	
	$H = $sheet->getHighestRow();
	
	$F = $H;
	$S = 1;
	
	// найти начало данных...
	
	for ($i=$S; $i<$H+1; $i++) 
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		if (strpos(strtolower($A), "п/п") !== false) {
			$S = $i + 1;
			break;
		}
	}

	// найти окончание данных - признак - пустая ячейка в столбце А.
	
	for ($i=$S; $i<$H+1; $i++) // начало данных в ряду $S
	{
		$B = trim_it($sheet->getCell("B".$i)->getValue());
		if (strlen($B)<1) {
			$F=$i-3;
			break;
		}
	}
	
	for ($i=$S; $i<$F+1; $i++) // данные идут с $S строки... по вычисленную строчку с номером F включительно...
	{
		$B = trim_it($sheet->getCell("B".$i)->getValue());
		
		$B = mb_strtolower($B);
		
		if (strpos($B, "сальдо")!==false) {

		// данная строка указывает на сальдо...
		$B = trim_it($sheet->getCell("C".$i)->getValue());
		$doc->setBalance(($B) ? $B : 0);
		continue;
		}
		
		// вытащить дату...
		$date = only_digits_dots($B);
		
		// переписать год...
		$EX = explode(".", $date);
		
		if (count($EX)<2) // тут тупо число дней...
		{
		$date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);		
		$date = $date->format('d.m.Y');
		}
		
		$EX = explode(".", $date);
		
		$year = $EX[2];
			
		$newDate = $EX[0].".".$EX[1].".".fullYear($year);
			
		// получить данные из таблицы...
		$plus = trim_it(kill_spaces($sheet->getCell("C".$i)->getValue()));
		$minus = trim_it(kill_spaces($sheet->getCell("D".$i)->getValue()));

		// суммируем результат...
		$doc->addItem($plus, 1, $newDate);
		$doc->addItem($minus, -1, $newDate);
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
