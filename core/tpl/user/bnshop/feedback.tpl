{* Шаблон формы обратной связи *}
{include file=&quot;header.tpl.html&quot; header=$smarty.const.STRING_FEEDBACK}
<table class="adn"><tr>

<td class="cbt vleft">{$smarty.const.STRING_FEEDBACK_DESCRIPTION}</td></tr></table>
{if $sent eq NULL}
<table class="adn"><tr><td class="hdbtop vleft">
{if $error ne NULL}<div class="vcent error cattop">{if $error eq 2}{$smarty.const.ERR_WRONG_CCODE}{elseif $error eq 3}{$smarty.const.ERR_WRONG_POST}{else}{$smarty.const.FEEDBACK_ERROR_FILL_IN_FORM}{/if}</div>{/if}


<form name="formfeedback" id="formfeedback" method="post" action="index.php">
<table class="adw">
                    <tr>
                        <td>
                            <div style="text-align:center;"><strong><span style="font-size: 160%"><span style="color: #000000"></span></span></strong></div>
                            <div style="text-align:center;"><span style="font-size: 160%"><span style="color: #000000"></span></span></div>
                            <div style="text-align:center;"><strong><span style="font-size: 100%"><span style="font-size: 130%; color: #ff0000">
                                <p><strong><span style="font-size: 10pt; font-family: Tahoma"><span style="color: #000000">
<br>С 2008 года интернет-магазин BNShop.ru занимается продажей игровых и карнавальных костюмов, клубной одежды, эротического белья и аксессуаров в г. Москве и по России. 
<br>Уважаемые покупатели интернет-магазина! Для того, чтобы сделать покупку, Вам нужно зарегистрироваться. В дальнейшем это поможет Вам отследить ваш заказ, получить новости о поступление товара и т.д. Внимательно ознакомьтесь&nbsp;с </span><a href="page_2.html"><span style="color: #ff0000">Правилами доставки и оплаты</span></a><span style="color: #000000">. <span>&nbsp;</span>
Если у вас возникли вопросы обращайтесь к нам по </span></span></strong><strong><span style="font-size: 8.5pt; font-family: Tahoma"><a href="mailto:info@bnshop.ru"><span style="font-size: 10pt; color: #ff0000">E-mail </span></a></span></strong><strong><span style="font-size: 10pt; color: #000000; font-family: Tahoma">или по тел.</span></strong><strong><span style="font-size: 8.5pt; font-family: Tahoma"> </span></strong><strong><span style="font-size: 10pt; color: #000080; font-family: Tahoma">
8 (495)6600499,&nbsp; с 10 до 18 ч. (будние дни, кроме выходных и праздников). <br /></span></strong><strong><span style="font-size: 10pt; color: #ff0000; font-family: Tahoma">
Заказы по телефону не принимаются.
                        </td>
						Наши реквизиты
						
                    </tr>
<tr><td>{$smarty.const.FEEDBACK_CUSTOMER_NAME}</td></tr>
<tr><td style="height: 3px;"></td></tr>
<tr><td><input name="customer_name" type="text" class="inbr" maxlength="80" style="width: 200px;" value="{$customer_name|replace:"\"":"&quot;"}"></td></tr>
<tr><td style="height: 8px;"></td></tr>
<tr><td>{$smarty.const.CUSTOMER_EMAIL}</td></tr>
<tr><td style="height: 3px;"></td></tr>
<tr><td><input name="customer_email" type="text" class="inbr" maxlength="80" style="width: 200px;" value="{$customer_email|replace:"\"":"&quot;"}"></td></tr>
<tr><td style="height: 8px;"></td></tr>
<tr><td>{$smarty.const.FEEDBACK_CUSTOMER_MESSAGE_SUBJ}</td></tr>
<tr><td style="height: 3px;"></td></tr>
<tr><td><input name="message_subject" type="text" class="inbr" style="width: 300px;" maxlength="200" value="{$message_subject|replace:"\"":"&quot;"}"></td></tr>
<tr><td style="height: 8px;"></td></tr>
<tr><td>{$smarty.const.FEEDBACK_CUSTOMER_MESSAGE_TEXT}</td></tr>
<tr><td style="height: 3px;"></td></tr>
<tr><td><textarea name="message_text" style="width: 360px; height: 120px;">{$message_text|replace:"<":"<"}
</textarea></td></tr>
{if $smarty.const.CONF_ENABLE_CONFIRMATION_CODE eq 1}
<tr><td style="height: 8px;"></td></tr>
<tr><td><img src="imgval.php?{php}echo session_name();{/php}={php}echo session_id();{/php}" alt="code"></td></tr>
<tr><td style="height: 3px;"></td></tr>
<tr><td><input name="fConfirmationCode" value="{$smarty.const.STR_ENTER_CCODE}" type="text" class="inbr" style="width: 200px; color: #aaaaaa;" onfocus="if(this.value=='{$smarty.const.STR_ENTER_CCODE}')
                        {literal}
                        {this.style.color='#000000';this.value='';}
                        {/literal}" onblur="if(this.value=='')
                        {literal}{{/literal}this.style.color='#aaaaaa';this.value='{$smarty.const.STR_ENTER_CCODE}'{literal}}{/literal}"></td>
</tr>
{/if}
<tr><td><input type="hidden" name="send" value="yes"><input type="hidden" name="feedback" value="yes"></td></tr>
</table>
</form>
</td></tr></table>
<table class="adn"><tr><td class="hdbot">[ <a href="#" onclick="document.getElementById('formfeedback').submit(); return false">{$smarty.const.OK_BUTTON3}</a>&nbsp;|&nbsp;<a href="#" onclick="document.getElementById('formfeedback').reset(); return false">{$smarty.const.RESET_BUTTON}</a> ]</td></tr></table>
{else}
<table class="adn"><tr><td class="vcent oki cattop">{$smarty.const.FEEDBACK_SENT_SUCCESSFULLY}</td></tr></table>
<table class="adn"><tr><td class="hdbot">&nbsp;</td></tr></table>
{/if}