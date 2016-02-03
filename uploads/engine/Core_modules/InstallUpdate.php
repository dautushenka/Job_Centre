<?PHP
/*
=====================================================
 Install & Update file v1.4.0
-----------------------------------------------------
 http://kaliostro.net/
-----------------------------------------------------
 Copyright (c) 2007-2009 kaliostro ICQ: 415-74-19
=====================================================
 Данный код защищен авторскими правами
=====================================================
*/

@ob_start(); 
@ob_implicit_flush(true);
set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_NOTICE);
@ini_set('zend.ze1_compatibility_mode', 0);
session_start();
define('DATALIFEENGINE', true);
define('ENGINE_DIR', dirname (__FILE__) . "/..");
define('ROOT_DIR', ENGINE_DIR.'/..');

include_once ENGINE_DIR.'/data/config.php';

if ($config['version_id'] < 6.3)
	require_once ENGINE_DIR.'/inc/mysql.php';
else
	require_once ENGINE_DIR.'/classes/mysql.php';
	
require_once ENGINE_DIR.'/data/dbconfig.php';
require_once(ENGINE_DIR.'/modules/functions.php');
require_once(ENGINE_DIR.'/modules/sitelogin.php');
//require_once(ENGINE_DIR.'/Core_modules/functions.php');

if (!defined('COLLATE'))
	define('COLLATE', 'cp1251');
if (!defined('USERPREFIX'))
	define('COLLATE', constant("PREFIX"));
	
define("INSTALL", true);

class install_update
{
	private $image_patch='';
	private $db;
	private $step = '';
	private $module_name = '';
	private $version = '';
	private $button = "Продолжить>>";
	private $buttons = array();
	private $error = array();
	private $hidden_array = array();
	private $handler;
	private $finish = false;
	private $debug = false;
	
	public $year = '';
	public $steps_array = array();
	public $fields = array();
	public $setting_menu = array();
	public $show_setting_menu = false;
	
	public function __construct($module_name, $version, $steps_array, $licence, db &$db, $image_patch)
	{
		$this->module_name = $module_name;
		$this->image_patch = $image_patch;
		$this->version = $version;
		$this->steps_array = $steps_array;
		$this->step = (intval($_POST['step']))?intval($_POST['step']):0;
		$this->hidden_array['step'] = $this->step;
		$this->hidden_array['type'] = ($_POST['type'])?$_POST['type']:'';
		
		if (!empty($_POST['fields']))
		{
			$this->hidden_array['fields'] = $_POST['fields'];
			$this->fields = $_POST['fields'];
		}
		
		if (!empty($_GET['debug']))
		{
		    $this->SetAdditionalField('debug', 1);
		}
		
		if (!empty($this->fields['debug']))
		{
		    $this->debug = true;
		}
		
		if ($db)
			$this->db =& $db;
		
		if ($licence)
			$this->CheckLicence($licence);
		
		if (!$GLOBALS['is_logged'] || $GLOBALS['member_id']['user_group'] != 1)
			$this->Authorization();
			
		if ($_POST['action'] == "finish" && @unlink(__FILE__))
		{
			$this->finish = true;
			$this->button = false;
			$this->show(<<<TEXT
<div style="text-align:center;font-size:120%;">Файл установки(обноления) автоматически удалился. Переход на главную страницу сайта...<br />
<a href="{$GLOBALS['config']['http_home_url']}" >Нажмите здесь, если ваш обозреватель не поддерживает автоматической переадресации.</a></div>
TEXT
,false);
		}
		elseif ($_POST['action'] == "finish")
		{
		    $this->button = false;
		    
			$this->show(<<<TEXT
<div style="text-align:center;font-size:150%;">Удалите файл установки(обновления) из корня сайта</div>
TEXT
,false);
		}
	}
	
	public function SetType($type, $button=false)
	{
		$this->hidden_array['type'] = $type;
		
		if ($type == 'update')
		{
			$this->buttons['type'] = "Переустановить";
			$this->hidden_array['step'] = -1;
		}
		if ($button)
			$this->button = $button;
	}
	
	public function SetAdditionalField($name, $value)
	{
		$this->hidden_array['fields'][$name] = $value;
		$this->fields[$name] = $value;
	}
	
	private function CheckLogin()
	{
		if ($_SESSION['dle_log'] >= 5)
		{
			die("Hacking attempt!");
		}
		
		$GLOBALS['user_group'] = get_vars ("usergroup");

		if (!$GLOBALS['user_group'])
		{
			$GLOBALS['user_group'] = array ();
		
			$this->db->query("SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC");
		
			while($row = $this->db->get_row())
			{
				$GLOBALS['user_group'][$row['id']] = array ();
				
				foreach ($row as $key => $value)
		     	{
		       		$GLOBALS['user_group'][$row['id']][$key] = $value;
		     	}
			}
			
			set_vars ("usergroup", $GLOBALS['user_group']);
			$this->db->free();
		}

		$hash_pass = md5(md5($_POST['password']));
		$login = $this->db->safesql($_POST['name']);
		
		if ($GLOBALS['member_id'] = $this->db->super_query("SELECT * FROM " . USERPREFIX . "_users WHERE name='{$login}' AND password='{$hash_pass}'"))
		{
		    if (!defined('DOMAIN'))
		    {
		        define( 'DOMAIN', "." . $_SERVER['HTTP_HOST']);
		    }
			
			setcookie("dle_password", md5($_POST['password']), time()+3600*24*365, "/", DOMAIN, NULL, TRUE);
			
	        @session_register('dle_password');
	        @session_register('member_lasttime');
	
	        if ($GLOBALS['config']['version_id'] < 7.2)
	        {
	        	@session_register('dle_name');
	        	setcookie("dle_name", $_POST['name'],time()+3600*24*365, "/", DOMAIN, NULL, TRUE);
	        	$_SESSION['dle_name'] = $_POST['name'];
			}
			else 
			{
				@session_register('dle_user_id');
				setcookie("dle_user_id", $GLOBALS['member_id']['user_id'], time()+3600*24*365, "/", DOMAIN, NULL, TRUE);
        		$_SESSION['dle_user_id'] = $GLOBALS['member_id']['user_id'];
			}
			
	        $_SESSION['dle_password']    = md5($_POST['password']);
	
			$_SESSION['dle_log'] = 0;
			
			return true;
		}
		else 
		{
			$_SESSION['dle_log']++;
			return false;
		}
	}
	
	private function Authorization()
	{
		if (empty($_POST['name']) || empty($_POST['password']) || !$this->CheckLogin())
		{
			if (isset($_SESSION['dle_log']))
			{
				if ($_SESSION['dle_log'] > 1)
					$count_login = " уже " . $_SESSION['dle_log'] . " раза из 5 возможных";
					
				$status_report = "Вы не вошли$count_login, попробуйте еще раз, если забыли пароль, то можно его востановить пройдя по след ссылки <a href=\"/index.php?do=lostpassword\" >Востановить пароль</a><br/>";
			}
			
			$text_full = "<table><tr><td align=right><b>Логин : </b> </td><td align=left height=\"20px\"><input class=edit type=edit name='name' value=''></td></tr><tr><td aling=right><b>Пароль : </b> </td><td height=\"20px\" align=left ><input class=edit type=password name='password' value=''></td></tr><tr><td></td><td><input class=buttons type='submit' value='Войти'></td></tr></table>";
			
			$this->button = false;
			$this->steps_array = array("Авторизация");
			$this->hidden_array['step'] = 0;
			$this->show($text_full, $status_report , 'module_error');
		}
	}
	
	private function CheckLicence($licence)
	{
			if (!eregi($licence, $_SERVER['HTTP_HOST']) && 
			    !eregi("localhost", $_SERVER['HTTP_HOST']) &&
			    !eregi($_SERVER['SERVER_ADDR'], $_SERVER['HTTP_HOST'])
			     )
			{
				if ($GLOBALS['config']['version_id'] < 6.3)
				{
					require_once ENGINE_DIR.'/inc/mail.class.php';
				}
				else
					require_once ENGINE_DIR.'/classes/mail.class.php';
					
				$mail = new dle_mail ($GLOBALS['config']);
				
				$text  = "Лиц домен:" . $licence . "\n";
				$text .= "Текущей домен: " . $_SERVER['HTTP_HOST'];
				
				$mail->send ("support@kaliostro.net", "Нарушение лицензии", $text);
				
				$this->FatalError("Вы используете не лицензионную версию модуля \"$this->module_name\".<br/>За информацией обращайтесь на форум <a href=\"http://forum.kaliostro.net/\" >http://forum.kaliostro.net/</a> или ICQ: 415-74-19");
			}
	}
	
	public function Main($description, $button=false)
	{
		if ($button)
			$this->button = $button;
		$this->hidden_array['step']++;
		$this->show($description);
	}
	
	public function Licence($licence, $licence_text)
	{
		if ($_POST['action'] == 'check_eula')
		{
			if (intval($_POST['eula']))
			{
				$this->step++;
				$this->hidden_array['step']++;
				return true;
			}
			else 
				$this->error[] = 'Если не примете соглашение лицензии, вы не имеете право устанавливать модуль';
		}
		$this->button .= '" disabled="disabled';
		$text = <<<HTML
<table width="100%">
    <tr>
        <td style="padding:2px;">$licence<br /><br /><div style="height: 300px; border: 1px solid #76774C; background-color: #FDFDD3; padding: 5px; overflow: auto;">$licence_text</div>
		<input onclick="agree();" type='checkbox' name='eula' value=1 id='eula'><b><label for="eula">Я принимаю данное соглашение</label></b>
		<br />
</td>
    </tr>
</table>
<script type="text/javascript" >
<!--
function agree()
{
if (document.form.eula.checked == true)
{
document.form.button.disabled=false;
}
else
{
document.form.button.disabled=true;
}
}
-->
</script>
HTML;
		$this->hidden_array['action'] = "check_eula";
		$this->show($text, false, 'module_error');
	}
	
	public function CheckHost(array $important_files = array(), $dle=false, $php=false, $mysql=false)
	{
		global $config; 
		
		$chmod_errors = 0;
		$not_found_errors = 0;
		
		function ShowCheckRow($name, $value, $status)
		{
			$text_full ="<tr>
			         <td height=\"22\" class=\"tableborder main\">&nbsp;$name</td>
			         <td>&nbsp; $value</td>
			         <td>&nbsp; $status</td>
			         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";
			
			return $text_full;
		}
		
		if ($important_files)
		{
		    foreach($important_files as $file)
		    {
		
		        if(!file_exists($file))
		        {
		            $file_status = "<font color=red>не найден!</font>";
		            $not_found_errors ++;
		        }
		        elseif(is_writable($file))
		            $file_status = "<font color=green>разрешено</font>";
		        else
		        {
		            @chmod($file, 0777);
		            
		            if(is_writable($file))
		                $file_status = "<font color=green>разрешено</font>";
		            else
		            {
		                @chmod("$file", 0755);
		                
		                if(is_writable($file))
		                    $file_status = "<font color=green>разрешено</font>";
		                else
		                {
		                    $file_status = "<font color=red>запрещено</font>";
		                    $chmod_errors ++;
		                }
		            }
		        }
				$chmod_value = @decoct(@fileperms($file)) % 1000;
			
			    $text_full .= ShowCheckRow($file, $chmod_value, $file_status);
			}
		}
	
		if ($dle && version_compare($config['version_id'], $dle, ">"))
			$dle_v = "<font color=\"green\">" . $config['version_id'] . "</font><br/>";
		elseif ($dle)
		{
			$dle_v = "<font color=\"red\">" .$config['version_id']."</font>";
			$this->error[] = "Модуль не работает на версиях DLE ниже <font color=red >$dle</font>. У вас ". $config['version_id'];
		}
		
		if ($dle)
			 $text_full .= ShowCheckRow('DataLife Engine', $dle_v, $dle);
		
		$this->db->connect(DBUSER, DBPASS, DBNAME, DBHOST);

		if ($mysql && version_compare($this->db->mysql_version, $mysql, ">"))
			$sql = "<font color=\"green\">" . $this->db->mysql_version . "</font><br/>";
		elseif ($mysql)
		{
			$sql = "<font color=\"red\">" .$this->db->mysql_version."</font>";
			$this->error[] = "Данная версия базы данных не поддерживается.";
		}
		
		if ($mysql)
			 $text_full .= ShowCheckRow('MySQL', $sql, $mysql);
		
		if ($php && version_compare(phpversion(), $php, ">"))
			$php_ok = "<font color=\"green\">" . phpversion() . "</font><br/>";
		elseif ($php)
		{
			$php_ok = "<font color=\"red\">" .phpversion()."</font>";
			$this->error[] = "Данная версия PHP не поддерживается.";
		}
		
		if ($php)
			 $text_full .= ShowCheckRow('PHP', $php_ok, $php);
		
		if($chmod_errors > 0)
		{
			$this->error[] = "Запрещена запись в $chmod_errors файлов.<br />Вы должны выставить для папок CHMOD 777, для файлов CHMOD 666, используя ФТП-клиент.";
		}
		if($not_found_errors > 0)
		{
			$this->error[] = "$not_found_errors файлов не найдено!";
		}
			
		if(!$this->error)
		{
			$status_report = 'Проверка успешно завершена! Можете продолжить установку!';
			$this->hidden_array['step']++;
			$this->show($text_full, $status_report, "module_ok");
		}
		else
		{
			$this->button = "Обновить";
			$this->show($text_full, $status_report, "module_error");
		}
	}
	
	public function EditFiles($files_array)
	{
		
	}
	
	public function Settings(array $settings_array, array $default, $var='', $file='')
	{
		if (!file_exists(ENGINE_DIR . "/data/" . $file) || isset($_POST['rewrite']) || $_POST['action'] == 'save')
		{
			if ($_POST['action'] == "save" && ($save_con = $_POST['save_con']))
			{
				foreach ($settings_array as $setting)
				{
					if ($setting['regexp'] && !empty($setting['name']) && !preg_match($setting['regexp'], $save_con[$setting['name']]))
						$this->error[] = '"' . $setting['title'] . "\" -- Заполнено не верно";
				}
				if (!$this->error)
				{
					if ($default)
						$save_con = array_merge($default, $save_con);
					
					$save_con['version_id'] = $this->version;
					
					if (is_writable(ENGINE_DIR . "/data/"))
					{
					    $this->handler = fopen(ENGINE_DIR.'/data/'.$file, "w");
					    fwrite($this->handler, "<?PHP \n\n//$this->module_name Configurations\n\n\$$var = array (\n\n");
					    
					    $this->Save_conf($save_con);
					    fwrite($this->handler, ");\n\n?>");
					    fclose($this->handler);
					    $this->hidden_array['step']++;
					    $this->step++;
					    return ;
					}
					else 
						$this->error[] = "Папка <b>./engine/data/</b> не доступна для записи";
				}
			}
			
			$text = "<table width=\"100%\">";
			if ($settings_array)
			{
                $this->show_setting_menu = true;
   
			    $i = 0;
				foreach ($settings_array as $setting)
				{
				    if (is_array($setting) && empty($setting['title']))
				    {
				        $text .= "<tr id='SetBlock$i' style='display:none'><td><table width=\"100%\">";
				        foreach ($setting as $block_name=>$set)
				        {
				            if (empty($set['noinstall'])) 
				            {
				                $text .= $this->SettingRow($set['title'], $set['descr'], $set['setting']);
				            }
				        }
				        $text .= "</table></td></tr>";
				        $i++;
				    }
				    else if(empty($setting['noinstall']))
				    {
				        $text .= $this->SettingRow($setting['title'], $setting['descr'], $setting['setting']);
				    }
				}
			}
			$text .= "</table>";
			$this->hidden_array['action'] = "save";
			$this->button = "Сохранить";
			$this->show($text, false);
		}
		elseif (file_exists(ENGINE_DIR . "/data/" . $file) && isset($_POST['skip']))
		{
			$this->hidden_array['step']++;
			$this->step++;
			return ;
		}
		else 
		{
			$text = "<div style='text-align:center;'>Обнаружен файл конфигурации молуля. Ваши действия?<br /><br /><input class='buttons' style='padding:2px' type='submit' name='rewrite' value='Перезаписать' /> &nbsp;&nbsp;&nbsp;<input class='buttons' type='submit' name='skip' value='Оставить' style='padding:2px' /></div>";
			$this->button = false;
			$this->show($text, false);
		}
	}
	
	public function Database(array $table_schema)
	{
		$error = FALSE;
		$create_tables = array();
		$add_fields = array();
		$isset_tables = array();
		$isset_column = array();
		$text_full = '';
		$status = true;

		if ($table_schema)
		{
			foreach ($table_schema as $table=>$action)
			{
				if (preg_match('#CREATE#i', $action))
					$create_tables[] = $table;
				elseif (preg_match('#^ALTER TABLE +`?([^ `]+)`?#i', $action, $matche_table) && preg_match_all('#(ADD +(COLUMN +)?`?([\w\d]+)`? .+?)(,|$)#i', $action, $fields))
				{
				    if (is_numeric($table) && !empty($matche_table[1]))
				    {
				        $table = $matche_table[1];
				    }
				    
					foreach ($fields[3] as $key=>$field)
					{
						$add_fields[$table][$field] = $fields[1][$key];
					}
				}
			}
			
			if ($create_tables)
			{
				$table_resource = $this->db->query("SHOW TABLES", false);
				while ($row = $this->db->get_row($table_resource))
				{
					if (in_array(reset($row), $create_tables))
						$isset_tables[] = reset($row);
				}
			}
			
			if ($add_fields)
			{
				foreach ($add_fields as $table=>$fields)
				{
					$fileds_resource = $this->db->query("DESCRIBE " . $table, false);
					while ($row = $this->db->get_row($fileds_resource))
					{
						if (array_key_exists($row['Field'], $fields))
							$isset_column[$table][] = $row['Field'];
					}
				}
			}
			
			function ShowDBRow($desc, $status, $other = false)
			{
				$text_full = "<tr><td height=\"22\" class=\"tableborder main\">&nbsp;".$desc."</td>";
				
				if (!$other)
				{
					if ($status)
						$text_full .= "<td><font color=\"green\"><b>OK</b></font></td></tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=2></td></tr>";
					else 
						$text_full .= "<td><font color=\"red\"><b>NO</b></font></td></tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=2></td></tr>";
				}
				else 
					$text_full .= "<td>$status</td></tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=2></td></tr>";
					
				return $text_full;
			}
			
			if ((!$isset_column && !$isset_tables) || $_POST['action'] == "doDB")
			{
				$this->db->query("SET SQL_MODE=''", false);
				
				foreach ($table_schema as $table=>$action)
				{
					$execute = true;
					
					if (is_numeric($table))
					{
					    if (preg_match('#^INSERT (INTO|IGNORE) `?([^ `]+)`?( |\()#i', $action, $action_table))
					    {
					        $table = $action_table[2];
					    }
					    else if (preg_match('#^ALTER TABLE +`?([^ `]+)`?#i', $action, $matche_table))
					    {
					        $table = $matche_table[1];
					    }
					}
					
					if ($table && in_array($table, $isset_tables))
					{
						switch ($_POST['table_action'][$table])
						{
							case "recreate":
								$status = $this->db->query("DROP TABLE " . $table, false);
								$text_full .= ShowDBRow("Удаление таблици " . $table, $status);
								break;
								
							case "truncate":
								$status = $this->db->query("TRUNCATE " . $table, false);
								$text_full .= ShowDBRow("Очистка таблици " . $table, $status);
								$execute = false;
								break;
								
							default:
								$execute = false;
								break;
						}
					}
					
					if (array_key_exists($table, $isset_column))
					{
						foreach ($isset_column[$table] as $field)
						{
							if ($_POST['field_action'][$table][$field] == "truncate")
							{
								$status = $this->db->query("UPDATE `$table` SET $field=DEFAULT", false);
								$text_full .= ShowDBRow("Очистка поля $field таблици " . $table, $status);
							}
							unset($add_fields[$table][$field]);
						}
						if ($add_fields[$table])
							$action = "ALTER TABLE `$table` " . implode(", ", $add_fields[$table]);
						else 
							$execute = false;
					}
					
					if ($execute)
					{
						if ($table && in_array($table, $create_tables))
							$desc = "Создание таблици " . $table;
						else if (array_key_exists($table, $add_fields))
							$desc = "Добавление поля(ей) " . implode(", ", array_keys($add_fields[$table])) . " в таблицу " . $table;
						else if (preg_match('#^DROP#i', $action))
							$desc = "Удаление таблици " . $table;
						else if (preg_match('#^INSERT (INTO|IGNORE) `?([^ `]+)`?( |\()#i', $action, $action_table))
							$desc = "Вставка данных в таблицу " . $action_table[2];
						else if (preg_match('#^UPDATE `?([^ `]+)`?#i', $action, $action_table))
                            $desc = "Обновление данных таблици " . $action_table[1];
						else 
						    $desc = "Другие изменения";
						
						if ($this->debug)
						{
						    $status = $this->db->query($action);
						}
						else 
						{
						    $status = $this->db->query($action, false);
						}
						
						$text_full .= ShowDBRow($desc, $status);
						
						if (!$status)
							$error = true;
					}
				}
				
				if ($error)
				{
					$this->error[] = "Работа с базой данной произошла с ошибкой";
					$this->show($text_full, false, "error");
				}
				else 
				{
					$this->hidden_array['step']++;
					$this->show($text_full, "Работа с базой завершена успешно", "module_ok");
				}
			}
			else 
			{
				if ($isset_tables)
				{
					$text_full .= ShowDBRow("<b>Следующие таблици уже существуют</b>", '', true);
					$action_array = array(
										 "0" => "Оставить",
										 "recreate" => "Пересоздать",
										 "truncate" => "Очистить",
										);
					foreach ($isset_tables as $table)
						$text_full .=  ShowDBRow($table, $this->Selection($action_array, "table_action[$table]"), true);
				}
				
				if ($isset_column)
				{
					$text_full .= ShowDBRow("<b>Следующие поля уже существуют</b>", '', true);
					$action_array = array(
										 "0" => "Оставить",
										 "truncate" => "Очистить",
										);
					foreach ($isset_column as $table=>$fields)
					{
						$text_full .= ShowDBRow("<span style='margin-left:15px;'><b>Таблица $table</b><span>", '', true);
						foreach ($fields as $field)
							$text_full .=  ShowDBRow("<span style='margin-left:30px;'>$field<span>", $this->Selection($action_array, "field_action[$table][$field]"), true);
					}
				}
				$this->hidden_array['action'] = 'doDB';
				
				$this->show($text_full, "Укажите действия при работе с имеющимися данными", "module_info");
			}
		}
		
	}
	
	public function OtherPage($text, $status = '', $checkfunction = '')
	{
		if ($_POST['action'] == "check")
		{
			if (!$checkfunction || !function_exists($checkfunction) || !($error = $checkfunction($this)))
			{
				$this->step++;
				$this->hidden_array['step']++;
				return true;
			}
			else 
				$this->error = $error;
		}
		$this->hidden_array['action'] = 'check';
		$this->show($text, $status);
	}
	
	public function ChangeVersion($file, $var, $config, $new_value = array(), $version = '')
	{
		if ($new_value)
			$config = array_merge($new_value, $config);
			
		if (!$version)
			$version = $this->version;
			
		$config['version_id'] = $version;
			
		$this->handler = fopen(ENGINE_DIR.'/data/'.$file, "w");
		fwrite($this->handler, "<?PHP \n\n//$this->module_name Configurations\n\n\$$var = array (\n\n");
					    
		$this->Save_conf($config);
		fwrite($this->handler, ");\n\n?>");
		fclose($this->handler);
		return ;
	}
	
	public function Finish($text, $version = '')
	{
		if ($version && $version != $this->version)
			$this->hidden_array['step'] = '';
		else 
			$this->hidden_array['action'] = "finish";
			
		$this->button = "Закончить";
		$this->show($text, false);
	}
	
	private function Save_conf($save_con, $array=false) 
	{
		foreach($save_con as $name => $value)
		{
		  	if (is_array($value))
		  	{
		  		fwrite($this->handler, "'{$name}' => array (\n\n"); 
		  		$this->save_conf($value, true);
		  	}
		  	else
		  	{
		    	$value = strtr($value, '"', "'");
		    	fwrite($this->handler, "'{$name}' => \"".stripslashes($value)."\",\n\n");
		  	}
	    }
		if ($array) fwrite($this->handler, "),\n\n");
	}
	
	private function OpenTable()
	{
		echo <<<HTML
		<div style="padding-top:5px;padding-bottom:2px;">
		<table width="100%">
	    <tr>
	        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
	        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
	        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
	    </tr>
	    <tr>
	        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
	        <td style="padding:5px;" bgcolor="#FFFFFF">
HTML;
	}
	
	private function OpenSubtable($title='', $script="")
	{
		echo <<<HTML
		<table width="100%" $script >
		    <tr>
		        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$title}</div></td>
		    </tr>
		</table>
		<div class="unterline"></div>
		<table width="100%">
		<tr><td>
HTML;
	}
	
	private function CloseSubtable()
	{
		echo <<<HTML
			</td>
		</tr>
		</table>
HTML;
	}
	
	private function CloseTable()
	{
		echo <<<HTML
		</td>
	        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
	    </tr>
	    <tr>
	        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
	        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
	        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
	    </tr>
		</table>
		</div>
HTML;
	}
	
	private function SettingRow($title="", $description="", $field="")
	{
		return "<tr>
		<td style=\"padding:4px\" class=\"option\">
	    <b>$title</b><br /><span class=small>$description</span>
	    <td width=394 align=middle >
	    $field
		</tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=2></td></tr>";
	}
	
	private function OpenForm($action = "", $script='')
	{
		echo <<<HTML
		<form action="$action" method="POST" name="form" $script >
HTML;
		if ($this->hidden_array)
		{
			foreach ($this->hidden_array as $key=>$value)
			{
				if (is_array($value))
				{
					foreach ($value as $key2=>$value2)
						echo "<input type=\"hidden\" name=\"{$key}[{$key2}]\" value=\"$value2\" />\n";
				}
				else 
					echo "<input type=\"hidden\" name=\"$key\" value=\"$value\" />\n";
			}
		}
	}
	
	private function CloseForm()
	{
		echo "</form>";
	}
	
	public function Selection($options = array(), $name = "", $selected = "", $script = "")
	{
		if (!count($options) || $name == "") return false;
		
		$output =  "<select name=\"$name\" $script >\r\n";
        foreach($options as $value=>$description)
        {
          $output .= "<option value=\"$value\"";
          if($selected == $value){ $output .= " selected "; }
          $output .= ">$description</option>\n";
        }
        $output .= "</select>";
      
        return $output;
	}
	
	public function ShowSettingMenu()
	{
		$this->OpenTable();
		echo "<table cellpadding=\"0\" cellspacing=\"0\" id=\"setting_menu\" width=\"100%\"><tr>";
		$i = 0;
		foreach ($this->setting_menu as $name=>$image)
		{
			echo "<td align='center'><a href=\"#\" OnClick=\"ShowBlock('SetBlock$i');return false;\" title=\"$name\"><img src=\"$image\" border=\"0\" /></a></td>\n";
			$i++;
		}
		$i--;
		echo "</tr></table>";
		echo <<<JS
	   <script type='text/javascript' >
	   function ShowBlock(name)
	   {
	       for (i = 0; ; i++)
	       {
	           if (block = document.getElementById('SetBlock' + i))
	           {
	               block.style.display = 'none';
	           }
	           else
	           {
	               break;
	           }
	       }
	       block = document.getElementById(name);
	       block.style.display = '';
	   }
	   window.onload = function()
	   {
	       block = document.getElementById('SetBlock$i');
	       if (block)
	           block.style.display = '';
	   }
	   </script>
JS;
		$this->CloseTable();
	}
	
	public function AddAdminSection($file_name, $title, $descr, $image, $permision = 'all')
	{
        if ($GLOBALS['config']['version_id'] >= 8.2)
        {
            $title = $this->db->safesql($title);
            $descr = $this->db->safesql($descr);
            $file_name = $this->db->safesql($file_name);
            $image = $this->db->safesql($image);
            
            $this->db->query("INSERT IGNORE `" . PREFIX . "_admin_sections` (allow_groups, name, icon, title, descr) VALUES ('all', '$file_name', '$image', '$title', '$descr')");
        }
	}
	
	private function FatalError($text)
	{
		$this->hidden_array = array("step"=>0);
		$this->step = 0;
		$this->steps_array = array("Fatal Error");
		$this->button = false;
		$this->show('', $text, 'module_error');
	}
	
	private function show($text='', $status_report = false, $status_type = 'module_info')
	{
		$step_count = count($this->steps_array);
		if ($step_count == 0)
			$step_count = 1;
			
		if (ereg("IE", $_SERVER['HTTP_USER_AGENT']) || ereg("Opera", $_SERVER['HTTP_USER_AGENT']))
			$size = @round(100/$step_count, 5);
		else 
			$size = @ceil(100/$step_count);
		
		$bar = "<table width=\"100%\" align=center ><tr>";
		for ($i=0; $i<$step_count; $i++)
		{
			$bar .= "<td align=center width=\"$size%\" >";
			if ($i < $this->step && $i != $step_count) $bar .= "<img width='32px' heidth='32px' src=\"" . $config['http_home_url'] . $this->image_patch."/module_ok.png\" />";
			elseif ($i == $this->step && $this->error && $i != $step_count) $bar .= "<img width='32px' heidth='32px' src=\"" . $config['http_home_url'] . $this->image_patch."/module_stop.png\" />";
			elseif ($i == $this->step && !$this->error && $i != $step_count) $bar .= "<img width='32px' heidth='32px' src=\"" . $config['http_home_url'] . $this->image_patch."/module_now.png\" />";
			elseif ($i+1 == $step_count) $bar .= "<img width='32px' heidth='32px' src=\"" . $config['http_home_url'] . $this->image_patch."/module_finish.png\" />";
			else $bar .= "<img src=\"" . $config['http_home_url'] . $this->image_patch."/module_next.png\" />";
			$bar .= "</td>";
		}
		$bar .= "</tr><tr style=\"padding-top:10px;\">";
		for ($i=0; $i<$step_count; $i++)
		{
			$bar .= "<td style=\"font-size:10px\" align=center width=\"$size%\" >";
			if ($i < $this->step && $i != $step_count) $bar .= $this->steps_array[$i];
			elseif ($i == $this->step && $this->error && $i != $step_count) $bar .= "<b>" . $this->steps_array[$i] . "</b>";
			elseif ($i == $this->step && !$this->error && $i != $step_count) $bar .= "<b>" . $this->steps_array[$i] . "</b>";
			elseif ($i+1 == $step_count) $bar .= "<font color=\"#cccccc\" >" . $this->steps_array[$i] . "</font>";
			else $bar .= "<font color=\"#cccccc\" >" . $this->steps_array[$i] . "</font>";
			$bar .= "</td>";
		}
		$bar .= "</tr></table>";
		
		if ($this->error)
		{
			$errors = "<font color=\"red\" >Были допущены следующие ошибки</font>\n<ol>\n";
			foreach ($this->error as $error)
			{
				$errors .= "<li>".$error."</li>\n";
			}
			$errors .= "</ol>";
			$status_report = $errors . $status_report;
		}
		
		if ($this->finish)
			$meta = "\n<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"5;URL={$GLOBALS['config']['http_home_url']}\">";
		else 
			$meta = '';
		
		echo <<<HTML
<html>
<head>
<title>$this->module_name</title>
<meta content="text/html; charset=windows-1251" http-equiv="content-type" />$meta
<script type="text/javascript" src="engine/skins/default.js"></script>

<style type="text/css">
html,body{
height:100%;
margin:0px;
padding: 0px;
background: #F4F3EE;
}

form {
margin:0px;
padding: 0px;
}

table{
border:0px;
border-collapse:collapse;
}

table td{
padding:0px;
font-size: 11px;
font-family: verdana;
}

a:active,
a:visited,
a:link {
	color: #4b719e;
	text-decoration:none;
	}

a:hover {
	color: #4b719e;
	text-decoration: underline;
	}

.navigation {
	color: #999898;
	font-size: 11px;
	font-family: tahoma;
}

.option {
	color: #717171;
	font-size: 11px;
	font-family: tahoma;
}

.upload input {
	border:1px solid #9E9E9E;
	color: #000000;
	font-size: 11px;
	font-family: Verdana; 
}

.small {
	color: #999898;
}

.navigation a:active,
.navigation a:visited,
.navigation a:link {
	color: #999898;
	text-decoration:none;
	}

.navigation a:hover {
	color: #999898;
	text-decoration: underline;
	}

.list {
	font-size: 11px;
}

.list a:active,
.list a:visited,
.list a:link {
	color: #0B5E92;
	text-decoration:none;
	}

.list a:hover {
	color: #999898;
	text-decoration: underline;
	}

.quick {
	color: #999898;
	font-size: 11px;
	font-family: tahoma;
	padding: 5px;
}

.quick h3 {
	font-size: 18px;
	font-family: verdana;
	margin: 0px;
	padding-top: 5px;
}
.system {
	color: #999898;
	font-size: 11px;
	font-family: tahoma;
	padding-bottom: 10px;
	text-decoration:none;
}

.system h3 {
	font-size: 18px;
	font-family: verdana;
	margin: 0px;
	padding-top: 4px;
}
.system a:active,
.system a:visited,
.system a:link,
.system a:hover {
	color: #999898;
	text-decoration:none;
	}

.quick a:active,
.quick a:visited,
.quick a:link,
.quick a:hover {
	color: #999898;
	text-decoration:none;
	}

.unterline {
	background: url(engine/skins/images/line_bg.gif);
	width: 100%;
	height: 9px;
	font-size: 3px;
	font-family: tahoma;
	margin-bottom: 4px;
} 

.hr_line {
	background: url(engine/skins/images/line.gif);
	width: 100%;
	height: 7px;
	font-size: 3px;
	font-family: tahoma;
	margin-top: 4px;
	margin-bottom: 4px;
}

.edit {
	border:1px solid #9E9E9E;
	color: #000000;
	font-size: 11px;
	font-family: Verdana;
	background: #FFF; 
}

.bbcodes {
	background: #FFF;
	border: 1px solid #9E9E9E;
	color: #666666;
	font-family: Verdana, Tahoma, helvetica, sans-serif;
	padding: 2px;
	vertical-align: middle;
	font-size: 10px; 
	margin:2px;
	height: 21px;
}

.buttons {
	background: #FFF;
	border: 1px solid #9E9E9E;
	color: #666666;
	font-family: Verdana, Tahoma, helvetica, sans-serif;
	padding: 0px;
	vertical-align: absmiddle;
	font-size: 11px; 
	height: 21px;
}

select {
	color: #000000;
	font-size: 11px;
	font-family: Verdana; 
	border:1px solid #9E9E9E;
}

.cat_select {
	color: #000000;
	font-size: 11px;
	font-family: Verdana; 
	border:1px solid #9E9E9E;
	width:316px;
	height:73px;
}

textarea {
	border: #9E9E9E 1px solid;
	color: #000000;
	font-size: 11px;
	font-family: Verdana;
	margin-bottom: 2px;
	margin-right: 0px;
	padding: 0px;
}

.xfields textarea {
width:98%; height:100px;border: #9E9E9E 1px solid; font-size: 11px;font-family: Verdana;
}
.xfields input {
width:350px; height:18px;border: #9E9E9E 1px solid; font-size: 11px;font-family: Verdana;
}
.xfields select {
height:18px; font-size: 11px;font-family: Verdana;
}

.xfields {
height:30px; font-size: 11px;font-family: Verdana;
}
.xprofile textarea {
width:100%; height:90px; font-family:verdana; font-size:11px; border:1px solid #E0E0E0;
}
.xprofile input {
width:250px; height:18px; font-family:verdana; font-size:11px; border:1px solid #E0E0E0;
}
#dropmenudiv{
border:1px solid white;
border-bottom-width: 0;
font:normal 10px Verdana;
background-color: #6497CA;
line-height:20px;
margin:2px;
filter: alpha(opacity=95, enabled=1) progid:DXImageTransform.Microsoft.Shadow(color=#CACACA,direction=135,strength=3);
}

#dropmenudiv a{
display: block;
text-indent: 3px;
border: 1px solid white;
padding: 1px 0;
MARGIN: 1px;
color: #FFF;
text-decoration: none;
font-weight: bold;
}

#dropmenudiv a:hover{ /*hover background color*/
background-color: #FDD08B;
color: #000;
}

#hintbox{ /*CSS for pop up hint box */
position:absolute;
top: 0;
background-color: lightyellow;
width: 150px; /*Default width of hint.*/ 
padding: 3px;
border:1px solid #787878;
font:normal 11px Verdana;
line-height:18px;
z-index:100;
border-right: 2px solid #787878;
border-bottom: 2px solid #787878;
visibility: hidden;
}

.hintanchor{ 
padding-left: 8px;
}

.editor_button {
	float:left;
	cursor:pointer;
	padding-left: 2px;
	padding-right: 2px;
}
.editor_buttoncl {
	float:left;
	cursor:pointer;
	padding-left: 1px;
	padding-right: 1px;
	border-left: 1px solid #BBB;
	border-right: 1px solid #BBB;
}
.editbclose {
	float:right;
	cursor:pointer;
}
	.dle_tabPane{
		height:26px;	/* Height of tabs */
	}
	.dle_aTab{
		border:1px solid #CDCDCD;
		padding:5px;		
		
	}
	.dle_tabPane DIV{
		float:left;
		padding-left:3px;
		vertical-align:middle;
		background-repeat:no-repeat;
		background-position:bottom left;
		cursor:pointer;
		position:relative;
		bottom:-1px;
		margin-left:0px;
		margin-right:0px;
	}
	.dle_tabPane .tabActive{
		background-image:url('engine/skins/images/tl_active.gif');
		margin-left:0px;
		margin-right:0px;	
	}
	.dle_tabPane .tabInactive{
		background-image:url('engine/skins/images/tl_inactive.gif');
		margin-left:0px;
		margin-right:0px;
	}

	.dle_tabPane .inactiveTabOver{
		margin-left:0px;
		margin-right:0px;
	}
	.dle_tabPane span{
		font-family:tahoma;
		vertical-align:top;
		font-size:11px;
		line-height:26px;
		float:left;
	}
	.dle_tabPane .tabActive span{
		padding-bottom:0px;
		line-height:26px;
	}
	
	.dle_tabPane img{
		float:left;
	}
</style>
</head>
<body>
<table align="center" width="94%">
    <tr>
        <td width="4" height="16"><img src="engine/skins/images/tb_left.gif" width="4" height="16" border="0" /></td>
		<td background="engine/skins/images/tb_top.gif"><img src="engine/skins/images/tb_top.gif" width="1" height="16" border="0" /></td>
		<td width="4"><img src="engine/skins/images/tb_right.gif" width="3" height="16" border="0" /></td>
    </tr>
	<tr>
        <td width="4" background="engine/skins/images/tb_lt.gif"><img src="engine/skins/images/tb_lt.gif" width="4" height="1" border="0" /></td>
		<td valign="top" style="padding-top:12px; padding-left:13px; padding-right:13px;" bgcolor="#FAFAFA">
HTML;
		$this->OpenTable();
		echo <<<HTML
<center><font style="font-size:22px; font-weight:bold; font-family:Verdana, Arial, Helvetica, sans-serif; font-stretch:expanded; color:#333333;">$this->module_name</font> <font style="color:#666666">&nbsp;&nbsp;v$this->version</font></center>
HTML;
		$this->CloseTable();
		$this->OpenTable();
		echo $bar;
		$this->CloseTable();
		
		if ($this->show_setting_menu && $this->setting_menu)
		{
		    $this->ShowSettingMenu();
		}
		
		$this->OpenForm('', 'name="form"');
		$this->OpenTable();
		$this->OpenSubtable($this->steps_array[$this->step]);

		echo $text;
		
		if ($status_report)
		{
			echo <<<HTML
<tr>
<td>
<table width="100%">
	<tr>
		<td width=80px align=center valign=middle style="padding:15px;" >
			<img src="{$this->image_patch}/$status_type.png" />
		</td>
		<td style="padding:15px;">
			$status_report
		</td>
	</tr>
</table>
</td></tr>
HTML;
        }
        
		if ($this->buttons)
		{
			$buttons = '';
			foreach ($this->buttons as $name=>$value)
				$buttons .= "&nbsp;&nbsp;<input class='buttons' name='$name' type='submit' style='padding:2px;' value='$value' />";
		}
		else 
		{
			$buttons = '';
		}
			
		echo <<<HTML
     <tr>
     <td height="40" colspan=3 align="right">$buttons&nbsp;&nbsp;
HTML;
		if ($this->button)
		{
			echo <<<HTML
     <input class=buttons id='but' name="button" type="submit" style="padding:2px" value="$this->button" />&nbsp;&nbsp;
HTML;
		}
		
		echo "</tr>";
		
		$this->CloseSubtable();
		$this->CloseTable();
		$this->CloseForm();
		
		if (!$this->year)
			$this->year = date("Y");
		elseif ($this->year != date("Y"))
			$this->year = $this->year ." - " . date("Y");
			
		
		echo <<<HTML
	 <!--MAIN area-->
<div style="padding-top:5px; padding-bottom:10px;">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="20" align="center" style="padding-right:10px;"><div class="navigation">Copyright © $this->year <a href="http://www.kaliostro.net" style="text-decoration:underline;color:green">kaliostro</a></div></td>
    </tr>
</table></div>		
		</td>
		<td width="4" background="engine/skins/images/tb_rt.gif"><img src="engine/skins/images/tb_rt.gif" width="4" height="1" border="0" /></td>
    </tr>
	<tr>
        <td height="16" background="engine/skins/images/tb_lb.gif"></td>
		<td background="engine/skins/images/tb_tb.gif"></td>
		<td background="engine/skins/images/tb_rb.gif"></td>
    </tr>
</table>
</body>

</html>
HTML;
		exit();
	}
}

class Licencing
{
    static private $dom;
    
    public $domain;
    
    public function __construct($domain)
    {
        self::$dom = $domain;
        $this->domain = $domain;
    }
        
    public static function check()
    {
        
    }
}

class Spacer
{
    public $config = array();
    
    public $lang = array();
    
    public function __construct(array $config, array $lang)
    {
        $this->config = $config;
        $this->lang = $lang;
    }
    
    public function __call($func_name, $args) {}
    
    public function __get($varname) {}
    
    public function __set($varname, $value) {}
}
?>
