<?php
/**
 * @package ModuleJob
 * @author Autushenko Denis <support@kaliostro.net>
 * @copyright (c) kaliostro
 * @version 1.0.0
 */

/**
 * Final class JobUser for using in site
 *
 * @access public
 * @package ModuleJob
 * @final 
 */
final class JobUser extends Job 
{
	/**
	 * Delegate of $template
	 *
	 * @var TemplateUser
	 */
	public $tpl = null;
	
	/**
	 * 
	 * Enter description here ...
	 * @var Fields
	 */
	public $xfields = null;
	
	public $favorites = array(
	                           'vacancy' => array(),
	                           'resume' => array(),
	                           );
	
	public function __construct($job_cfg, $lang_job, &$dbase)
	{
		parent::__construct($job_cfg, $lang_job, $dbase);
		
		$this->PreparationSearchArray();
		
		if (!empty($_COOKIE['favorites_vacancy']))
		{
		    $this->favorites['vacancy'] = explode(",", $_COOKIE['favorites_vacancy']);
		}
		
		if (!empty($_COOKIE['favorites_resume']))
		{
		    $this->favorites['resume'] = explode(",", $_COOKIE['favorites_resume']);
		}
	}
	
	public function PreparationSearchArray()
	{

        if (!empty($_GET['sub_sort']) && in_array($_GET['sub_sort'], array("ASC", "DESC")))
        {
            $this->sub_sort = $_GET['sub_sort'];
        }
		
        switch ($_GET['action'])
        {
            case "vacancy":
                $this->search_array = array_intersect_key($_GET, array_flip(Vacancy::$search_fields));
                
                if (!empty($_GET['sort']) && !empty(Vacancy::$sort_array[$_GET['sort']]))
                {
                    $this->sort = $_GET['sort'];
                }
                else if (!empty($this->config['vacancy_default_sort']))
                {
                    $this->sort = $this->config['vacancy_default_sort'];
                }
                
                if (!$this->sub_sort)
                {
                    $this->sub_sort = $this->config['vacancy_default_sub_sort'];
                }
                break;
                
            case "resume":
                $this->search_array = array_intersect_key($_GET, array_flip(Resume::$search_fields));
                
                if (!empty($_GET['sort']) && !empty(Resume::$sort_array[$_GET['sort']]))
                {
                    $this->sort = $_GET['sort'];
                }
                else if (!empty($this->config['resume_default_sort']))
                {
                    $this->sort = $this->config['resume_default_sort'];
                }
                
                if (!$this->sub_sort)
                {
                    $this->sub_sort = $this->config['resume_default_sub_sort'];
                }
                break;
                
            case "company":
                $this->search_array = array_intersect_key($_GET, array_flip(Company::$search_fields));
                break;
                
            default:
                $this->search_array = array();
                break;
        }
        
		if ($this->use_country && !$this->search_array['country_id'] && $this->search_array['city_id'])
		{
			$this->search_array['city_id'] = 0;
		}
			
		if (!$this->search_array['sphere_id'] && $this->search_array['city_id'])
		{
			$this->search_array['specialty_id'] = 0;
		}
	}
	
	public function VacancyAddView($id)
	{
		$this->dbase->Update('job_vacancies', array('view_count' => 'view_count+1'), array('id' => $id), true);
	}
	
	public function ResumeAddView($id)
	{
		$this->dbase->Update('job_resumes', array('view_count' => 'view_count+1'), array('id' => $id), true);
	}
	
    /**
	 * Create new vacancy
	 *
	 * @param array $add_values
	 * @param Company $company
	 */
	public function NewVacancy(array $add_values, &$company = null)
	{
	    $this->add_value = $add_values;
	    
	    if ((!MODER_EDIT && !CheckGroupAllow('vacancy_allow_change_exp', $company?-1:0)) || !in_array($this->add_value['count_day'], Vacancy::$count_day_array))
	    {
	        if ($this->config['vacancy_default_day'] == -1)
	        {
	            $this->add_value['date_exp'] = 0;
	        }
	        else
	        {
	            $this->add_value['date_exp'] = $this->dbase->timer->cur_time + $this->config['vacancy_default_day']*24*3600;
	        }
	        
	    }
	    else 
	    {
	        if ($this->add_value['count_day'] == -1)
	        {
	           $this->add_value['date_exp'] = 0;
	        }
	        else 
	        {
	           $this->add_value['date_exp'] = $this->dbase->timer->cur_time + $this->add_value['count_day']*24*3600; 
	        }
	    }
	    
	    if (CheckGroupAllow('vacancy_add_capcha', $company?-1:0))
	    {
	        if (!$this->CheckCapcha())
	        {
	            return false;
	        }
	    }
	    
	    if (!$this->member['id'] && $this->config['vacancy_allow_reg'])
	    {
	        $this->CheckRegisterUser();
	    }
	    
	    $this->CheckVacancyError();
	    
	    if (!$this->Errors && !$this->member['id'] && $this->config['vacancy_allow_reg'])
	    {
	        $this->RegisterUser();
	    }
	    
	    if (CheckGroupAllow('vacancy_add_no_moder'))
	    {
	        $this->add_value['allow_site'] = 1;
	    }
	    else
	    {
	        $this->add_value['allow_site'] = 0;
	    }
	    
	    if ($company)
	    {
	        $this->add_value['company_id'] = $company->company_id;
	    }
	    else 
	    {
	        $this->add_value['company_id'] = 0;
	    }
	    
        
        $this->add_value['xfields'] =  $this->xfields->EncodeFields($this->add_value);
        
        $this->Errors = $this->Errors +  $this->xfields->getErrors();

	    if (!$this->Errors)
	    {
	        return $this->AddVacancy();
	    }
	    else
	    {
	        return 0;
	    }
	}
	
	/**
	 * Update vacancy
	 *
	 * @param array $update_array
	 * @param int $id
	 * @param Company $company
	 * @return boolean
	 */
	public function UpdateVacancy(array $update_array, $id , &$company_id = null)
	{
		$this->add_value = $update_array;
		
		$this->old_value = $this->GetVacancyForEdit($id);
		
		if (!$this->old_value)
		{
			$this->Errors[] = $this->lang['vacancy_not_found'];
			return false;
		}
		
		if (!MODER_EDIT && $this->old_value['user_id'] != $this->member['id'] && !empty($company) && $company->company_id != $this->old_value['company_id'])
		{
		    $this->Errors[] = $this->lang['vacancy_wrong_author'];
			return false;
		}
		
		if ($this->add_value['count_extend'] != -1 && 
		     (
		      (MODER_EDIT || CheckGroupAllow('vacancy_allow_extend', $company?-1:0)) && 
		      in_array($this->add_value['count_extend'], Vacancy::$count_day_array)
		     )
		   )
		{
		    $this->add_value['date_exp'] = $this->old_value['date_exp'] + $this->add_value['count_extend']*24*3600;
		}
		elseif (isset($this->add_value['date_exp']))
		{
		    unset($this->add_value['date_exp']);
		}
		
		if (!MODER_EDIT && isset($this->add_value['allow_site']))
		{
		    unset($this->add_value['allow_site']);
		}
		
		if (!MODER_EDIT && isset($this->add_value['company_id']))
		{
		    unset($this->add_value['company_id']);
		}
		
		return $this->SaveVacancy($id);
	}
	
	/**
	 * Delete vacancy
	 *
	 * @param array $id
	 * @param Company $company
	 */
	public function DeleteVacancy(array $id, &$company = null)
	{
	    $this->dbase->SetWhere('id', $id, "IN", "job_vacancies");
	    
	    if (MODER_DEL)
	    {
	        $this->dbase->Delete("job_vacancies");
	    }
	    else
	    {
	        $this->dbase->Select('job_vacancies', array("user_id", "id", "company_id"));
	        
	        $new_id = array();
	        
	        while ($vac = $this->dbase->FetchArray())
	        {
	            if (($vac['user_id'] == $this->member['id']) || (!empty($company) && $company->company_id == $vac['company_id']))
	            {
	                $new_id[] = $vac['id'];
	            }
	        }
	        
	        if ($new_id)
	        {
	            $this->dbase->SetWhere('id', $new_id, "IN", "job_vacancies");
	            $this->dbase->Delete("job_vacancies");
	        }
	    }
	}
	
	/**
	 * Create new resume
	 *
	 * @param array $add_values
	 * @return int
	 */
	public function NewResume(array $add_values)
	{
	    $this->add_value = $add_values;
	    
	    if (CheckGroupAllow('resume_add_capcha'))
	    {
	        if (!$this->CheckCapcha())
	        {
	            return false;
	        }
	    }
	    
	    if (!$this->member['id'] && $this->config['resume_allow_reg'])
	    {
	        $this->CheckRegisterUser();
	    }
	    
	    $this->CheckResumeError();
	    
	    if (!$this->Errors && !$this->member['id'] && $this->config['resume_allow_reg'])
	    {
	        $this->RegisterUser();
	    }
	    
	     if ((!MODER_EDIT && !CheckGroupAllow('resume_allow_change_exp')) || !in_array($this->add_value['count_day'], Resume::$count_day_array))
	    {
	        if ($this->config['resume_default_day'] == -1)
	        {
	            $this->add_value['date_exp'] = 0;
	        }
	        else
	        {
	            $this->add_value['date_exp'] = $this->dbase->timer->cur_time + $this->config['resume_default_day']*24*3600;
	        }
	    }
	    else 
	    {
	        if ($this->add_value['count_day'] == -1)
	        {
	            $this->add_value['date_exp'] = 0;
	        }
	        else
	        {
	            $this->add_value['date_exp'] = $this->dbase->timer->cur_time + $this->add_value['count_day']*24*3600;
	        }
	    }
	    
	    if (CheckGroupAllow('resume_add_no_moder'))
	    {
	        $this->add_value['allow_site'] = 1;
	    }
	    else
	    {
	        $this->add_value['allow_site'] = 0;
	    }
	    
	    $this->add_value['xfields'] =  $this->xfields->EncodeFields($this->add_value);
        
        $this->Errors = $this->Errors +  $this->xfields->getErrors();
	    
	    if (!$this->Errors)
	    {
	    	return $this->AddResume();
	    }
	    else 
	    {
	        return 0;
	    }
		
	}
	
	/**
	 * Update resume
	 *
	 * @param array $update_array
	 * @param int $id
	 * @return boolean
	 */
	public function UpdateResume(array $update_array, $id)
	{
		$this->add_value = $update_array;
		
		$this->old_value = $this->GetResumeForEdit($id);
		
		if (!$this->old_value)
		{
			$this->Errors[] = $this->lang['resume_not_found'];
			return false;
		}
		
		if (!MODER_EDIT && $this->old_value['user_id'] != $this->member['id'])
		{
		    $this->Errors[] = $this->lang['resume_wrong_author'];
			return false;
		}
		
		if ($this->add_value['count_extend'] != -1 && 
		      (
		          (MODER_EDIT || CheckGroupAllow('resume_allow_extend')) && 
		          in_array($this->add_value['count_extend'], Resume::$count_day_array)
		      )
		   )
		{
		    $this->add_value['date_exp'] = $this->old_value['date_exp'] + $this->add_value['count_extend']*24*3600;
		}
		elseif (isset($this->add_value['date_exp']))
		{
		    unset($this->add_value['date_exp']);
		}
		
		if (!MODER_EDIT && isset($this->add_value['allow_site']))
		{
		    unset($this->add_value['allow_site']);
		}
		
		return $this->SaveResume($id);
	}
	
	/**
	 * Delete resume
	 *
	 * @param array $id
	 */
	public function DeleteResume(array $id)
	{
	    $this->dbase->SetWhere('id', $id, "IN", "job_resumes");
	    
	    if (MODER_DEL)
	    {
	        $this->dbase->Delete("job_resumes");
	    }
	    else
	    {
	        $this->dbase->Select('job_resumes', array("user_id", "id"));
	        
	        $new_id = array();
	        
	        while ($res = $this->dbase->FetchArray())
	        {
	            if ($res['user_id'] == $this->member['id'])
	            {
	                $new_id[] = $res['id'];
	            }
	        }
	        
	        if ($new_id)
	        {
	            $this->dbase->SetWhere('id', $new_id, "IN", "job_resumes");
	            $this->dbase->Delete("job_resumes");
	        }
	    }
	}
	
	/**
	 * Create new company
	 *
	 * @param array $add_values
	 * @return int
	 */
	public function NewCompany(array $add_values)
	{
		$this->add_value = $add_values;
		
		if (CheckGroupAllow('company_add_capcha'))
		{
		    if (!$this->CheckCapcha())
	        {
	            return false;
	        }
		}
		
		if (!$this->member['id'] && $this->config['company_allow_reg'])
	    {
	        $this->CheckRegisterUser();
	    }
	    
	    if ($this->dbase->SelectOne('job_companies', array("company_id"), array("name" => $this->add_value['name'])))
		{
            $this->Errors[] = $this->lang['company_error_name_exist'];
		}
	    
		$this->CheckCompanyError();
		
		if (!$this->Errors && !$this->member['id'] && $this->config['company_allow_reg'])
	    {
	        $this->RegisterUser();
	    }
	    
        $this->add_value['xfields'] = $this->xfields->EncodeFields($this->add_value);
        
        $this->Errors = $this->Errors + $this->xfields->getErrors();
		
		if (!$this->Errors)
		{
		    $this->add_value['user_id'] = $this->member['id'];
		    $this->add_value['user_name'] = $this->member['name'];
		    return $this->AddCompany();
		}
		else
		{
		    return 0;
		}
	}
	
	/**
	 * Update Company
	 *
	 * @param array $update_values
	 * @param int $id
	 * @return boolean
	 */
	public function UpdateCompany(array $update_values, $id)
	{
		$this->add_value = $update_values;
		
		$this->old_value = $this->GetCompanyForEdit($id);
		
		if (!$this->old_value)
		{
			$this->Errors[] = $this->lang['company_not_found'];
			return false;
		}
		
		if (!MODER_EDIT && $this->old_value['user_id'] != $this->member['id'])
		{
		    $this->Errors[] = $this->lang['company_wrong_author'];
			return false;
		}
		
		return $this->SaveCompany($id);
	}
	
	/**
	 * Delete company
	 *
	 * @param integer $id
	 */
	public function DeleteCompany($id)
	{
	    $this->old_value = $this->GetCompanyForEdit($id);
	    
	    if (MODER_DEL || $this->old_value['user_id'] == $this->member['id'])
	    {
	        $this->DoDeleteCompany($id);
	    }
	}
	
	public function &ShowVacancy($id, array $options=array(), $company = null)
	{
		$default_options = array(
		                          'show_edit' => 1 
								);
								
		$options = array_merge($default_options, $options);
		
		$vac = array();
		$v =& $this->vacancies[$id];
		
		foreach (Vacancy::$fields as $field_name=>$value_array)
		{
			if ($v->$field_name)
			{
				$vac['{' . $field_name . '}'] = $v->$field_name;
				$this->tpl->SetBlock($field_name);
			}
		}
		
		if ($v->salary)
		{
		    $vac['{salary}'] = $v->salary;
		    $this->tpl->SetBlock('salary');
		}
		
		if ($v->age)
		{
		    $vac['{age}'] = $v->age;
		    $this->tpl->SetBlock('age');
		}
		
		$vac['{sphere}'] = $v->sphere_name;
		$vac['{specialty}'] = $v->specialty_name;
		$vac['{vacancy_url}'] = $v->vacancy_url;
        $res['{view_count}'] = $v->view_count;
		
		if ($this->use_country)
		{
            $vac['{country}'] = $v->country_name;
            $this->tpl->SetBlock('country');
		}
		
		if ($this->use_city)
		{
            $vac['{city}'] = $v->city_name;
            $this->tpl->SetBlock('city');
		}
		
		if ($v->description)
		{
			$vac['{description}'] = $v->description;
			$this->tpl->SetBlock('description');
		}
		$vac['{add_date}'] = $v->add_date;
		
		if (is_object($v->company))
		{
			$this->tpl->SetBlock('company');
			$vac['{company}'] = $v->company->name;
			
			$vac += $this->ShowCompany(0, array(), $v->company);
		}
		else 
		{
			$this->tpl->SetBlock('no_company');
			$vac['{phone}'] = $v->phone;
			$vac['{email}'] = $v->email;
			$vac['{contact_person}'] = $v->contact_person;
		}
		
		$this->SetFavorites($id, 'vacancy', $vac);
		
		if ($options['show_edit'])
		{
		    if ($v->user_id == $this->member['id'] || MODER_EDIT || MODER_DEL)
    		{
    		    $this->SetEdit($id, 'vacancy', $vac);
    		}
		}
		
		$this->SetModerClass($vac, $v, $id);
		
	    $vac['{xfields}'] = '';
	    $this->initXFields(XFIELDS_VACANCY);
        foreach ($this->xfields->showFields($v->xfields) as $fid => $field)
        {
            if ($field['value'])
            {
                $vac["{xfield_{$fid}_title}"] = $field['title'];
                $vac["{xfield_{$fid}_descr}"] = $field['description'];
                $vac["{xfield_{$fid}_value}"] = $field['value'];
                
                $vac['{xfields}'] .= $field['title'] . ": " . $field['value'] . "<br />";
                
                $this->tpl->SetBlock('xfield_' . $fid);
            }
        }
		
		return $vac;
	}
	
	public function ShowResume($id, array $options=array())
	{
		$default_options = array(
		                          'show_edit' => 1
								);
								
		$options = array_merge($default_options, $options);
		
		$res = array();
		$r =& $this->resumes[$id];
		
		foreach (Resume::$fields as $field_name=>$ralue_array)
		{
			if ($r->$field_name)
			{
				$res['{' . $field_name . '}'] = $r->$field_name;
				$this->tpl->SetBlock($field_name);
			}
		}
		
		$res['{sphere}'] = $r->sphere_name;
		$res['{specialty}'] = $r->specialty_name;
		$res['{resume_url}'] = $r->resume_url;
		$res['{age}'] = $r->age;
		$res['{view_count}'] = $r->view_count;
		
		if ($r->salary)
		{
		    $this->tpl->SetBlock('salary');
		    $res['{salary}'] = $r->salary;
		}
		
		if ($this->use_country)
		{
            $res['{country}'] = $r->country_name;
            $this->tpl->SetBlock('country');
		}
		
		if ($this->use_city)
		{
            $res['{city}'] = $r->city_name;
            $this->tpl->SetBlock('city');
		}
		
		if ($r->photo_url)
		{
		    $this->tpl->SetBlock('photo');
		    $res['{photo_url}'] = $r->photo_url;
		}
		
		if ($r->description)
		{
			$res['{description}'] = $r->description;
			$this->tpl->SetBlock('description');
		}
		$res['{add_date}'] = $r->add_date;
		$res['{phone}'] = $r->phone;
		$res['{email}'] = $r->email;
		$res['{contact_person}'] = $r->contact_person;
		
		$this->SetFavorites($id, 'resume', $res);
		
		if ($options['show_edit'])
		{
		    if ($r->user_id == $this->member['id'] || MODER_EDIT || MODER_DEL)
    		{
    		    $this->SetEdit($id, 'resume', $res);
    		}
		}
		
		$this->SetModerClass($res, $r, $id);
		
	    $this->initXFields(XFIELDS_RESUME);
        foreach ($this->xfields->showFields($r->xfields) as $fid => $field)
        {
            if ($field['value'])
            {
                $res["{xfield_{$fid}_title}"] = $field['title'];
                $res["{xfield_{$fid}_descr}"] = $field['description'];
                $res["{xfield_{$fid}_value}"] = $field['value'];
                
                $res['{xfields}'] .= $field['title'] . ": " . $field['value'] . "<br />";
                
                $this->tpl->SetBlock('xfield_' . $fid);
            }
        }
		
		return $res;
	}
	
	private function SetModerClass(array &$array, &$obj, $id)
	{
	    if (MODER_EDIT || MODER_DEL)
		{
			if (!$obj->allow_site && ($obj->date_exp > $this->dbase->timer->cur_time || !$obj->date_exp))
				$array['{moder_class}'] = 'moder_new id' . $id;
			elseif (!$obj->allow_site && $obj->date_exp < $this->dbase->timer->cur_time)
				$array['{moder_class}'] = 'moder_old id' . $id;
			else 
				$array['{moder_class}'] = 'id' . $id;
		}
		else 
			$array['{moder_class}'] = 'id' . $id;
	}
	
	private function SetFavorites($id, $type, array &$array)
	{
	    if (in_array($id, $this->favorites[$type]))
		{
		    $array['{favorites}'] = "<img OnClick=\"favorites(this, $id, '$type');\" src=\"{THEME}/job/images/minus.gif\" class=\"favorites\" />";
		}
		else
		{
		    $array['{favorites}'] = "<img OnClick=\"favorites(this, $id, '$type');\" src=\"{THEME}/job/images/plus.gif\" class=\"favorites\" />";
		}
	}
	
	private function SetEdit($id, $type, array &$array)
	{
	    if ($type == 'vacancy')
	    {
	        $allow = $this->vacancies[$id]->allow_site?0:1;
	    }
	    else
	    {
	        $allow = $this->resumes[$id]->allow_site?0:1;
	    }
	    
	    $array['[edit]'] = "<a OnClick=\"dropdownmenu(this, event, ShowMenu($id, $allow), '170px');return false;\" href=\"#\" >";
	    $array['[/edit]'] = "</a>";
	}
	
	public function ShowCompany($id, array $options=array(), Company &$comp = null)
	{
	    $default_options = array(
								);
								
		$options = array_merge($default_options, $options);
		
		$company = array();
		
		if ($comp)
		{
		    $c =& $comp;
		}
		else
		{
		    $c =& $this->companies[$id];
		}
		
		if ($this->use_country)
		{
            $company['{company_country}'] = $c->country_name;
            $this->tpl->SetBlock('country');
		}
		
		if ($this->use_city)
		{
            $company['{company_city}'] = $c->city_name;
            $this->tpl->SetBlock('city');
		}
		
		if ($c->logo_url)
		{
			$this->tpl->SetBlock('logo');
			$company['{logo_url}'] = $c->logo_url;
		}
		
		if ($c->description)
		{
			$company['{company_description}'] = $c->description;
			$this->tpl->SetBlock('company_description');
		}
		
		if ($c->date_register != '0000-00-00')
		{
		    $company['{date_register}'] = $c->date_register;
		    $this->tpl->SetBlock('date_register');
		}
		
		 $company['{name}'] = $c->name;
		 $company['{company_type}'] = $c->company_type;
		 $company['{OPF}'] = $c->OPF;
		 
		 $company['{company_url}'] = $this->GetCompanyURL($c->company_id, $c->alt_name);
		 
		 $company['{contact_person}'] = $c->contact_person;
		 $company['{phone}'] = $c->phone;
		 $company['{site}'] = $c->site;
		 $company['{email}'] = $c->email;
		 $company['{address}'] = $c->address;
		 $company['{company_add_date}'] = $c->add_date;
		 
	    $this->initXFields(XFIELDS_COMPANY);
        foreach ($this->xfields->showFields($c->xfields) as $fid => $field)
        {
            if ($field['value'])
            {
                $company["{xfield_{$fid}_title}"] = $field['title'];
                $company["{xfield_{$fid}_descr}"] = $field['description'];
                $company["{xfield_{$fid}_value}"] = $field['value'];
                
                $company['{xfields}'] .= $field['title'] . ": " . $field['value'] . "<br />";
                
                $this->tpl->SetBlock('xfield_' . $fid);
            }
        }
		 
        return $company;
	}
	
	private function ShowSearch(&$JS)
	{
	    if (!empty($this->search_array['specialty_id']))
		{
		    $specialty_str = '';
    		foreach ($this->GetSpecialties($this->search_array['sphere_id']) as $id=>$name)
    		{
    			if (@in_array($id, $this->search_array['specialty_id']))
    			{
    			    if ($specialty_str)
    				    $specialty_str .= ", ";
    				
    				$specialty_str .= "{id:$id, name:'$name'}";
    			}
    		}
    		
    		if ($specialty_str)
    		{
                $JS .= "SetSpecialties(new Array($specialty_str));";
            }
		}
		
		if (!empty($this->search_array['city_id']))
		{
		    $city_str = '';
    		foreach ($this->GetCities($this->search_array['country_id']) as $id=>$name)
    		{
    			if (@in_array($id, $this->search_array['city_id']))
    			{
    			    if ($city_str)
    				    $city_str .= ", ";
    				
    				$city_str .= "{id:$id, name:'$name'}";
    			}
    		}
    		
    		if ($city_str)
    		{
                $JS .= "\nSetCities(new Array($city_str));";
            }
		}
	}
	
	public function ShowSearchVacancy(array $options = array(), &$JS = '')
	{
	    $JS = '';
	    
		if ($this->use_country)
		{
			$this->tpl->SetBlock('country')
				 ->Set($this->tpl->Selection($this->GetCountries('search'), 'country_id', $this->search_array['country_id'], 'id="country_id_search"'), "{country}");
		}
		
		if ($this->use_city)
		{
			$this->tpl->SetBlock('city')
				 ->Set($this->tpl->Selection($this->GetCities($this->search_array['country_id'], 'search'), 'city_id', $this->search_array['city'], 'id="city_id_search"'), "{city}");
		}
		
		$this->tpl->Set($this->tpl->Selection($this->GetSpheres('search'), 'sphere_id', $this->search_array['sphere_id'], "id='sphere_id_search'"), '{sphere}');
		$this->tpl->Set($this->tpl->Selection($this->GetSpecialties($this->search_array['sphere_id'], 'search'), 'specialty_id', $this->search_array['specialty_id'], "id='specialty_id_search'"), '{specialty}');
		$this->tpl->Set($this->tpl->Selection(Job::$currecy_array, 'currency', $this->search_array['currency']), '{currency}');
		
		$fields = array(
						"age",
						"salary_min",
						"salary_max",
						"description"
						);
						
		foreach ($fields as $field)
		{
			if (!empty($this->search_array[$field]))
				$this->tpl->Set($this->search_array[$field], '{' . $field . '}');
		}
		
		foreach (Vacancy::$fields as $field_name=>$values_array)
		{
			if (empty($this->search_array[$field_name]))
				$this->search_array[$field_name] = '';
			
			$this->tpl->Set($this->tpl->Selection(array(0 => $this->lang['any']) + $values_array, $field_name, $this->search_array[$field_name]),  '{' . $field_name . '}');
		}
		
		$this->tpl->Set($this->tpl->Selection(Vacancy::$search_count_day, 'search_day'), "{sel_count}");
		$this->tpl->Set($this->tpl->Selection(array(0 => $this->lang['any'],
		                                            "M" => $this->lang['sex_man'],
                                                    "W" => $this->lang['sex_woman']), 'sex'), "{sex}");
                                                    
        $this->ShowSearch($JS);
                                                    
        if ($JS)
        {
            $JS =  <<<JS
$(document).ready(function()
{
	$JS
});
JS;
        }
	}
	
	public function ShowSearchResume(array $options = array(), &$JS = '')
	{
		if ($this->use_country)
		{
			$this->tpl->SetBlock('country')
				 ->Set($this->tpl->Selection($this->GetCountries('search'), 'country_id', $this->search_array['country_id'], 'id="country_id_search"'), "{country}");
		}
		
		if ($this->use_city)
		{
			$this->tpl->SetBlock('city')
				 ->Set($this->tpl->Selection($this->GetCities($this->search_array['country_id'], 'search'), 'city_id', $this->search_array['city'], 'id="city_id_search"'), "{city}");
		}
		
		$this->tpl->Set($this->tpl->Selection($this->GetSpheres('search'), 'sphere_id', $this->search_array['sphere_id'], "id='sphere_id_search'"), '{sphere}');
		$this->tpl->Set($this->tpl->Selection($this->GetSpecialties($this->search_array['sphere_id'], 'search'), 'specialty_id', $this->search_array['specialty_id'], "id='specialty_id_search'"), '{specialty}');
		$this->tpl->Set($this->tpl->Selection(Job::$currecy_array, 'currency', $this->search_array['currency']), '{currency}');
		
		$fields = array(
						"age_min",
						"age_max",
						"salary_min",
						"salary_max",
						"description"
						);
						
		foreach ($fields as $field)
		{
			if (!empty($this->search_array[$field]))
				$this->tpl->Set($this->search_array[$field], '{' . $field . '}');
		}
		
		foreach (Resume::$fields as $field_name=>$values_array)
		{
			if (empty($this->search_array[$field_name]))
				$this->search_array[$field_name] = '';
			
			$this->tpl->Set($this->tpl->Selection(array(0 => $this->lang['any']) + $values_array, $field_name, $this->search_array[$field_name]),  '{' . $field_name . '}');
		}
		
		$this->tpl->Set($this->tpl->Selection(array(0 => $this->lang['any'],
		                                            "M" => $this->lang['sex_man'],
                                                    "W" => $this->lang['sex_woman']), 'sex'), "{sex}");
                                                    
        $this->ShowSearch($JS);
                                                    
        if ($JS)
        {
            $JS =  <<<JS
$(document).ready(function()
{
	$JS
});
JS;
        }
	}
	
	public function ShowVacancySort()
	{
	    $this->tpl->load('vacancy_sort');
	    
	    foreach (Vacancy::$sort_array as $field=>$name)
	    {
	        if ($this->sort == $field && $this->sub_sort == "ASC")
	        {
	            $sub_sort = "DESC";
	        }
	        else
	        {
	            $sub_sort = "ASC";
	        }
	        
	        $this->tpl->Set("<a class=\"inside_ajax\" href=\"" . $this->tpl->GetUrl($this->search_array, array(), array('sort' => $field, 'sub_sort' => $sub_sort, 'action' => 'vacancy'), array(), array('use_alt_url' => false, 'clear' => true)) . "\" >", "[$field]");
	        $this->tpl->Set("</a>", "[/$field]");
	    }
	    
	    $this->tpl->Compile('sort');
	}
	
	public function ShowResumeSort()
	{
	    $this->tpl->load('resume_sort');
	    
	    foreach (Resume::$sort_array as $field=>$name)
	    {
	        if ($this->sort == $field && $this->sub_sort == "ASC")
	        {
	            $sub_sort = "DESC";
	        }
	        else
	        {
	            $sub_sort = "ASC";
	        }
	        
	        $this->tpl->Set("<a href=\"" . $this->tpl->GetUrl($this->search_array, array(), array('sort' => $field, 'sub_sort' => $sub_sort, 'action' => 'resume'), array(), array('use_alt_url' => false, 'clear' => true)) . "\" />", "[$field]");
	        $this->tpl->Set("</a>", "[/$field]");
	    }
	    
	    $this->tpl->Compile('sort');
	}
	
	public function GetCompanyURL($id, $alt_name)
	{
		return $this->tpl->GetUrl(array('action' => 'company', 'id' => $id), array('id' =>'/', $id . "-" . $alt_name));
	}
	
	/**
	 * 
	 * @param int $ctype
	 * @return Fields
	 */
    public function &initXFields($ctype = XFIELDS_VACANCY)
    {
        require_once ENGINE_DIR . '/job/classes/Fields.php';
        
        if (!is_object($this->xfields) || $this->xfields->getType() != $ctype)
        {
            $this->xfields = new Fields($ctype, $this);
        }
        
        return $this->xfields;
    }
	
	/**
	 * Check capcha
	 *
	 * @return boolean
	 */
	private function CheckCapcha()
	{
	    $sec_code_session = (!empty($_SESSION['sec_code_session']) && $_SESSION['sec_code_session'] != '') ? $_SESSION['sec_code_session'] : false;
		$_SESSION['sec_code_session'] = false;
			
		if ( $this->add_value['sec_code'] != $sec_code_session OR !$sec_code_session)
		{
		    unset($_SESSION['sec_code_session']);
		    $this->Errors[] = $this->lang['error_code'];
		    return false;
		}
		else 
		{
		    return true;
		}
	}
	
	private function CheckRegisterUser()
	{
		$name = $this->dbase->EscapeString(htmlspecialchars(trim($this->add_value['name'])));
		$email = $this->dbase->EscapeString(htmlspecialchars(trim($this->add_value['email'])));
		$member_id = $this->dbase->FetchArray($this->dbase->DirectQuery("SELECT * FROM " . USERPREFIX . "_users where name='$name' and password='".md5(md5($this->add_value['password1']))."'"));
		
		if ($member_id)
		{
			$this->LoginIn($this->add_value['password1']);
			$GLOBALS['member_id']  = $member_id;
			$this->member['id']    = $member_id['user_id'];
			$this->member['name']  = $member_id['name'];
			$this->member['group'] = $member_id['user_group'];
			$this->member['ip']    = $_SERVER['REMOTE_ADDR'];
		}
		else 
		{
			job_check_reg ($name, $email, $this->add_value['password1'], $this->add_value['password2']);
		}
	}
	
	private function RegisterUser()
	{
	    if (!class_exists('job_ParseFilter'))
	    {
	        throw new ExceptionAllError('Class job_ParseFilter not found');
	    }
	    
	    $parse = new job_ParseFilter(Array(), Array(), 1, 1);
	    
	    if (intval($GLOBALS['config']['reg_group']) < 3) $GLOBALS['config']['reg_group'] = 4;
			
		$regpassword = md5(md5($this->add_value['password1']));
		$name = $this->dbase->EscapeString($parse->process(htmlspecialchars(trim($this->add_value['name']))));
		$email = $this->dbase->EscapeString($parse->process(htmlspecialchars(trim($this->add_value['email']))));
		
		$this->dbase->DirectQuery("INSERT INTO " . USERPREFIX . "_users (name, password, email, reg_date, lastdate, user_group, info, signature, favorites, xfields, logged_ip) VALUES ('$name', '$regpassword', '$email', '{$this->dbase->timer->cur_time}', '{$this->dbase->timer->cur_time}', '" . $GLOBALS['config']['reg_group'] . "', '', '', '', '', '" . $this->dbase->EscapeString($this->member['ip']) . "')");
		    
		    $this->member['id']    = $this->dbase->Insert_id();
		    $this->member['name']  = $this->add_value['name'];
		    $this->member['group'] = $GLOBALS['config']['reg_group'];
		    
		    $this->LoginIn($this->add_value['password1']);
	}
	
	private function LoginIn($pass)
	{
		set_cookie ("dle_password",  md5($pass), 365);
		@session_register('dle_password');    
		$_SESSION['dle_password']    = md5($pass);
		
		if ($GLOBALS['config']['version_id'] < 7.2)
		{
			set_cookie ("dle_name", $this->member['name'], 365);
        	@session_register('dle_name');
        	$_SESSION['dle_name'] = $this->member['name'];
		}
		else 
		{
			set_cookie ("dle_user_id", $this->member['id'], 365);
        	@session_register('dle_user_id');
        	$_SESSION['dle_user_id'] = $this->member['id'];
		}
        
        
        $GLOBALS['is_logged'] = TRUE;
	}
	
	protected function AdditionSearchVacancy(array $search_param, array $options)
	{
		$this->PreparationSearch_User($search_param, $options);
	}
	
	protected function AdditionSearchResume(array $search_param, $options)
	{
		$this->PreparationSearch_User($search_param, $options);
	}
	
	private function PreparationSearch_User(array $search_param, array $options)
	{
	    if (!$options['no_moder'] && (MODER_DEL || MODER_EDIT))
		{
			switch ($this->config['general_show_moder']) 
			{
				case 1:
					break;
					
				case 2:
					$this->dbase->SetBeginBlockWhere();
					$this->dbase->SetWhere('date_exp', $this->dbase->timer->cur_time, ">");
					$this->dbase->SetWhere('date_exp', 0, "=", '', 'OR');
					$this->dbase->SetEndBlockWhere();
					$this->dbase->SetWhere('allow_site', 0, "=");
					break;
					
				case 3:
					$this->dbase->SetWhere('allow_site', 0, "=");
					break;
					
				case 4:
					$this->dbase->SetWhere('date_exp', $this->dbase->timer->cur_time, "<");
					break;
					
				case 0:
				default:
					$this->dbase->SetBeginBlockWhere();
					$this->dbase->SetWhere('date_exp', $this->dbase->timer->cur_time, ">");
					$this->dbase->SetWhere('date_exp', 0, "=", '', "OR");
					$this->dbase->SetEndBlockWhere();
					break;
			}
		}
		else 
		{
			$this->dbase->SetWhere('allow_site', 1, "=");
			$this->dbase->SetBeginBlockWhere();
			$this->dbase->SetWhere('date_exp', 0, "=");
			$this->dbase->SetWhere('date_exp', $this->dbase->timer->cur_time, ">", '', "OR");
			$this->dbase->SetEndBlockWhere();
		}
	}
	
	public function __destruct() { }
}
?>