<?PHP

require dirname(__FILE__) . '/engine/Core_modules/InstallUpdate.php';

require_once(ROOT_DIR.'/language/'.$config['langs'].'/job.lng');
require(ENGINE_DIR . "/Core_modules/TemplateAdmin.php");
require(ENGINE_DIR . "/job/classes/JobCore.php");
require(ENGINE_DIR . "/job/classes/company.php");
require(ENGINE_DIR . "/job/classes/resume.php");
require(ENGINE_DIR . "/job/classes/vacancy.php");
require(ENGINE_DIR . "/job/version.php");
require(ENGINE_DIR . "/job/functions.php");
require(ENGINE_DIR . '/job/constants.php');

$tpl = new TemplateAdmin();
$tpl->echo = FALSE;
		
$version = VERSION;
$module_name = 'Job Centre (Биржа труда)';
$year = 2007;
$licence = /*licadm*/'.'/*/licadm*/;
$var = 'job_cfg';
$file= 'job_conf.php';
$dle = 5.3;
$php = 5.0;
$mysql = 4.1;
$image_patch = "engine/job/images/install";
$lic = new Licencing($licence);
$important_files = array();

$text_main = <<<HTML
<b>Основные возможности:</b>
- Скрипт ставиться как отдельный модуль и требует минимум изменений файлов скрипта
- Модуль имеет гибкие настройки, которые можно проводить из админки или путём изменения шаблонов
- Использует для хранения данных MySQL
- Мощная система безопасности
- Минимальная нагрузка на базу данных (от 0 до 5 запросов)
- Использование продвинутой технологии AJAX.
- Поддержка ЧПУ (человеко-понятный URL) позволяет использовать тему статьи или любой другой текст в URL броузера, благодаря этому индексация сайта в поисковых системах будет более качественной (необходим modrewrite)
- Возможность отключение ЧПУ
- Общая статистика данных
- Создание сфер и специальностей
- Создание стран и городов
- СОздание/редактирование/удаление вакансий авторами (настраивается)
- Создание/редактирование/удаление резюме авторами (настраивается)
- Создание/редактирование/удаление компаний администраторами компаний (настраивается)
- Разные права для каждой группы и администраторов компаний
- Возможность использование только некотрые сущности из стран/регонов/городов
- Поиск вакансий/резюме по любым параметрам
- Мощныйые, полностью настраивающиеся фильтры
- Поддержка нескольких языков
- Вывод новых вакансий и резюме в отдельном блоке
- Добавление, редактирование и удаление вакансий и резюме
- Ограничение сущестования на сайте
- Кэширование практически всех страниц, что намного поднимает скорость работы скрипта и уменьшает нагрузку на SQL сервер
- Возможность загружать логотипы для компаний
- Сортировка по нескольким параметрам
- RSS канал практически по любым параметрам
- Добавление пользователями с последующей модерацией или без (настройки)
- Администратор может назначать группы пользователей которые смогут модерировать данные на сайте
- Полностью задействован тег &lt;title&gt;
- Встараивается в стандартный SpeedBar скрипта
- Возможность редактирование объявлений пользоателями (настройки)
- Практически каждому тегу {tag} соотвествует свой блок [tag][/tag], для убирание текста, если значение отсутствует
- У каждого пользователя есть личный кабинет с его добавлениями, блокнотом (избранные)
- Модуль может быть установлен на гавную страницу сайта
- Возможность указывать обязательные поля при добавлении
- Функция печати
- Функция продливания размещения на сайте для разрешенных групп (настройки)
- Фукнция регистрации гостя во время добавления (настрофки)
- И многое другое
HTML;
$text_main = nl2br($text_main);

if ($_POST['type'] == "update")
{
	$obj = new install_update($module_name, $version, array(), $licence, $db, $image_patch);
	$obj->year = $year;
	require(ENGINE_DIR . "/data/" . $file);
	$module_config = $$var;
	
	switch ($module_config['version_id'])
	{
		case VERSION:
			$obj->Finish("<div style=\"text-align:center;font-size:150%;\">Вы используете актуальную версию скрипта. Обновление не требуется</div>");
			break;
			
		case '1.0.0':
		case '1.0.0b':
	       $to_version = VERSION;
            $obj->steps_array = array(
                                    "ChangeLog",
                                    "Проверка хостинга",
                                    "Работа с базой данных",
                                    "Завершение обновления"
                                    );
                                    $ChangeLog = <<<TEXT
<b>Обновление до версии $to_version</b>
            
[+] - Дополнительные поля
[+] - Страница вывода списка сфер для вакансий и резюме со счетчиком
[+] - Ввод специальности самостоятельно пользователем
[+] - Поддержка новых версий ДЛЕ

TEXT;
                                    $ChangeLog = nl2br($ChangeLog);
                                    $important_files = array(
                        './install.php',
                        './engine/data/',
                                    );
                                    
                                    $table_schema[PREFIX . "_job_vacancies"] = "ALTER TABLE `" . PREFIX . "_job_vacancies` add column `xfields` MEDIUMTEXT";
                                    $table_schema[PREFIX . "_job_companies"] = "ALTER TABLE `" . PREFIX . "_job_companies` add column `xfields` MEDIUMTEXT";
                                    $table_schema[PREFIX . "_job_resumes"] = "ALTER TABLE `" . PREFIX . "_job_resumes` add column `xfields` MEDIUMTEXT";
                                    
                                    $table_schema[PREFIX . "_job_fields"] = "CREATE TABLE `" . PREFIX . "_job_fields` (                        
                                                                                 `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                                                  `ctype` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
                                                                                  `title` VARCHAR(110) NOT NULL DEFAULT '',
                                                                                  `description` VARCHAR(255) NOT NULL DEFAULT '',
                                                                                  `type` ENUM('text','select','textarea','checkbox') DEFAULT NULL,
                                                                                  `data` MEDIUMTEXT,
                                                                                  `required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                                                                                  `regex` VARCHAR(55) NOT NULL DEFAULT '',
                                                                                  `default` VARCHAR(255) DEFAULT NULL,
                                                                                  `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                                                                                  PRIMARY KEY (`id`),
                                                                                  KEY `ctype` (`ctype`)
                                                                       ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
                                    
                                    $finish_text = <<<HTML
<div style="text-align:center;">Обновление модуля до версии $to_version прошло успешно.</div>
HTML;
                                    switch (intval($_POST['step']))
                                    {
                                        case 0:
                                            $obj->Main($ChangeLog, 'Начать обновление');
                                            break;

                                        case 1:
                                            $obj->CheckHost($important_files, $dle, $php, $mysql);
                                            break;
                                            
                                        case 2:
                                            $obj->Database($table_schema);
                                            break;
                                                
                                        case 3:
                                            $obj->ChangeVersion($file, $var, $module_config, array(), $to_version);
                                            $obj->Finish($finish_text, $to_version);
                                            break;
                                    }
		    break;
			
		default:
			$text = <<<TEXT
<b>Не известная версия модуля. Переустановите модуль.</b>
TEXT;
			$obj->OtherPage($text);
			break;
	}
}
else 
{
	$title = array(
					"Описание модуля",
					"Лицензионное соглашение",
					"Проверка хостинга",
					"Страны/Города",
					"Создание файла настроек",
					"Заполнение базы",
					"Работа с базой данных",
					"Завершение установки"
				);
				
	$obj = new install_update($module_name, $version, $title, $licence, $db, $image_patch);
	$obj->year = $year;

	switch ($_POST['step'])
	{
	    case 1:
	        $head_licence = <<<HTML
Пожалуйста внимательно прочитайте и примите пользовательское соглашение по использованию модуля "$module_name".
HTML;

	        $text_licence = <<<HTML
Покупатель имеет право:</b><ul><li>Изменять дизайн и структуру программного продукта в соответствии с нуждами своего сайта.</li><br /><li>Производить и распространять инструкции по созданным Вами модификациям шаблонов и языковых файлов, если в них будет иметься указание на оригинального разработчика программного продукта до Ваших модификаций.</li><br /><li>Переносить программный продукт на другой сайт после обязательного уведомления меня об этом, а также полного удаления скрипта с предыдущего сайта.</li><br /></ul><br /><b>Покупатель не имеет право:</b><br /><ul><li>Передавать права на использование интеграции третьим лицам, кроме случаев, перечисленных выше в нашем соглашении.</li><br /><li>Изменять структуру программных кодов, функции программы или создавать родственные продукты, базирующиеся на нашем программном коде</li><br /><li>Использовать более одной копии модуля <b>$module_name</b> по одной лицензии</li><br /><li>Рекламировать, продавать или публиковать на своем сайте пиратские копии модуля</li><br /><li>Распространять или содействовать распространению нелицензионных копий модуля <b>$module_name</b></li><br /></ul>
HTML;
	        
			$obj->Licence($head_licence, $text_licence);
			
			
		case 2:
		    $important_files = array(
						'./install.php',
						'./engine/data/',
						'./uploads/company_logo/',
						'./uploads/resume_photo/',
						'./engine/job/cache/',
						'./engine/job/cache/array/',
						'./engine/job/logs/database.log',
						'./engine/job/logs/errors.log',
						'./engine/job/logs/HandlerErrors.log'
						);
		    
			$obj->CheckHost($important_files, $dle, $php, $mysql);
			
		case 3:
		    $text_use = <<<HTML
<div style="padding:15px;" >
<input type="checkbox" value="1" name="use_country" style="vertical-align:middle" checked="checked" />&nbsp; Использовать страны в модуле<br/>
<input style="vertical-align:middle" name="use_city" type="checkbox" checked="checked" />&nbsp; Использовать города в модуле
</div>	
HTML;
        	$use_status = "Изменить эти параметры в сторону увеления потом будет невозможно";
        	
        	function CheckUse(install_update $obj)
        	{
        	    if (!empty($_POST['use_country']))
        	    {
        	        $obj->SetAdditionalField('use_country', 1);
        		}
        		if (!empty($_POST['use_city']))
        		{
        		    $obj->SetAdditionalField('use_city', 1);
        		}
        		
        		return false;
        	}
        	
		    $obj->OtherPage($text_use, $use_status, 'CheckUse');
		    
        case 4:
            $job_cfg = array(
                            'vacacny_only_company' => "0",
                            'vacancy_allow_add' => array (
                            '0' => "1",
                            '1' => "2",
                            '2' => "3",
                            '3' => "4",
                            '4' => "5",
                            ),
                            'vacancy_add_capcha' => array (
                            '0' => "5",
                            ),
                            'vacancy_add_no_moder' => array (
                            '0' => "-1",
                            '1' => "1",
                            '2' => "2",
                            '3' => "3",
                            ),
                            'vacancy_allow_edit' => array (
                            '0' => "-1",
                            '1' => "1",
                            '2' => "2",
                            '3' => "3",
                            '4' => "4",
                            ),
                            'vacancy_allow_del' => array (
                            '0' => "-1",
                            '1' => "1",
                            '2' => "2",
                            '3' => "3",
                            '4' => "4",
                            ),
                            'vacancy_allow_change_exp' => array (
                            '0' => "-1",
                            '1' => "1",
                            '2' => "2",
                            '3' => "3",
                            ),
                            'vacancy_allow_extend' => array (
                            '0' => "-1",
                            '1' => "1",
                            '2' => "2",
                            '3' => "3",
                            ),
                            'vacancy_need_field' => array (
                            'age_min' => "age_min",
                            'age_max' => "age_max",
                            'salary_min' => "salary_min",
                            'salary_max' => "salary_max",
                            'description' => "description",
                            ),
                            'vacancy_allow_reg' => "1",
                            'vacancy_allow_show_filter' => "1",
                            'vacancy_default_sort' => "add_date",
                            'vacancy_default_sub_sort' => "DESC",
                            'vacancy_default_day' => "10",
                            'vacancy_count_per_page' => "20",
                            'block_last_vacancies' => "1",
                            'block_last_vacancies_count' => "10",
                            'RSS_vacancies_count' => "10",
                            'resume_allow_add' => array (
                            '0' => "1",
                            '1' => "2",
                            '2' => "3",
                            '3' => "4",
                            '4' => "5",
                            ),
                            'resume_add_capcha' => array (
                            '0' => "5",
                            ),
                            'resume_add_no_moder' => array (
                            '0' => "1",
                            '1' => "2",
                            '2' => "3",
                            ),
                            'resume_allow_edit' => array (
                            '0' => "1",
                            '1' => "2",
                            '2' => "3",
                            '3' => "4",
                            ),
                            'resume_allow_del' => array (
                            '0' => "1",
                            '1' => "2",
                            '2' => "3",
                            '3' => "4",
                            ),
                            'resume_allow_change_exp' => array (
                            '0' => "1",
                            '1' => "2",
                            '2' => "3",
                            ),
                            'resume_allow_extend' => array (
                            '0' => "1",
                            '1' => "2",
                            '2' => "3",
                            ),
                            'resume_need_field' => array (
                            'age' => "age",
                            'salary_min' => "salary_min",
                            'salary_max' => "salary_max",
                            'description' => "description",
                            ),
                            'resume_allow_one_resume' => "0",
                            'resume_allow_reg' => "1",
                            'resume_allow_show_filter' => "1",
                            'resume_default_sort' => "add_date",
                            'resume_default_sub_sort' => "DESC",
                            'resume_default_day' => "10",
                            'resume_count_per_page' => "20",
                            'resume_watermark' => "0",
                            'resume_photo_size_byte' => "1000",
                            'resume_photo_size' => "150",
                            'resume_size_for_watermark' => "200",
                            'block_last_resumes' => "1",
                            'block_last_resumes_count' => "10",
                            'RSS_resumes_count' => "10",
                            'company_allow_manage' => array (
                            '0' => "1",
                            '1' => "2",
                            '2' => "3",
                            '3' => "4",
                            '4' => "5",
                            ),
                            'company_add_capcha' => array (
                            '0' => "5",
                            ),
                            'company_allow_reg' => "1",
                            'company_allow_del' => "0",
                            'company_show_vacancies' => "1",
                            'company_need_field' => array (
                            '0' => "contact_person",
                            '1' => "description",
                            '2' => "logo",
                            '3' => "email",
                            '4' => "address",
                            ),
                            'company_per_page' => "30",
                            'company_watermark' => "1",
                            'company_logo_size_byte' => "500",
                            'company_logo_size' => "200",
                            'company_size_for_watermark' => "180",
                            'general_allow_module' => "1",
                            'general_name_module' => "job",
                            'general_mod_rewrite' => "1",
                            'general_AJAX' => "1",
                            'general_cache' => "1",
                            'general_main_page' => "0",
                            'general_RSS' => "1",
                            'general_inform' => "0",
                            'general_email' => "",
                            'currency' => array (
                            'USD' => "1",
                            'RUR' => "31",
                            'EUR' => "0.68",
                            ),
                            'general_moderator_edit' => array (
                            '0' => "1",
                            '1' => "2",
                            '2' => "3",
                            ),
                            'general_moderator_del' => array (
                            '0' => "1",
                            '1' => "2",
                            ),
                            'general_show_moder' => "0",
                            'general_image_quality' => "75",
                            'general_count_last_vacancy' => "10",
                            'general_count_last_resume' => "10",
                            'general_allow_statistic' => "1",
                            'general_debug' => "0",
                            'use_country' => (empty($obj->fields['use_country']))?0:1,
                            'use_city' => (empty($obj->fields['use_city']))?0:1,
                            );
        
            $job = new Spacer($job_cfg, $lang_job);
            
            $obj->setting_menu = array(
				$job->lang['vacancy_setting'] => '/engine/job/images/admin/submenu/vacancy_set.png', 
				$job->lang['resume_setting'] => '/engine/job/images/admin/submenu/resume_set.png', 
				$job->lang['company_setting'] => '/engine/job/images/admin/submenu/company_set.png', 
				$job->lang['general_title']=> '/engine/job/images/admin/submenu/settings2.png'
				);
        	
        	require(ENGINE_DIR . "/job/admin/settings_array.php");
        	
			$obj->Settings($settings_array, $job_cfg, $var, $file);
			
			$obj->setting_menu = array();
			
		case 5:
		    if (!empty($obj->fields['use_country']) || !empty($obj->fields['use_city']))
		    {
		        $fill_text = '';
		        
		        if (!empty($obj->fields['use_country']))
		        {
            		$fill_text .= '<input style="vertical-align:middle" type="checkbox" value="1" OnClick="UseCountry(this)" name="fill_country" />&nbsp; Заполнить базу данных стран (105 стран)<br/>';
		        }
            		
            	if (!empty($obj->fields['use_city']))
            	{
            	    if (empty($obj->fields['use_country']))
            	    {
            	        $fill_text .= "<input style=\"vertical-align:middle\" type=\"checkbox\" value=\"1\" name=\"fill_city\" /> Заполнить БД городов cтраны : <select name='country_id'><option value=''>Города всех стран</option>\n";
            	        
            	        include_once(ENGINE_DIR . "/job/import/import_countries.php");
            	        
            	        foreach ($countries as $id=>$country)
            	        {
            	            $fill_text .= "<option value='$id'>{$country['name']}</option>\n";
            	        }
            	        $fill_text .= "</select><br/>";
            	        
            	    }
            	    else 
            	    {
                        $fill_text .= '<input style="vertical-align:middle" type="checkbox" OnClick="UseCity(this)" value="1" name="fill_city" />&nbsp; Заполнить базу данныше городов (10912 городов)<br/>';
            	    }
            	}
            		
            	$fill_database = <<<HTML
            <div style="padding:15px;" >$fill_text
            <input type="checkbox" value="1" name="fill_sphere" OnClick="UseSphere(this)" style="vertical-align:middle" />&nbsp; Заполнить базу данных сфер деятельности (24 сферы)<br/>
            <input type="checkbox" value="1" name="fill_specialty" OnClick="UseSpecialty(this)" style="vertical-align:middle" />&nbsp; Заполнить базу данных специальностей (1550 специальностей)
            </div>	
            <script type="text/javascript">
            var form = document.form;
            function UseCity(obj)
            {
            	if (obj.checked)
            	{
            		if (form.fill_country)
            			form.fill_country.checked = true;
            	}
            }
            function UseCountry(obj)
            {
            	if (!obj.checked)
            	{
            		if (form.fill_city)
            			form.fill_city.checked = false;
            	}
            }
            function UseSpecialty(obj)
            {
            	if (form.fill_sphere)
            		form.fill_sphere.checked = true;
            }
            function UseSphere(obj)
            {
            	if (!obj.checked)
            	{
            		if (form.fill_specialty)
            			form.fill_specialty.checked = false;
            	}
            }
            </script>
HTML;
            
            	$fill_database_status = "Изменить эти параметры в сторону увеления потом будет невозможно";
            	
            	function CheckFillDataBase(install_update $obj)
            	{
            	    foreach (array('fill_country', 'fill_sphere', 'fill_specialty') as $value)
            	    {
                	    if (!empty($_POST[$value]))
                		{
                		    $obj->SetAdditionalField($value, 1);
                		}
            	    }
            	    
            		if (!empty($_POST['fill_city']) && $obj->fields['use_city'])
            		{
            		    $obj->SetAdditionalField('fill_city', 1);
            		    
            		    if (!empty($_POST['country_id']) && empty($obj->fields['use_country']))
            		    {
            		        $obj->SetAdditionalField('country_id', $_POST['country_id']);
            		    }
            		}
            		
            		return false;
            	}
            	
		        $obj->OtherPage($fill_database, $fill_database_status, 'CheckFillDataBase');
		    }
		    
		    
        case 6:
            if (!empty($obj->fields['use_country']))
            {
                $table_schema[PREFIX . "_job_countries"] = "CREATE TABLE `" . PREFIX . "_job_countries` (                        
                             `id` int(10) unsigned NOT NULL auto_increment,  
                             `name` varchar(110) NOT NULL default '',        
                             `alt_name` varchar(110) NOT NULL default '',    
                             PRIMARY KEY  (`id`)
                            ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
            }
            
            if (!empty($obj->fields['use_city']))
            {
                $table_schema[PREFIX . "_job_cities"] = "CREATE TABLE `" . PREFIX . "_job_cities` (                      
                  `id` int(10) unsigned NOT NULL auto_increment,  
                  `country_id` int(10) unsigned NOT NULL,         
                  `name` varchar(110) NOT NULL,                   
                  `alt_name` varchar(110) NOT NULL,               
                  PRIMARY KEY  (`id`),                            
                  KEY `country_id` (`country_id`)     
                ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
		    }
		    
		    $table_schema[PREFIX . "_job_companies"] = "CREATE TABLE `" . PREFIX . "_job_companies` (                        
                             `company_id` int(10) unsigned NOT NULL auto_increment,           
                             `user_id` int(10) unsigned NOT NULL default '0',                 
                             `user_name` varchar(110) NOT NULL default '',                    
                             `name` varchar(110) NOT NULL default '',                         
                             `alt_name` varchar(110) NOT NULL default '',                     
                             `company_type` enum('PA','EM') default NULL,                     
                             `OPF` varchar(10) NOT NULL default '',                           
                             `contact_person` varchar(110) NOT NULL default '',               
                             `description` text NOT NULL,                  
                             `logo` varchar(255) NOT NULL default '',                         
                             `country_id` int(10) unsigned NOT NULL default '0',              
                             `city_id` int(10) unsigned NOT NULL default '0',                 
                             `phone` varchar(255) NOT NULL default '',                        
                             `site` varchar(110) NOT NULL default '',                         
                             `email` varchar(110) NOT NULL default '',                        
                             `address` varchar(110) NOT NULL default '',                      
                             `date_register` date default NULL,
                             `xfields` mediumtext,
                             `add_date` int(10) unsigned NOT NULL default '0',                
                             PRIMARY KEY  (`company_id`),                                     
                             KEY `Index` (`company_type`,`country_id`,`city_id`)
                   ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
        	
        	$table_schema[PREFIX . "_job_resumes"] = "CREATE TABLE `" . PREFIX . "_job_resumes` (                        
                             `id` int(10) unsigned NOT NULL auto_increment,                                                                                   
                           `user_id` int(10) unsigned NOT NULL,                                                                                             
                           `user_name` varchar(110) NOT NULL,                                                                                               
                           `photo` varchar(110) NOT NULL,                                                                                               
                           `country_id` int(10) unsigned NOT NULL default '0',                                                                              
                           `city_id` int(10) unsigned NOT NULL default '0',                                                                                 
                           `sphere_id` int(10) unsigned NOT NULL default '0',                                                                               
                           `specialty_id` int(10) unsigned NOT NULL default '0',                                                                            
                           `view_count` smallint(5) unsigned NOT NULL default '0',                                                                          
                           `experience` smallint(5) unsigned NOT NULL default '0',                                                                          
                           `student` smallint(5) unsigned NOT NULL default '0',                                                                             
                           `education` smallint(5) unsigned NOT NULL default '0',                                                                           
                           `language` smallint(5) unsigned NOT NULL default '0',                                                                            
                           `work_place` smallint(5) unsigned NOT NULL default '0',                                                                          
                           `work_type` smallint(5) unsigned NOT NULL default '0',                                                                           
                           `work_schedule` smallint(5) unsigned NOT NULL default '0',                                                                       
                           `sex` enum('M','W','0') NOT NULL default '0',                                                                                    
                           `age` tinyint(4) unsigned NOT NULL default '0',                                                                                  
                           `salary_min` int(10) unsigned NOT NULL default '0',                                                                              
                           `salary_min_search` int(10) NOT NULL default '0',                                                                                
                           `salary_max` int(10) unsigned NOT NULL default '0',                                                                              
                           `salary_max_search` int(10) unsigned NOT NULL default '0',                                                                       
                           `currency` varchar(5) NOT NULL default 'USD',                                                                                    
                           `description` text NOT NULL,                                                                                                     
                           `contact_person` varchar(110) NOT NULL default '',                                                                               
                           `email` varchar(110) NOT NULL default '',                                                                                        
                           `phone` varchar(110) NOT NULL default '',
                           `xfields` mediumtext,                                                                                        
                           `add_date` int(10) NOT NULL default '0',                                                                                         
                           `date_exp` int(10) NOT NULL default '0',                                                                                         
                           `allow_site` tinyint(1) NOT NULL default '0',                                                                                    
                           PRIMARY KEY  (`id`),                                                                                                             
                           KEY `Index` (`country_id`,`city_id`,`sphere_id`,`specialty_id`,`salary_min_search`,`salary_max_search`,`add_date`,`allow_site`)
                   ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
        	
        	$table_schema[PREFIX . "_job_specialties"] = "CREATE TABLE `" . PREFIX . "_job_specialties` (                        
                             `id` int(10) unsigned NOT NULL auto_increment,  
                               `sphere_id` int(10) unsigned NOT NULL,          
                               `name` varchar(110) NOT NULL,                   
                               `alt_name` varchar(110) NOT NULL,               
                               PRIMARY KEY  (`id`),                            
                               KEY `sphere_id` (`sphere_id`)
                   ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
        	
        	$table_schema[PREFIX . "_job_spheres"] = "CREATE TABLE `" . PREFIX . "_job_spheres` (                        
                             `id` int(10) unsigned NOT NULL auto_increment,  
                           `name` varchar(110) NOT NULL default '',        
                           `alt_name` varchar(110) NOT NULL default '',    
                           PRIMARY KEY  (`id`) 
                   ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
        	
        	$table_schema[PREFIX . "_job_vacancies"] = "CREATE TABLE `" . PREFIX . "_job_vacancies` (                        
                             `id` int(10) unsigned NOT NULL auto_increment,                                           
                             `user_id` int(10) unsigned NOT NULL,                                                     
                             `user_name` varchar(110) NOT NULL default '',                                            
                             `country_id` int(10) unsigned NOT NULL default '0',                                      
                             `city_id` int(10) unsigned NOT NULL default '0',                                         
                             `sphere_id` int(10) unsigned NOT NULL default '0',                                       
                             `specialty_id` int(10) unsigned NOT NULL default '0',                                    
                             `company_id` int(10) unsigned NOT NULL,                                                  
                             `view_count` smallint(5) unsigned NOT NULL default '0',                                  
                             `experience` smallint(5) unsigned NOT NULL,                                              
                             `student` smallint(5) unsigned NOT NULL,                                                 
                             `education` smallint(5) unsigned NOT NULL,                                               
                             `language` smallint(5) unsigned NOT NULL,                                                
                             `work_place` smallint(5) unsigned NOT NULL,                                              
                             `work_type` smallint(5) unsigned NOT NULL,                                               
                             `work_schedule` smallint(5) unsigned NOT NULL,                                           
                             `sex` enum('M','W','0') NOT NULL default '0',                                            
                             `age_min` tinyint(4) unsigned NOT NULL default '0',                                      
                             `age_max` tinyint(4) unsigned NOT NULL default '0',                                      
                             `salary_min` int(10) unsigned NOT NULL default '0',                                      
                             `salary_min_search` int(10) NOT NULL default '0',                                        
                             `salary_max` int(10) unsigned NOT NULL default '0',                                      
                             `salary_max_search` int(10) unsigned NOT NULL default '0',                               
                             `currency` varchar(5) NOT NULL default 'USD',                                            
                             `description` text NOT NULL,                                                             
                             `contact_person` varchar(110) NOT NULL,                                                  
                             `email` varchar(110) NOT NULL default '',                                                
                             `phone` varchar(110) NOT NULL default '',
                             `xfields` mediumtext,                                                
                             `add_date` int(10) NOT NULL,                                                             
                             `date_exp` int(10) NOT NULL,                                                             
                             `allow_site` tinyint(1) NOT NULL default '0',                                            
                             PRIMARY KEY  (`id`),                                                                     
                             KEY `Index` (`country_id`,`city_id`,`sphere_id`,`company_id`,`salary_min`,`salary_max`)
                   ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
        	
        	$table_schema[PREFIX . "_job_fields"] = "CREATE TABLE `" . PREFIX . "_job_fields` (                        
                                                     `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                      `ctype` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
                                                      `title` VARCHAR(110) NOT NULL DEFAULT '',
                                                      `description` VARCHAR(255) NOT NULL DEFAULT '',
                                                      `type` ENUM('text','select','textarea','checkbox') DEFAULT NULL,
                                                      `data` MEDIUMTEXT,
                                                      `required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                                                      `regex` VARCHAR(55) NOT NULL DEFAULT '',
                                                      `default` VARCHAR(255) DEFAULT NULL,
                                                      `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                                                      PRIMARY KEY (`id`),
                                                      KEY `ctype` (`ctype`)
                                           ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
                    
        	if (!empty($obj->fields['fill_country']))
        	{
        	    include_once(ENGINE_DIR . "/job/import/import_countries.php");
        	    
        	    $values = '';
        	    foreach ($countries as $id=>$country)
        	    {
        	        $name = $db->safesql($country['name']);
        	        $alt_name = $db->safesql($country['alt_name']);
        	        
        	        if ($values)
        	        {
        	            $values .= ", ";
        	        }
        	        
        	        $values .= "($id, '$name', '$alt_name')";
        	    }
        	    
        	    $table_schema[] = "INSERT INTO `" . PREFIX . "_job_countries` VALUES $values";
        	}
        	
        	if (!empty($obj->fields['fill_city']))
        	{
        	    ini_set("memory_limit","20M");
        	    
        	    include_once(ENGINE_DIR . "/job/import/import_cities.php");
        	    
        	    if (empty($obj->fields['use_country']) && !empty($obj->fields['country_id']))
        	    {
        	        $values = '';
        	        foreach ($cities[$obj->fields['country_id']] as $id=>$city)
        	        {
        	           $name = $db->safesql($city['name']);
        	           $alt_name = $db->safesql($city['alt_name']);
        	           
        	           if ($values)
            	       {
            	           $values .= ", ";
            	       }
            	       
            	       $values .= "($id, {$city['country_id']}, '$name', '$alt_name')";
        	        }
        	        
        	        $table_schema[] = "INSERT IGNORE `" . PREFIX . "_job_cities` VALUES $values";
                }
                else 
                {
                    foreach ($cities as $c_id=>$cities_array)
                    {
                        $values = '';
                        foreach ($cities_array as $id=>$city)
                        {
                            $name = $db->safesql($city['name']);
        	                $alt_name = $db->safesql($city['alt_name']);
        	                
        	                if ($values)
                	        {
                	            $values .= ", ";
                	        }
                	        
                	        $values .= "($id, $c_id, '$name', '$alt_name')";
                        }
                        
                        $table_schema[] = "INSERT IGNORE `" . PREFIX . "_job_cities` VALUES $values";
                    }
                }
        	}
        	
        	if (!empty($obj->fields['fill_specialty']))
        	{
        	    include_once(ENGINE_DIR . "/job/import/import_spheres.php");
        	    include_once(ENGINE_DIR . "/job/import/import_specialties.php");
        	}
        	else if(!empty($obj->fields['fill_sphere']))
        	{
        	    include_once(ENGINE_DIR . "/job/import/import_spheres.php");
        	}
        	
        	if ($config['version_id'] >= 8.2)
        	{
        	    $table_schema[] = "INSERT IGNORE `" . PREFIX . "_admin_sections` (allow_groups, name, icon, title, descr) VALUES ('all', 'job', 'job.png', 'Job Centre (Биржа труда)', 'Job Centre (Биржа труда)')";
        	}
        	
			$obj->Database($table_schema);
			
		case 7:
		    $text_finish = <<<TEXT
	<div style="font-size:120%;text-align:center">Благодарим вас за покупку модуля. Надеемся что работа с ним доставит Вам только удовольствие!!! Все возникшие вопросы вы можете найти в документации или задать их на форуме поддержки <a href="http://forum.kaliostro.net/" >http://forum.kaliostro.net/</a> . </div>
TEXT;
			$obj->Finish($text_finish);
			break;
			
		default:
			if (file_exists(ENGINE_DIR.'/data/'.$file) && empty($_POST['type']))
			{
				require(ENGINE_DIR . "/data/" . $file);
				$config = $$var;
				$obj->steps_array = array();
				$obj->steps_array[] = "Описание модуля";
				
				switch ($config['version_id'])
				{
					case VERSION:
						$obj->steps_array[] = VERSION;
						
					default:
						$obj->steps_array[] = "Завершение обновления";
				}
				$obj->SetType("update", "Начать обновление");
				$obj->Main($text_main, "Начать обновление");
			}
			else 
			{
				$obj->SetType("install");
				$obj->Main($text_main, "Начать установку");
			}
			
			break;
	}
}

?>
