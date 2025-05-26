<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function recognize($sheet, &$doc) {

	$doc->documentType = 'Формат УК';
 
    // Установка организации из ячейки A1
    $A1 = trim_it($sheet->getCell("A1")->getValue());
    if (strpos($A1, "ООО") !== false) {
        $doc->organization = "ООО";
    }
 
    // Извлечение адреса и периода из ячейки A2
    $A2 = trim_it($sheet->getCell("A2")->getValue());
    if ($A2) {
        // Пример строки: "Начисления и оплаты"
        if (strpos($A2, "Начисления и оплаты обл") !== false) {
            // Ищем "c XX.XXXX" в строке для периода
            if (preg_match('/c\s+(\d+\.\d+)/', $A2, $matches)) {
                $doc->period = "с " . $matches[1];
 
                // Извлекаем адрес (всё между "обл" и "c XX.XXXX")
                $addressPattern = '/обл\s+(.*?)\s+c\s+\d+\.\d+/';
                if (preg_match($addressPattern, $A2, $addressMatches)) {
                    $doc->address = trim($addressMatches[1]);
                } else {
                    // Альтернативный способ извлечения адреса
                    $parts = explode("c ", $A2);
                    if (count($parts) > 1) {
                        $addressPart = str_replace("Начисления и оплаты", "", $parts[0]);
                        $doc->address = trim($addressPart);
                    }
                }
            }
        }
    }
 
    // Установка начального баланса (0, т.к. в строке A3 указано "Долг на начало периода отсутствует")
    $A3 = trim_it($sheet->getCell("A3")->getValue());
    if (strpos($A3, "Долг на начало периода отсутствует") !== false) {
        $doc->setBalance(0);
    } else {
        // Если есть другое значение баланса, можно попробовать его извлечь
        if (preg_match('/Долг на начало периода\s+([\d\.,]+)/i', $A3, $matches)) {
            $balance = floatval(str_replace([' ', ','], ['', '.'], $matches[1]));
            $doc->setBalance($balance);
        } else {
            $doc->setBalance(0); // По умолчанию 0
        }
    }
 
    // Найдем строку с заголовками таблицы
    $headerRow = 0;
    for ($i = 1; $i <= 10; $i++) {
        $A = trim_it($sheet->getCell("A" . $i)->getValue());
        if ($A == "Месяц") {
            $headerRow = $i;
            break;
        }
    }
 
    if ($headerRow == 0) return; // Если заголовки не найдены, прекращаем обработку
 
    // Определим индексы столбцов
    $columns = [
        'month' => 'A',
        'date' => 'B',
        'amount' => 'C',
        'payment' => 'D',
        'paymentDate' => 'E',
        'offset' => 'F',
        'offsetDate' => 'G'
    ];
 
    // Проходим по строкам с A до H и ищем нужные заголовки
    $highestColumn = $sheet->getHighestColumn();
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $header = trim_it($sheet->getCell($col . $headerRow)->getValue());
 
        if ($header == "Месяц") $columns['month'] = $col;
        else if ($header == "Дата начисления") $columns['date'] = $col;
        else if ($header == "Сумма начисления") $columns['amount'] = $col;
        else if ($header == "Платежи") $columns['payment'] = $col;
        else if ($header == "Дата платежа") $columns['paymentDate'] = $col;
        else if ($header == "Офсеты") $columns['offset'] = $col;
        else if ($header == "Дата погашения") $columns['offsetDate'] = $col;
    }
 
    // Обработка данных таблицы, начиная со следующей строки после заголовков
    $startRow = $headerRow + 1;
    $endRow = $sheet->getHighestRow();
 
    for ($i = $startRow; $i <= $endRow; $i++) {

        // Проверяем, есть ли данные в строке
        $month = trim_it($sheet->getCell($columns['month'] . $i)->getValue());
        if (!$month || strpos($month, "Долг на конец периода") !== false) continue;
 
        // Получаем дату начисления
        $dateCell = $sheet->getCell($columns['date'] . $i);
        $dateStr = trim_it($dateCell->getFormattedValue());
 
        // Если дата отформатирована как число, используем getValue и преобразуем в дату
        if (is_numeric($dateStr) || !preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $dateStr)) {
            $dateValue = $dateCell->getValue();


			if (is_numeric($dateValue)) {
				try {
					$date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
					$dateStr = $date->format('d.m.Y');
				} catch (Exception $e) {
					$dateStr = (string)$dateValue;
				}
			} else {
				$dateStr = (string)$dateValue;
			}
		}
 
        // Получаем сумму начисления
        $amountValue = $sheet->getCell($columns['amount'] . $i)->getValue();
        $amount = 0;
        if (is_numeric($amountValue)) {
            $amount = (float)$amountValue;
        } else {
            $amountStr = trim_it($amountValue);
            if ($amountStr) {
                $amount = floatval(str_replace([' ', ','], ['', '.'], $amountStr));
            }
        }
 
        // Получаем сумму оплаты
        $paymentValue = $sheet->getCell($columns['payment'] . $i)->getValue();
        $payment = 0;
        if (is_numeric($paymentValue)) {
            $payment = (float)$paymentValue;
        } else {
            $paymentStr = trim_it($paymentValue);
            if ($paymentStr) {
                $payment = floatval(str_replace([' ', ','], ['', '.'], $paymentStr));
            }
        }
 
        // Получаем дату оплаты
        $paymentDateCell = $sheet->getCell($columns['paymentDate'] . $i);
        $paymentDateStr = trim_it($paymentDateCell->getFormattedValue());
 
        // Если дата отформатирована как число, используем getValue и преобразуем в дату
        if (is_numeric($paymentDateStr) || !preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $paymentDateStr)) {
            $paymentDateValue = $paymentDateCell->getValue();
            if (is_numeric($paymentDateValue)) {
                try {
                    $paymentDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($paymentDateValue);
                    $paymentDateStr = $paymentDate->format('d.m.Y');
                } catch (Exception $e) {
                    $paymentDateStr = (string)$paymentDateValue;
                }
            } else {
                $paymentDateStr = (string)$paymentDateValue;
            }
        }
 
        // Добавляем начисление
        if ($amount > 0 && $dateStr) {
            $doc->addItem($amount, 1, $dateStr);
        }
 
        // Добавляем оплату
        if ($payment > 0 && $paymentDateStr) {
            $doc->addItem($payment, -1, $paymentDateStr);
        }
	}
}

?>
