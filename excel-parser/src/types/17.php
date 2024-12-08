<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function recognize($sheet, &$doc){

	$doc->documentType = 'Акт сверки # взаимных расчетов по состоянию на дату';
	
	// метаданные...
	$A3 = $sheet->getCell("A3")->getValue();
	$A4 = $sheet->getCell("A4")->getValue();

	$doc->organization = $A4;
	$doc->period = str_replace(["взаимных расчетов по состоянию на"], "", $A3);
	
	$doc->setBalance(0); // тут не нашел сведений о начальном балансе (задолженности)
	
	$row = 15;
	
	while (true) {
		
		$operation = trim_it($sheet->getCell("A".$row)->getValue());
		
		if (strpos($operation, "ВСЕГО по контагенту") !== false) // признак конца таблицы...
			break;
			
		if (strpos($operation, "Итого по") !== false){ // промежуточные строки, пропускаем...
			$row++;
			continue;
		}
		
		$date = $sheet->getCell("F".$row)->getValue();
		
		if (!$date){
			
			$row++;
			continue;
		}
		
		$plus = floatval(trim_it($sheet->getCell("H".$row)->getValue()));
		$minus = floatval(trim_it($sheet->getCell("I".$row)->getValue()));
		
		$row++;
		
		// суммируем результат...
		$doc->addItem($plus, 1, $date);
		$doc->addItem($minus, -1, $date);

	}
	
}

?>
