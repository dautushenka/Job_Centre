<?php
/**
 * @package ModuleJob
 * @author Autushenko Denis <support@kaliostro.net>
 * @copyright (c) 2009 kaliostro
 * @version 1.0.0
 */

/**
 * Abstract class Job
 *
 * @access public
 * @package ModuleJob
 * @final 
 */
abstract class Job
{
    /**
     * Currency of array
     *
     * @var array
     */
    public static $currecy_array = array();
    
	/**
	 * Delegate of DataBase
	 *
	 * @var DataBaseCore
	 */
	public $dbase = null;
	
	/**
	 * Array of current language
	 *
	 * @var array
	 */
	public $lang = array();
	
	/**
	 * Array of settings
	 *
	 * @var array
	 */
	public $config = array();
	
	/**
	 * Array of countries
	 *
	 * @var array
	 */
	public $countries = array();
	
	/**
	 * Array of cities 
	 *
	 * Array( country_id => array ( city_id => city_name ));
	 * 
	 * @var array
	 */
	public $cities = array();
	
	/**
	 * Array of spheres
	 *
	 * @var array
	 */
	public $spheres = array();
	
	/**
	 * Array of specialties
	 *
	 * @var array
	 */
	public $specialties = array();
	
	/**
	 * Array of current property user
	 *
	 * array(
	 * 		"id"    => '',
	 * 		"name"  => '',
	 * 		"email"  => '',
	 * 		"group" => '',
	 * 		"ip"    => '',
	 * 		);
	 * 
	 * @var array
	 */
	public $member = array( "id"    => '',
					 		"name"  => '',
					  		"email"  => '',
					  		"group" => '',
					  		"ip"    => ''
					  		);
	
	/**
	 * If use country in module this 1, else 0
	 *
	 * @var bit
	 */
	public $use_country = 1;
	
	/**
	 * If use city in module this 1, else 0
	 *
	 * @var bit
	 */
	public $use_city = 1;
	
	/**
	 * Array of company from searching
	 *
	 * @var array
	 */
	public $companies = array();
	
	/**
	 * Count all company by searching param
	 *
	 * @var int
	 */
	public $companies_count = 0;
	
	/**
	 * Array of vacancy from searching
	 *
	 * @var unknown_type
	 */
	public $vacancies = array();
	
	/**
	 * Count all vacancies
	 *
	 * @var int
	 */
	public $vacancies_count = 0;
	
	/**
	 * Array of resumes from searching
	 *
	 * @var array
	 */
	public $resumes = array();
	
	/**
	 * Count of resumes
	 *
	 * @var int
	 */
	public $resumes_count = 0;
	
	/**
	 * Array of value by new add object
	 *
	 * @var array
	 */
	protected $add_value = array();
	
	/**
	 * Array of old value, when update
	 *
	 * @var array
	 */
	protected $old_value = array();
	
	/**
	 * Array of errors
	 *
	 * @var array
	 */
	public $Errors = array();
	
    public $search_array = array();
    
    public $sort = '';
    
    public $sub_sort = '';
	
	public function __construct($config, $lang, DataBaseCore &$dbase)
	{
		$this->config = $config;
		$this->lang = $lang;
		$this->dbase =& $dbase;
		
	    $this->use_country = (bool)$this->config['use_country']?1:0;
	    $this->use_city = (bool)$this->config['use_city']?1:0;
	}
	
	public function __destruct() { }
	
	/**
	 * Return company name from id
	 *
	 * @param int $company_id
	 * @return string
	 */
	public function GetCompanyName($company_id)
	{
		if (empty($company_id))
			throw new ExceptionAllError('Argument is null');
			
		$company = $this->dbase->SelectOne('job_companies', array('name'), array('company_id' => $company_id));
		
		if (empty($company['name']))
			return '';
		else
			return $company['name'];
	}
	
	/**
	 * Return country name from id
	 *
	 * @param int $country_id
	 * @return string Country name
	 */
	public function GetCountryName($country_id)
	{
		if (empty($country_id))
			throw new ExceptionAllError('Argument is null');
			
		if (!$this->countries)
			$this->GetCountries();
			
		return (empty($this->countries[$country_id]))?'':$this->countries[$country_id];
	}
	
	/**
	 * Return city name from id
	 *
	 * @param int $country_id
	 * @param int $city_id
	 * @return string City name
	 */
	public function GetCityName($country_id, $city_id)
	{
		if (empty($city_id))
			throw new ExceptionAllError('Argument is null');
			
		if (empty($country_id))
		{
			$info = $this->dbase->SelectOne('job_cities', array("*"), array('id' => $city_id));
			
			return (empty($info['name']))?'':$info['name'];
		}
		else 
		{
			if (empty($this->cities[$country_id]))
				$this->GetCities($country_id);
				
			return (empty($this->cities[$country_id][$city_id]))?'':$this->cities[$country_id][$city_id];
		}
	}
	
	/**
	 * Return sphere name from id
	 *
	 * @param int $sphere_id
	 * @return string sphere name
	 */
	public function GetSphereName($sphere_id)
	{
		if (empty($sphere_id))
			throw new ExceptionAllError('Argument is null');
			
		if (!$this->spheres)
			$this->Getspheres();
			
		return (empty($this->spheres[$country_id]))?'':$this->spheres[$country_id];
	}
	
	/**
	 * Return Specialty name from id
	 *
	 * @param int $sphere_id
	 * @param array $specialty_id
	 * @return array Specialties name
	 */
	public function GetSpecialtyName($sphere_id, array $specialty_id)
	{
		if (empty($specialty_id))
			throw new ExceptionAllError('Argument is null');
			
		$specialty_name = array();
		if (empty($sphere_id))
		{
			$this->dbase->SetWhere('id', $specialty_id, "IN", 'job_specialties');
			$info = $this->dbase->Select('job_specialties', array("*"));
			
			while ($row = $this->dbase->FetchArray())
			{
				$specialty_name[$row['id']] = $row['name'];
			}
		}
		else 
		{
			foreach ($this->GetSpecialty($sphere_id) as $id=>$name)
			{
				if (in_array($id, $specialty_id))
					$specialty_name[$id] = $name;
			}
				
			return $specialty_name;
		}
	}
	
	/**
	 * Retrun array of countries
	 *
	 * Array(id => country_name);
	 * 
	 * @param string $type add or search
	 * @return array
	 */
	public function &GetCountries($type = "")
	{
		if (!$this->countries && (!$this->config['general_cache'] || !($this->countries = Cache::GetArrayCache('countries'))))
		{
			$this->dbase->Select('job_countries', array("*"), array(), array("name" => 'ASC'));
			while ($row = $this->dbase->FetchArray())
			{
				$this->countries[$row['id']] = $row['name'];
			}
			if ($this->config['general_cache'])
				Cache::SetArrayCache('countries', $this->countries);
		}
		
		if ($type == "add")
			$this->countries = array('' => $this->lang['sel_country']) + $this->countries;
		elseif ($type == "search")
			$this->countries = array('' => $this->lang['any']) + $this->countries;
		
		return $this->countries;
	}
	
	/**
	 * Return array of cities currenct country
	 *
	 * @param int $country_id
	 * @param string $type add or search
	 * @return array
	 */
	public function &GetCities($country_id, $type = "")
	{
		if ($this->use_country && !intval($country_id))
			return array('' => $this->lang['sel_country']);
		elseif (!$this->use_country)
		{
		    $country_id = 0;
		}
		
		if (empty($this->cities[$country_id]) && (!$this->config['general_cache'] || !($this->cities[$country_id] = Cache::GetArrayCache('cities_c' . $country_id))))
		{
		    if ($this->use_country)
		    {
		        $this->dbase->Select('job_cities', array("*"), array('country_id' => $country_id), array("name" => 'ASC'));
		    }
		    else
		    {
		        $this->dbase->Select('job_cities', array("*"), array(), array("name" => 'ASC'));
		    }
			while ($row = $this->dbase->FetchArray())
			{
				$this->cities[$country_id][$row['id']] = $row['name'];
			}
			if ($this->config['general_cache'])
				Cache::SetArrayCache('cities_c' . $country_id, $this->cities[$country_id]);
		}
		
		if ($type == "add")
			return array('' => $this->lang['sel_city']) + $this->cities[$country_id];
		elseif ($type == "search")
			return array('' => $this->lang['any']) + $this->cities[$country_id];
		
		return $this->cities[$country_id];
	}
	
	/**
	 * Return array of sphere 
	 *
	 * @param string $type
	 * @return array
	 */
	public function &GetSpheres($type = "")
	{
		if (!$this->spheres && (!$this->config['general_cache'] || !($this->spheres = Cache::GetArrayCache('spheres'))))
		{
			$this->dbase->Select('job_spheres', array("*"), array(), array("name" => 'ASC'));
			while ($row = $this->dbase->FetchArray())
			{
				$this->spheres[$row['id']] = $row['name'];
			}
			if ($this->config['general_cache'])
				Cache::SetArrayCache('spheres', $this->spheres);
		}
		
		if ($type == "add")
			return array('' => $this->lang['sel_sphere']) + $this->spheres;
		elseif ($type == "search")
			return array('' => $this->lang['any']) + $this->spheres;
			
		return $this->spheres;
	}
	
	/**
	 * Return array of specialties
	 *
	 * @param int $sphere_id
	 * @param string $type add or search
	 * @return array
	 */
	public function &GetSpecialties($sphere_id, $type = '')
	{
		if (!$sphere_id)
			return array('' => $this->lang['sel_sphere']);
		
		if (empty($this->specialties[$sphere_id]) && (!$this->config['general_cache'] || !($this->specialties[$sphere_id] = Cache::GetArrayCache('specialties_s' . $sphere_id))))
		{
			$this->dbase->Select('job_specialties', array("*"), array('sphere_id' => $sphere_id), array("name" => 'ASC'));
			$this->specialties[$sphere_id] = array();
			while ($row = $this->dbase->FetchArray())
			{
				$this->specialties[$sphere_id][$row['id']] = $row['name'];
			}
			if ($this->config['general_cache'])
				Cache::SetArrayCache('specialties_s' . $sphere_id, $this->specialties[$sphere_id]);
		}
		
		if ($type == "add")
			return array('' => $this->lang['sel_specialty']) + $this->specialties[$sphere_id];
		elseif ($type == "search")
			return array('' => $this->lang['any']) + $this->specialties[$sphere_id];
		
		return $this->specialties[$sphere_id];
	}
	
	/**
	 * Search company by search_param
	 *
	 * Searchimg options
	 * <code>
	 * $options = array(
	 *							"count"      => 10,
	 *							"page"       => 0,
	 *							"get_count"  => 1,
	 *							);
	 * </code>
	 * @param array $search_param search array array('column' => 'value');
	 * @param array $options searching options
	 */
	public function SearchCompany(array $search_param = array(), array $options = array())
	{
	    $search_param = array_merge($this->search_array, $search_param);
	    
		$default_options = array(
								"count"      => 10,
								"page"       => $_REQUEST['page'],
								"get_count"  => 1,
								);
								
		$options = array_merge($default_options, $options);
		
		$selection = array(
							'job_companies'  => array('*'),
							);
							
		if ($this->use_country)
		{
			$join_table['job_countries'] = array("id" => 'country_id');
			$selection['job_countries'] = array('country_name' => 'name');
		}
		
		if ($this->use_city)
		{
			$join_table['job_cities'] = array('id' => 'city_id');
			$selection['job_cities'] = array('city_name' => 'name');
		}
		
		$this->dbase->BuildQuery('job_companies', $join_table);
		$this->dbase->SetSelection($selection);
		
		$this->PreparationSearch($search_param);
		$this->PreparationCompanySearch($search_param);
		
		if (!empty($search_param['id']))
		{
			if (is_array($search_param['id']))
				$this->dbase->SetWhere('id', $search_param['id'], "IN");
			else 
				$this->dbase->SetWhere('id', $search_param['id']);
		}
		
		if (intval($options['count']) <= 0)
			$count['limit'] = 10;
		else 
			$count['limit'] = intval($options['count']);
				
		if ((int)$options['page'] > 0)
			$count['start'] = ((int)$options['page'] - 1) * $count['limit'];
		else 
			$count['start'] = 0;
				
		$this->dbase->ExecuteBuildQuery(array(), $count);
		
		while ($row = $this->dbase->FetchArray())
		{
			$this->companies[$row['company_id']] = new Company($row);
		}
		
		if ($options['get_count'])
			$this->companies_count = $this->dbase->CountForBuldQuery();
	}
	
	/**
	 * Search vacancy of search param
	 *
	 * Searchimg options
	 * <code>
	 * $options = array(
	 *							"count"      => 10,
	 *							"page"       => 0,
	 *							"get_count"  => 1,
	 *							);
	 * </code>
	 * 
	 * @param array $search_param
	 * @param array $options
	 */
	public function SearchVacancy(array $search_param, array $options = array())
	{
	    $search_param = array_merge($this->search_array, $search_param);
	    
		$default_options = array(
								"count"      => 10,
								"page"       => $_REQUEST['page'],
								"get_count"  => 1,
								"no_moder"   => 0,
								);
								
		$options = array_merge($default_options, $options);
		
		$join_table = array("job_spheres"  => array('id' => 'sphere_id'),
							"job_companies"  => array('company_id' => 'company_id'),
							"job_specialties"  => array('id' => 'specialty_id')
							);
						
		$selection = array(
							'job_companies'  => array('*', 'company_name' => 'name'),
							'job_vacancies'  => array('*'),
							'job_spheres'  => array('sphere_name' => 'name'),
							'job_specialties'  => array('specialty_name' => 'name', "spe_id" => 'id')
							);
							
		if ($this->use_country)
		{
			$join_table['job_countries'] = array("id" => 'country_id');
			$selection['job_countries'] = array('country_name' => 'name');
		}
		
		if ($this->use_city)
		{
			$join_table['job_cities'] = array('id' => 'city_id');
			$selection['job_cities'] = array('city_name' => 'name');
		}
		
		$this->dbase->BuildQuery('job_vacancies', $join_table);
		$this->dbase->SetSelection($selection);
		
		$this->PreparationSearch($search_param);
		$this->PreparationVacancySearch($search_param);
		$this->AdditionSearchVacancy($search_param, $options);
		
		if (!empty($search_param['id']))
		{
			if (is_array($search_param['id']))
				$this->dbase->SetWhere('id', $search_param['id'], "IN");
			else 
				$this->dbase->SetWhere('id', $search_param['id']);
		}
		
		if (intval($options['count']) <= 0)
			$count['limit'] = 10;
		else 
			$count['limit'] = intval($options['count']);
				
		if ((int)$options['page'] > 0)
			$count['start'] = ((int)$options['page'] - 1) * $count['limit'];
		else 
			$count['start'] = 0;
			
		if ($this->sort)
		{
		    switch ($this->sort)
		    {
		        case "salary":
		            if ($this->sub_sort == "ASC")
		            {
		                $order = array("salary_min_search" => "ASC");
		            }
		            else
		            {
		                $order = array("salary_max_search" => "DESC");
		            }
		            break;
		            
		        case "age":
		            if ($this->sub_sort == "ASC")
		            {
		                $order = array("age_min" => "ASC");
		            }
		            else
		            {
		                $order = array("age_max" => 'DESC');
		            }
		            break;
		            
		        default:
		            $order = array($this->sort => $this->sub_sort);
		            break;
		    }
		}
		else
		{
            $order = array();
		}
				
		$resourse = $this->dbase->ExecuteBuildQuery($order, $count);
		
		while ($row = $this->dbase->FetchArray($resourse))
		{
			$this->vacancies[$row['id']] = new Vacancy($row);
		}
		
		if ($options['get_count'])
			$this->vacancies_count = $this->dbase->CountForBuldQuery();
	}
	
	/**
	 * Search resume of search param
	 *
	 * Searchimg options
	 * <code>
	 * $options = array(
	 *							"count"      => 10,
	 *							"page"       => 0,
	 *							"get_count"  => 1,
	 *							);
	 * </code>
	 * 
	 * @param array $search_param
	 * @param array $options
	 */
	public function SearchResume(array $search_param, array $options = array())
	{
	    $search_param = array_merge($this->search_array, $search_param);
	    
		$default_options = array(
								"count"      => 10,
								"page"       => $_REQUEST['page'],
								"get_count"  => 1,
								"no_moder"   => 0,
								);
								
		$options = array_merge($default_options, $options);
		
		$join_table = array("job_spheres"  => array('id' => 'sphere_id'),
							"job_specialties"  => array('id' => 'specialty_id')
							);
						
		$selection = array(
							'job_resumes'  => array('*'),
							'job_spheres'  => array('sphere_name' => 'name'),
							'job_specialties'  => array('specialty_name' => 'name', "spe_id" => 'id')
							);
							
		if ($this->use_country)
		{
			$join_table['job_countries'] = array("id" => 'country_id');
			$selection['job_countries'] = array('country_name' => 'name');
		}
		
		if ($this->use_city)
		{
			$join_table['job_cities'] = array('id' => 'city_id');
			$selection['job_cities'] = array('city_name' => 'name');
		}
		
		$this->dbase->BuildQuery('job_resumes', $join_table);
		$this->dbase->SetSelection($selection);
		
		$this->PreparationSearch($search_param);
		$this->PreparationResumeSearch($search_param);
		$this->AdditionSearchResume($search_param, $options);
		
		if (!empty($search_param['id']))
		{
			if (is_array($search_param['id']))
				$this->dbase->SetWhere('id', $search_param['id'], "IN");
			else 
				$this->dbase->SetWhere('id', $search_param['id']);
		}
		
		if (intval($options['count']) <= 0)
			$count['limit'] = 10;
		else 
			$count['limit'] = intval($options['count']);
				
		if ((int)$options['page'] > 0)
			$count['start'] = ((int)$options['page'] - 1) * $count['limit'];
		else 
			$count['start'] = 0;
			
		if ($this->sort)
		{
		    switch ($this->sort)
		    {
		        case "salary":
		            if ($this->sub_sort == "ASC")
		            {
		                $order = array("salary_min_search" => "ASC");
		            }
		            else
		            {
		                $order = array("salary_max_search" => "DESC");
		            }
		            break;
		            
		        default:
		            $order = array($this->sort => $this->sub_sort);
		            break;
		    }
		}
		else
		{
            $order = array();
		}
				
		$resourse = $this->dbase->ExecuteBuildQuery($order, $count);
		
		while ($row = $this->dbase->FetchArray($resourse))
		{
			$this->resumes[$row['id']] = new Resume($row);
		}
		
		if ($options['get_count'])
			$this->resumes_count = $this->dbase->CountForBuldQuery();
	}
	
	/**
	 * Check errors in add and save
	 *
	 */
	protected function CheckError()
	{
		if ($this->use_country && (empty($this->add_value['country_id']) || !array_key_exists($this->add_value['country_id'], $this->GetCountries())))
			$this->Errors[] = $this->lang['sel_country'];
			
		if ($this->use_city && (empty($this->add_value['city_id']) || !array_key_exists($this->add_value['city_id'], $this->GetCities($this->add_value['country_id']))))
			$this->Errors[] = $this->lang['sel_city'];
	}
	
	/**
	 * Check errors in add company
	 *
	 */
	protected function CheckCompanyError()
	{
		$this->CheckError();
		
		if (!empty($this->add_value['email']) && !job_check_email($this->add_value['email']))
			$this->Errors[] = $this->lang['company_wrong_email'];
			
		if (!empty($this->add_value['phone']) && !job_check_phone($this->add_value['phone']))
			$this->Errors[] = $this->lang['company_wrong_phone'];
			
		if (!empty($this->add_value['site']) && !job_check_site($this->add_value['site']))
			$this->Errors[] = $this->lang['company_wrong_site'];
		
		if (empty($this->add_value['name']))
			$this->Errors[] = $this->lang['company_error_name'];
		
		if (!empty($this->config['company_need_field']))
		{
			foreach ($this->config['company_need_field'] as $field)
			{
				if (empty($this->add_value[$field]) && $field != "logo")
					$this->Errors[] = $this->lang['company_error_' .$field];
			}
		}
		
		if (!empty($_FILES['logo']['name']))
			$this->CheckUploadError('logo');
		elseif (in_array('logo', $this->config['company_need_field']) && empty($this->old_value['logo']))
			$this->Errors[] = $this->lang['company_error_logo'];
			
		if (empty($this->add_value['OPF']) || !array_key_exists($this->add_value['OPF'], Company::$OPF_array))
			$this->Errors[] = $this->lang['company_error_OPF'];
			
		if (empty($this->add_value['company_type']) || !in_array($this->add_value['company_type'], array("EM", "PA")))
			$this->Errors[] = $this->lang['company_error_company_type'];
			
		if (!empty($this->add_value['date_register']) && (strtotime($this->add_value['date_register']) === -1 || strtotime($this->add_value['date_register']) > $this->dbase->timer->cur_time))
			$this->Errors[] = $this->lang['company_wrong_date_register'];
	}
	
	/**
	 * Check errors in add and save vacancy
	 *
	 */
	protected function CheckVacancyError()
	{
		$this->CheckError();
		
		if (empty($this->add_value['sphere_id']) || !array_key_exists($this->add_value['sphere_id'], $this->GetSpheres()))
			$this->Errors[] = $this->lang['sel_sphere'];
		else if (empty($this->add_value['specialty']))
		{
		    $this->Errors[] = $this->lang['sel_specialty'];
		}
		else
		{
		    $spec_id = $this->getSpecialtyByName($this->add_value['specialty']);
		    
		    if ($spec_id)
		    {
		        $this->add_value['specialty_id'] = $spec_id['id'];
		    }
		    else
		    {
		        $alt_name = job_totranslit($this->add_value['specialty']);
		        $this->add_value['specialty_id'] = $this->dbase->Insert('job_specialties', array("name" => $this->add_value['specialty'], "alt_name" => $alt_name, "sphere_id" => $this->add_value['sphere_id']));
		    }
		}
		/*
		if ($this->add_value['sphere_id'] && (empty($this->add_value['specialty_id']) || !array_key_exists($this->add_value['specialty_id'], $this->GetSpecialties($this->add_value['sphere_id']))))
			$this->Errors[] = $this->lang['sel_specialty'];
		*/
			
		if ($this->add_value['sphere_id'] && (empty($this->add_value['specialty_id']) || !array_key_exists($this->add_value['specialty_id'], $this->GetSpecialties($this->add_value['sphere_id']))))
		{
		    
		}
		
		if (!empty($this->config['vacancy_need_field']))
		{
			foreach ($this->config['vacancy_need_field'] as $field)
			{
				if (empty($this->add_value[$field]))
					$this->Errors[] = $this->lang['vacancy_error_' .$field];
			}
		}
		
		if (empty($this->add_value['company_id']))
		{
			if (!empty($this->add_value['phone']) && !job_check_phone($this->add_value['phone']))
				$this->Errors[] = $this->lang['vacancy_error_phone_invalid'];
				
			if (!empty($this->add_value['email']) && !job_check_email($this->add_value['email']))
				$this->Errors[] = $this->lang['vacancy_error_email_invalid'];
		}
	}
	
	/**
	 * Check errors in add and save resume
	 *
	 */
	protected function CheckResumeError()
	{
		$this->CheckError();
		
		if (empty($this->add_value['sphere_id']) || !array_key_exists($this->add_value['sphere_id'], $this->GetSpheres()))
		{
		    $this->Errors[] = $this->lang['sel_sphere'];
		}
		else if (empty($this->add_value['specialty']))
		{
		    $this->Errors[] = $this->lang['sel_specialty'];
		}
		else
		{
		    $spec_id = $this->getSpecialtyByName($this->add_value['specialty']);
		    
		    if ($spec_id)
		    {
		        $this->add_value['specialty_id'] = $spec_id['id'];
		    }
		    else
		    {
		        $alt_name = job_totranslit($this->add_value['specialty']);
		        $this->add_value['specialty_id'] = $this->dbase->Insert('job_specialties', array("name" => $this->add_value['specialty'], "alt_name" => $alt_name, "sphere_id" => $this->add_value['sphere_id']));
		    }
		}
			/*
		if ($this->add_value['sphere_id'] && (empty($this->add_value['specialty_id']) || !array_key_exists($this->add_value['specialty_id'], $this->GetSpecialties($this->add_value['sphere_id']))))
			$this->Errors[] = $this->lang['sel_specialty'];
		*/
		if (!empty($this->config['resume_need_field']))
		{
			foreach ($this->config['resume_need_field'] as $field)
			{
				if (empty($this->add_value[$field]))
					$this->Errors[] = $this->lang['resume_error_' .$field];
			}
		}
		
		if (!empty($_FILES['photo']['name']))
			$this->CheckUploadError('photo');
		elseif (in_array('photo', $this->config['resume_need_field']) && empty($this->old_value['photo']))
			$this->Errors[] = $this->lang['resume_error_photo'];
		
		if (!empty($this->add_value['phone']) && !job_check_phone($this->add_value['phone']))
			$this->Errors[] = $this->lang['resume_error_phone_invalid'];
			
		if (!empty($this->add_value['email']) && !job_check_email($this->add_value['email']))
			$this->Errors[] = $this->lang['resume_error_email_invalid'];
			
	}
	
	/**
	 * Check uploads errors
	 *
	 */
	protected function CheckUploadError($name = 'logo')
	{
		if ($_FILES[$name]['name'])
		{
			switch ($_FILES[$name]['error'])
			{
				case 0 : break;
				case 1 : $this->Errors[] = $_FILES[$name]['name'] . $this->lang['company_error_max_php_ini']; break;
				case 2 : $this->Errors[] = $_FILES[$name]['name'] . $this->lang['company_error_max_php_ini']; break;
				case 3 : throw new ExceptionAllError($_FILES[$name]['name'].' was only partially uploaded'); break;
				case 4 : throw new ExceptionAllError('No file was uploaded'); break;
				case 6 : throw new ExceptionAllError('Missing a temporary folder'); break;
				case 7 : throw new ExceptionAllError('Failed to write '.$_FILES[$name]['name'].' to disk'); break;
				case 8 : $this->Errors[] = $this->lang['company_error_type_image'] . $_FILES[$name]['name']; break;
				default : throw new ExceptionAllError('Unidentified Error, caused by '.$_FILES[$name]['name']); break;
			}
			
			switch ($name)
			{
			    case "logo":
			        if ($_FILES[$name]['size'] <= 0)
        				$this->Errors[] = $_FILES[$name]['name'] . $this->lang['company_error_logo_empty'];
        						
        			if ($this->config['company_logo_size_byte'] && ($_FILES[$name]['size'] >= $this->config['company_logo_size_byte']*1024)) 
        				$this->Errors[] = $this->lang['company_error_logo_big_image'] . formatsize($this->config['company_logo_size_byte']*1024);
        							
        			if (!in_array(strtolower(end(explode(".", $_FILES[$name]['name']))), Company::$allowed_mime_types))
        				$this->Errors[] = $this->lang['company_error_type_image'] . $_FILES[$name]['name'];
        				
        			if (!is_writable(LOGO_PATCH))
                        throw new ExceptionAllError("Папка " . LOGO_PATCH . " недоступна для записи");
			        break;
			        
			    case "photo":
			        if ($_FILES[$name]['size'] <= 0)
        				$this->Errors[] = $_FILES[$name]['name'] . $this->lang['resume_error_photo_empty'];
        						
        			if ($this->config['resume_photo_size_byte'] && ($_FILES[$name]['size'] >= $this->config['resume_photo_size_byte']*1024)) 
        				$this->Errors[] = $this->lang['resume_error_photo_big_image'] . formatsize($this->config['resume_photo_size_byte']*1024);
        							
        			if (!in_array(strtolower(end(explode(".", $_FILES[$name]['name']))), Resume::$allowed_mime_types))
        				$this->Errors[] = $this->lang['resume_error_type_image'] . $_FILES[$name]['name'];
        				
        			if (!file_exists(PHOTO_PATCH) || !is_writable(PHOTO_PATCH))
                        throw new ExceptionAllError("Папка " . PHOTO_PATCH . " не найдена или недоступна для записи");
			        break;
			        
			    default:
			        break;
			}
		}
	}
	
	/**
	 * Preparation values for search
	 *
	 */
	protected function PreparationSearch(array &$search_param)
	{
		if ($this->use_country && !empty($search_param['country_id']))
			$this->dbase->SetWhere('country_id', $search_param['country_id']);
				
		if ($this->use_city && !empty($search_param['city_id']))
			$this->dbase->SetWhere('city_id', $search_param['city_id'], "IN");
			
		if (!empty($search_param['user_id']) && intval($search_param['user_id']))
            $this->dbase->SetWhere('user_id', $search_param['user_id']);
	}
	
	/**
	 * Preparation values for search vacancy
	 *
	 * @param array $search_param
	 */
	protected function PreparationVacancySearch(array $search_param)
	{
		if (!empty($search_param['company_id']))
			$this->dbase->SetWhere('company_id', $search_param['company_id']);
				
		if (!empty($search_param['age']))
		{
			$this->dbase->SetWhere('age_min', $search_param['age'], "<=");
			$this->dbase->SetWhere('age_max', $search_param['age'], ">=");
		}
		
		if (empty($search_param['currency']) || empty($this->config['currency'][$search_param['currency']]))
			$search_param['currency'] = 'USD';	
		
		if (!empty($search_param['salary']))
		{
			$this->dbase->SetBeginBlockWhere();
			$this->dbase->SetWhere('salary_max_search', $search_param['salary']*$this->config['currency'][$search_param['currency']], '>');
			$this->dbase->SetBeginBlockWhere();
			$this->dbase->SetWhere('salary_min_search', $search_param['salary']*$this->config['currency'][$search_param['currency']], "<");
			$this->dbase->SetWhere('salary_max_search', 0, '=');
			$this->dbase->SetEndBlockWhere("OR");
			$this->dbase->SetEndBlockWhere();
		}
		
		foreach (Vacancy::$fields as $field=>$values)
		{
		    if (!empty($search_param[$field]) && !empty($values[$search_param[$field]])) 
		    {
		        $this->dbase->SetWhere($field, $search_param[$field]);;
		    }
		}
		
		if (!empty($search_param['sphere_id']))
			$this->dbase->SetWhere('sphere_id', $search_param['sphere_id']);
			
		if (!empty($search_param['specialty_id']) && is_array($search_param['specialty_id']))
			$this->dbase->SetWhere('specialty_id', $search_param['specialty_id'], 'IN');
			
		if (!empty($search_param['description']))
			$this->dbase->SetWhere('description', $search_param['description'], 'LIKE');
	}
	
	/**
	 * Preparation values for search resume
	 *
	 * @param array $search_param
	 */
	protected function PreparationResumeSearch(array $search_param)
	{
		if (!empty($search_param['age_min']) && !empty($search_param['age_max']))
			$this->dbase->SetWhere('age', array($search_param['age_min'], $search_param['age_max']), 'BETWEEN');
		elseif (!empty($search_param['age_min']))
			$this->dbase->SetWhere('age', $search_param['age_min'], ">=");
		elseif (!empty($search_param['age_max']))
			$this->dbase->SetWhere('age', $search_param['age_max'], "<=");
		
		if (empty($search_param['currency']) || empty($this->config['currency'][$search_param['currency']]))
			$search_param['currency'] = 'USD';	
		
		if (!empty($search_param['salary']))
		{
			$this->dbase->SetBeginBlockWhere();
			$this->dbase->SetWhere('salary_max_search', $search_param['salary']*$this->config['currency'][$search_param['currency']], '>');
			$this->dbase->SetBeginBlockWhere();
			$this->dbase->SetWhere('salary_min_search', $search_param['salary']*$this->config['currency'][$search_param['currency']], "<");
			$this->dbase->SetWhere('salary_max_search', 0, '=');
			$this->dbase->SetEndBlockWhere("OR");
			$this->dbase->SetEndBlockWhere();
		}
		
        foreach (Resume::$fields as $field=>$values)
        {
            if (!empty($search_param[$field]) && !empty($values[$search_param[$field]])) 
            {
                $this->dbase->SetWhere($field, $search_param[$field]);;
            }
        }
		
		if (!empty($search_param['sphere_id']))
			$this->dbase->SetWhere('sphere_id', $search_param['sphere_id']);
			
		if (!empty($search_param['specialty_id']) && is_array($search_param['specialty_id']))
			$this->dbase->SetWhere('specialty_id', $search_param['specialty_id'], 'IN');
			
		if (!empty($search_param['description']))
			$this->dbase->SetWhere('description', $search_param['description'], 'LIKE');
	}
		
	/**
	 * Preparation values for search company
	 *
	 * @param array $search_param
	 */
	protected function PreparationCompanySearch(array $search_param)
	{
		if (!empty($search_param['company_id']))
			$this->dbase->SetWhere('company_id', $search_param['company_id']);
				
		if (!empty($search_param['user_id']))
			$this->dbase->SetWhere('user_id', $search_param['user_id']);
				
		if (!empty($search_param['name']))
			$this->dbase->SetWhere('name', $search_param['name'], 'LIKE');
				
		if (!empty($search_param['company_type']))
			$this->dbase->SetWhere('company_type', $search_param['company_type']);
			
		if (!empty($search_param['OPF']))
			$this->dbase->SetWhere('OPF', $search_param['OPF']);
			
		if (!empty($search_param['description']))
			$this->dbase->SetWhere('description', $search_param['description'], 'LIKE');
	}
	
	/**
	 * Preparation values for vacancy and resume
	 *
	 */
	protected function PreparationValues()
	{
		if (empty($this->add_value['currency']) || !array_key_exists($this->add_value['currency'], self::$currecy_array))
		{
		    if ($currency = reset(array_keys(self::$currecy_array)))
		    {
		        $this->add_value['currency'] = $currency;
		    }
		    else 
		    {
		        $this->add_value['currency'] = '';
		    }
		}
			
		if ($this->add_value['salary_max'])
			$this->add_value['salary_max_search'] = $this->Calculate_SalarySearch('salary_max');
			
		if ($this->add_value['salary_min'])
			$this->add_value['salary_min_search'] = $this->Calculate_SalarySearch('salary_min');
			
		if ((int)$this->add_value['salary_min'] > (int)$this->add_value['salary_max'])
			$this->add_value['salary_max'] = 0;
	}
	
	/**
	 * Preparation Values for companies
	 *
	 */
	protected function PreparationCompanyValues()
	{
		if (!class_exists('job_ParseFilter'))
			throw new ExceptionAllError('Не найден класс job_job_ParseFilter');
			
		$parse = new job_ParseFilter(Array(), Array(), 1, 1);
		
		$this->add_value['name'] = $parse->process(trim($this->add_value['name']));
		$this->add_value['alt_name'] = $parse->process(trim($this->add_value['alt_name']));
		$this->add_value['description'] = $parse->process(trim($this->add_value['description']));
		$this->add_value['contact_person'] = $parse->process(trim($this->add_value['contact_person']));
		$this->add_value['phone'] = $parse->process(trim($this->add_value['phone']));
		$this->add_value['site'] = $parse->process(trim($this->add_value['site']));
		$this->add_value['address'] = $parse->process(trim($this->add_value['address']));
		$this->add_value['date_register'] = $parse->process(trim($this->add_value['date_register']));
		
		if (isset($this->add_value['date_register']) && $this->add_value['date_register'] == '')
		{
		    unset($this->add_value['date_register']);
		}
		
		if (empty($this->add_value['alt_name']))
			$this->add_value['alt_name'] = job_totranslit($this->add_value['name']);
		else 
			$this->add_value['alt_name'] = job_totranslit($this->add_value['alt_name']);
		
		if (!empty($_FILES['logo']['name']))
			$this->UploadImage('logo');
		elseif (empty($this->old_value['logo']) || !empty($this->add_value['del_logo']))
			$this->add_value['logo'] = '';
	}
	
	/**
	 * Preparation Values for vacancies
	 *
	 */
	protected function PreparationVacancyValues()
	{
		$this->PreparationValues();
		
		if (!class_exists('job_ParseFilter'))
			throw new ExceptionAllError('Не найден класс job_ParseFilter');
			
		$parse = new job_ParseFilter(Array(), Array(), 1, 1);

		$this->add_value['description'] = $parse->process(trim($this->add_value['description']));
		
		if (!empty($this->add_value['company_id']))
		{
			$this->add_value['contact_person'] = '';
			$this->add_value['phone'] = '';
			$this->add_value['email'] = '';
		}
		else 
		{
			$this->add_value['contact_person'] = $parse->process(trim($this->add_value['contact_person']));
			$this->add_value['phone'] = $parse->process(trim($this->add_value['phone']));
			$this->add_value['email'] = $parse->process(trim($this->add_value['email']));
		}
		
		if ((int)$this->add_value['age_min'] > (int)$this->add_value['age_max'])
			$this->add_value['age_max'] = '';
	}
	
	/**
	 * Preparation Values for resumes
	 *
	 */
	protected function PreparationResumeValues()
	{
		$this->PreparationValues();
		
		if (!class_exists('job_ParseFilter'))
			throw new ExceptionAllError('Не найден класс job_ParseFilter');
			
		$parse = new job_ParseFilter(Array(), Array(), 1, 1);

		$this->add_value['description'] = $parse->process(trim($this->add_value['description']));
		
		$this->add_value['contact_person'] = $parse->process(trim($this->add_value['contact_person']));
		$this->add_value['phone'] = $parse->process(trim($this->add_value['phone']));
		$this->add_value['email'] = $parse->process(trim($this->add_value['email']));
		
		if (!empty($_FILES['photo']['name']))
			$this->UploadImage('photo');
		elseif (empty($this->old_value['photo']) || !empty($this->add_value['del_photo']))
			$this->add_value['photo'] = '';
	}
	
	/**
	 * Calculate salary for search with currency
	 *
	 * @param string $salary
	 * @return int
	 */
	protected function Calculate_SalarySearch($salary)
	{
	    if (!empty($this->add_value['currency']) && !empty($this->config['currency'][$this->add_value['currency']]))
	    {
	        return $this->add_value[$salary] * $this->config['currency'][$this->add_value['currency']];
	    }
	    else 
	    {
	        return $this->add_value[$salary];
	    }
	}
	
	/**
	 * Add new company
	 *
	 * @return mixed false or new company id
	 */
	protected function AddCompany()
	{
		$this->PreparationCompanyValues();
		
		$this->add_value['add_date'] = $this->dbase->timer->cur_time;
			
		return $this->dbase->Insert('job_companies', $this->add_value);
	}
	
	/**
	 * Save updating company
	 *
	 * @param int $id
	 */
	protected function SaveCompany($id)
	{
		$this->CheckCompanyError();
		
		$xfields = new Fields(XFIELDS_COMPANY, $this);
        
        $this->add_value['xfields'] = $xfields->EncodeFields($this->add_value);
        
        $this->Errors = $this->Errors + $xfields->getErrors();
		
		if (!$this->Errors)
		{
			if (!empty($_FILES['logo']['name']) || (!empty($this->add_value['del_logo']) && $this->old_value['logo']))
				@unlink(LOGO_PATCH . $this->old_value['logo']);
				
			$this->PreparationCompanyValues();
			
			$this->dbase->Update('job_companies', $this->add_value, array('company_id' => $id));
			
			return true;
		}
		else 
		{
		    return false;
		}
	}
	
	/**
	 * Delete company
	 *
	 * @param int $id
	 */
	protected function DoDeleteCompany($id)
	{
	    if (!$this->old_value || $this->old_value['company_id'] != $id)
            $this->old_value = $this->dbase->SelectOne('job_companies', array("logo"), array('company_id' => $id));
		
		if ($this->old_value['logo'])
			@unlink(LOGO_PATCH . $this->old_value['logo']);
			
		$this->dbase->Delete('job_vacancies', array('company_id' => $id));
		$this->dbase->Delete('job_companies', array('company_id' => $id));
	}
	
	/**
	 * Upload logo for company
	 *
	 */
	protected function UploadImage($name = 'logo')
	{
		if (!class_exists('shop_thumbnail'))
			throw new ExceptionAllError('Не найден класс shop_thumbnail');
						
		switch ($name)
		{
		    case "logo":
		        $patch = LOGO_PATCH;
		        break;
		        
		    case "photo":
		        $patch = PHOTO_PATCH;
		        break;
		        
		    default:
		        break;
		}
			
		if ($_FILES[$name]['name'])
		{
			$type = strtolower(end(explode(".", $_FILES[$name]['name'])));
			$temp_name = md5(time() . microtime(true)) . "." . $type;
		
			if (@move_uploaded_file($_FILES[$name]['tmp_name'], $patch . $temp_name)) 
			{
				$thumb = new shop_thumbnail($patch . $temp_name);
							
				switch ($name)
				{
				    case "logo":
				        $name_now = $this->dbase->timer->cur_time . "." . $type;
				        $thumb->size_auto($this->config['company_logo_size']);
				        if ($this->config['company_watermark'])
					       $thumb->insert_watermark($this->config['company_size_for_watermark']);
				        break;
				        
				    case "photo":
				        $name_now = $this->member['id'] . "_" . $this->dbase->timer->cur_time . "." . $type;
				        $thumb->size_auto($this->config['resume_photo_size']);
				        if ($this->config['resume_watermark'])
					       $thumb->insert_watermark($this->config['resume_size_for_watermark']);
				        break;
				        
				    default:
				        break;
				}
				
				$thumb->jpeg_quality($this->config['general_image_quality']);
				$thumb->save($patch . $name_now);
				
				@unlink($patch . $temp_name);
				
				$this->add_value[$name] = $name_now;
			}
			else 
				throw new ExceptionAllError('Ошибка при перемещении файла');
		}
	}
	
	/**
	 * Create new vacancy
	 *
	 * @return int;
	 */
	protected function AddVacancy()
	{
		$this->PreparationVacancyValues();
		
		$this->add_value['user_id'] = $this->member['id'];
    	$this->add_value['user_name'] = $this->member['name'];
		$this->add_value['add_date'] = $this->dbase->timer->cur_time;
			
		return $this->dbase->Insert('job_vacancies', $this->add_value);
	}
	
	/**
	 * Save editing vacancy
	 *
	 * @param int $id
	 */
	protected function SaveVacancy($id)
	{
		$this->CheckVacancyError();
		
		$xfields = new Fields(XFIELDS_VACANCY, $this);
        
        $this->add_value['xfields'] = $xfields->EncodeFields($this->add_value);
        
        $this->Errors = $this->Errors + $xfields->getErrors();
		
		if (!$this->Errors)
		{
			$this->PreparationVacancyValues();
			
			$this->dbase->Update('job_vacancies', $this->add_value, array('id' => $id));
			
			return true;
		}
		else 
		{
		    return false;
		}
	}
	
	/**
	 * Delete vacancy
	 *
	 * @param int $id
	 */
	public function DeleteVacancy($id)
	{
		$this->dbase->Delete('job_vacancies', array('id' => $id));
	}
	
	/**
	 * Create new resume
	 *
	 */
	protected function AddResume()
	{
		$this->PreparationResumeValues();
		
		$this->add_value['user_id'] = $this->member['id'];
		$this->add_value['user_name'] = $this->member['name'];
		$this->add_value['add_date'] = $this->dbase->timer->cur_time;
	
		return $this->dbase->Insert('job_resumes', $this->add_value);
	}
	
	/**
	 * Save editing resume
	 *
	 * @param int $id
	 * @return boolean
	 */
	protected function SaveResume($id)
	{
		$this->CheckResumeError();
		
		$xfields = new Fields(XFIELDS_RESUME, $this);
        
        $this->add_value['xfields'] = $xfields->EncodeFields($this->add_value);
        
        $this->Errors = $this->Errors + $xfields->getErrors();
		
		if (!$this->Errors)
		{
			$this->PreparationResumeValues();
			
			if (!empty($_FILES['photo']['name']) || (!empty($this->add_value['del_photo']) && $this->old_value['photo']))
			{
				@unlink(PHOTO_PATCH . $this->old_value['photo']);
			}
			
			$this->dbase->Update('job_resumes', $this->add_value, array('id' => $id));
			
			return true;
		}
		else 
		{
		    return false;
		}
	}
	
	/**
	 * Delete resume
	 *
	 * @param int $id
	 */
	public function DeleteResume($id)
	{
	    if (!$this->old_value)
	    {
	        $this->old_value = $this->dbase->SelectOne('job_resumes', array('*'), array('id' => $id));
	    }
	    
	    if ($this->old_value['photo'])
	    {
			@unlink(PHOTO_PATCH . $this->old_value['photo']);
	    }
	    
		$this->dbase->Delete('job_resumes', array('id' => $id));
	}
	
	/**
	 * Get array of vacancy fields
	 *
	 * @param int $id
	 * @return array
	 */
	public function GetVacancyForEdit($id)
	{
		$edit = $this->dbase->SelectOne('job_vacancies', array("*"), array("id" => $id));
		
		if (!empty($edit['specialty_id']))
		{
		    if ($specialty = $this->dbase->SelectOne('job_specialties', array('name'), array('id' => $edit['specialty_id'])))
		    {
		        $edit['specialty'] = $specialty['name'];
		    }
		}
		
		return $edit;
	}
	
	public function getSpecialtyByName($name)
	{
	    return $this->dbase->SelectOne('job_specialties', array('*'), array('name' => $name));
	}
	
	/**
	 * Get array of resume fields
	 *
	 * @param int $id
	 * @return array
	 */
	public function GetResumeForEdit($id)
	{
		$edit = $this->dbase->SelectOne('job_resumes', array("*"), array("id" => $id));
		
		if (!empty($edit['specialty_id']))
		{
		    if ($specialty = $this->dbase->SelectOne('job_specialties', array('name'), array('id' => $edit['specialty_id'])))
		    {
		        $edit['specialty'] = $specialty['name'];
		    }
		}
		
		return $edit;
	}
	
	/**
	 * Get array of company fields
	 *
	 * @param int $id
	 * @return array
	 */
	public function GetCompanyForEdit($id)
	{
		return $this->dbase->SelectOne('job_companies', array("*"), array('company_id' => $id));
	}

	/**
	 * Module statistic
	 *
	 * @return array of stats
	 */
	public function Stats()
	{
	    if (!($stats = Cache::GetArrayCache('stats')))
        {
            $vac_all = $this->dbase->SelectOne('job_vacancies', array('count' => 'COUNT(*)'));
    		$vac_on_site = $this->dbase->SelectOne('job_vacancies', array('count' => 'COUNT(*)'), array('allow_site' => 1));
    		$this->dbase->SetWhere('add_date', $this->dbase->timer->cur_time - 24*60*60, ">");
    		$vac_today = $this->dbase->SelectOne('job_vacancies', array('count' => 'COUNT(*)'));
    		$this->dbase->SetBeginBlockWhere();
    		$this->dbase->SetWhere("date_exp", $this->dbase->timer->cur_time, '>');
    		$this->dbase->SetWhere("vac_", '', "=", 'job_vacancies', 'OR');
    		$this->dbase->SetEndBlockWhere();
    		$vac_no_moder = $this->dbase->SelectOne("job_vacancies", array("count" => "COUNT(*)"), array('allow_site' => 0));
    		$vac_max = $this->dbase->SelectOne('job_vacancies', array('max' => 'MAX(id)'));
    				
    		$res_all = $this->dbase->SelectOne('job_resumes', array('count' => 'COUNT(*)'));
            
    		$res_on_site = $this->dbase->SelectOne('job_resumes', array('count' => 'COUNT(*)'), array('allow_site' => 1));
    		$this->dbase->SetWhere('add_date', $this->dbase->timer->cur_time - 24*60*60, ">");
    		$res_today = $this->dbase->SelectOne('job_resumes', array('count' => 'COUNT(*)'));
    		$this->dbase->SetBeginBlockWhere();
    		$this->dbase->SetWhere("date_exp", $this->dbase->timer->cur_time, '>');
            $this->dbase->SetWhere("vac_", '', "=", 'job_resumes', 'OR');
    		$this->dbase->SetEndBlockWhere();
    		$res_no_moder = $this->dbase->SelectOne("job_resumes", array("count" => "COUNT(*)"), array('allow_site' => 0));
    		$res_max = $this->dbase->SelectOne('job_resumes', array('max' => "MAX(id)"));
    				
    		$comp_all = $this->dbase->SelectOne('job_companies', array('count' => 'COUNT(*)'));
    		$this->dbase->SetWhere('add_date', $this->dbase->timer->cur_time - 24*60*60, ">");
    		$comp_today = $this->dbase->SelectOne('job_companies', array('count' => 'COUNT(*)'));
    				
    		$stats = array(
    		                'vacancy_all' => $vac_all['count'],
    		                'vacancy_today' => $vac_today['count'],
    		                'vacancy_on_site' => $vac_on_site['count'],
    		                'vacancy_no_noder' => $vac_no_moder['count'],
    		                'vacancy_max' => $vac_max['max'],
    		                'resume_all' => $res_all['count'],
    		                'resume_today' => $res_today['count'],
    		                'resume_on_site' => $res_on_site['count'],
    		                'resume_no_moder' => $res_no_moder['count'],
    		                'resume_max' => $res_max['max'],
    		                'company_all' => $comp_all['count'],
    		                'company_today' => $comp_today['count'],
    		                );
    				                
            Cache::SetArrayCache('stats', $stats);
        }
        
        return $stats;
	}

}
?>