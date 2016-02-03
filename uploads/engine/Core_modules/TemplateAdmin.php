<?php
/*
 =====================================================
 Template class for AdminCenter v1.1.0
 -----------------------------------------------------
 http://kaliostro.net/
 -----------------------------------------------------
 Copyright (c) 2007 - 2009 kaliostro ICQ: 415-74-19
 =====================================================
 Данный код защищен авторскими правами
 =====================================================
 */

if(!defined('DATALIFEENGINE'))
{
    die("Hacking attempt!");
}

final class TemplateAdmin extends Licencing
{
    public  $line = "<div class=\"unterline\"></div>";
    private $calendar = 0;
    private $CountTD = array();
    private $CountTable = 0;
    public  $ajax = FALSE;
    public  $echo = TRUE;
    private $output = "";
    private $inclass = FALSE;
    public  $head = TRUE;
    public  $footer = TRUE;
    public  $superhead = '';
    public  $subhead = '';
    public  $menu = '';
    private $style = '';
    private $script = '';
    private $year = '';

    private $_tdStyle = array();
    
    public static $Debug_info = array();

    public function __construct($year = '')
    {
        $this->year = $year;
    }

    public function header($header_text = '', $menu = false, $subhead = '', $css = array(), $script = array())
    {
        if ($this->head)
        {
            echoheader('', $header_text);
            	
            echo $this->superhead;
            echo $this->style;
            foreach ($css as $cs)
            {
                echo '<style type="text/css" media="all">@import url(' . $cs . ");</style>\n";
            }
            echo $this->script;
            foreach ($script as $sc)
            {
                echo '<script type="text/javascript" src="' . $sc . "\"></script>\n";
            }
            echo $this->subhead;
            echo $subhead;
        }

        $this->head = FALSE;

        if ($menu && $this->menu)
        return $this->show_return($this->menu);
    }

    public function menu(array $action_array, $mod, $image_patch)
    {
        if (!is_array($action_array) || !count($action_array))
        return "";

        if ($this->menu)
        return $this->menu;
        else
        {
            $this->inclass = TRUE;
            $this->OpenTable('id="menu"');
            $this->OTable();
            foreach ($action_array AS $name=>$value)
            {
                $td[] = "<a href=\"$mod$value[0]\" title=\"$name\"><img src=\"$image_patch/$value[1]\" border=\"0\" /></a>";
            }
            $this->row($td, false);
            $this->CTable();
            $this->CloseTable();
            $this->inclass = FALSE;
            $this->menu = $this->output;
            $this->output = '';
        }
    }

    public function submenu($action_array, $mod, $image_patch)
    {
        if (!is_array($action_array) || !count($action_array))
        return "";

        $this->inclass = TRUE;
        $this->OpenTable();
        $this->OTable(array(), 'id="submenu"');
        foreach ($action_array AS $name=>$value)
        {
            $td[] = "<a href=\"$mod&action=$value[0]\" $value[2] title=\"$name\"><img src=\"$image_patch/$value[1]\" border=\"0\" /></a>";
        }
        $this->row($td, false);
        $this->CTable();
        $this->CloseTable();
        $this->inclass = FALSE;

        $this->show_return();
    }

    public function SetStyleScript($style = array(), $script = array())
    {
        foreach ($style as $css)
        {
            $this->style .= '<style type="text/css" media="all">@import url(' . $css . ");</style>\n";
        }
        foreach ($script as $sc)
        {
            $this->script .= '<script type="text/javascript" src="' . $sc . "\"></script>\n";
        }
    }

    public function stats($array)
    {
        if (!is_array($array) || !count($array))
        return "";
        	
        $output = "";
        $this->inclass = TRUE;
        $this->OTable();

        foreach ($array as $desc=>$value)
        {
            $output .=<<<HTML
			<tr>
		        <td style="padding-top:2px;padding-bottom:2px;width:300px;">$desc</td>
		        <td>$value</td>
		    </tr>
HTML;
        }
        $this->show_return($output);
        $this->CTable();
        $this->inclass = FALSE;

        return $this->show_return();
    }

    public function footer($footer = false, $year = '')
    {
        if (self::$Debug_info)
        {
            echo "<pre>";
            echo function_exists(StringLength)?StringLength(print_r(self::$Debug_info, true)):print_r(self::$Debug_info, true);
            echo "</pre>";
        }

        if ($year && $year < date('Y'))
        $year = $year . " - ";
        elseif ($this->year && $this->year < date('Y'))
        $year = $this->year . " - ";
        	
        $cur_yaer = date('Y');

        $output = <<<HTML
		<table width="100%">
		    <tr>
		        <td bgcolor="#EFEFEF" height="20" align="center" style="padding-right:10px;"><div class="navigation">Copyright © $year$cur_yaer <a href="http://www.kaliostro.net" style="text-decoration:underline;color:green">kaliostro</a></div></td>
		    </tr>
		</table>
HTML;
        if ($this->footer || $footer)
        {
            $this->show_return($output);
            echofooter();
        }
        exit();
    }

    public function OpenTable($row_script = '')
    {
        $output = <<<HTML
		<div style="padding-top:5px;padding-bottom:2px;">
		<table width="100%">
	    <tr>
	        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
	        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
	        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
	    </tr>
	    <tr $row_script>
	        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
	        <td style="padding:5px;" bgcolor="#FFFFFF">
HTML;
        $this->show_return($output);
    }

    public function OpenSubtable($title='', $script="")
    {
        $output = <<<HTML
		<table width="100%">
		    <tr>
		        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$title}</div></td>
		    </tr>
		</table>
		<div class="unterline"></div>
		<table width="100%" $script >
		<tr><td>
HTML;
        return $this->show_return($output);
    }

    public function CloseSubtable($button=false)
    {
        if ($button)
        $button = <<<HTML
<tr>
	<td style="padding:10px;"><input type="submit" class="buttons" value="$button"></td>
</tr>
HTML;
        $output = <<<HTML
			</td>
		</tr>
		$button
		</table>
HTML;
		return $this->show_return($output);
    }

    public function RowTable($script, $td_script)
    {
        $output = <<<HTML
			</td>
			 <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
		</tr>
		<tr $script>
			<td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
	        <td style="padding:5px;" bgcolor="#FFFFFF" $td_script>
HTML;
        return $this->show_return($output);
    }

    public function CloseTable($button = '')
    {
        $output = '';
        if ($button)
        {
            $this->inclass = TRUE;
            $this->RowTable();
            $output .= '<input type="submit" class="buttons" value="' . $button . '">';
            $this->inclass = FALSE;
        }

        $output .= <<<HTML
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
        return $this->show_return($output);
    }

    public function msg($title, $text, $back=false)
    {
        if ($back === true)
        $back = "<a href=\"javascript:window.history.go(-1);\" >Вернуться назад</a>";
        elseif ($back)
        $back = "<a href=\"$back\" >Вернуться назад</a>";
        $this->inclass = TRUE;
        if ($this->head)
        $this->header($title);
        $this->OpenTable();
        $this->OpenSubtable($title, "align=center");
        $this->OTable(array(), "style=\"text-align:center; padding:20px;\"", false);
        $this->row(array('height="100" align="center"' => $text . "<br />" . $back), false);
        $this->CTable();
        $this->CloseSubtable();
        $this->CloseTable();
        $this->inclass = FALSE;
        $this->show_return();
        $this->footer(true);
        exit();
    }

    public function msg_yes_no($title, $text, array $yes, $no='')
    {
        $this->inclass = TRUE;
        if ($this->head)
        $this->header($title);
        $this->OpenTable();
        $this->OpenSubtable($title);
        $this->OTable(array(), "style=\"text-align:center; padding:20px;\"", false);
        $this->OpenForm('', $yes);
        $this->row(array('height="100" align="center"' => $text."<br /><br /><input class=bbcodes type=submit value=\"Да\"> &nbsp; <input type=button class=bbcodes value=\"Нет\" onclick=\"javascript:document.location='$no'\">"), false);
        $this->CloseForm();
        $this->CTable();
        $this->CloseSubtable();
        $this->CloseTable();
        $this->inclass = FALSE;
        $this->show_return();
        $this->footer(true);
        exit();
    }

    public function selection($options=array(), $name = "", $selected = "", $script = "")
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

        return $this->show_return($output);
    }

    public function SelectionMulti($options=array(), $name = "", $selected = array(), $script = "")
    {
        if (!count($options) || $name == "") return false;

        $output =  "<select name=\"{$name}\" multiple class=\"cat_select\" $script >\r\n";
        foreach($options as $value=>$description)
        {
            $output .= "<option value=\"$value\"";
            if(@in_array($value, $selected)){ $output .= " selected "; }
            $output .= ">$description</option>\n";
        }
        $output .= "</select>";

        return $this->show_return($output);
    }

    public function InputText($name, $value, $script = "")
    {
        if ($name == "")
        return false;
        	
        $output =  "<input class='edit' type=\"text\" name=\"$name\" value=\"$value\" $script />";

        return $this->show_return($output);
    }

    public function InputHidden($name, $value, $script = "")
    {
        if ($name == "")
        return false;
        	
        $output =  "<input type=\"hidden\" name=\"$name\" value=\"$value\" $script />";

        return $this->show_return($output);
    }

    public function InputPassword($name, $value, $script = "")
    {
        if ($name == "")
        return false;
        	
        $output = "<input type=\"password\" name=\"$name\" value=\"$value\" $script />";

        return $this->show_return($output);
    }

    public function InputFile($name, $script = '')
    {
        $output = "<input class='edit' type=\"file\" value=\"\" name=\"$name\" $script />";

        return $this->show_return($output);
    }

    public function InputCheckbox($name, $value, $checked = 0, $script = "")
    {
        if ($checked)
        $script .= " checked=\"checked\"";
        	
        $output = "<input type=\"checkbox\" name=\"$name\" value=\"$value\" $script />";

        return $this->show_return($output);
    }

    public function InputButton($value, $script = "")
    {
        $output = "<input class='buttons' type=\"button\" value=\"$value\" $script />";

        return $this->show_return($output);
    }

    public function InputSubmit($value, $script = "")
    {
        $output =  "<input class='buttons' type=\"submit\" value=\"$value\" $script />";

        return $this->show_return($output);
    }

    public function InputRadio($name, $value, $script = "")
    {
        $output = "<input type='radio' name='$name' value='$value' $script />";
         
        return $this->show_return($output);
    }

    public function TextArea($name, $value = '', $script ='')
    {
        $output ="<textarea name='$name' $script >$value</textarea>";

        return $this->show_return($output);
    }

    public function SettingRow($title="", $description="", $field="")
    {
        $output = "<tr>
		<td style=\"padding:4px\" class=\"option\">
	    <b>$title</b><br /><span class=small>$description</span>
	    <td width=394 align=middle style=\"padding:1px;\" >
	    $field
		</tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=2></td></tr>";

	    return $this->show_return($output);
    }

    public function OpenForm($action = "", array $hidden = array(), $script='')
    {
        $output = <<<HTML
		<form action="$action" method="post" $script >
HTML;
        if ($hidden)
        {
            foreach ($hidden as $name=>$value)
            {
                $output .= "<input type=\"hidden\" name=\"$name\" value=\"$value\"  />\n";
            }
        }

        return $this->show_return($output);
    }

    public function OpenGetForm($action = "", array $hidden = array(), $script='')
    {
        $output = <<<HTML
		<form action="$action" method="get" $script >
HTML;
        if ($hidden)
        {
            foreach ($hidden as $name=>$value)
            {
                $output .= "<input type=\"hidden\" name=\"$name\" value=\"$value\"  />\n";
            }
        }

        return $this->show_return($output);
    }

    public function CloseForm()
    {
        $output = "</form>";

        return $this->show_return($output);
    }

    public function OTable($thead = array(), $script = "", $line = true)
    {
        $this->CountTable++;
        $output = "<table cellpadding=\"0\" cellspacing=\"0\" $script width=\"100%\">";
        if (count($thead))
        {
            $this->CountTD[$this->CountTable] = count($thead);
            $output .= "\n<thead><tr>\n";
            foreach ($thead as $th)
            {
                $output .= "<td align=\"center\"><b>". $th . "</b></td>\n";
            }
            $output .= "</tr></thead>\n";
            if ($line)
            $output .= "<tbody><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=\"{$this->CountTD[$this->CountTable]}\"></td></tr>";
            else
            $output .= "<tbody>";
        }

        return $this->show_return($output);
    }

 	/**
     * 
     * @param array $td_style
     * @return TemplateAdmin
     */
    public function setTableStyle(array $td_style)
    {
        $first_key = key(reset($td_style));
        
        if (!isset($this->_tdStyle[$this->CountTable]))
        {
            $this->_tdStyle[$this->CountTable] = array();
        }
        
        foreach ($td_style as $td_num => $style)
        {
            if (!$first_key)
            {
                $td_num++;
            }
            
            $this->_tdStyle[$this->CountTable][$td_num] = $style;
        }
        
        return $this;
    }
    
    public function row($td_array, $line=true, $split=false, $script='')
    {
        if (!$td_array)
        return '';
        	
        if (is_array($td_array) && $split)
        $td_array = $td_array[0];
        	
        if (!isset($this->CountTD[$this->CountTable]))
        $this->CountTD[$this->CountTable] = count($td_array);
        	
        $output = "<tr $script >";
        if (!$split)
        {
            $i = 0;
            foreach ($td_array as $script=>$value)
            {
                if ($i >= $this->CountTD[$this->CountTable])
                break;
                	
                if (is_numeric($script))
                {
                    if (!empty($this->_tdStyle[$this->CountTable][$i+1]))
                    {
                        $script = $this->_tdStyle[$this->CountTable][$i+1];
                    }
                    else 
                    {
                        $script = "align=\"center\"";
                    }
                }
                	
                $output .= "<td $script >".$value."</td>";
                $i++;
            }
        }
        else
        $output .= "<td align=\"center\" colspan=\"{$this->CountTD[$this->CountTable]}\">". $td_array ."</td>";
        $output .= "</tr>";

        if ($line)
        $output .= "<tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=\"{$this->CountTD[$this->CountTable]}\"></td></tr>";
        	
        return $this->show_return($output);
    }

    public function CTable()
    {
        $output = "</tbody></table>";
        unset($this->CountTD[$this->CountTable--]);

        return $this->show_return($output);
    }

    public function url($param, $url="")
    {
        if (count($param))
        {
            $i = 0;
            foreach ($param as $key=>$value)
            {
                if ($i != 0) $url .= "&";
                $url .= $key."=".$value;
                $i++;
            }
        }

        return $this->show_return($url);
    }

    public function calendar($field, $showsTime = true, $format = '')
    {
        global $lang;

        if ($showsTime)
        $showsTime = 'true';
        else
        {
            $showsTime = 'false';
            	
            if (!$format)
            $format = '%Y-%m-%d';
        }

        if (!$format)
        $format = '%Y-%m-%d %H:%M';

        if (!$this->calendar)
        {
            $output = <<<HTML
				<link rel="stylesheet" type="text/css" media="all" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
				<script type="text/javascript" src="engine/skins/calendar.js"></script>
				<script type="text/javascript" src="engine/skins/calendar-en.js"></script>
				<script type="text/javascript" src="engine/skins/calendar-setup.js"></script>
HTML;
        }
        $output .= <<<HTML
			<img src="engine/skins/images/img.gif"  align="absmiddle" id="b_trigger_$this->calendar" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>&nbsp;
			<script type="text/javascript">
			    Calendar.setup({
			        inputField     :    "$field",     // id of the input field
			        ifFormat       :    "$format",      // format of the input field
			        button         :    "b_trigger_$this->calendar",  // trigger for the calendar (button ID)
			        align          :    "Br",           // alignment
					timeFormat     :    "24",
					showsTime      :    $showsTime,
			        singleClick    :    true
			    });
			</script>
HTML;
        $this->calendar++;

        return $this->show_return($output);
    }

    public function check_uncheck_all($formname)
    {
        $output = <<<HTML
		<script language='JavaScript' type="text/javascript">
		<!--
		function ckeck_uncheck_all() {
		    var frm = document.$formname;
		    for (var i=0;i<frm.elements.length;i++) {
		        var elmnt = frm.elements[i];
		        if (elmnt.type=='checkbox') {
		            if(frm.master_box.checked == true){ elmnt.checked=false; }
		            else{ elmnt.checked=true; }
		        }
		    }
		    if(frm.master_box.checked == true){ frm.master_box.checked = false; }
		    else{ frm.master_box.checked = true; }
		}
		-->
		</script>
		<input type='checkbox' name='master_box' title='' onclick='javascript:ckeck_uncheck_all()'>
HTML;
        return $this->show_return($output);
    }

    public function navigation($page, $per_page, $all_count, $url = "")
    {
        if ($url == "")
        $url = "/?";
        else
        $url .= "&";
        	
        $npp_nav ="";

        if ((int)$page)
        $start = ($page-1)*$per_page;
        else
        $start = 0;
        	
        $i = $per_page + $start;

        $cur_page = $start/$per_page + 1;

        if($start > 0)
        {
            $previous = ceil($i/$per_page)-1;
            $npp_nav .= "<a href=\"".$url."per_page=".$per_page."&page=".$previous."\">&lt;&lt; Previous</a>  ";
        }

        if($all_count > $per_page)
        {
            $enpages_count = @ceil($all_count/$per_page);
            $pages = "[ ";
            	
            $cstart = ($cstart / $per_page) + 1;

            if ($enpages_count <= 10 )
            {
                for( $j=1; $j <= $enpages_count; $j++)
                {
                    if($j != $cur_page)
                    $pages .= "<a href=\"" . $url . "per_page=" . $per_page . "&page=" . $j . "\">$j</a> ";
                    else
                    $pages .= "{{$j}} ";
                }
            }
            else
            {
                $start_count = 3;
                $end_count = $enpages_count - 2;
                $need_middle = true;

                if ($cur_page <= 5 || $cur_page > ($enpages_count - 5))
                $need_middle = false;
                	
                if ($cur_page  <= 5 && $cur_page > 2)
                $start_count = $cur_page + 1;
                	
                if ($enpages_count - $cur_page >= 2 && $cur_page > $enpages_count - 5)
                $end_count = $cur_page - 1;
                	
                for ($j = 1; $j <= $start_count; $j++)
                {
                    if($j != $cur_page)
                    $pages .= "<a href=\"" . $url . "per_page=" . $per_page . "&page=" . $j . "\">$j</a> ";
                    else
                    $pages .= "{{$j}} ";
                }

                $pages .= " .... ";

                if ($need_middle)
                {
                    for ($j = $cur_page - 1; $j <= $cur_page + 1; $j++)
                    {
                        if($j != $cur_page)
                        $pages .= "<a href=\"" . $url . "per_page=" . $per_page . "&page=" . $j . "\">$j</a> ";
                        else
                        $pages .= "{{$j}} ";
                    }
                    	
                    $pages .= " .... ";
                }

                for ($j = $end_count; $j <= $enpages_count; $j++)
                {
                    if($j != $cur_page)
                    $pages .= "<a href=\"" . $url . "per_page=" . $per_page . "&page=" . $j . "\">$j</a> ";
                    else
                    $pages .= "{{$j}} ";
                }
            }
            	
            $npp_nav .= $pages . "]";
        }

        if($all_count > $i)
        {
            $how_next = ceil($i/$per_page)+1;
            $npp_nav .= "  <a href=\"".$url."per_page=".$per_page."&page=".$how_next."\">Next &gt;&gt;</a>";
        }

        return $this->show_return($npp_nav);
    }

    private function show_return($echo = "")
    {
        if ($this->inclass)
        $this->output .= $echo;
        elseif ($this->echo)
        {
            echo $this->output.$echo;
            $this->output = '';
        }
        else
        {
            $this->output = '';
            return $echo;
        }
        	
        return $this;
    }

    public function __destruct()
    {

    }
}
?>