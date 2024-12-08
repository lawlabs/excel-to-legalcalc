<?php
function recognize($sheet, &$doc)
{
	// три столбца: дата, начисления, списания (оплаты)...
	$doc->documentType = 'Простой формат';
	$doc->setBalance(0);

	$H = $sheet->getHighestRow();

	for ($i=1; $i<$H+1; $i++)
	{
		// проверка даты...
		
		$A = $sheet->getCell("A".$i)->getValue();
		
		if (!$A) continue;
		
		$ex = explode(".", $A);
		
		if (count($ex) == 3) {
			
			$date = $ex[0] . "." . $ex[1] . "." . fullyear($ex[2]);
			
		}
		
		else {
			
			if (!is_int($A) && !is_float($A)) continue;
				
			$date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($A);
			$date = $date->format('d.m.Y');

		}
		
		$plus = trim($sheet->getCell("B".$i)->getValue());
		$minus = trim($sheet->getCell("C".$i)->getValue());
		
		if ($plus) $doc->addItem($plus, 1, $date);
		if ($minus) $doc->addItem($minus, -1, $date);

	}
}

?>
