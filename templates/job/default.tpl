<script type="text/javascript">
$().ready(function()
				   {
					   $('#tabl_vac tr::nth-child(even), #tabl_res tr::nth-child(even)').addClass('even');
				   });
</script>


<div class="main">
<table id="tabl_vac" width="100%" cellpadding="0px" cellspacing="0px">
<tr>
	<th>
    	Последние вакансии
    </th>
    <th>
    	Зарплата
    </th>
    <th>
    	Компания
    </th>
</tr>
[row_vacancy separator="" count="1"]
	<tr>
<td width="50%">
<a class="inside_ajax" href="{vacancy_url}" >{specialty} ({sphere})</a>
</td>
<td>
{salary}
</td>
<td>
[company]<a href="{company_url}">{company}</a>[/company]
[no_company]
{contact_person}
[/no_company]
</td>
</tr>
[/row_vacancy]
</table>
<br />
<br />
<table id="tabl_res" width="100%" cellpadding="0px" cellspacing="0px">
<tr>
	<th>
    	Последние резюме
    </th>
    <th>
    	Зарплата
    </th>
    <th>
		Город
    </th>
</tr>
[row_resume separator="" count="1"]
	<tr>
<td width="50%">
<a href="{resume_url}" >{specialty} ({sphere})</a>
</td>
<td>
{salary}
</td>
<td>
[country]{country}/[/country]{city}
</td>
</tr>
[/row_resume]
</table>
</div>