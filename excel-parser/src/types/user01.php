<?php
function recognize($sheet, &$doc)
{
	// три столбца: дата, начисления, списания (оплаты)...
	$doc->documentType = 'Пользовательский формат - №01';
	$doc->setBalance(0);

	$H = $sheet->getHighestRow();

	for ($i=3; $i<$H+1; $i++)
	{
		// проверка даты...
		$A = $sheet->getCell("A".$i)->getValue();
		
		if (!$A) continue;
		
		$ex = explode(".", $A);
		
		if (count($ex) == 3) {
		$date = $ex[0] . "." . $ex[1] . "." . fullyear($ex[2]);
		}
		
		else {
		
		continue;
		/*
		$date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($A);
		$date = $date->format('d.m.Y');
		if ($date == "01.01.1970") continue;
		*/
		}

		
		$plus = trim($sheet->getCell("C".$i)->getValue());
		$minus = trim($sheet->getCell("E".$i)->getValue());
		
		if ($plus) $doc->addItem($plus, 1, $date);
		if ($minus) $doc->addItem($minus, -1, $date);

	}
}

?>
