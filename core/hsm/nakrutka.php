<?php
include ('sclad_class.php');
/*
Скрипт изменяет автоинкрементное поле номера заказа. Позволяя накручивать в большую сторону номер следующего заказа
просто ввести в поле цифру на сколько увеличить счетчик и дождаться ответа системы что все ок. 
Данные о запуске - логируются
*/



	if (Sclad.set_db_connection ())	{
	//	Получаем номер последнего заказа
	
		$schetchik = getLastOrderNumber();
//		if (!$_POST)	
	
		
		
	
	if ($_POST) {
		
		
		/* проверяем на число */
		$check4num = isNumber($_POST[countInc]);
		if ($check4num["result"]) 
			{
				$schetchik = $schetchik + $_POST["countInc"];
				$query ="ALTER TABLE `".DB_PRFX."orders` AUTO_INCREMENT =".$schetchik ;	
				//echo "<br>".$query;
			
				   $result = mysql_query($query) or die( mysql_error( $db_conn ) );      		
								
					if  ($result)	
					{
						$logtext = "\r\n-------\r\n Накрутили ".$_POST["countInc"]." позиций ". date('l jS \of F Y h:i:s A');
						writelog($logtext);							

						unset($_POST);
						header("Location:".$_SERVER['DOMAIN_NAME'].$_SERVER['PHP_SELF']);
					exit;					
					
										}
					else 
					
					{
						writelog ("Ошибка при выполнении обновления склада. Запрос ".$query);
					}
					
				
			}
			
		else
			echo $check4num["answ"];
		
		
	}
		echo "<br>текущее значение счетчика:". $schetchik ."</br>";	
	Sclad.showFormNakrutka();		
	
	
	
	}
	
	
	
?>