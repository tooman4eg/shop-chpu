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

echo '<table cellspacing=5><tr valign=top><td><b>№</b></td><td width=200><b>ФИО</b></td><td><b>Дата заказа</b></td><td><b>IP</b></td><td width="150"><b>Email</b></td><td><b>Стоимость доставки </b></td><td><b>Цена</b></td><td><b>Доставка и платеж</b></td></tr>';
for ($i=1; $i < $num_results+1; $i++)
{
$row = mysql_fetch_array($result);

$res=$i%2;

if ($res===0) echo '<tr>'; 
 else echo '<tr bgcolor="eeeeee">';

echo '<td>';
echo htmlspecialchars (stripslashes($row['orderID']));
echo '</td><td>';
echo '<a href="#'.htmlspecialchars (stripslashes($row['Login'])).'"></a>';
echo htmlspecialchars (stripslashes($row['customer_firstname']));
echo ' ';
echo htmlspecialchars (stripslashes($row['customer_lastname']));
echo '</td><td>';
echo htmlspecialchars (stripslashes($row['order_time']));
echo '</td><td>';
echo htmlspecialchars (stripslashes($row['customer_ip']));
echo '</td><td>';
echo htmlspecialchars (stripslashes($row['customer_email']));
echo '</td><td>';

//echo htmlspecialchars (stripslashes($row['reg_field_value']));
//echo '</td><td>';


echo htmlspecialchars (stripslashes($row['shipping_cost']));
echo '</td><td><nowarp>';
echo htmlspecialchars (stripslashes($row['order_amount']));
echo ' ';
echo htmlspecialchars (stripslashes($row['currency_code']));

 	
echo ' (Скидка: ';
echo htmlspecialchars (stripslashes($row['order_discount']));

echo '%)</nowarp></td><td><b>Тип доставки:</b>';
echo htmlspecialchars (stripslashes($row['shipping_type']));

echo '<br><b>Вариант оплаты:</b>';
echo htmlspecialchars (stripslashes($row['payment_type']));

echo '</td><td><b>Заказчик:</b> ';
echo htmlspecialchars (stripslashes($row['billing_firstname']));
echo ' ';
echo htmlspecialchars (stripslashes($row['billing_lastname']));
echo '<br>';
echo htmlspecialchars (stripslashes($row['billing_country']));
echo ', ';
echo htmlspecialchars (stripslashes($row['billing_zip']));
echo ', ';
echo htmlspecialchars (stripslashes($row['billing_city']));
echo ', ';
echo htmlspecialchars (stripslashes($row['billing_address']));

echo '<br><br><b>Грузополучатель:</b> ';
echo htmlspecialchars (stripslashes($row['shipping_firstname']));
echo ' ';
echo htmlspecialchars (stripslashes($row['shipping_lastname']));
echo '<br>';
echo htmlspecialchars (stripslashes($row['shipping_country']));
echo ', ';
echo htmlspecialchars (stripslashes($row['shipping_zip']));
echo ', ';
echo htmlspecialchars (stripslashes($row['shipping_city']));
echo ', ';
echo htmlspecialchars (stripslashes($row['shipping_address']));
echo '</td></tr>';
}
echo '</table>';



?>		


	</div>
</div>
</body>
</html>