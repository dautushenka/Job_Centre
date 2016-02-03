// JavaScript Document

(function($){
  // очищаем select
  $.fn.clearSelect = function() {
    return this.each(function(){
      if(this.tagName=='SELECT') {
        this.options.length = 0;
        $(this).attr('disabled','disabled');
      }
    });
  }
  // заполняем select
  $.fn.fillSelect = function(dataArray) {
    return this.clearSelect().each(function(){
      if(this.tagName=='SELECT') {
        var currentSelect = this;
        $.each(dataArray,function(index,data){
          var option = new Option(data.name,data.id);
          if($.support.cssFloat) {
            currentSelect.add(option,null);
          } else {
            currentSelect.add(option);
          }
        });
      }
    });
  }
  
  $.fn.AJAXLoading = function()
  {
	  return this.each(function()
								{
									if (typeof(dle_skin) != 'undefined')
										$(this).hide().addClass('ajax_remove').before("<img class='loader' src='" + dle_root + "templates/" + dle_skin + "/job/images/loader.white.gif' />");
									else
										$(this).hide().addClass('ajax_remove').before("<img class='loader' src='" + dle_root + "engine/job/images/loader.white.gif' />");
								});
  }
})(jQuery);

var SelectedCity = new Array();
var SelectedSpecialties = new Array();
var edit = new Array();

function SetSpecialties(specialties)
{
	var specialty_id_search = $('#specialty_id_search');
	$.each(specialties, function(index, data)
	{
		if (data.id != '' && $.inArray(data.id, SelectedSpecialties) == -1)
		 {
			SelectedSpecialties.push(data.id);
			specialty_id_search.find("option[value=" + data.id + "]").hide();
			var str = "<span class=\"specialty\" OnClick=\"removeSpecialty('" + data.id + "');\" id=\"i" + data.id + "\"><br /><span>" + data.name + "<input type='hidden' name='specialty_id[]' value='" + data.id + "' /> " + "<img src=\"" + dle_root + "engine/job/images/admin/minus.gif\" /></span></span>";
			$('#specialty_id_search').after(str);
		 }
	});
}

function removeCity(id)
{
	$('#city_id_search').find("option[value=" + id + "]").show();
	$("#c" + id).remove();
	var index = $.inArray(id, SelectedCity);
	if (index != -1)
		SelectedCity.splice(index, 1);
}

function removeSpecialty(id)
{
	$('#specialty_id_search').find("option[value=" + id + "]").show();
	$("#i" + id).remove();
	var index = $.inArray(id, SelectedSpecialties);
	if (index != -1)
		SelectedSpecialties.splice(index, 1);
}

function SetCities(cities)
{
	var city_id_search = $('#city_id_search');
	$.each(cities, function(index, data)
	{
		if (data.id != '' && $.inArray(data.id, SelectedCity) == -1)
		 {
			SelectedCity.push(data.id);
			city_id_search.find("option[value=" + data.id + "]").hide();
			var str = "<span class=\"city\" OnClick=\"removeCity('" + data.id + "');\" id=\"c" + data.id + "\"><br /><span>" + data.name + "<input type='hidden' name='city_id[]' value='" + data.id + "' /> " + "<img src=\"" + dle_root + "engine/job/images/admin/minus.gif\" /></span></span>";
			$('#city_id_search').after(str);
		 }
	});
}

function CheckDisable()
{
	if (use_country && $('#city_id').val() == '')
		$('#city_id').attr('disabled', 'disabled');
		
	if (use_country && $('#country_id_search').val() == '')
		$('#city_id_search').attr('disabled', 'disabled');
		/*
	if ($('#sphere_id').val() == '')
		$('#specialty_id').attr('disabled', 'disabled');
	*/
	if ($('#sphere_id').val() == '')
	    $('#specialty').attr('disabled', 'disabled');
		
	if ($('#sphere_id_search').val() == '')
		$('#specialty_id_search').attr('disabled', 'disabled');
}

jQuery(document).ready(function()
{
	$.ajaxSetup({
					complete:function()
					{
						$('img.loader').remove();
						$('.ajax_remove').show().removeClass('ajax_remove');
					}
				});

	CheckDisable();
	
	$('#country_id').change(function()
	 {
		 if (this.value == '')
		 {
			 $('#city_id').clearSelect();
		 }
		 else
		 {
			 $('#city_id').AJAXLoading();
			 $.post(ajax_url, {'action':'GetCities', 'id':this.value}, function(data)
																					   {
																						   $('#city_id').fillSelect(eval(data)).attr('disabled', '');
																					   });
		 }
	 });
	/*
	$('#sphere_id').change(function()
	 {
		 if (this.value == '')
		 {
			 $('#specialty_id').clearSelect();
		 }
		 else
		 {
			 $('#specialty_id').AJAXLoading();
			 $.post(ajax_url, {'action':'GetSpecialties', 'id':this.value}, function(data)
																					   {
																						   $('#specialty_id').fillSelect(eval(data)).attr('disabled', '');
																					   });
		 }
	 });
	*/
	$('#sphere_id').change(function()
	        {
	    if (this.value == '')
	    {
	        $('#specialty').val('').attr('disabled', 'disabled');
	    }
	    else
	    {
	        $('#specialty').val('').removeAttr('disabled');
	    }
    });
	
	$('#country_id_search').change(function()
	 {
		 $(".city").remove();
		 SelectedCity = new Array();
		 if (this.value == '')
		 {
			 $('#city_id_search').val('').attr('disabled', 'disabled');
		 }
		 else
		 {
			 $('#city_id_search').AJAXLoading();
			 $.post(ajax_url, {'action':'GetCities', 'id':this.value, 'search':true}, function(data)
																									   {
																										   $('#city_id_search').fillSelect(eval(data)).attr('disabled', '');
																									   });
		 }
	 });
	
	$('#city_id_search').change(function()
	 {
		 if (this.value != '')
		 {
//			 $(this.options[this.selectedIndex]).hide();
			 SetCities(new Array({id:this.value, name:this.options[this.selectedIndex].text}));
		 }
		 else
		 {
			 $(".city").remove();
			 $('#city_id_search').find("option").show();
			 SelectedCity = new Array();
		 }
	 });
	
	$('#sphere_id_search').change(function()
	{
		$(".specialty").remove();
		SelectedSpecialties = new Array();
		if (this.value == '')
			$('#specialty_id_search').val('').attr('disabled', 'disabled');
		else
		{
			$('#specialty_id_search').AJAXLoading();
			$.post(ajax_url, {'action':'GetSpecialties', 'id':this.value, 'search':true}, function(data)
																									   {
																										   $('#specialty_id_search').fillSelect(eval(data)).attr('disabled', '');
																									   });
		}
	});
	
	$('#specialty_id_search').change(function()
	 {
		 if (this.value != '')
		 {
			 //$(this.options[this.selectedIndex]).hide();
			 SetSpecialties(new Array({id:this.value, name:this.options[this.selectedIndex].text}));
		 }
		 else
		 {
			 $(".specialty").remove();
			 $('#specialty_id_search').find("option").show();
			 
			SelectedSpecialties = new Array();
		 }
	 });
});

function BlockContent(jobj)
{
	var width = jobj.width();
    var	height = jobj.height();
	
	jobj.html("<center style=\"padding:20px;\"><img src=\"" + dle_root + "templates/" + dle_skin + "/job/images/loading.gif\" /></center>");
}

function FindIndex(t, v)  
{
	var k=-1 

	for (var i=0; i <= v.length-1; i++)  
		if (v[i] == t )  
			{k=i; break}  
	return k  
}

function favorites(img, id, type)
{
	if ($.cookie('favorites_' + type))
		var favorites = $.cookie('favorites_' + type).split(",");
	else
		var favorites = new Array();
		
	var index = FindIndex(id, favorites);

	if (index != -1)
	{
		favorites.splice(index, 1);
		img.src = dle_root + "templates/" + dle_skin + "/job/images/plus.gif";
	}
	else
	{
		favorites.push(id);
		img.src = dle_root + "templates/" + dle_skin + "/job/images/minus.gif";
	}
		
	$.cookie('favorites_' + type, favorites.toString(), {expires: 365, path:"/"});
}

function AllowSite(type, id, allow)
{
	if (allow == 0)
		$(".id" + id).addClass("moder_new");
	else
	{
		$(".id" + id).removeClass("moder_new");
		$(".id" + id).removeClass("moder_old");
	}
	edit[id] = allow;
	$.post(ajax_url, {'action':'allow_site', 'id':id, 'allow':allow, 'type':type});
	
	return false;
}