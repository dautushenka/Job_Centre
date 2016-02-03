<div class="resume_add_title">[add]Добавление[/add][edit]Редактирование[/edit] резюме</div>
<div class="resume_add_content">
<table width="100%" cellpadding="0px" cellspacing="0px">
<tr>
	<td width="40%">
Сфера деятельности</td>
<td>{sphere}</td>
</tr>
<tr>
	<td>Специальность</td>
    <td>{specialty}</td>
</tr>
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
	<td>Опыт работы</td>
    <td>{experience}</td>
</tr>
<tr>
	<td>Студент</td>
    <td>{student}</td>
</tr>
<tr>
	<td>Образование</td>
    <td>{education}</td>
</tr>
<tr>
	<td>Пол</td>
    <td>{sex}</td>
</tr>
<tr>
	<td>Иностранный язык</td>
    <td>{language}</td>
</tr>
<tr>
	<td>Место работы</td>
    <td>{work_place}</td>
</tr>
<tr>
	<td>Тип работы</td>
    <td>{work_type}</td>
</tr>
<tr>
	<td>График работы</td>
    <td>{work_schedule}</td>
</tr>
<tr>
	<td>Возраст</td>
    <td><input type="text" value="{age}" name="age" size="10" /></td>
</tr>
<tr>
	<td>Зарплата</td>
    <td><input type="text" value="{salary_min}" name="salary_min" size="10" /> - <input type="text" value="{salary_max}" name="salary_max" size="10" />&nbsp; {currency}</td>
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
	<td>Фотография</td>
    <td><input type="file" name="photo" /></td>
</tr>
<tr>
	<td>Контактное лицо</td>
    <td>{contact_person}</td>
</tr>
<tr>
	<td>Электронный адрес</td>
    <td>{email}</td>
</tr>
<tr>
	<td>Телефон</td>
    <td>{phone}</td>
</tr>
[count_day]
                        <tr>
							<td width="40%">Срок размещения</td>
							<td style="padding:5px;">{count_day}</td>
						</tr>
[/count_day]
[extend]
                        <tr>
							<td width="40%">Продлить вакансию на:</td>
							<td style="padding:5px;">{count_extend}</td>
						</tr>
[/extend]
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