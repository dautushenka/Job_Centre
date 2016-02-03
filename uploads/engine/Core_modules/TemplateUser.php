<?php
/*
 =====================================================
 Template class v1.0.0
 -----------------------------------------------------
 http://kaliostro.net/
 -----------------------------------------------------
 Copyright (c) 2008 kaliostro ICQ: 415-74-19
 =====================================================
 Данный код защищен авторскими правами
 =====================================================
 */

if(!defined('DATALIFEENGINE'))
{
    die("Hacking attempt!");
}

final class TemplateUser extends Licencing
{
    private $tpl = null;
    private $patch = '';
    private $content = '';

    private $separator = array();
    private $sep_count = array();
    private $row_count = array();
    private $row_content = array();
    private $row_template = array();
    private $row_including_count = 0;
    private $row_current_content = '';

    public  $subhead = '';
    public  $superhead = '';
    public  $_blank = false;
    public  $AJAX_script = '';
    private $style_cur ='';
    private $script_cur ='';
    private $ajax_style_cur ='';
    private $ajax_script_cur ='';

    public  $use_alt_url = false;
    public  $alt_url_array = array();
    public  $clear_url = true;
    public  $no_clear =  array();
    public  $main_url = '';
    public  $temp_main_url = '';
    public  $main_alt_url = '';
    public  $temp_main_alt_url = '';
    public  $safe_blocks = array();

    private $title;
    private $speedbar;

    private static $style = '';
    private static $script = '';
    private static $show_sub = false;
    private static $show_superhead = false;

    private static $ajax_style = '';
    private static $ajax_script = '';

    public static $Debug_info = array();

    public function __construct(dle_template &$tpl, $patch = '')
    {
        $this->tpl =& $tpl;
        $this->patch = $patch;
        $this->content =& $this->tpl->copy_template;
    }

    public function load($template)
    {

        $this->tpl->load_template($this->patch . $template . ".tpl");

        return $this;
    }

    public function AddToContent($data)
    {
        $this->tpl->result['content'] .= $data;

        return $this;
    }

    /**
     * Allowing block
     *
     * @param string $block Block tag
     * @param bool $set Show/Hide block
     * @return TemplateUser
     */
    public function SetBlock($block, $set = 1)
    {
        if ($this->row_including_count)
        {
            $ext_content =& $this->row_current_content[$this->row_including_count];
        }
        else
        {
            $ext_content =& $this->content;
        }

        if ($set == 1)
        {
            $ext_content = preg_replace("#\[$block\](.*?)\[/$block\]#si", "\\1", $ext_content);
        }
        else
        {
            $ext_content = preg_replace("#\[$block\](.*?)\[/$block\]#si", "", $ext_content);
        }

        return $this;
    }

    public function GetBlockContent($block_name)
    {
        preg_match("#\[$block_name( +separator=\"(.*?)\" +count=\"([0-9]+)\")?\](.*?)\[/$block_name\]#si", $this->content, $matches);

        return $matches[4];
    }

    public function SetBlockContent($block_name, $content)
    {
        if ($this->row_including_count)
        {
            $ext_content =& $this->row_current_content[$this->row_including_count];
        }
        else
        {
            $ext_content =& $this->content;
        }
         
        $this->ClaerContent($content);
        $ext_content = preg_replace("#\[$block_name( +separator=\"(.*?)\" +count=\"([0-9]+)\")?\](.*?)\[/$block_name\]#si", $content, $ext_content);

        return $this;
    }

    public function WrapBlock($block_name, $wrap_begin, $wrap_end)
    {
        if ($this->row_including_count)
        {
            $ext_content =& $this->row_current_content[$this->row_including_count];
        }
        else
        {
            $ext_content =& $this->content;
        }
         
        $ext_content = preg_replace("#\[$block_name( +separator=\"(.*?)\" +count=\"([0-9]+)\")?\](.*?)\[/$block_name\]#si", $wrap_begin . "\\0" . $wrap_end, $ext_content);
         
        return $this;
    }

    public function OpenRow($row_name)
    {
        $this->row_including_count++;
         
        if (empty($this->row_template[$row_name]))
        {
            preg_match("#\[$row_name( +separator=\"(.*?)\" +count=\"([0-9]+)\")?\](.*?)\[/$row_name\]#si", $this->content, $matches);

            if (!$matches)
            {
                return $this;
            }
            	
            $this->row_template[$row_name] = $this->row_current_content[$this->row_including_count] = $matches[4];
            $this->separator[$row_name] = $matches[2];
            $this->sep_count[$row_name] = (int)$matches[3];
        }
        else
        {
            $this->row_current_content[$this->row_including_count] = $this->row_template[$row_name];
        }

        $this->row_content[$row_name] = '';
        $this->row_count[$row_name] = 0;

        return $this;
    }

    public function SetRow($replace, $row_name)
    {
        $separator = '';

        //$this->GetRow($row_name);

        if (empty($this->row_template[$row_name]))
        {
            return $this;
        }

        if ($this->separator[$row_name] && $this->sep_count[$row_name])
        {
            if ($this->row_count[$row_name]%$this->sep_count[$row_name] == 0)
            $separator = $this->separator[$row_name];
        }

        $this->row_current_content[$this->row_including_count] = strtr($this->row_current_content[$this->row_including_count], $replace);
        $this->ClaerContent($this->row_current_content[$this->row_including_count]);

        $this->row_content[$row_name] .= $separator . $this->row_current_content[$this->row_including_count] . "\n";
        $this->row_current_content[$this->row_including_count] = $this->row_template[$row_name];
        $this->row_count[$row_name]++;

        return $this;
    }

    /**
     * Closing row
     *
     * @param string $row_name
     * @return TemplateUser
     */
    public function CloseRow($row_name)
    {
        $this->row_including_count--;

        $this->SetBlockContent($row_name, $this->row_content[$row_name]);

        return $this;
    }
    /**
     * Set value in tag
     *
     * @param mixed $set value or array(tag=>value)
     * @param string $find
     * @return TemplateUser
     */
    public function Set($set, $find = '')
    {
        if ($this->row_including_count)
        {
            $ext_content =& $this->row_current_content[$this->row_including_count];
        }
        else
        {
            $ext_content =& $this->content;
        }
         
        if (is_array($set))
        $ext_content = strtr($ext_content, $set);
        else
        $ext_content = str_replace($find, $set, $ext_content);
        	
        return $this;
    }

    private function ClaerContent(&$content)
    {
        preg_match_all('#\[([^/\] ]+)( +separator=\".*?\" +count=\"[0-9]+\")?\]#si', $content, $matches);
        //echo $content."\n\n\n\n===========================================================================================\n\n\n\n\n\n";print_r($matches[1]);
        foreach ($matches[1] as $row)
        {
            if (!in_array($row, $this->safe_blocks) && ((RSS && strpos($row, 'CDATA') === false) || (!RSS && strpos($content, "[/$row]") !== false)))
            $content = preg_replace("#\[{$row}[^\]]*\].*?\[/$row\]#si", '', $content);
        }

        $content = str_replace('{THEME}', $GLOBALS['config']['http_home_url'] . 'templates/' . $GLOBALS['config']['skin'], $content);
        //		echo "\n**********************************************************************************************************************\n\n" . $content.
        //			"\n************************************************************************************************************************\n\n\n\n";
        $content = preg_replace('#{.*?}#i', '', $content);

        return $this;
    }

    /**
     * Set complited result into tag
     *
     * @param string $result_name result name
     * @param string $tag tag
     * @return TemplateUser
     */
    public function SetResult($result_name, $tag)
    {
        if (!empty($this->tpl->result[$result_name]))
        {
            $this->Set($this->tpl->result[$result_name], $tag);
        }

        return $this;
    }

    public function Compile($cont_name, $subhead = '')
    {
        foreach ($this->row_content as $row_name=>$row_content)
        {
            if ($row_content)
            {
                $this->SetBlockContent($row_name, $row_content);
            }
        }
         
        $this->ClaerContent($this->content);

        if (!self::$show_sub)
        {
            $subhead = $this->subhead . $subhead;
            self::$show_sub = true;
        }

        if (!self::$show_superhead)
        {
            $this->style_cur = $this->superhead . $this->style_cur;
            self::$show_superhead = true;
        }
        	
        if ((!defined('AJAX') || !AJAX) && (!defined('RSS') || !RSS))
        {
            if ($this->_blank)
            {
                $this->Set($this->style_cur, "__style__")
                ->Set($this->script_cur, "__script__")
                ->Set($this->superhead, "__superhead__")
                ->Set($subhead, "__subhead__");
                $this->content = preg_replace('#__.*?__#i', '', $this->content);
                $this->content = str_replace('{THEME}', $GLOBALS['config']['http_home_url'] . 'templates/' . $GLOBALS['config']['skin'], $this->content);
            }
            else
            {
                $this->content = $this->style_cur . $this->script_cur . $subhead . $this->content;
            }
        }
        elseif (!defined('RSS') || !RSS)
        {
            $this->content = $this->ajax_style_cur . $this->ajax_script_cur . $this->AJAX_script . $this->content;
        }

        $this->tpl->compile($cont_name);
        $this->tpl->clear();
        $this->row_name = $this->sep_count = $this->row_count = $this->separator = $this->row_template = $this->row_content = array();
        $cur_content = $this->tpl->result[$cont_name];
        $this->content = $this->style_cur = $this->script_cur = $this->ajax_style_cur = $this->ajax_script_cur = '';

        return $cur_content;
    } 

    public function SetStyleScript($style = array(), $script = array())
    {
        global $js_array;
        
        foreach ($style as $css)
        {
            if (empty(self::$style[$css]))
            {
                self::$style[] = $css;
                $this->style_cur .= '<style type="text/css" media="all">@import url(' . $css . ");</style>\n";
            }
        }
        foreach ($script as $sc)
        {
            $sc = trim($sc, "/");
            if (!in_array($sc, $js_array)) {
                $js_array[] = $sc;
            }
        }
        return $this;
    }

    public function AJAXStyleScript($style = array(), $script = array())
    {
        foreach ($style as $css)
        {
            if (empty(self::$ajax_style[$css]))
            {
                self::$ajax_style[] = $css;
                $this->ajax_style_cur .= '<style type="text/css" media="all">@import url(' . $css . ");</style>\n";
            }
        }
        foreach ($script as $sc)
        {
            if (empty(self::$ajax_script[$sc]))
            {
                self::$ajax_script[] = $sc;
                $this->ajax_script_cur .= '<script type="text/javascript" src="' . $sc . "\"></script>\n";
            }
        }
        return $this;
    }

    public function SetForm($hidden, $action = '', $method = 'POST', $script = '', $close = false)
    {
        $form = "<form action='$action' method='$method' $script >\n";
        foreach ($hidden as $name=>$value)
        {
            $form .= "<input type='hidden' name='$name' value='$value' />\n";
        }
        $this->content = $form . $this->content;

        if ($close)
        $this->content .= "</form>";
        	
        return $this;
    }

    public function WrapContent($begin = '', $end = '')
    {
        $this->content = $begin . $this->content . $end;

        return $this;
    }

    public function msg($title, $text, $back = false)
    {
        if ($back === true)
        $text .= "<br />" . "<a href=\"#\" OnClick=\"window.history.go(-1);return false;\" >Вернуться назад</a>";
        elseif ($back)
        $text .= "<br />" . "<a href=\"$back\" >Вернуться назад</a>";
        	
        msgbox($title, $text);
    }

    public function Selection($options=array(), $name = "", $selected = "", $script = "")
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

    public function SelectionMulti($options=array(), $name = "", $selected = array(), $script = "")
    {
        if (!count($options) || $name == "") return false;

        $output =  "<select name=\"{$name}\" multiple $script >\r\n";
        foreach($options as $value=>$description)
        {
            $output .= "<option value=\"$value\"";
            if(@in_array($value, $selected)){ $output .= " selected "; }
            $output .= ">$description</option>\n";
        }
        $output .= "</select>";

        return $output;
    }

    public function InputText($name, $value, $script = "")
    {
        if ($name == "")
        return false;
        	
        return "<input type=\"text\" name=\"$name\" value=\"$value\" $script />";
    }

    public function InputHidden($name, $value, $script = "")
    {
        if ($name == "")
        return false;
        	
        return "<input type=\"hidden\" name=\"$name\" value=\"$value\" $script />";
    }

    public function InputPassword($name, $value, $script = "")
    {
        if ($name == "")
        return false;
        	
        return "<input type=\"password\" name=\"$name\" value=\"$value\" $script />";
    }

    public function InputFile($name, $script = '')
    {
        return "<input type=\"file\" value=\"\" name=\"$name\" $script />";
    }

    public function InputCheckbox($name, $value, $checked = 0, $script = "")
    {
        if ($checked)
        $script .= " checked=\"checked\"";
        return "<input type=\"checkbox\" name=\"$name\" value=\"$value\" $script />";
    }

    public function InputButton($value, $script = "")
    {
        return "<input type=\"button\" value=\"$value\" $script />";
    }

    public function InputSubmit($value, $script = "")
    {
        return "<input type=\"submit\" value=\"$value\" $script />";
    }

    private function GetAltUrl(&$url_array, $optinos)
    {
        if ($this->temp_main_alt_url)
        $url_str = $this->temp_main_alt_url;
        else
        $url_str = $this->main_alt_url;

        foreach ($url_array as $name=>$value)
        {
            if (!empty($this->alt_url_array[$name]))
            $url_str .= $this->alt_url_array[$name] . $value;
            else
            $url_str .= $value;
        }

        $this->temp_main_alt_url = '';

        return $url_str . ".html";
    }

    private function GetNormalUrl(&$url_array, $optinos)
    {
        if ($this->temp_main_url)
        $url_str = $this->temp_main_url;
        else
        $url_str = $this->main_url;

        if (strpos($url_str, "?") !== false)
        $url_str .= $optinos['separator'];
        else
        $url_str .= "?";

        $i = 0;
        foreach ($url_array as $name=>$value)
        {
            if ($i) $url_str .= $optinos['separator'];
            	
            $url_str .= $name . "=" . $value;
            	
            $i++;
        }

        $this->temp_main_url = '';

        return $url_str;
    }

    /**
     * Generate Url from array considering Friendly URL
     *
     * @param array $url_array array of var and values
     * @param array $alt_url_array array of replace in url_array for Friendly URL
     * @param array $add addition var
     * @param array $del deletion var from url_array
     * @param array $options addition options
     * @return string
     */
    public function GetUrl(array $url_array = array(), array $alt_url_array = array(), array $add = array(), array $del = array(), array $options = array())
    {
        $default_options = array(
								"clear" => false,
								"use_alt_url" => true,
								"dell_all" => false,
								"separator" => '&amp;',
        );
        $options = array_merge($default_options, $options);

        if ($options['use_alt_url'] && $this->use_alt_url && $alt_url_array)
        $url_array = @array_merge($url_array, $alt_url_array);

        if ($options['dell_all'])
        $url_array = array();
        else
        $url_array = @array_diff_key($url_array, @array_flip($del));
        	
        $url_array = @array_merge($url_array, $add);

        if ($options['clear'] && $url_array)
        {
            foreach ($url_array as $key=>$value)
            {
                if (!$value/* && !in_array($key, $url_array)*/)
                unset($url_array[$key]);
            }
        }

        if ($options['use_alt_url'] && $this->use_alt_url)
        return $this->GetAltUrl($url_array, $options);
        else
        return $this->GetNormalUrl($url_array, $options);
    }

    public function TitleSpeedBar($text = '', $link  = '')
    {
        if ($text)
        {
            if ($this->title)
            $this->title .= " &raquo; ";

            $this->title .= $text;
            	
            if ($this->speedbar)
            $this->speedbar .= " &raquo; ";
            	
            if ($link)
            $this->speedbar .= "<a href=\"$link\" title=\"$text\" />$text</a>";
            else
            $this->speedbar .= $text;

            return $this;
        }
        else
        return array('title' => $this->title, "speedbar" => $this->speedbar);
    }

    public function StringFormat()
    {
        $set_num = func_num_args() - 1;
        $arg = func_get_args();
        $string = $arg[0];
         
        if ($string && $set_num >= 1)
        {
            for ($i = 0; $i < $set_num; $i++)
            {
                $string = str_replace("{" . $i . "}", $arg[$i + 1], $string);
            }
        }
         
        return $string;
    }

    public function PageNavigation(array $url, $count_all, $per_page, $cur_page = 1, $i = 0, array $options = array())
    {
        $default_options = array(
								"link_script" => '',
        );
        $options = array_merge($default_options, $options);

        if (!$i)
        $i = $per_page;
        	
        if ((int)$cur_page <= 0)
        $cur_page = 1;
        	
        $current_show_sub = self::$show_sub;
        	
        $this_2 = $this->_clone();

        $this_2->tpl->load_template('navigation.tpl');

        $no_prev = false;
        $no_next = false;
        $cstart = ((int)$cur_page - 1) * $per_page;
        $i +=$cstart;

        if (isset($cstart) and $cstart != "" and $cstart > 0)
        {
            $prev = $cstart / $per_page;
            	
            if ($prev > 1)
            $page_url = $this_2->GetUrl($url, array(), array("page" => $prev), array(), $options);
            else
            $page_url = $this_2->GetUrl($url, array(), array(), array(), $options);

            $this_2->Set("<a {$options['link_script']} href=\"" . $page_url . "\">", "[prev-link]");
            $this_2->Set("</a>", "[/prev-link]");
        }
        else
        $no_prev = TRUE;

        if($per_page)
        {
            if($count_all > $per_page)
            {
                $enpages_count = @ceil($count_all/$per_page);
                $pages = "";

                $cstart = ($cstart / $per_page) + 1;

                if ($enpages_count <= 10 )
                {
                    for( $j=1; $j <= $enpages_count; $j++)
                    {
                        if ($j > 1)
                        $page_url = $this_2->GetUrl($url, array(), array("page" => $j), array(), $options);
                        else
                        $page_url = $this_2->GetUrl($url, array(), array(), array(), $options);

                        if($j != $cstart)
                        $pages .= "<a {$options['link_script']} href=\"" . $page_url . "\">$j</a> ";
                        else
                        $pages .= "<span>$j</span> ";
                    }
                }
                else
                {
                    $start =1;
                    $end = 10;
                    $nav_prefix = "... ";

                    if ($cstart > 0)
                    {
                        if ($cstart > 5)
                        {
                            $start = $cstart - 4;
                            $end = $start + 8;

                            if ($end >= $enpages_count)
                            {
                                $start = $enpages_count - 9;
                                $end = $enpages_count - 1;
                                $nav_prefix = "";
                            }
                            else
                            $nav_prefix = "... ";
                        }
                    }

                    if ($start >= 2)
                    $pages .= "<a {$options['link_script']} href=\"" . $this_2->GetUrl($url, array(), array(), array(), $options) . "\">1</a> ... ";

                    for( $j = $start; $j <= $end; $j++)
                    {
                        if ($j > 1)
                        $page_url = $this_2->GetUrl($url, array(), array("page" => $j), array(), $options);
                        else
                        $page_url = $this_2->GetUrl($url, array(), array(), array(), $options);
                        	
                        if($j != $cstart)
                        $pages .= "<a {$options['link_script']} href=\"" . $page_url . "\">$j</a> ";
                        else
                        $pages .= "<span>$j</span> ";
                    }

                    if ($cstart != $enpages_count)
                    $pages .= $nav_prefix."<a {$options['link_script']} href=\"" . $this_2->GetUrl($url, array(), array("page" => $enpages_count), array(), $options) . "\">{$enpages_count}</a>";
                    else
                    $pages .= "<span>{$enpages_count}</span> ";
                }
            }
            $this_2->Set($pages, '{pages}');
        }

        if($per_page AND $per_page < $count_all AND $i < $count_all)
        {
            $next_page = @floor($i / $per_page) + 1;
            $this_2->Set("<a {$options['link_script']} href=\"" . $this_2->GetUrl($url, array(), array("page" => $next_page), array(), $options) . "\">", "[next-link]");
            $this_2->Set("</a>", "[/next-link]");
        }
        else
        $no_next = TRUE;

        if  (!$no_prev OR !$no_next)
        $this_2->Compile('PageNavigation');

        $this->tpl->result['PageNavigation'] = $this_2->tpl->result['PageNavigation'];

        unset($this_2);

        self::$show_sub = $current_show_sub;

        return $this;
    }

    public function __call($method, $arg)
    {

    }

    public function __get($var)
    {
        if (!empty($this->tpl->result[$var]))
        {
            return $this->tpl->result[$var];
        }
    }

    public function __set($var, $value)
    {
        $this->tpl->result[$var] = $value;
    }

    public function __destruct()
    {

    }

    private function _clone()
    {
        $tpl = new dle_template();
        $tpl->dir = ROOT_DIR.'/templates/'.$GLOBALS['config']['skin'];

        $this_2 = new TemplateUser($tpl, $this->patch);

        $this_2->use_alt_url   = $this->use_alt_url;
        $this_2->alt_url_array = $this->alt_url_array;
        $this_2->clear_url     = $this->clear_url;
        $this_2->no_clear      = $this->no_clear;
        $this_2->main_url      = $this->main_url;
        $this_2->main_alt_url  = $this->main_alt_url;

        return $this_2;
    }

}
?>