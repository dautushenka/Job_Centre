if (typeof(Loaded_script) == 'undefined') 
{
	var Loaded_script = new Array();
	
	var NeedLoad = new Array();
	
	function _initJS()
	{
		{
			$.extend( {

				LoadScript : function(script_src)
				{
					if ($.inArray(script_src, Loaded_script) != -1)
					{
						return;
					}
					
					Loaded_script.push(script_src);
					
					$.ajax( {
						async : false,
						dataType : 'script',
//						scriptCharset : 'windows-1251',
						type : "GET",
						url : script_src,
						cache: true
					});
				}
			});
			
			$.LoadScript(dle_root + "engine/job/javascript/job.js?v=1.1.0");
			
            for ( var index in NeedLoad)
            {
                if (typeof(NeedLoad[index]) == 'string')
                {
                    $.LoadScript(NeedLoad[index]);
                }
                else
                {
                    $.LoadScript(NeedLoad[index][0]);
                    NeedLoad[index][1]();
                }
            }
		}
	}
	
	if (typeof(jQuery) == 'undefined')
	{
	    var httpRequest;
	    
	    if (window.XMLHttpRequest) { // Mozilla, Safari, ...
	        httpRequest = new XMLHttpRequest();
	        if (httpRequest.overrideMimeType) {
	            httpRequest.overrideMimeType('text/javascript');
	            // See note below about this line
	        }
	    } 
	    else if (window.ActiveXObject) { // IE
	        try {
	            httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	        } 
	        catch (e) {
	            try {
	                httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
	            } 
	            catch (e) {}
	        }
	    }

	    if (!httpRequest) {
	        alert('Giving up :( Cannot create an XMLHTTP instance');

	    }
	    else
	    {
//	        httpRequest.onreadystatechange = function(){alert('ff');};
	        httpRequest.open('GET', dle_root + 'engine/job/javascript/jquery.js', false);
	        httpRequest.send('');
	        
	        try {
                if (httpRequest.readyState == 4) {
                    if (httpRequest.status == 200) {
                        eval(httpRequest.responseText);
                        _initJS();
                    } else {
                        alert('There was a problem with the request.');
                    }
                }
            }
            catch( e ) {
                alert('Caught Exception: ' + e.description);
            }
	    }
	    
	    
//	    jq = document.createElement('script');
//	    jq.src = '/engine/car-market/javascript/jquery.js';
//	    jq.type = 'text/javascript';
//	    document.getElementsByTagName('head')[0].appendChild(jq);
	}
	else
	{
        $(function() {
            _initJS();
        });
	}
}
