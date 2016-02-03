{sort} &nbsp; [rss]<img border="0px" src="{THEME}/job/images/rss.gif" title="RSS поток по текущим критериям поиска" />[/rss]
{pages}
<table width="100%" cellpadding="0px" cellspacing="0px">
	<tr>
[row_vacancy separator="</tr><tr>" count="2"]
<td width="50%" class="vacancy {moder_class}">
<table width="100%" cellpadding="0px" cellspacing="0px" height="100%">
<tr>
	<td colspan="3"><a class="inside_ajax" href="{vacancy_url}" ><div class="vacancy_title">{specialty} ({sphere})</div></a></td>
</tr>
<tr>
[logo]
<td class="vacancies_td_logo">
<a class="inside_ajax" href="{vacancy_url}" ><img border="0px" title="{company}" src="{logo_url}" /></a>
</td>
[/logo]
	<td>
<strong>Расположение:</strong> [country]{country}/[/country][city]{city}[/city]<br />
[salary]<strong>Зарплата:</strong> {salary}<br />[/salary]
[sex]<strong>Пол:</strong> {sex}<br />[/sex]
[age]<strong>Возраст:</strong> {age}<br />[/age]
[company]<strong>Компания:</strong> <a class="content_ajax" href="{company_url}">{company}</a>[/company]
[no_company]
<strong>Телефон:</strong> {phone}<br />
<strong>Контактное лицо:</strong> {contact_person} <br />
<strong>E-mail:</strong> {email}
[/no_company]
</td>
<td>
[work_type]<strong>Тип работы:</strong> {work_type}<br />[/work_type]
[work_schedule]<strong>График работы:</strong> {work_schedule}<br />[/work_schedule]
[work_place]<strong>Место работы:</strong> {work_place}<br />[/work_place]
[education]<strong>Образование:</strong> {education}<br />[/education]
[language]<strong>Иностранный язык:</strong> {language}<br />[/language]
[student]<strong>Студент:</strong> {student}<br />[/student]
[experience]<strong>Опыт работы:</strong> {experience}[/experience]
</td>
</tr>
[description]
<tr>
	<td colspan="2">
    {description}
    </td>
</tr>
[/description]
<tr>
	<td colspan="3" style="text-align:right; height:100%; vertical-align:bottom">
    [edit]Редактировать[/edit] &nbsp;{favorites} &nbsp;&nbsp;Добавлена {add_date}
    </td>
</tr>
</table>
</td>
[/row_vacancy]
</tr>
</table>
{pages}
{sort}