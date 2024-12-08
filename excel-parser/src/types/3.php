<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Карточка расчетов за период';
	
	// найти заголовок...
	
	$row = 4; // номер столбца с заголовком...
	
	$colPlus = "F";
	$colMinus = "G";
	$startB = "H";
	
	$Y = Coordinate::columnIndexFromString($sheet->getHighestColumn());
	
	for ($i = 4; $i < 12; $i++){
			
		$A = trim_it($sheet->getCell("A" . $i)->getValue());
		if ($A == "Дата"){
			
			$row = $i;
			break;
			
		}
	}
	
	for ($j = 1; $j < $Y + 1; $j++) {
		
		$letter = Coordinate::stringFromColumnIndex($j);

		$A = trim_it($sheet->getCell($letter.$row)->getValue());
		
		if (strpos($A, "енность до") !== false) {
			
			$startB = Coordinate::stringFromColumnIndex($j);
			$colPlus = Coordinate::stringFromColumnIndex($j + 1);
			$colMinus = Coordinate::stringFromColumnIndex($j + 2);
			break;
			
		}
	}

	
	// идем по столбцу G...
	
	$H = $sheet->getHighestRow();
	
		for ($i = $row + 1; $i < $H + 1; $i++)
		{
			
			// вытащить дату...
			$A = trim_it($sheet->getCell("A".$i)->getValue());
			
			if (!$A) continue;
			
			//preg_match('/\d{2}\.\d{2}\.(\d{4}|\d{2})/', $A, $match);
			
			preg_match('/[^\d\.:\s]/', $A, $match);
			
			if ($match && count($match) > 0) continue; // лишние символы, не дата...
			
			$A = str_replace(" ", "|", $A);
			
			$ex = explode("|", $A);

			$date = $ex[0];
		
			// вытащить сумму...
			$amount = trim_it(kill_spaces($sheet->getCell($colPlus.$i)->getValue()));
			
			if ($amount) {
				
				if ($amount < 0)
					$doc->addItem(abs($amount), -1, $date);
					
				else 
					$doc->addItem($amount, 1, $date);
			
			}				
				
			// вытащить сумму...
			$amount = trim_it(kill_spaces($sheet->getCell($colMinus.$i)->getValue()));
			
			if (!$amount) continue; // пустная строка, пропускаем...
			
			// суммируем результат...
			$doc->addItem(abs($amount), -1, $date);
				
		}

	
	
	// метаданные...
	$A = $sheet->getCell("A2")->getValue();

	$EX = explode("\n", $A);
	$doc->organization = $EX[2];
	$doc->period = str_replace(["за период "], "", $EX[1]);

	$B = explode(" ", trim_it($EX[3]), 5);
	
	$doc->person = $B[4];
	
	$B = trim_it($sheet->getCell($startB.($row + 2))->getValue());

	$doc->setBalance(($B) ? $B : 0);
	
}



?>
