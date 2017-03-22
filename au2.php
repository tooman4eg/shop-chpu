<?php
include ('core/config/connect.inc.php');
$username =  $_GET["username"];
?>

<html>
<head>
<title>Администрирование пользоватлей</title>
</head>
<body>

<div width="100%">
	<div width=20% align="left">
		<form action="aunew2.php" method="post">
		<input name ="search1" type="text">
		<input type="submit" value="Найти">
		</form>
	</div>

	<div width=80%>
<?
$pref='ikej';
//if ($_POST) {$search1=$HTTP_POST_VARS ['search1'];}

	@ $db= mysql_pconnect (DB_HOST,DB_USER,DB_PASS);
	if (!$db)
		{
		echo 'Не могу подключиться к базе данных.';
		exit;
		}
mysql_query('set names cp1251');


mysql_select_db(DB_NAME);


if  (!isset($username) ) $query ="select  * from ".$pref."_customers limit 0,200" ; else 
// выбираем заказы по пользователю
$query ="select * from 	".$pref."_orders as a, ".$pref."_customers as b".
		" where  b.customerID =a.customerID and ".
		" b.Login = '".$username."'";

//echo $query;
		
$result =mysql_query($query);

$num_results= mysql_num_rows($result);

echo '<p><b> Всего заказов у пользователя:</b>'.$num_results.'</p>';

echo '<table cellspacing="5" cellpadding="5"><tr valign="top" bgcolor="#faa"><td width="200"><b>Клиент</b></td><td><b>Дата заказа</b></td><td><b>Цена</b></td><td><b>Доставка и платеж</b></td></tr>';
for ($i=1; $i < $num_results+1; $i++)
{
	$row = mysql_fetch_array($result);

/*echo '<pre>';
print_r($row);
echo '</pre>';
*/

	$res = $i % 2;
	echo $res==0 ? '<tr>':  '<tr bgcolor="eeeeee">';

echo "<td><a href=\"#".$row['Login']."\"></a><br>Номер заказа <b>".$row['orderID']."</b><br>".
	$row['customer_firstname']." ".$row['customer_lastname']."<br> ".$row['customer_ip'].'<br> '.
	$row['customer_email'].'</td><td>'.$row['order_time'].'</td><td><nowarp>'.$row['order_amount'];
echo $row['order_discount']!=0 ? ' (Скидка: '.$row['order_discount']."%)": "";
echo $row['currency_code']=="RUR" ? "р.":" ".$row['currency_code']. 	
	" (+доставка:".$row['shipping_cost'].")</nowarp></td><td>".$row['shipping_type'].", ".$row['payment_type'];

echo '<br></td><td><b>Заказчик:</b> '.$row['billing_firstname'].' '.$row['billing_lastname'].'<br>'.
	$row['billing_country'].', '.$row['billing_zip'].', '.$row['billing_city'].', '.$row['billing_address'];

if ($row['billing_firstname'].$row['billing_lastname'].$row['billing_country'].$row['billing_zip'].$row['billing_city'].$row['billing_address']!=$row['shipping_firstname'].$row['shipping_lastname'].$row['shipping_country'].$row['shipping_zip'].$row['shipping_city'].$row['shipping_address'])
{
	echo '<br><br><b>Грузополучатель:</b> '.
		$row['shipping_firstname']. ' '.$row['shipping_lastname'].'<br>'.$row['shipping_country'].', '.
		$row['shipping_zip'].', '.$row['shipping_city'].', '.	$row['shipping_address'];
}
echo '</td></tr>';
}

echo '</table>';
?>		

	</div>
</div>
</body>
</html>