<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
__style__
<title>Печать компании</title>
</head>
<body>
<div class="print">
<div class="comapny_title"> {name}</div>
<div class="comapny_content">
<strong>Расположение:</strong> [country]{company_country}/[/country][city]{company_city}[/city]<br />
<strong>Тип компании:</strong> {company_type}<br />
<strong>Организационно-правовая форма:</strong> {OPF}<br />
[date_register]<strong>Дата регистрации компании:</strong> {date_register}<br />[/date_register]
[company_description]
{company_description}
<br />
[/company_description]
[logged]
<strong>Контактное лицо:</strong> {contact_person}<br />
<strong>Телефон:</strong> {phone}<br />
<strong>Веб-сайт:</strong> {site}<br />
<strong>Электронный адрес:</strong> {email}<br />
<strong>Физический адрес:</strong> {address}<br />
[/logged]
[not-logged]
Чтобы видеть контакты компании вы должны авторизоваться
[/not-logged]

<strong>Компания добавлена:</strong> {company_add_date}
</div>
</div>
</body>
</html>