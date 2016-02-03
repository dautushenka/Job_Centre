<?php


/** 
 * @author Администратор
 * 
 * 
 */
class Fields
{
    
    /**
     * 
     * @var JobCore
     */
    protected $_job;
    
    /**
     * 
     * @var DataBaseCore
     */
    protected $_db;
    
    /**
     * 
     * @var array
     */
    protected $_fields = array();

    /**
     * 
     * @var array
     */
    protected $_errors = array();
    
    protected $_ctype;
    
    public function __construct($ctype, JobCore $job)
    {
        $this->_db =& $job->dbase;
        $this->_job =& $job;
        $this->_ctype = $ctype;
    }
    
    /**
     * @return array
     */
    protected function _getFields()
    {
        if ($this->_fields)
        {
            return $this->_fields;
        }
        
        if (!$this->_job->config['general_cache'] || !$this->_fields = Cache::GetArrayCache('fields' . $this->_ctype))
        {
            $this->_fields = array();
            
            $this->_db->Select('job_fields', array('*'), array('active' => 1, 'ctype' => $this->_ctype));
            
            while ($field = $this->_db->FetchArray())
            {
                $this->_fields[$field['id']] = $field;
            }
            
            Cache::SetArrayCache('fields' . $this->_ctype, $this->_fields);
        }
        
        return $this->_fields;
    }
    
    /**
     * 
     * @param string $values
     * return array array(id => field_html)
     */
    public function DecodeFields($values)
    {
        $this->_getFields();
        $output = array();
        
        if (is_array($values))
        {
            $fields_array = $values;
        }
        else if ($values && is_string($values))
        {
            $fields_array = (array)@unserialize($values);
        }
        else 
        {
            $fields_array = array();
        }

        foreach ($this->_fields as $id => $field)
        {
            $value = (isset($fields_array[$id]))?$fields_array[$id]:$field['default'];
            $required = $field['required']?' validate="required:true"':'';
            $output[$id] = $field;
            
            switch ($field['type']) 
            {
                case 'text':
                    $output[$id]['html'] = "<input type='text' value='$value' name='xfields[$id]' class='field edit' id='field$id'$required />";
                    break;
                
                case 'textarea':
                    $output[$id]['html'] = "<textarea class='field' name='xfields[$id]' id='field$id'$required>$value</textarea>";
                    break;
                    
                case 'checkbox':
                    // fix
                    if (empty($fields_array[$id]))
                    {
                        $value = 0;
                    }
//                    var_dump($value);
                    $output[$id]['html'] = "<input class='field' id='field$id' type='checkbox'$required name='xfields[$id]' value='1' " . ($value?"checked='checked' ":"") . "/>";
                    break;
                    
                case 'select':
                    $data = unserialize($field['data']);
                    $output[$id]['html'] = "<select name='xfields[$id]' class='field' id='field$id'$required >\n";
                    
                    if ($field['default'] == 1)
                    {
                        $output[$id]['html'] .= "<option value='' " . (empty($fields_array[$id])?"selected='selected'":'') . "> </option>\n";
                        if (empty($fields_array[$id]))
                        {
                            $value = '';
                        }
                    }
                    else if ($field['default'] == 2 && empty($fields_array[$id]))
                    {
                        $value = reset($data);
                    }
                    
                    foreach ($data as $index => $opt)
                    {
                        $index++;
                        $output[$id]['html'] .= "<option value='$index' " . (($value == $opt || $index == $value)?"selected='selected'":'') . ">" . $opt . "</option>\n"; 
                    }
                    $output[$id]['html'] .= "</select>";
                    break;
            	
                default:
                    throw new ExceptionAllError('Unknow type of field');
                    break;
            }
        }
        
        return $output;
    }
    
    /**
     * 
     * @param array $form_values
     */
    public function EncodeFields(array $form_values)
    {
        $this->_getFields();
        
        if (!empty($form_values['xfields']) && is_array($form_values['xfields']))
        {
            $fields = $form_values['xfields'];
            $save_array = array();
            
            foreach ($this->_fields as $id => $field)
            {
                $value = isset($fields[$id])?$fields[$id]:'';
                
                if ($field['required'] && !$value)
                {
                    $this->_errors[] = sprintf($this->_job->lang['field_error_require'], $field['title']);
                    
                    continue;
                }
                else if ($field['regex'])
                {
                    if (!preg_match($field['regex'], $value))
                    {
                        $this->_errors[] = $this->_job->lang['field_error_regex'] . $field['title'];
                        
                        continue;
                    }
                }
                
                switch ($field['type']) 
                {
                    case 'text':
                    case 'textarea':
                        $save_array[$id] = $value;
                        break;
                        
                    case 'checkbox':
                        $save_array[$id] = (int)$value;
                        break;
                        
                    case 'select':
                        $data = unserialize($field['data']);
                        $index = $fields[$id] - 1;
                        if (isset($data[$index]))
                        {
                            $save_array[$id] = $data[$index];
                        }
                        break;
                	
                    default:
                        throw new ExceptionAllError('Unknow type of field');
                        break;
                }
            }
            
            if ($save_array)
            {
                return serialize($save_array);
            }
            else 
            {
                return '';
            }
        }
        
        return '';
    }
    
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * 
     * @param string $data
     * @return array
     */
    public function showFields($data)
    {
        if (empty($data) || !$data = @unserialize($data))
        {
            return array();
        }
        
        $output = array();
        foreach ($this->_getFields() as $fid => $field)
        {
            $value = isset($data[$fid])?$data[$fid]:null;
            
            switch ($field['type'])
            {
                case 'checkbox':
                    if ($value)
                    {
                        $value = $field['title'];
                    }
                    break;
            }
            
            $output[$fid] = $field;
            $output[$fid]['value'] = $value;
        }
        
        return $output;
    }
    
    public function getType()
    {
        return $this->_ctype;
    }
    
    /**
     * 
     */
    public function __destruct()
    {

    }
}

?>