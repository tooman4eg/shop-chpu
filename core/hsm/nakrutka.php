<?php
include ('sclad_class.php');
/*
������ �������� ���������������� ���� ������ ������. �������� ����������� � ������� ������� ����� ���������� ������
������ ������ � ���� ����� �� ������� ��������� ������� � ��������� ������ ������� ��� ��� ��. 
������ � ������� - ����������
*/



	if (Sclad.set_db_connection ())	{
	//	�������� ����� ���������� ������
	
		$schetchik = getLastOrderNumber();
//		if (!$_POST)	
	
		
		
	
	if ($_POST) {
		
		
		/* ��������� �� ����� */
		$check4num = isNumber($_POST[countInc]);
		if ($check4num["result"]) 
			{
				$schetchik = $schetchik + $_POST["countInc"];
				$query ="ALTER TABLE `".DB_PRFX."orders` AUTO_INCREMENT =".$schetchik ;	
				//echo "<br>".$query;
			
				   $result = mysql_query($query) or die( mysql_error( $db_conn ) );      		
								
					if  ($result)	
					{
						$logtext = "\r\n-------\r\n ��������� ".$_POST["countInc"]." ������� ". date('l jS \of F Y h:i:s A');
						writelog($logtext);							

						unset($_POST);
						header("Location:".$_SERVER['DOMAIN_NAME'].$_SERVER['PHP_SELF']);
					exit;					
					
										}
					else 
					
					{
						writelog ("������ ��� ���������� ���������� ������. ������ ".$query);
					}
					
				
			}
			
		else
			echo $check4num["answ"];
		
		
	}
		echo "<br>������� �������� ��������:". $schetchik ."</br>";	
	Sclad.showFormNakrutka();		
	
	
	
	}
	
	
	
?>