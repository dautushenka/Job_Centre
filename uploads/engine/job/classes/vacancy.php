<?php
/**
 * @package ModuleJob
 * @author Autushenko Denis <support@kaliostro.net>
 * @copyright (c) kaliostro
 * @version 1.0.0
 */

/**
 * Class for vacancy
 *
 * @access public
 * @package ModuleJob
 * @final 
 */
final class Vacancy
{
	private $vacancy = array();
	
	public static $fields = array();
	
	public static $DB_fields = array();
	
	public static $search_fields = array();
	
	public static $sort_array = array();
	
	public static $count_day_array = array();
	
	/**
	 * Count day for search, select user in search page
	 *
	 * @var array
	 */
	public static $search_count_day = array();
	
	/**
	 * Delegate Job object
	 *
	 * @var Job
	 */
	public static $job = null;
	
	/**
	 * Constructor for vacancy
	 *
	 * @param array $vacancy_row
	 */
	public function __construct(array $vacancy_row)
	{
		$this->vacancy = array_intersect_key($vacancy_row, array_flip(self::$DB_fields));
		
		if (!empty($this->vacancy['company_id']))
			$this->vacancy['company'] = new Company($vacancy_row);
		else 
			$this->vacancy['company'] = null;
			
		if (!empty(self::$job->tpl))
		{
		    $this->vacancy['vacancy_url'] = self::$job->tpl->GetUrl(array('action' => 'vacancy',
		                                                                  'id' => $this->vacancy['id'])
		                                                                  );
		}
		
		$this->vacancy['rss_add_date'] = date( "r", $this->vacancy['add_date']);
		
		$this->vacancy['description'] = nl2br($this->vacancy['description']);
			
		if (!empty($this->vacancy['add_date']))
			$this->vacancy['add_date'] = ConvertDate($this->vacancy['add_date'], self::$job);
			
		if (!empty($this->vacancy['date_exp']))
			$this->vacancy['date_exp'] = $this->vacancy['exp_date'] = ConvertDate($this->vacancy['date_exp'], self::$job);
		else
		{
		    $this->vacancy['date_exp'] = self::$job->lang['no_limit'];
		}
			
		if ($this->vacancy['salary_min'] && $this->vacancy['salary_max'] && $this->vacancy['salary_min'] != $this->vacancy['salary_max'])
			$this->vacancy['salary'] = $this->vacancy['salary_min'] . " - " . $this->vacancy['salary_max'] . " " . self::$job->lang[$this->vacancy['currency']];
		elseif ($this->vacancy['salary_min'] == $this->vacancy['salary_max'] && $this->vacancy['salary_min'])
			$this->vacancy['salary'] = $this->vacancy['salary_min'] . " " . self::$job->lang[$this->vacancy['currency']];
		elseif ($this->vacancy['salary_min'])
			$this->vacancy['salary'] = self::$job->lang['from'] . $this->vacancy['salary_min'] . " " . self::$job->lang[$this->vacancy['currency']];
		elseif ($this->vacancy['salary_max'])
			$this->vacancy['salary'] = self::$job->lang['to'] . $this->vacancy['salary_max'] . " " . self::$job->lang[$this->vacancy['currency']];
		else 
			$this->vacancy['salary'] = self::$job->lang['contract_salary'];
			
		if ($this->vacancy['age_min'] && $this->vacancy['age_max'] && $this->vacancy['age_min'] != $this->vacancy['age_max'])
			$this->vacancy['age'] = $this->vacancy['age_min'] . " - " . $this->vacancy['age_max'];
		elseif ($this->vacancy['age_min'] == $this->vacancy['age_max'])
			$this->vacancy['age'] = $this->vacancy['age_min'];
		elseif ($this->vacancy['age_min'])
			$this->vacancy['age'] = self::$job->lang['from'] . $this->vacancy['age_min'];
		elseif ($this->vacancy['age_max'])
			$this->vacancy['age'] = self::$job->lang['to'] . $this->vacancy['age_max'];
		else 
			$this->vacancy['age'] = self::$job->lang['no_show'];
			
		foreach (self::$fields as $field_name=>$values_array)
		{
			if ($values_array[$this->vacancy[$field_name]])
				$this->vacancy[$field_name] = $values_array[$this->vacancy[$field_name]];
		}
	}
	
	public function __destruct() { }
	
	public function __get($varname)
	{
	    switch ($varname)
	    {
	        case "sphere_name":
	            if (empty($this->vacancy['sphere_name']) && !empty($this->vacancy['sphere_id']))
			         $this->vacancy['sphere_name'] = self::$job->GetSphereName($this->vacancy['sphere_id']);
	            break;
	            
	         case "specialty_name":
	             if (empty($this->vacancy['specialty_name']) && !empty($this->vacancy['specialty_id']))
			         $this->vacancy['specialty_name'] = self::$job->GetIncidenceName($this->vacancy['sphere_id'], $this->vacancy['specialty_id']);
	            break;
	            
	         case "country_name":
	             if (self::$job->use_country && empty($this->vacancy['country_name']) && !empty($this->vacancy['country_id']))
        			$this->vacancy['country_name'] = self::$job->GetCountryName($this->vacancy['country_id']);
        		elseif (!self::$job->use_country)
                    $this->vacancy['country_name'] = '';
	             break;
	             
	         case "city_name":
	             if (self::$job->use_city && empty($this->vacancy['city_name']) && !empty($this->vacancy['city_id']))
        			$this->vacancy['city_name'] = self::$job->GetCityName($this->vacancy['counry_id'], $this->vacancy['city_id']);
        		elseif (!self::$job->use_city)
                    $this->vacancy['city_name'] = '';
	             break;
	    }
	    
		if (array_key_exists($varname, $this->vacancy))
		{
			return $this->vacancy[$varname];
		}
		
		throw new ExceptionAllError('Поле ' . $varname . ' не существует в вакансии');
	}
	
	public function __set($varname, $value)
	{
		throw new ExceptionAllError('The fields only for read');
	}
}
?>