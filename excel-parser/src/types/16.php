<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function recognize($sheet, &$doc){

	$doc->documentType = 'Карточка расчетов v.3';
	
	// метаданные...
	$A3 = $sheet->getCell("A3")->getValue();
	$A4 = $sheet->getCell("A4")->getValue();
	$A5 = $sheet->getCell("A5")->getValue();
	$A6 = $sheet->getCell("A6")->getValue();

	$doc->organization = $A4;
	$doc->period = str_replace(["за период "], "", $A3);
	$ex = explode(", ", $A5);
	$doc->person = trim_it($ex[1]);
	$doc->address = trim_it($A6);
	
	$B = floatval($sheet->getCell("F9")->getValue()); // вроде бы баланс всегда в одном месте...
	$doc->setBalance(($B) ? $B : 0);
	
	$row = 10;
	
	while (true) {
		
		$date = $sheet->getCell("A".$row)->getValue();
		
		if (strpos($date, "Обороты за период") !== false) // признак конца таблицы...
			break;
		
		$plus = floatval(trim_it($sheet->getCell("D".$row)->getValue()));
		$minus = floatval(trim_it($sheet->getCell("E".$row)->getValue()));
		
		$operation = mb_strtolower(trim_it($sheet->getCell("C".$row)->getValue()));
		
		$row++;
		
		if (strpos($operation, "пени") !== false)
			continue;
		
		// суммируем результат...
		$doc->addItem($plus, 1, $date);
		$doc->addItem($minus, -1, $date);

	}
	
}

?>
