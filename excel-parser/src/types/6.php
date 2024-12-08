<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function recognize($sheet, &$doc)
{
	$doc->documentType = 'История начислений за период';

	$H = $sheet->getHighestRow();
	$Y = Coordinate::columnIndexFromString($sheet->getHighestColumn());
	
	$F = $H; // данные идут до самого конца таблицы...

	// определить еще и столбцы...

	$row = 7; // номер столбца с заголовком...
	
	$colPlus = "O";
	$colMinus = "P";
	
	for ($j=1; $j<$Y+1; $j++) 
	{
	$letter = Coordinate::stringFromColumnIndex($j);

	$A = trim_it($sheet->getCell($letter.$row)->getValue());
	
	if ($A=="Начислено итого") $colPlus = $letter; 
	if ($A=="Оплата") $colMinus = $letter; 
	}

	
	for ($i=8; $i<$F+1; $i++) // данные идут с 8 строки... по вычисленную строчку с номером F включительно...
	{
		$A = trim_it($sheet->getCell("A".$i)->getValue());
		
		$B = trim_it(kill_spaces($A));
		
		// проверка что строка содержит хоть что-то...
		if (!$B) continue;

		// вытащить дату...
		$netDate = $A;

		$newDate = GetFullDate($netDate, ".");		

		$plus = trim_it(kill_spaces($sheet->getCell($colPlus.$i)->getValue()));
		$minus = trim_it(kill_spaces($sheet->getCell($colMinus.$i)->getValue()));
	
		// суммируем результат...
		$doc->addItem($plus, 1, $newDate);
		$doc->addItem($minus, -1, $newDate);
	}
	
	// метаданные...
	$A = $sheet->getCell("A2")->getValue();
	$A5 = $sheet->getCell("A5")->getValue();
	
	$B = trim_it($sheet->getCell("D8")->getValue());
	
	// ФИО
	preg_match('/ФИО(.*?)ФЛС/', $A5, $fio);
	if ($fio[0]) $doc->person = trim_it(str_replace(["ФИО", "ФЛС"], "", $fio[0]));
	
	// адрес
	preg_match('/АДРЕС(.*?)ФИО/', $A5, $address);
	
	if ($address[0]) $doc->address = trim_it(str_replace(["АДРЕС", "ФИО"], "", $address[0]));
	$doc->period = str_replace(["Период: "], "", $A);
	
	$doc->setBalance(($B) ? $B : 0);
	
	
}











?>
