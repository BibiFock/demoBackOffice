/**
 * parcours object
 * utile for debug
 */
function objToStr( obj ){
	if( typeof(obj) == 'object' ){
		var txt = "";
		for( var p in obj ) {
			txt  += p +  ": " + obj[p] + "\n";
		}
		return txt;
	} else {
		return obj;
	}
}

/**
 * Custom events bindings
 */
function bindCustomEvents( baseElement ){
	if( typeof baseElement == 'object' ){
		var roles = $(baseElement).find('[data-role]');
	} else {
		var roles = $(baseElement + ' [data-role]'); 
	}
	
	if( roles ){
		$.each(roles, function(k, v){
			console.log($(v).attr('data-role') + '/' + $(v).attr('data-options'));
			var fct = $(v).attr('data-role');
			if( fct && functions[fct] ){
				//prevent event accumulation
				$( v ).unbind();
				// bind function
				functions[fct].call( {'el':v} );
			} else {
				return false;
			}
		});
	} else {
		alert('not found');
	}
}

/**
 * get options available on the element
 */
function fetchOptions(el){
	var opt = $( el ).attr('data-options')
	if( opt ) {
		//opt = opt.replace(/'/gi, '"');
		opt = $.parseJSON( opt );
		return opt;
	} else {
		return false;
	}
}


var functions = {
	'sendAjax': function(method, url, params, target){
			$.ajax({
				type: method,
				url: url,
				data: params,
				'beforeSend': function(){
					functions.displayLoadingImg( target );
				},
				'complete': function( res ) {
					if(res != "false"){
						$( target ).empty().append( res.responseText ).delay(2000);
						bindCustomEvents(target);
					}
				}
			});
	},
	'displayLoadingImg': function( el ){
		$( el ).html($('#waiting').html());
	},
	'hideAlert': function( el ){
		$( this.el ).click(function(){
			var target = $( this ).parentsUntil('hero-unit', '.alert');
			$( target ).addClass('hidden');
			return false;
		});
	},
	'loadUrl': function(){
		$( this.el ).click(function(){
			var opts = fetchOptions( this ); 
			var target = $('.hero-unit');
			functions.sendAjax('GET', opts.url, '&from=user', target);
			return false;
		});
	},
	'saveForm': function(){
		$( this.el ).submit(function(){
			var opts = fetchOptions( this ); 
			var target = $('.hero-unit');
			var formParams = $( this ).serialize();
			functions.sendAjax('POST', opts.url, formParams, target);
			return false;
		});
	},
	'delSection': function(){
		$( this.el ).click(function(){
			if(confirm('Are you sure?')){
				var opts = fetchOptions( this ); 
				var target = $('.hero-unit');
				functions.sendAjax('POST', opts.url, null, target);
			}
			return false;
		});
	},
} 
