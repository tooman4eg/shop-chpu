<?php
include ('core/config/connect.inc.php');
?>
<html>
<head>
<title>����������������� ������������</title>
</head>
<body>
<div width="100%">
	<div width=20% align="left">
		<form action="#" method="post">
		<input name ="search1" type="text">
		<input type="submit" value="�����">
		</form>
	</div>

	<div width=80%>
                                                                   
<?
$pref=DB_PRFX;
if ($_POST){ $search1=$_POST['search1'];} else $search1="";
	@ $db= mysql_pconnect (DB_HOST,DB_USER,DB_PASS);
	if (!$db)
		{
		echo '�� ���� ������������ � ���� ������.';
		exit;
		}
	//else {echo "������� ��������";}
mysql_query('set names cp1251');
//mysql_query('set names utf-8');

mysql_select_db(DB_NAME);

/*echo"
<pre> ";
print_r($_POST);
echo "</pre>";
*/




$query ="select DISTINCT a.Login, a.first_name, a.cust_password, a.Email, a.last_name, a.customerID, a.custgroupID, b.address, c.customer_ip
from ".$pref."customers as a, ".$pref."customer_addresses as b, ".$pref."orders AS c where ".
		" b.addressID =a.addressID and  c.customerID = a.customerID and  (".		
		" c.customer_firstname like '%".$search1."%' or ".
		" c.customer_lastname like '%".$search1."%' or ".
		" c.customer_email like '%".$search1."%' or ".
		" c.shipping_firstname like '%".$search1."%' or ".
		" c.shipping_lastname like '%".$search1."%' or ".
		" c.shipping_country like '%".$search1."%' or ".
		" c.shipping_state like '%".$search1."%' or ".		
		" c.shipping_city like '%".$search1."%' or ".
		" c.shipping_address like '%".$search1."%' or ".
		" c.billing_firstname like '%".$search1."%' or ".
		" c.billing_lastname like '%".$search1."%' or ".
		" c.billing_country like '%".$search1."%' or ".
		" c.billing_state like '%".$search1."%' or ".		
		" c.billing_city like '%".$search1."%' or ".
		" c.customer_ip like '%".$search1."%' or ".
		" c.billing_address like '%".$search1."%' or ".
		" a.Login like '%".$search1."%' or ".
		" a.first_name like '%".$search1."%' or ".
		" a.cust_password like '%".$search1."%' or ".
		" a.Email like '%".$search1."%' or ".
		" a.last_name like '%".$search1."%' ) limit 0,200";


echo "<!--".$query."-->";
echo '</br>����� ������: ';
echo $search1;


		
$result =mysql_query($query);

$num_results= mysql_num_rows($result);

echo '<p><b>������� �����������:</b>'.$num_results.'</p>';

echo '<table cellspacing="5" cellpadding="3"><tr valign="top" bgcolor="#ccc"><td><b>�</b></td><td width=200><b>������</b></td><td width="150"><b>IP ������</b></td><td><b>������</b></td><td><b>�����</b></td><!--td><b>�������</b></td--><td><b>�����</b></td></tr>';
for ($i=0; $i < $num_results; $i++)
{
$row = mysql_fetch_array($result);


$queryrep="select *  from  ".$pref."orders where  customerID=".$row['customerID']."";
$order=mysql_query ($queryrep);
$num_order= mysql_num_rows($order);


$res=$i%2;

if ($res===0) echo '<tr>'; 
 else echo '<tr bgcolor="eeeeee">';
echo '<td>'.($i+1).'</td><td> '.
	"<a name=\"".$row['Login']."\"></a>".
	"<a href=\"/au2.php?username=".$row['Login']."\">".$row['Login']."</a><br>".$row['Email'].
	"<br>".$row['first_name']." ".$row['last_name']."<br>";
echo htmlspecialchars (stripslashes($row['customer_ip']));

echo "</td><td>".$row['customer_ip']."</td><td>";
$blacklist=false;
$cgID =$row['custgroupID'];
if ($cgID == null)	{
		echo '--';
	}
else
	{
	$querygroup="select *  from  ".$pref."custgroups as d where  d.custgroupID=".$cgID."";
	
	
	$usergroup=mysql_query ($querygroup);
	$num_gr= mysql_num_rows($usergroup);
	if ($num_gr>0) {
		$ug = mysql_fetch_array($usergroup);
		$ug_name= $ug['custgroup_name'];
		//echo $ug_name;
		if (strpos($ug_name, "����� ������")>0)
			{$blacklist= true;
				//���� ������� ���������				
			$black_list_reason_SQL="select A.admin_comment, B.status_comment from ".$pref."orders as A,  ".$pref."order_status_changelog  as B  where A.customerID=".$row['customerID']." and B.orderID=A.orderID  and (A.admin_comment!='' or B.status_comment!='')";
			//echo  $black_list_reason_SQL;
			$get_admin_comm = mysql_query ($black_list_reason_SQL);
			$admin_comm_num = mysql_num_rows($get_admin_comm);
			if  ($admin_comm_num>0)
				{
					$admin_comm = mysql_fetch_array($get_admin_comm);	
					
					if (isset($admin_comm['admin_comment']))	{
						echo "<br>���������� ������ <i>".$admin_comm['admin_comment']."</i>";
					}
					if (isset($admin_comm['status_comment']))	{
						echo "<br>���������� ��� ����� ������� <i> ".$admin_comm['status_comment']."</i>";
					}
				}
			}	
			else  			{
				$ug_name ="";
}			
		}
} 
echo '</td><td>';

if ($num_order >0) 
	{
		if ($blacklist)	{
			echo "<font color=\"red\">� ������ ������!</font>";
		}
		else
			echo '<font color="green">��������</font>'; 
	}
else 
	
	echo '<font color="grey">�� ���������</font>'; 
	echo '<br>';

$queryuser="select *  from  ".$pref."customers where  cust_password='".$row['cust_password']."' limit 1,10";


$user1=mysql_query ($queryuser);
$num_user= mysql_num_rows($user1);
if ($num_user>1) 
	{
		echo '<br><b>������������ � �������� ��������</b><br>';
		for ($j=0; $j < $num_user; $j++)	{
			$users1 = mysql_fetch_array($user1);
			echo $users1['Login']."<br>";		
		}
	}

echo "</td><td>".$row['address']."</td></tr>";
}
echo '</table>';


?>		


	</div>
</div>
</body>
</html>