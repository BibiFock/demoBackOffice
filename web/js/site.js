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
	'myFunction': function(){
		$( this.el ).click(function(){

			var fields = $('#fields');
			var formParams = $( this ).parentsUntil( '.search', 'form').serialize();
			var target = $('.patterns');

			$.ajax({
				type: 'POST',
				url: 'model',
				data: 'todo=get_sentences&'+formParams,
				'beforeSend': function(){
					functions.displayLoadingImg( target );
				},
				'complete': function( res ) {
					if(res != "false"){
						$( target ).empty().append( res.response ).delay(2000);
						bindCustomEvents('.patterns');
					}
				}
			});
			return false;
		});
	},
} 
