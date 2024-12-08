<?php

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Карточка счета 76.87';
	
	$M10 = $sheet->getCell("M10")->getValue();
	$doc->setBalance(($M10) ? $M10 : 0);
	
	$doc->organization = $sheet->getCell("A1")->getValue();

	$A2 = $sheet->getCell("A2")->getValue();
	$EX = explode("за", $A2);
	
	if (isset($EX[1])) $doc->period = trim_it($EX[1]);

	$C6 = $sheet->getCell("C6")->getValue();

	$doc->person = str_replace(["Контрагенты Равно "], "", $C6);
	
	
	$H = $sheet->getHighestRow();
	
	$F = $H;
	
	for ($i=11; $i<$F+1; $i++) // данные идут с 11 строки... 
	{
		$DateStr = trim_it($sheet->getCell("A".$i)->getValue());
		
		// вытащить дату...
		
		if ($DateStr && preg_match('/[0-9]{2}\.[0-9]{2}\.[0-9]{4}/', $DateStr, $match))
		{
			$plus = trim($sheet->getCell("G".$i)->getCalculatedValue());

			if ($plus)
			$doc->addItem($plus, 1, $match[0]);
			
			$minus = trim($sheet->getCell("J".$i)->getCalculatedValue());

			if ($minus)
			$doc->addItem($minus, -1, $match[0]);
		}
	}
}






?>
