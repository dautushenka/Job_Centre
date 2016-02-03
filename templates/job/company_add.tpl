<div class="company_add_title">[add]Добавление[/add][edit]Редактирование[/edit] компании</div>
<div class="company_add_content">
<table width="100%" cellpadding="0px" cellspacing="0px">
[country]
<tr>
	<td>Страна</td>
    <td>{country}</td>
</tr>[/country]
[city]
<tr>
	<td>Город</td>
    <td>{city}</td>
</tr>[/city]
<tr>
	<td>Название компании</td>
    <td>{name}</td>
</tr>
<tr>
	<td>Альтернативное название (латинские символы)</td>
    <td>{alt_name}</td>
</tr>
<tr>
	<td>Тип компании</td>
    <td>{company_type}</td>
</tr>
<tr>
	<td>ОПФ (Организационно-правовая форма)</td>
    <td>{OPF}</td>
</tr>
<tr>
	<td>Дата регистрации компании</td>
    <td>{date_register}</td>
</tr>
<tr>
	<td>Описание</td>
    <td><textarea name="description" style="width:460px; height:160px" />{description}</textarea></td>
</tr>
<tr>
	<td></td>
    <td>{xfields}</td>
</tr>
<tr>
	<td>Логотип</td>
    <td><input type="file" name="logo" /></td>
</tr>
<tr>
	<td>Контактное лицо</td>
    <td>{contact_person}</td>
</tr>
<tr>
	<td>Телефон</td>
    <td>{phone}</td>
</tr>
<tr>
	<td>Веб-сайт</td>
    <td>{site}</td>
</tr>
<tr>
	<td>Электронный адрес</td>
    <td>{email}</td>
</tr>
<tr>
	<td>Физический адрес</td>
    <td>{address}</td>
</tr>
[register]
                          <tr>
                              <td width="40%" height="25">Логин:</td>
                              <td>{user_name}</td>
                            </tr>
                            <tr>
                              <td width="40%" height="25">Пароль:</td>
                              <td>{password}</td>
                            </tr>
                            <tr>
                              <td width="40%" height="25">Повторите пароль:</td>
                              <td>{password_confirm}</td>
                            </tr>
[/register]
[code]
                            <tr>
                              <td align="center" colspan="2" height="25"><strong>Подтверждение кода безопасности</strong></td>
                            </tr>
                            <tr>
                              <td width="40%" height="25">Код безопасности:</td>
                                  <td>{code}</td>
                            </tr>
                            <tr>
                              <td width="40%" height="25">Введите код:</td>
                              <td><input type="text" name="sec_code" style="width:115px" class="f_input" /></td>
                            </tr>      
[/code]
</table>
<input type="submit" value="[add]Добавить[/add][edit]Сохранить[/edit]" /></form>
</div>