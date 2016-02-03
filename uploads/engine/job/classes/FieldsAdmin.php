<?php


/** 
 * @author Администратор
 * 
 * 
 */
class FieldsAdmin
{
    /**
     * 
     * @var DataBaseCore
     */
    protected $_db;
    
    /**
     * 
     * @var CarMarket
     */
    protected $_job;

    /**
     * 
     * @var array
     */
    protected $_errors = array();
    
    protected $_support_types = array(
                                      'text',
                                      'textarea',
                                      'select',
                                      'checkbox'
                                      );
    
    public function __construct(DataBaseCore &$db, JobCore $job)
    {
        $this->_db =& $db;
        $this->_job =& $job;
    }

    public function AddField($type, array $data)
    {
        if (empty($type) || !in_array($type, $this->_support_types))
        {
            throw new ExceptionAllError('Не указан тип дополнительного поля');
        }
        
        $this->_check_error($data);
        
        $field_data = $this->_pre_save($type, $data);
        
        if (!$this->_errors)
        {
            Cache::ClearArrayCache('fields');
            
            return $this->_db->Insert('job_fields', array(
                                                    'title'       => $data['title'],
                                                    'ctype'       => $data['ctype'],
                                                    'description' => $data['description'],
                                                    'type'        => $type,
                                                    'data'        => $field_data,
                                                    'required'    => $data['required'],
                                                    'regex'       => $data['regex'],
                                                    'default'     => $data['default'],
                                                    'active'      => $data['active'],
                                                    ));
        }
        else
        {
            return 0;
        }
    }
    
    
    protected function _check_error(array $data)
    {
        if (!count($data))
        {
            throw new ExceptionAllError('Не переданы данные для обновления дополнительного поля');
        }
        
        if (empty($data['title']))
        {
            $this->_errors[] = $this->_job->lang['xfields_error_empty_title'];
        }
    }
    
    /**
     * 
     * @param string $type
     * @param array $data
     * @return string
     */
    protected function _pre_save($type, array &$data)
    {
        $field_data = '';
        $data['required'] = empty($data['required'])?0:1;
        
        if (!empty($data['regex']))
        {
            $data['regex'] = trim($data['regex']);
        }
        else 
        {
            $data['regex'] = '';
        }
        
        if (empty($data['ctype']))
        {
            $data['ctype'] = 0;
        }
        
        switch ($type) 
        {
            case 'select':
                if (empty($data['data']))
                {print_r($data);
                    $this->_errors[] = $this->_job->lang['xfields_error_select_data'];
                }
                else 
                {
                    $opts = explode("\n", $data['data']);
                    
                    if (!count($opts))
                    {
                        $this->_errors[] = $this->_job->lang['xfields_error_select_data'];
                    }
                    else 
                    {
                        $opts_array = array(); $i = 0;
                        
                        foreach ($opts as $opt)
                        {
                            $opt = strip_tags(trim($opt));
                            
                            if ($opt)
                            {
                                $opts_array[$i] = $opt;
                                $i++;
                            }
                        }
                        
                        if ($opts_array)
                        {
                            $field_data = serialize($opts_array);
                        }
                        else 
                        {
                            $this->_errors[] = $this->_job->lang['xfields_error_select_data'];
                        }
                    }
                }
                $data['default'] = ((int)$data['default'] == 2)?2:1;
                break;
            
            case 'checkbox':
                $data['default'] = empty($data['default'])?0:1;
                break;
                
            default:
                $data['default'] = strip_tags(trim($data['default']));
                break;
        }
        
        return $field_data;
    }
    
    public function UpdateField($id, array $data)
    {
        if (empty($id))
        {
            throw new ExceptionAllError('Не передан индификатор дополнительного поля');
        }
        
        $this->_check_error($data);
        
        $field = $this->GetField($id);
        
        $field_data = $this->_pre_save($field['type'], $data);
        
        if (!$this->_errors)
        {
            Cache::ClearArrayCache('fields');
            
            $this->_db->Update('job_fields', array(
                                                    'title'       => $data['title'],
                                                    'description' => $data['description'],
                                                    'data'        => $field_data,
                                                    'required'    => $data['required'],
                                                    'regex'       => $data['regex'],
                                                    'default'     => $data['default'],
                                                    'active'      => $data['active'],
                                                    ),
                                                    array('id' => $id));
                                                    
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function DeleteField($id)
    {
        if (empty($id))
        {
            throw new ExceptionAllError('не передан индификатор поля');
        }
        
        $this->_db->Delete('auto_fields', array('id' => $id));
        
        return $this;
    }
    
    /**
     * 
     * @param integer $id
     * @return array
     */
    public function GetField($id)
    {
        $field = $this->_db->SelectOne('job_fields', array('*'), array('id' => $id));
        
        if (!$field)
        {
            throw new ExceptionAllError('Field not founf');
        }
        
        switch ($field['type']) 
        {
            case 'select':
                $data = unserialize($field['data']);
                $field['data'] = implode("\n", $data);
                break;
        	
            default:
            break;
        }
        
        return $field;
    }
    
    public function GetFields()
    {
        $fields = array();
        
        $this->_db->Select('job_fields', array('ctype', 'title', 'required', 'type', 'default', 'id', 'active'));
        
        while ($row = $this->_db->FetchArray())
        {
            $fields[$row['id']] = $row;
        }
        
        return $fields;
    } 
    
    /**
     * @return array()
     */
    public function getErrors()
    {
        return $this->_errors;
    }
    
    /**
     * 
     */
    public function __destruct()
    {

    }
}

?>