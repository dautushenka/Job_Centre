<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

if (!function_exists('array_map_recursive'))
{
	function array_map_recursive($function, &$data)
	{
		foreach ($data as $i=>$item)
		{
			$data[$i] = is_array($item) ? array_map_recursive($function, $item) : $function($item);
		}
		
		return $data ;
	}
}

function save_conf($save_con, $array=false)
{
	global $handler;
    	
	foreach($save_con as $name => $value)
	{
		if (is_array($value))
		{
			fwrite($handler, "'{$name}' => array (\n\n"); save_conf($value, true);
		}
		else
		{
		    $value = strtr($value, '"', "'");
		    $value = trim(stripslashes ($value));
			$value = htmlspecialchars ($value, ENT_QUOTES);
		    fwrite($handler, "'{$name}' => \"$value\",\n\n");
		}
	}
	if ($array)
		fwrite($handler, "),\n\n");
}

function get_usergroup()
{
	global $db, $user_group;
	
	if (empty($user_group))
	{
		$user_group = array();
		$groups = $db->query("SELECT * FROM " . USERPREFIX . "_usergroups", true);	
		while ($row = $db->get_row())
		{
			$user_group[$row['id']] = $row;
		}
		
	}
	
	$group = array();
	
	foreach ($user_group as $id=>$group_setting)
	{
		$group[$id] = $group_setting['group_name'];
	}
	
	return $group;
}

function ConvertTypeToValue(array $columns)
{
	foreach ($columns as &$type)
	{
		switch ($type)
		{
			case "int":
				$type = 0;
				break;
				
			case "string":
				$type = '';
				break;
				
			default:
				$type = '';
				break;
		}
	}

	return $columns;
}

function ConvertDBResultToType(array $columns, array &$data)
{
	foreach ($columns as $column=>$type)
	{
		if (isset($data[$column]))
		{
			if ($type == 'int')
				$data[$column] = (int)$data[$column];
			elseif ($type == 'float') 
				$data[$column] = floatval($data[$column]);
		}
	}
}

function PageURL(array $where)
{
	$str = '';
	foreach ($where as $param=>$value)
	{
		$str .= "&where[" . $param . "]=" . $value;
	}
	
	return $str;
}

function job_totranslit($var, $lower = true, $check_length = true, $punkt = true)
{
	$NpjLettersFrom = "àáâãäåçèêëìíîïðñòóôöû³";
	$NpjLettersTo = "abvgdeziklmnoprstufcyi";
	$NpjBiLetters = array ("é" => "j", "¸" => "yo", "æ" => "zh", "õ" => "x", "÷" => "ch", "ø" => "sh", "ù" => "shh", "ý" => "ye", "þ" => "yu", "ÿ" => "ya", "ú" => "", "ü" => "", "¿" => "yi", "º" => "ye" );
	
	$NpjCaps = "ÀÁÂÃÄÅ¨ÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÛÝÞß¯ª²";
	$NpjSmall = "àáâãäå¸æçèéêëìíîïðñòóôõö÷øùüúûýþÿ¿º³";
	
	$var = str_replace( ".php", "", $var );
	$var = trim( strip_tags( $var ) );
	$var = preg_replace( "/\s+/ms", "-", $var );
	$var = strtr( $var, $NpjCaps, $NpjSmall );
	$var = strtr( $var, $NpjLettersFrom, $NpjLettersTo );
	$var = strtr( $var, $NpjBiLetters );
	
	if ( $punkt ) $var = preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var );
	else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );

	$var = preg_replace( '#[\-]+#i', '-', $var );

	if ( $lower ) $var = strtolower( $var );
	
	if($check_length && strlen( $var ) > 50 )
		$var = substr( $var, 0, 50 );
	
	return $var;
}

function job_check_email($email)
{
	return (!ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'. '@'.'[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.'[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email))?false:true;
}

function job_check_phone($phone)
{
	return preg_match('#^[\d +\(\);:-]+$#i', $phone);
}

function job_check_site($site)
{
	return preg_match('#^http://(www\.)?\w+(.[a-z]{2,4}){1,2}/?$#i', $site);
}

if (!function_exists('langdate'))
{
	function langdate($format, $stamp)
	{
		return date($format, $stamp);
	}
}

function GetValidateRules(array $fields_array, array $validete_options = array())
{
	$validate_str = $messages_str = ''; $j = $m = 0;
	foreach ($fields_array as $fields=>$options)
	{
		$fields = explode(",", $fields);
		
		foreach ($fields as $field)
		{
			if ($options['valid'])
			{
				if ($j)
					$validate_str .= ",\n";
				
				$validate_str .= trim($field) . ":{\n";
		
				$i = 0;
				foreach ($options['valid'] as $type=>$type_opt)
				{
					if ($i) 
						$validate_str .= ",\n";
						
					$validate_str .= $type . ":" . $type_opt;
					$i++;
				}
				$validate_str .= "}";
				$j++;
			}
			if ($options['messages'])
			{
				if ($m)
					$messages_str .= ",\n";
					
				$messages_str .= trim($field) . ":{\n";
				
				$i = 0;
				foreach ($options['messages'] as $type=>$type_opt)
				{
					if ($i) 
						$messages_str .= ",\n";
						
					if (empty($validete_options[$field]['messages'][$type]['no_string']))
					   $messages_str .= $type . ":'" . $type_opt . "'";
					else 
					   $messages_str .= $type . ":" . $type_opt;
					$i++;
				}
				$messages_str .= "}";
				$m++;
			}
		}
	}
	
	return array('rules'=>$validate_str, 'messages' => $messages_str);
}

function ConvertDate($timestap, Job &$job)
{
	global $config;
	
	$timestap = $timestap + $config['date_adjust']*60;
	switch (date("d.m.Y", $timestap))
	{
		case date("d.m.Y"):
			$date = date($job->lang['today_in'] . "H:i", $timestap);	
    		break;
    	case date("d.m.Y", time() - 86400 + $config['date_adjust']*60):
           	$date = date($job->lang['yestoday_in'] . "H:i", $timestap);	
    		break;
    	default:
    		$date = langdate("d-m-Y H:i", $timestap);
    		break;
	}
	
	return $date;
}

function YesNo($value)
{
	global $tpl, $job;
	
	return $tpl->selection(array(0 => $job->lang['no'], 1 => $job->lang['yes']), "save_con[$value]", intval($job->config[$value]));
}

function include_lng($file, $varname)
{
    global $config, $$varname;
    
    if (isset($config["lang_" . $config['skin']]) and $config["lang_" . $config['skin']] != '')
    	require ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . "/$file.lng";
    else
        require(ROOT_DIR . "/language/".$config['langs']."/$file.lng");
}

function CheckGroupAllow($param_name, $group = 0)
{
    global $job;
    
    if (!$group)
    {
        $group = $job->member['group'];
    }
    
    if (!empty($job->config[$param_name]) && in_array($group, $job->config[$param_name]))
    {
        return true;
    }
    else 
    {
        return false;
    }
}

function job_check_reg($name, $email, $password1, $password2)
{
	global $job, $db;
	$Errors = array();

	if ($password1 != $password2) $Errors[] = $job->lang['reg_err_1'];
	if (strlen($password1) < 6) $Errors[] = $job->lang['reg_err_2'];
	if (strlen($name) > 20) $Errors[] = $job->lang['reg_err_3'];
	if (preg_match("/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $name)) $Errors[] = $job->lang['reg_err_4'];
	if ($name == "") $Errors[] = $job->lang['reg_err_7'];

	if (!$Errors)
	{

		@setlocale(LC_CTYPE, array("ru_RU.CP1251", "ru_SU.CP1251", "ru_RU.KOI8-r", "ru_RU", "russian", "ru_SU", "ru"));
		$replace_word = array ('e' => '[eå¸]', 'r' => '[rã]', 't' => '[tò]', 'y' => '[yó]','u' => '[uè]','i' => '[i1l!]','o' => '[oî0]','p' => '[pð]','a' => '[aà]','s' => '[s5]','w' => 'w','q' => 'q','d' => 'd','f' => 'f','g' => '[gä]','h' => '[hí]','j' => 'j','k' => '[kê]','l' => '[l1i!]','z' => 'z','x' => '[xõ%]','c' => '[cñ]','v' => '[vuè]','b' => '[bâü]','n' => '[nïë]','m' => '[mì]','é' => '[éèu]','ö' => 'ö','ó' => '[óy]','å' => '[åe¸]','í' => '[íh]','ã' => '[ãr]','ø' => '[øwù]','ù' => '[ùwø]','ç' => '[ç3ý]','õ' => '[õx%]','ú' => '[úü]','ô' => 'ô','û' => '(û|ü[i1l!]?)','â' => '[âb]','à' => '[àa]','ï' => '[ïn]','ð' => '[ðp]','î' => '[îo0]','ë' => '[ën]','ä' => 'ä','æ' => 'æ','ý' => '[ý3ç]','ÿ' => '[ÿ]','÷' => '[÷4]','ñ' => '[ñc]','ì' => '[ìm]','è' => '[èué]','ò' => '[òt]','ü' => '[üb]','á' => '[á6]','þ' => '(þ|[!1il][oî0])','¸' => '[¸åe]','1' => '[1il!]','2' => '2','3' => '[3çý]','4' => '[4÷]','5' => '[5s]','6' => '[6á]','7' => '7','8' => '8','9' => '9','0' => '[0îo]','_' => '_','#' => '#','%' => '[%x]','^' => '[^~]','(' => '[(]',')' => '[)]','=' => '=','.' => '[.]','-' => '-');
		$name=strtolower($name);
		$name=strtr($name, $replace_word);

		$row = $db->super_query ("SELECT COUNT(*) as count FROM " . USERPREFIX . "_users WHERE email = '$email' OR LOWER(name) REGEXP '[[:<:]]{$name}[[:>:]]'");

        if ($row['count']) $Errors[] = $job->lang['reg_err_8'];
	}
	
	if ($Errors)
		$job->Errors = array_merge($job->Errors, $Errors);
}

function StringLength(&$text, $length = 150)
{
    $strings = explode("\n", $text);
    
    $new_text = array();
    
    foreach ($strings as $string)
    {
        if (strlen($string) > 100)
        {
            while (strlen($string) > 100)
            {
                $i = $length;
                while (!empty($string{$i}) && $string{$i} != " ")
                {
                    $i++;
                }
                
                if ($string{$i} == " ")
                {
                    $new_text[] = substr($string, 0, $i);
                    $string = substr($string, $i - strlen($string));
                }
                else 
                {
                    $new_text[] = $string;
                    break;
                }
            }
        }
        else 
        {
            $new_text[] = $string;
        }
    }
    
    return implode("\n", $new_text);
}

function SetRegisterBlock(array &$set_param)
{
    global $template, $config, $validete_array, $validete_options, $job, $edit;
    
    $template->SetBlock('register');
    $set_param['{user_name}'] = $template->InputText('name', $edit['name'], 'id="name" validate="required:true, remote:\'' . $config['http_home_url'] . "engine/job/ajax.php?action=CheckLogin" . '\'"');
	$set_param['{password}'] = $template->InputPassword('password1', $edit['password1'], 'id="password1"');
	$set_param['{password_confirm}'] = $template->InputPassword('password2', $edit['password2'], 'id="password2"');
	
	$validete_array += array(
						"password1" 		=> array( "valid" => array("required" => "true",
						                                               "rangelength" => "[6, 16]",
																	),
													),
						"password2" 		=> array( "valid" => array("required" => "true",
						                                               "rangelength" => "[6, 16]",
						                                               "equalTo" => "'#password1'",
																	),
													),
						"name" 			=> array(	 "messages" => array(
													 					 "required" => $job->lang['reg_err_7'],
													 					 "remote"  => "$.validator.format('\"{0}\" {$job->lang['reg_err_9']}')"
													 					 )
													 )
						);
	$validete_options['name'] = array();
    $validete_options['name']['messages'] = array();
	$validete_options['name']['messages']['remote'] = array('no_string' => 1);
}

function SetCapchaBlock(array &$set_param)
{
    global $template, $config, $lang, $JS;
    
    $template->SetBlock('code');
    $path = parse_url($config['http_home_url']);
	$set_param['{code}'] = "<span id=\"dle-captcha\"><img src=\"" . $path['path'] . "engine/modules/antibot/antibot.php\" alt=\"{$lang['sec_image']}\" width=\"160\" height=\"80\" /><br /><a onclick=\"reload(); return false;\" href=\"#\">{$lang['reload_code']}</a></span>";
		$JS .= <<<JS
function reload () {

	var rndval = new Date().getTime(); 

	document.getElementById('dle-captcha').innerHTML = '<img src="{$path['path']}engine/modules/antibot/antibot.php?rndval=' + rndval + '" border="0" width="160" height="80"><br /><a onclick="reload(); return false;" href="#">{$lang['reload_code']}</a>';

};
JS;
}

function SetCountryCityBlock(array &$set_param)
{
    global $job, $template, $edit;
    
    if ($job->use_country)
    {
        $template->SetBlock('country');
        $set_param['{country}'] = $template->Selection($job->GetCountries('add'), 'country_id', $edit['country_id'], "id='country_id'");
    }
    if ($job->use_city)
    {
        $template->SetBlock('city');
        $set_param['{city}'] = $template->Selection($job->GetCities($edit['country_id']), 'city_id', $edit['city_id'], "id='city_id'");
    }
}

function Vacancy_ShowEditMenu()
{
    global $job, $company;
    
    $menu = '';
    $i = 0;
    
    if (MODER_EDIT || CheckGroupAllow('vacancy_allow_edit', $company?-1:0))
    {
        $menu .= "\nmenu[$i] = '<a href=\"' + dle_root + '?do=job&action=vacancy_edit&id=' + id + '\" target=\"_blank\">{$job->lang['action_edit']}</a>';";
        $i++;
    }
            
    if (MODER_DEL || CheckGroupAllow('vacancy_allow_del', $company?-1:0))
    {
        $menu .= "\nmenu[$i] = '<a href=\"' + dle_root + '?do=job&action=vacancy_del&selected[]=' + id + '\" target=\"_blank\">{$job->lang['action_del']}</a>';";
        $i++;
    }
    
    if (MODER_EDIT || MODER_DEL)
    {
        $menu .= "\nmenu[$i] = '<a OnClick=\"AllowSite(\'vacancy\', ' + id + ', ' + allow_site + ');return false;\" href=\"#\">' + allow + '</a>';";
        $i++;
    }
    
    return $menu;
}

function Resume_ShowEditMenu()
{
    global $job;
    
    $menu = '';
    $i = 0;
    
    if (MODER_EDIT || CheckGroupAllow('resume_allow_edit'))
    {
        $menu .= "\nmenu[$i] = '<a href=\"' + dle_root + '?do=job&action=resume_edit&id=' + id + '\" target=\"_blank\">{$job->lang['action_edit']}</a>';";
        $i++;
    }
            
    if (MODER_DEL || CheckGroupAllow('resume_allow_del'))
    {
        $menu .= "\nmenu[$i] = '<a href=\"' + dle_root + '?do=job&action=resume_del&selected[]=' + id + '\" target=\"_blank\">{$job->lang['action_del']}</a>';";
        $i++;
    }
    
    if (MODER_EDIT || MODER_DEL)
    {
        $menu .= "\nmenu[$i] = '<a OnClick=\"AllowSite(\'resume\', ' + id + ', ' + allow_site + ');return false;\" href=\"#\">' + allow + '</a>';";
        $i++;
    }
    
    return $menu;
}

/**
 * Generate Menu for Edit
 *
 * @param string $type
 * @return string JavaScript string
 */
function ShowEditMenu($type)
{
    if ($type == "vacancy")
    {
        $menu = Vacancy_ShowEditMenu();
    }
    else
    {
        $menu = Resume_ShowEditMenu();
    }
    
    if ($menu)
    {
        return <<<JS
function ShowMenu(id, allow_site)
{
    var menu = new Array();
    
    if (edit[id] != null)
    {
    	allow_site = (edit[id])?0:1;
    }
    
    if (allow_site == 0)
    {
    	allow = allow_no;
    }
    else
    {
    	allow = allow_yes;	
    }
    
    $menu
    
    return menu;
};
JS;
    }
    else
    {
        return '';
    }
}

function SendMail($mail_template, $id)
{
    global $job;
    
    switch ($mail_template)
    {
        case "vacancy":
            $field_aray = Vacancy::$fields;
            $subj = $job->lang['vacancy_mail_subj'];
            $url = $job->tpl->GetUrl(array('action' => 'vacancy', "id" => $id));
            $moder = CheckGroupAllow('vacancy_add_no_moder')?$job->lang['no']:$job->lang['yes'];
            break;
            
        case "resume":
            $field_aray = Resume::$fields;
            $subj = $job->lang['resume_mail_subj'];
            $moder = CheckGroupAllow('resume_add_no_moder')?$job->lang['no']:$job->lang['yes'];
            $url = $job->tpl->GetUrl(array('action' => 'resume', "id" => $id));
            break;
            
        case 'company':
            $field_aray = array();
            $subj = $job->lang['company_mail_subj'];
            $url = $job->GetCompanyURL($id, $_POST['alt_name']);
            break;
    }
    
    if ($job->config['general_inform'] && $job->config['general_email'])
    {
        if (file_exists(ENGINE_DIR . "/job/mail_templates/" . $mail_template . ".txt"))
        {
            $content = file_get_contents(ENGINE_DIR . "/job/mail_templates/" . $mail_template . ".txt");
            
            $content = str_replace("{site_url}", $GLOBALS['config']['http_home_url'], $content);
            $content = str_replace("{url}", $url, $content);
            $content = str_replace("{moder}", $moder, $content);
            
            if (!empty($_POST['sphere_id']))
            {
                $content = str_replace("{sphere}", $job->spheres[$_POST['sphere_id']], $content);
            }
            
            if (!empty($_POST['specialty_id']))
            {
                $content = str_replace("{specialty}", $job->specialties[$_POST['specialty_id']], $content);
            }
            
            if (!empty($_POST['country_id']))
            {
                $content = str_replace("{country}", $job->countries[$_POST['country_id']], $content);
            }
            
            if (!empty($_POST['city_id']))
            {
                $content = str_replace("{city}", $job->cities[$_POST['city_id']], $content);
            }
            
            if (!empty($_POST['currency']))
            {
                $content = str_replace("{currency}", Job::$currecy_array[$_POST['currency']], $content);
            }
            
            foreach ($_POST as $key=>$value)
            {
                if (key_exists($key, $field_aray))
                {
                    $content = str_replace("{" . $key . "}", $field_aray[$key][$value], $content);
                }
                else
                {
                    $content = str_replace("{" . $key . "}", $value, $content);
                }
            }
            
           // $content = preg_replace('#{.+?}#si', '', $content);
            
            include_once DLE_CLASSES.'mail.class.php';
			$mail = new dle_mail ($GLOBALS['config']);
				
			$mail->from = $GLOBALS['config']['admin_mail'];
			$mail->send ($job->config['general_email'], $subj, $content);
        }
    }
}

function php2js($var)
    {
        if (is_null($var))
        {
            return 'null';
        }
        if ($var === false)
        {
            return 'false';
        }
        if ($var === true)
        {
            return 'true';
        }
        if (is_scalar($var))
        {
            if (is_float($var))
            {
                $var = str_replace(",", ".", strval($var));
            }
            static $jsonReplaces = array(
            array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), 
            array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
            
            return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $var) .
             '"';
        }
        $isList = true;
        for ($i = 0, reset($var); $i < count($var); $i++, next($var))
        {
            if (key($var) !== $i)
            {
                $isList = false;
                break;
            }
        }
        $result = array();
        if ($isList)
        {
            foreach ($var as $v)
            {
                $result[] = php2js($v);
            }
            return '[ ' . join(', ', $result) . ' ]';
        }
        else
        {
            foreach ($var as $k => $v)
                $result[] = php2js($k) . ': ' . php2js($v);
            return '{ ' . join(', ', $result) . ' }';
        }
        return $var;
    }
?>