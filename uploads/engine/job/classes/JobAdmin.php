<?php
/**
 * @package ModuleJob
 * @author Autushenko Denis <support@kaliostro.net>
 * @copyright (c) kaliostro
 * @version 1.0.0
 */

/**
 * Final class JobAdmin for using in AdminCenter
 * 
 * @access public
 * @package ModuleJob
 * @final 
 */
final class JobAdmin extends Job 
{
	
	public function __construct($config, $lang, DataBaseCore &$dbase, array $other_select = array(), array $other_checkbox = array())
	{
		parent::__construct($config, $lang, $dbase, $other_select, $other_checkbox);
	}
	
	public function __destruct() { }
	
	/**
	 * Create new company
	 *
	 * @param array $add_values
	 * @return integer
	 */
	public function NewCompany(array $add_values)
	{
		$this->add_value = $add_values;
		
		if (empty($this->add_value['company_admin']))
		{
		    $this->Errors[] = $this->lang['company_error_admin_is_empty'];
		}
		else 
		{
		    $name = $this->dbase->EscapeString($this->add_value['company_admin']);
    		$this->dbase->DirectQuery('SELECT user_id, name FROM ' . USERPREFIX . "_users WHERE name='$name'");
    		
    		if (!$this->dbase->NumRows())
    		{
    		    $this->Errors[] = $this->lang['company_error_admin_not_found'];
    		}
    		else 
    		{
    		    $user_id = $this->dbase->FetchArray();
    		    
    		    if (Company::GetCompany_by_user($user_id['user_id']))
    		    {
    		        $this->Errors[] = $this->lang['company_error_admin_exist'];
    		    }
    		}
    		
    		
		}
		
		if ($this->dbase->SelectOne('job_companies', array("company_id"), array("name" => $this->add_value['name'])))
		{
            $this->Errors[] = $this->lang['company_error_name_exist'];
		}
		
		$this->CheckCompanyError();
		
		$xfields = new Fields(XFIELDS_COMPANY, $this);
        
        $this->add_value['xfields'] = $xfields->EncodeFields($this->add_value);
        
        $this->Errors = $this->Errors + $xfields->getErrors();
		
		if (!$this->Errors)
		{
		    $this->add_value['user_id'] = $user_id['user_id'];
		    $this->add_value['user_name'] = $this->add_value['company_admin'];
		    
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
		
		return $this->SaveCompany($id);
	}
	
	/**
	 * Delete company
	 *
	 * @param int $id
	 */
	public function DeleteCompany($id)
	{
		$this->DoDeleteCompany($id);
	}
	
	/**
	 * Get All companies for vacancy
	 *
	 * @return array
	 */
	public function GetCompaniesArray()
	{
		if (!$this->companies)
		{
			$this->dbase->Select('job_companies', array('company_id', 'name', 'OPF'), array(), array('name' => 'ASC'));
			$this->companies = array();
			while ($company = $this->dbase->FetchArray())
			{
				$this->companies[$company['company_id']] = $company['name'] . ", " . $this->lang[$company['OPF'] . "_short"];
			}
		}
		return $this->companies;
	}
	
	/**
	 * Create new vacancy
	 *
	 * @param array $add_values
	 */
	public function NewVacancy(array $add_values)
	{
		$this->add_value = $add_values;
		
		$this->add_value['date_exp'] = strtotime($this->add_value['date_exp']);
		
		$this->CheckVacancyError();
		
		$xfields = new Fields(XFIELDS_VACANCY, $this);
        
        $this->add_value['xfields'] = $xfields->EncodeFields($this->add_value);
        
        $this->Errors = $this->Errors + $xfields->getErrors();
		
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
	 */
	public function UpdateVacancy(array $update_array, $id)
	{
		$this->add_value = $update_array;
		
		$this->old_value = $this->dbase->SelectOne('job_vacancies', array("*"), array('id' => $id));
		
		if (!$this->old_value)
			$this->Errors[] = $this->lang['vacancy_not_found'];
		
		$this->add_value['date_exp'] = strtotime($this->add_value['date_exp']);
		
		$this->SaveVacancy($id);
	}
	
	/**
	 * Create new resume
	 *
	 * @param array $add_values
	 */
	public function NewResume(array $add_values)
	{
		$this->add_value = $add_values;
		
		$this->CheckResumeError();
		
		$xfields = new Fields(XFIELDS_RESUME, $this);
        
        $this->add_value['xfields'] = $xfields->EncodeFields($this->add_value);
        
        $this->Errors = $this->Errors + $xfields->getErrors();
		
		$this->add_value['date_exp'] = strtotime($this->add_value['date_exp']);
		
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
		
		$this->add_value['date_exp'] = strtotime($this->add_value['date_exp']);
		
		$this->SaveResume($id);
	}
	
	protected function AdditionSearchVacancy(array $search_param)
	{
		
	}
	
	protected function AdditionSearchResume(array $search_param)
	{
		
	}
	
}
?>