<?php
/**
 * Abstract class for DataBase class
 *
 * @copyright (c) kaliostro
 * @author Autushenko Denis <support@kaliostro.net>
 * @package Core
 * @version 1.0
 */

/**
 * Abstract Class for DataBase Access classes
 *
 * @package DataBaseCore
 */
abstract class DataBaseCore extends Licencing
{
    /**
     * Connect id
     *
     * @var resource
     */
    protected $connect_id = NULL;

    /**
     * Resource for last query
     *
     * @var resource
     */
    protected $resource = NULL;

    /**
     * Count Query
     *
     * @var int
     */
    public $query_num = 0;

    /**
     * List all queries
     *
     * @var array
     */
    public $query_list = array();

    public $debug = false;

    /**
     * Array of All tables, sample array(table_name => array(column => type));
     *
     * @var array
     */
    private $tables = array();

    /**
     * Timer
     *
     * @var Timer
     */
    public $timer;

    protected $db_host = 'localhost';

    protected $db_port = '3306';

    protected $db_user = 'root';

    protected $db_pass = '';

    protected $db_base = '';

    protected $db_usepconnect = '';

    protected $db_charset = '';

    protected $db_table_prefix = '';

    protected $query = '';

    protected $cur_table = '';

    private $group;

    private $cur_where = '';

    private $count_block_where = 0;

    private $block_where = '';

    private $build_query = array();

    private $build_query_for_count = '';

    public function __construct(array $tables, $db_table_prefix, &$timer)
    {
        $this->db_table_prefix = $db_table_prefix;

        if (!is_object($timer))
        $this->timer = new Timer(time());
        else
        $this->timer =& $timer;

        if ($tables && is_array($tables))
        $this->tables = $tables;
    }

    /**
     * Connect to DataBase
     *
     * @param string $servername Server Name for DataBase Server, default localhost
     * @param string $port Port DataBase Server
     * @param string $username Name user for access to DataBase
     * @param string $password Password
     * @param string $database_name DataBase name
     * @param boolean $usepconnect If use permanent connect to DataBase, default false
     * @param string $charset If require change charset connect on another, default ''
     */
    public function Connect($servername = 'localhost', $port = '3306', $username = 'root', $password = '', $database_name = '', $usepconnect = false, $charset = '')
    {
        $this->db_host        = $servername;
        $this->db_port        = $port;
        $this->db_user        = $username;
        $this->db_pass        = $password;
        $this->db_usepconnect = $usepconnect;
        $this->db_charset     = $charset;
        $this->db_base        = $database_name;

        $this->db_Connect();

        if (!$this->connect_id)
        throw new ExceptionDataBase($this->GetError(), $this->GetErrorNo, 'Fatal');
        	
        $this->db_SelectDB();
    }

    /**
     * Select DataBse
     *
     * @param string $database_name
     */
    public function SelectDB($database_name)
    {
        $this->db_base = $database_name;

        if (!$this->db_SelectDB())
        throw new ExceptionDataBase($this->GetError, $this->GetErrorNo, 'Fatal');
    }

    final public function AddTable($name, array $table)
    {

    }

    final protected function Execute()
    {
        if (!$this->connect_id)
        {
            $this->db_connect($this->db_host, $this->db_port, $this->db_user, $this->db_pass, false, $this->db_charset);
            $this->db_SelectDB($this->db_base);
        }
        Licencing::check();
        if ($this->query)
        {
            $this->timer->DBTimer();
            $this->ExecuteQuery();
            	
            $this->query_list[] = array("query" => $this->query, 'time' => $this->timer->DBTimer());
            	
            if (!$this->resource)
            throw new ExceptionDataBase($this->GetError(), $this->query, $this->GetErrorNo());

            $this->query = $this->group = $this->cur_where = '';
            $this->count_block_where = 0;
            $this->block_where = array();
            $this->query_num++;
        }
    }

    final protected function ArrayIntersectKey(array &$array, $infunc = false)
    {
        $array_new = $array;
        foreach ($array_new as $key=>&$value)
        {
            if (preg_match('#^(RAND|COUNT|MAX|MIN|MD5|CONCAT|SUM)\((.*)\)$#i', $key, $keys))
            {
                $colums = array();

                if (strtoupper($keys[1]) == 'RAND')
                {
                    if ($keys[2])
                    {
                        unset($array[$key]);

                        if (floatval($keys[2]))
                        $key = 'RAND(' . floatval($keys[2]) . ')';
                        else
                        $key = 'RAND()';
                        	
                        $array[$key] = $value;
                    }
                    	
                }
                elseif (!strpos($keys[2], '('))
                $colums = explode(', ', $keys[2]);
                else
                $colums[] = $keys[2];
                	
                if($colums && !(strtoupper($keys[1]) == 'COUNT' && $keys[2] == '*'))
                {
                    $colums = array_flip($colums);
                    $this->ArrayIntersectKey($colums, true);
                    	
                    unset($array[$key]);

                    if ($colums)
                    $array[$keys[1] . "(" . implode(", ", array_keys($colums)) . ")"] = $value;
                }
            }
            elseif (!array_key_exists($key, $this->tables[$this->cur_table]))
            {
                unset($array[$key]);
            }
            else if (!$infunc)
            {
                $array['`' . $key . '`'] = $value;
                unset($array[$key]);
            }
        }
    }

    final protected function Escape(array &$array, $column = '')
    {
        if ($column)
        {
            if (!array_key_exists($column, $this->tables[$this->cur_table]))
            {
                $array = array();
                return ;
            }
        }
        else
        $this->ArrayIntersectKey($array);

        foreach ($array as $key => &$value)
        {
            $column = trim($column, '`');
            $key = trim($key, '`');
            
            if (is_array($value))
            {
                $this->Escape($value, $key);
            }
            else
            {
                if (is_array($this->tables[$this->cur_table][(($column)?$column:$key)]))
                {
                    if (!in_array($value, $this->tables[$this->cur_table][(($column)?$column:$key)]))
                    unset($array[$key]);
                    else
                    $value = $this->EscapeString($value);
                }
                else
                {
                    switch ($this->tables[$this->cur_table][($column)?$column:$key])
                    {
                        case 'int':
                            $value = intval($value);
                            break;
                            	
                        case 'float':
                            $value = floatval($value);
                            break;
                            	
                        case 'string':
                            $value = $this->EscapeString($value);
                            break;
                            	
                        default:
                            $value = $this->EscapeString($value);
                            break;
                    }
                }
            }
        }
    }

    final protected function GetInsertValue(array $values, $multi = false)
    {
        $this->Escape($values);

        $array = array();
        foreach ($values as $column=>&$value)
        {
            if ($multi)
            {
                $count = count($value);
                for ($i = 0; $i < $count; $i++)
                {
                    $array[$i][] = "'" . $value[$i] . "'";
                }
            }
            else
            {
                $value = "'$value'";
            }
        }
        if ($array)
        {
            $return_value = '';
            foreach ($array as $key=>$value)
            {
                if ($return_value) $return_value .= ", ";
                $return_value .= "(" . implode(", ", $value) . ")";
            }
            return array('column' => '(' . implode(", ", array_keys($values)) . ')', 'value' => $return_value);
        }
        else
        {
            return array('column' => '(' . implode(", ", array_keys($values)) . ')', 'value' => '(' . implode(", ", $values) . ')');
        }
    }

    final protected function GetWhere(array $where)
    {
        $this->Escape($where);Licencing::check();
        $array = $this->GetArrayString($where);

        if ($this->cur_where && $array)
        return ' WHERE ' . $this->cur_where . " AND " . implode(" AND ", $array);
        elseif ($this->cur_where || $array)
        return ' WHERE ' . $this->cur_where . implode(" AND ", $array);
        else
        return '';
    }

    final public function SetWhere($column, $value, $type = '=', $table = '', $operand = 'AND')
    {
        if ($table)
        {
            $cur_table = $this->cur_table;
            $this->SetTable($table);
        }
         
        if ($operand && !in_array(strtoupper($operand), array("AND", "OR", "XOR")))
        {
            $this->Bug("Unknow operand for set where");
            return ;
        }
         
        $data = array($column => $value);
         
        $this->Escape($data);

        if ($data)
        {
            $cur_where = '';
            $value = reset($data);

            switch (strtoupper($type))
            {
                case "=":
                case "<":
                case ">":
                case ">=":
                case "<=":
                case "!=":
                    $cur_where .= "`" . $this->db_table_prefix . $this->cur_table . "`." . $column . $type;
                    $cur_where .= "'" . $value . "'";
                    break;
                    	
                case "IN":
                    if (!is_array($value))
                    {
                        break;
                    }
                    $cur_where .= "`" . $this->db_table_prefix . $this->cur_table . "`." . $column;
                    $cur_where .= " IN ('" . implode("', '", $value) . "')";
                    break;
                    	
                case "LIKE":
                    $cur_where .= "`" . $this->db_table_prefix . $this->cur_table . "`." . $column . " LIKE '%" . $value . "%'";
                    break;
                    	
                case "BETWEEN":
                    $cur_where .= "`" . $this->db_table_prefix . $this->cur_table . "`." . $column;
                    $cur_where .= " BETWEEN '" . $value[0] . "' AND '" . $value[1] . "'";
                    break;
                    	
                case "REGEXP":
                    $cur_where .= "`" . $this->db_table_prefix . $this->cur_table . "`." . $column;
                    if (count($value) == 1)
                    $cur_where .= " REGEXP '[[:<:]]" . implode("|", $value) . "[[:>:]]'";
                    else
                    $cur_where .= " REGEXP '[[:<:]](" . implode("|", $value) . ")[[:>:]]'";
                    break;
                    	
                default:
                    $this->Bug('Unknow type in WHERE clause');
                    $cur_where .= "`" . $this->db_table_prefix . $this->cur_table . "`." . $column . "=" . $value;
                    break;
            }
            	
            if ($cur_where)
            {
                if ($this->count_block_where)
                {
                    if ($this->block_where[$this->count_block_where]['value'] != "(")
                    {
                        $this->block_where[$this->count_block_where]['value'] .= " " . $operand . " " . $cur_where;
                    }
                    else
                    {
                        $this->block_where[$this->count_block_where]['value'] .= $cur_where;
                    }
                }
                else
                {
                    if ($this->cur_where)
                    {
                        $this->cur_where .= " " . $operand . " " . $cur_where;
                    }
                    else
                    {
                        $this->cur_where .= $cur_where;
                    }
                }
            }
        }
         
        if ($table)
        $this->cur_table = $cur_table;
    }

    final public function SetBeginBlockWhere()
    {
        $this->count_block_where++;
        $this->block_where[$this->count_block_where]['value'] .= "(";
    }

    final public function SetEndBlockWhere($operand = 'AND')
    {
        if ($operand && !in_array(strtoupper($operand), array("AND", "OR", "XOR")))
        {
            $this->Bug("Unknow operand in EndBlockWhere");
            return ;
        }
        	
        $this->block_where[$this->count_block_where]['operand'] = $operand;
        	
        $this->count_block_where--;

        if ($this->count_block_where == 0)
        {
            if ($this->cur_where)
            $this->cur_where .= " " . $operand . " " . $this->block_where[1]['value'] . ") ";
            else
            $this->cur_where .=  $this->block_where[1]['value'] . ") ";
            	
            $this->count_block_where = 0;
            $this->block_where = array();
        }
        elseif ($this->count_block_where > 0)
        {
            if ($this->block_where[$this->count_block_where]['value'])
            $this->block_where[$this->count_block_where]['value'] .= " " . $operand . " " . $this->block_where[$this->count_block_where + 1]['value'] . ") ";
            else
            $this->block_where[$this->count_block_where]['value'] .= $this->block_where[$this->count_block_where + 1]['value'] . ") ";

            unset($this->block_where[$this->count_block_where + 1]);
        }
    }

    final protected function GetSet($array_set, $special = false)
    {
        if ($special)
        {
            $this->ArrayIntersectKey($array_set);
        }
        else
        {
            $this->Escape($array_set);
        }

        if($array_str = $this->GetArrayString($array_set, $special))
        {
            return ' SET ' . implode(", ", $array_str);
        }
        else
        {
            throw new ExceptionDataBase("Ошибка обновления таблици, данные для обновления не верны или не переданы" );
        }
    }

    final protected function GetColumn(array $columns)
    {
        if ($columns[0] == '*')
        return '*';
        else
        {
            $columns = array_flip($columns);
            	
            $this->ArrayIntersectKey($columns);
            	
            if ($columns)
            {
                $column_str = '';
                foreach ($columns as $column=>$as)
                {
                    if ($column_str) $column_str .= ", ";
                     
                    if (!is_numeric($as))
                    {
                        $column_str .= $column . " as " . preg_replace('#[^a-z]#i', "_", $as);
                    }
                    else
                    {
                        $column_str .= $column;Licencing::check();
                    }
                }
                return $column_str;
            }
            return '*';
        }
    }

    protected function GetOrder(array $order_array)
    {
        $this->ArrayIntersectKey($order_array);

        $order = array();
        foreach ($order_array as $column=>$type)
        {
            if ($type === 'ASC' || $type === '')
            $order[] =  "`" . $this->db_table_prefix . $this->cur_table . "`." . $column . " ASC";
            elseif ($type === 'DESC')
            $order[] =  "`" . $this->db_table_prefix . $this->cur_table . "`." . $column . " DESC";
        }

        if ($order)
        return ' ORDER BY ' . implode(", ", $order);
        else
        return '';
    }

    protected function GetLimit(array $array_count)
    {
        if (!empty($array_count['start']) && !($array_count['start'] = intval($array_count['start'])))
        $array_count['start'] = 0;
        	
        if (!empty($array_count['limit']) && $array_count['limit'] = intval($array_count['limit']))
        return ' LIMIT ' . $array_count['start'] . ", " . $array_count['limit'];
        else
        return '';
    }

    public function Group(array $column_array, $table = '')
    {
        if ($table)
        {
            $cur_table = $this->cur_table;
            $this->SetTable($table);
        }

        $column_array = array_flip($column_array);

        $this->ArrayIntersectKey($column_array);

        if ($column_array)
        $this->group = " GROUP BY `" . $this->db_table_prefix . $this->cur_table . "`." . implode(", `$this->db_table_prefix$this->cur_table`.", array_keys($column_array));

        if ($table)
        $this->cur_table = $cur_table;
    }

    final private function GetArrayString(array $array_str, $special = false)
    {
        $array = array();
        foreach ($array_str as $column=>$value)
        {
            if ($special)
            {
                $array[] =  "`" . $this->db_table_prefix . $this->cur_table . "`." . $column . "=$value";
            }
            else
            {
                $array[] =  "`" . $this->db_table_prefix . $this->cur_table . "`." . $column . "='$value'";
            }
        }

        return $array;
    }

    final public function SetTable($table_name)
    {
        if (array_key_exists($table_name, $this->tables))
        $this->cur_table = $table_name;
        else
        throw new ExceptionDataBase("Table $table_name doesn't exist");
    }

    final public function SetPrefix($prefix)
    {
        if (preg_match('#[a-z_]#i'))
        $this->db_table_prefix = $prefix;
        else
        throw new ExceptionDataBase('bad Table Prefix');
    }

    final public function BuildQuery($table, array $join_array)
    {
        $this->SetTable($table);

        $this->build_query = array();

        $this->build_query['table'] = $this->cur_table;

        foreach ($join_array as $table=>$value)
        {
            foreach ($value as $this_column=>$main_column)
            {
                if (!empty($this->tables[$this->cur_table][$main_column]) && !empty($this->tables[$table][$this_column]))
                {
                    $this->build_query['outer'][$table] = $value;
                }
            }
        }
    }

    /**
     * Set selection column for BuilQuery
     *
     * @param mixed $selection_array value * or array: array(table=>array(alias => column))
     * @return void
     */
    final public function SetSelection($selection_array)
    {
        if ($selection_array == "*")
        {
            $this->build_query['selection'] = '*';
            return;
        }
        	
        if (is_array($selection_array))
        {
            $cur_table = $this->cur_table;
            foreach ($selection_array as $table=>$columns)
            {
                $this->SetTable($table);
                $this->build_query['selection'][$table] = array();

                $columns = array_flip($columns);
                	
                if (isset($columns["*"]))
                {
                    unset($columns["*"]);
                    $this->build_query['selection'][$table][] = "*";
                }
                	
                $this->ArrayIntersectKey($columns);

                if ($columns)
                $this->build_query['selection'][$table] += $columns;
            }
            $this->cur_table = $cur_table;
        }
        else
        $this->build_query['selection'] = "*";
    }

    final public function Join($table, $columns, $outer = true)
    {
        if (empty($this->tables[$table]))
        return ;
        	
        foreach ($columns as $this_column=>$main_column)
        {
            if (!empty($this->tables[$this->cur_table][$main_column]) && !empty($this->tables[$table][$this_column]))
            {
                $this->build_query[($outer)?'outer':'join'][$table][$this_column] = $main_column;
            }
        }
    }

    final public function ExecuteBuildQuery($order = array(), $count = array())
    {
        $param = $this->GetParametrForBuildQuery();

        $this->query  = "SELECT " . $param['selection'] . " FROM " . $this->db_table_prefix . $this->cur_table . "\n";
        $this->query .= $param['join'];
        $this->query .= $this->GetWhere(array()) . $this->group . $this->GetOrder($order) . $this->GetLimit($count);

        $this->build_query_for_count  = "SELECT COUNT(*) as count FROM " . $this->db_table_prefix . $this->cur_table . "\n";
        $this->build_query_for_count .= $param['join'] . "\n";
        $this->build_query_for_count .= $this->GetWhere(array());
        $this->build_query_for_count .= $this->group;

        $this->Execute();

        return $this->resource;
    }

    final public function CountForBuldQuery()
    {
        $this->query = $this->build_query_for_count;

        $this->Execute();

        $count = $this->FetchArray();

        return $count['count'];
    }

    private function GetParametrForBuildQuery()
    {
        $join = ''; $selection = '';

        foreach ($this->build_query as $type=>$action)
        {
            switch ($type)
            {
                case 'selection':
                    if ($action == "*")
                    $selection = '*';
                    elseif (is_array($action) && $action)
                    {
                        foreach ($action as $table=>$value)
                        {
                            foreach ($value as $column=>$as)
                            {
                                if ($selection)	$selection .= ", ";

                                if ($as == "*")
                                $selection .=  "`" . $this->db_table_prefix . $table . "`.*";
                                else
                                {
                                    if ($as && !is_numeric($as))
                                    $selection .=  "`" . $this->db_table_prefix . $table . "`." . $column . " as " . preg_replace('#[^a-z]#i', "_", $as);
                                    else
                                    $selection .=  "`" . $this->db_table_prefix . $table . "`." . $column;
                                }
                            }
                        }
                    }
                    else
                    $selection = '*';
                    break;
                    	
                case 'join':
                    if ($action)
                    {
                        foreach ($action as $table=>$value)
                        {
                            foreach ($value as $this_column=>$main_column)
                            {
                                $join .= "LEFT JOIN " . $this->db_table_prefix . $table ."\n";
                                $join .= "ON `" . $this->db_table_prefix . $table . "`." . $this_column . "=`" . $this->db_table_prefix . $this->cur_table . "`." . $main_column . "\n";
                            }
                        }
                    }
                    break;
                    	
                case 'outer':
                    if ($action)
                    {
                        foreach ($action as $table=>$value)
                        {
                            foreach ($value as $this_column=>$main_column)
                            {
                                $join .= "LEFT OUTER JOIN " . $this->db_table_prefix . $table ."\n";
                                $join .= "ON `" . $this->db_table_prefix . $table . "`." . $this_column . "=`" . $this->db_table_prefix . $this->cur_table . "`." . $main_column . "\n";
                            }
                        }
                    }
                    break;
                    	
                default:
                    break;
            }
        }

        return array('selection' => $selection, 'join' => $join);
    }

    /**
     * Insert Data to DataBase
     *
     * @param string $table Table for insert data
     * @param array $value Array column and value, sample array('id'=>1);
     *
     * @return integer id
     */
    public function Insert($table, array $value, $multi = false)
    {
        $this->SetTable($table);
        $fields = $this->GetInsertValue($value, $multi);

        $this->query = 'INSERT INTO ' . $this->db_table_prefix . $this->cur_table . ' ' . $fields['column'] . ' VALUES ' . $fields['value'];

        $this->Execute();

        return $this->Insert_id();
    }

    /**
     * Select one row From DataBase
     *
     * @param string $tanle
     * @param array $column Selected array('name1','name2')
     * @param array $where
     * @param array $order array("clolumn" => "ASC");
     *
     * @return array
     */
    public function SelectOne($table, $column, array $where = array(), array $order = array())
    {
        $this->SetTable($table);

        $this->query = 'SELECT ' . (($column == '*')?'*':$this->GetColumn($column)) . ' FROM ' . $this->db_table_prefix . $this->cur_table . $this->GetWhere($where) . $this->group . $this->GetOrder($order) . " LIMIT 0,1";
        $this->Execute();

        return $this->FetchArray();

    }

    /**
     * Select Data from DataBase
     *
     * @param string $table
     * @param array $column
     * @param array $where
     * @param array $order
     * @param array $count
     *
     * @return resource
     */
    public function Select($table, $column, array $where = array(), array $order = array(), array $count = array())
    {
        $this->SetTable($table);

        $this->query = 'SELECT ' . (($column == '*')?'*':$this->GetColumn($column)) . ' FROM ' . $this->db_table_prefix . $this->cur_table . $this->GetWhere($where) . $this->group . $this->GetOrder($order) . $this->GetLimit($count);
        $this->Execute();

        return $this->resource;
    }

    /**
     * Update Value in Table
     *
     * @param string $table Table for update data
     * @param array $set_value Array column and new value
     * @param array $where
     *
     * @return integer affected rows
     */
    public function Update($table, array $set_value, array $where = array(), $special = false)
    {
        if (!$set_value)
        {
            throw new ExceptionDataBase("Не передан парамер обновления таблици", $set);
        }

        $this->SetTable($table);

        $this->query = "UPDATE " . $this->db_table_prefix . $this->cur_table . $this->GetSet($set_value, $special) . $this->GetWhere($where);

        $this->Execute();

        return $this->Affected();
    }

    /**
     * Delete data from table
     *
     * @param string $table
     * @param array $where
     *
     * @return integer affected rows
     */
    public function Delete($table, array $where = array())
    {
        $this->SetTable($table);

        $this->query = ' DELETE FROM ' . $this->db_table_prefix . $this->cur_table . $this->GetWhere($where);
        $this->Execute();

        return $this->Affected();
    }

    /**
     * Direct Query
     *
     * Using only inside code
     *
     * @param string $query
     * @return resourse
     */
    public function DirectQuery($query)
    {
        $this->query = $query;
        $this->ExecuteQuery();
         
        return $this->resource;
    }
    
    abstract public function NumRows($resource = null);

    protected function __call($method, $arg)
    {
        throw new ExceptionDataBase('Вызов несуществующего метода ' . $method);
    }

    public function __destruct()
    {

    }

    protected function ExecuteQuery()
    {

    }

    public function EscapeString($value)
    {

    }

    public function FetchArray()
    {
    }

    private function Bug($message)
    {
        if ($this->debug)
        throw new ExceptionDataBase($message, $this->query, 0);
    }
}

?>