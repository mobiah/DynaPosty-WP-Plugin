/*
*	Dynaposty Admin Functions 
*/


// decides whether to show a confirmation about leaving the page, if the user has changed
// content settings, and has not saved them.
var dypo_unsavedEdits = false;
function dypo_contentOnUnload () {
	if ( typeof(dypo_unsavedEdits) == 'boolean' && dypo_unsavedEdits ) {
		return "You may have changed some Dynaposty Settings without saving.  Are you sure?";
	}
}

// when a user clicks on a table cell, we assume they want to edit it.
// hide the span in the td, and show the editable form field
function dypo_editCell ( cell ) {
	jQuery(cell).find('span').hide();
	jQuery(cell).find(':input').show().focus();
	dypo_unsavedEdits = true;
	return;
}

// make all editable cells go back to spans instead of inputs
// and make sure the span holds the same value as the edited input
function dypo_resetEdits ( classname, strongClassname ) {
	jQuery('.'+classname).find(':input').hide().each( function () {
		inputVal = this.value;
		if ( jQuery(this).parent().hasClass(strongClassname) ){
			inputVal = '<strong>'+inputVal+'</strong>';
		}
		jQuery(this).parent().find('span').html(inputVal).show();
	}) ;
}

// build a list of shortcodes from table in the page
// also looks for duplicates.  if one is found, returns false.
function dypo_buildShortcodes ( scnPrefix, scPrefix ) {
	scObject = {};
	jQuery('[id^='+scnPrefix+']').each( function () {
		// get the index so we can find the corresponding shortcode
		id = this.id;
		shortcodeIndex = id.substring( id.indexOf('|') + 1 );
		
		// get the name entered
		shortcodeName = jQuery(this).val();
		// now get the value from the corresponding shortcode in the next row
		shortcode = jQuery( '#'+scPrefix+'\\|'+shortcodeIndex ).val();
		
		// have we already seen this shortcode?  if so, return false.  
		if ( shortcode in scObject ){
			scObject = false;
			return false;
		}
		
		// put 'em in the object.
		scObject[shortcode] = shortcodeName;
	} );
	return scObject;
}

// build a list of setnames from the table in the page
function dypo_buildValueSets ( vsPrefix ) {
	vsObject = {};
	counter = 1;
	jQuery('[id^='+vsPrefix+']').each( function () {
		vsObject[counter] = this.value;
		counter++;
	} );
	return vsObject;
}

// build a 2-dimensional array of all the shortcode values that have been entered.
function dypo_buildValues ( idPrefix, scPrefix ) {
	valObject = {};
	jQuery('[id^='+idPrefix+']').each( function () {
		id = this.id;
		valSet = id.substring( idPrefix.length, id.indexOf('|') );
		shortcodeIndex = id.substring( id.indexOf('|') + 1 );
		shortcode = jQuery( '#'+scPrefix+'\\|'+shortcodeIndex ).val();
		if ( typeof(shortcode) == 'undefined' ) {
			shortcode = shortcodeIndex;
		}
		newValue = this.value;
		
		if ( typeof(valObject[valSet]) == 'undefined' ) {
			// the sub-array doesn't exist, let's make it.
			valObject[valSet] = {};
		}
		// now add the new value
		valObject[valSet][shortcode] = newValue;
	} );
	return valObject;
}

// find duplicate URL variables
function dypo_findDupeURLVars ( valEditPrefix ) {
	varObj = {};
	foundDupe = false;
	jQuery(':input[id$=urlvar]').each( function () {
		if ( this.value in varObj ) {
			foundDupe = true;
		}
		varObj[this.value] = '.';
	} );
	return foundDupe;
}

// find the current maximum shortcode Index in the table
function dypo_getMaxShortcodeIndex ( scPrefix ) {
	curMax = 1;
	jQuery('span[id^='+scPrefix+']').each( function () {
		id = this.id;
		shortcodeIndex = Number(id.substring( id.indexOf('|') + 1 ));		
		curMax = Math.max( curMax, shortcodeIndex );
	} );
	return curMax;
}

// find the current maximum valueSet Index in the table
function dypo_getMaxValueSetIndex ( vsPrefix ) {
	curMax = 1;
	jQuery('span[id^='+vsPrefix+']').each( function () {
		id = this.id;
		shortcodeIndex = Number(id.substring( id.indexOf('|') + 1 ));		
		curMax = Math.max( curMax, shortcodeIndex );
	} );
	return curMax;
}

// some values in the page shouldn't have anything but 
// * alphanumeric
// * underscores
// * dashes
// * no leading and trailing whitespace, either.
// * shortcodes and urlvars also cannot have spaces.
// this function gets rid of anything we decide we don't want.  
// basically, anything in a text input and not in a textarea
function dypo_sanitizeInput( divID, noSpaceClass ) {
	jQuery('#'+divID+' :text').each( function () {
		if ( jQuery(this).hasClass(noSpaceClass) ) {
			this.value = this.value.replace(/[^a-zA-Z0-9\-\_]+/g,'');
		} else {
			this.value = jQuery.trim(this.value.replace(/[^a-zA-Z 0-9\-\_]+/g,''));
		}
	} );
}

// creates a new row (value set) at the end of the table, with some dummy default values
// and properly names ids for spans and inputs.
function dypo_newValueSetRow ( vsPrefix, vsEditPrefix, valPrefix, valEditPrefix, rowClassPrefix ) {
	curMaxValueSet = dypo_getMaxValueSetIndex(vsPrefix);
	newVSIndex = String(Number(curMaxValueSet) + 1);
	newSCIndex = 1;

	// clone a new row with data and Events (mostly events)
	newRow = jQuery('#dypo_shortcodeSettings tr#'+curMaxValueSet).clone(true).insertAfter('#dypo_shortcodeSettings tr#'+curMaxValueSet); 
	newRow.attr('id',newVSIndex).removeClass(rowClassPrefix+curMaxValueSet).addClass( rowClassPrefix+newVSIndex );
	
	// change the Value set ids and values of span and input
	newRow.find('#'+vsPrefix+'\\|'+curMaxValueSet).html('<strong>Set '+newVSIndex+'</strong>').attr('id',vsPrefix+'|'+newVSIndex);
	newRow.find('#'+vsEditPrefix+'\\|'+curMaxValueSet).val('Set '+newVSIndex).attr('id',vsEditPrefix+'|'+newVSIndex);
	
	// change the urlvars
	newRow.find('span[id^='+valPrefix+']').each( function() {
		if (this.id.indexOf('urlvar') != -1) {
			newVal = 'URLVariable'+newVSIndex;

			//change the ids
			this.id = valPrefix+newVSIndex+'|urlvar'
			jQuery(this).parent().find(':input').attr('id',valEditPrefix+newVSIndex+'|urlvar');
		} else {
			thisSCIndex = String(newSCIndex);
			newVal = 'value';
			
			//change the ids
			this.id = valPrefix+newVSIndex+'|'+thisSCIndex
			jQuery(this).parent().find(':input').attr('id',valEditPrefix+newVSIndex+'|'+thisSCIndex);

			// increment the shortcode counter
			newSCIndex++;
		}
		// set the spans contents and the input's value.
		jQuery(this).html(newVal).parent().find(':input').val(newVal);
	} );	
	
	dypo_unsavedEdits = true; // make sure they confirm before exiting
}

// creates a new column (shortcode) at the right of each row that is editable
// once again, with dummy values and properly set ids
function dypo_newShortcodeCol( scNamePrefix, scPrefix, columnClassPrefix ) {
	curMaxShortcode = dypo_getMaxShortcodeIndex( scPrefix );
	newSCIndex = String(Number(curMaxShortcode)+1);

	jQuery('#dypo_shortcodeSettings .dypo_editable:last-child').each( function () {
		// create the new element - with event handlers
		newCell = jQuery(this).clone(true).insertAfter(jQuery(this));
		oldSpanID = newCell.find('span').attr('id');
		oldInputID = newCell.find(':input').attr('id');
		if ( oldSpanID.indexOf(scNamePrefix) != -1 ) {
			// shortcode Name
			newVal = 'Shortcode Name';
		} else if ( oldSpanID.indexOf(scPrefix) != -1 ) {
			// shortcode
			newVal = 'shortcode'+newSCIndex;
		} else {
			// shortcode value
			newVal = 'value';
		}
		newSpanID = oldSpanID.replace('|'+String(curMaxShortcode),'|'+newSCIndex);
		newInputID = oldInputID.replace('|'+String(curMaxShortcode),'|'+newSCIndex);
		
		// update the new values and ids
		newCell.find('span').html(newVal).attr('id',newSpanID);
		newCell.find(':input').val(newVal).attr('id',newInputID);
		
		// and give it the right class to represent its column number
		newCell.removeClass(columnClassPrefix+curMaxShortcode).addClass(columnClassPrefix+newSCIndex);
	} );
	
	dypo_unsavedEdits = true; // make sure they confirm before exiting
}

// deleting rows (value sets)  easy, because we just delete a row.
function dypo_delValSet( domObj ) {
	// remove the row containing the object that was clicked
	jQuery(domObj).parent().parent().remove();
}
// deleting columns (shortcodes) harder, because we have to delete separate things
function dypo_delShortcode( domObj, columnClassPrefix ) {
	// get the classes from the parent cell
	classes = jQuery(domObj).parent().attr('class');
	// now, get the class which represents the column number
	colIndexClass = classes.substring(classes.indexOf(columnClassPrefix));
	if (colIndexClass.indexOf(' ') != -1) {
		// remove trailing spaces, and anything that comes after spaces.
		colIndexClass = colIndexClass.substring(0,colIndexClass.indexOf(' '));
	}
	// zzzzzzaap!
	jQuery('.'+colIndexClass).remove();
}

// show a message to the user on the main admin/config page, then fade it out.
// give it a message and a div to dump the message into
function dypo_showMessage( strMsg, divID, useFadeOut, isError, timeOut, fadeTime ) {

	if ( typeof(strMsg) == 'undefined' || strMsg.length == 0 ) {
		// no message to show?  don't do anything.
		return;
	}
	if ( typeof(divID) == 'undefined' ) {
		divID = 'dypo_message';
	}
	if ( typeof(useFadeOut) == 'undefined' ) {
		useFadeOut = true;
	}
	if ( typeof(isError) == 'undefined' ) {
		isError = false;
	}
	if ( typeof(timeOut) == 'undefined' ) {
		timeOut = 3000;  // wait for a default of 3 seconds.
	}
	if ( typeof(fadeTime) == 'undefined' ) {
		fadeTime = 1000; // fade for a default of 1 second
	}
	
	if ( isError ) {
		jQuery('div#'+divID).addClass('dypo_error_message');
	} else {
		jQuery('div#'+divID).removeClass('dypo_error_message');
	}
	// show the confirmation/message
	jQuery('div#'+divID).html(strMsg).show(); 
	// resize the container.
	jQuery('div#'+divID).parent().height(jQuery('div#'+divID).outerHeight());
	// then maybe set a timeout to let the div disappear
	if ( useFadeOut ) {
		setTimeout( function(){ jQuery('div#'+divID).fadeOut(fadeTime); }, timeOut );
	}
}

// send an ajax request to a URL.
// and show/hide the 'loading' div and result message container
function dypo_ajax ( url, data, msgDivID, loadingDivID, useFadeOut ) {

	if ( typeof(msgDivID) == 'undefined' ) {
		msgDivID = 'dypo_message';
	}
	if ( typeof(loadingDivID) == 'undefined' ) {
		loadingDivID = 'dypo_loading';
	}
	if ( typeof(useFadeOut) == 'undefined' ) {
		useFadeOut = true;
	}

	// hide any existing messages
	jQuery('div#'+msgDivID).hide();
	// post to the url specified
	jQuery.post( url , data,
					function ( strMsg ){  
						// upon completion/callback hide the spinning/loading animation
						jQuery('div#'+loadingDivID).hide(); 
						// and show the message returned.
						dypo_showMessage( strMsg, msgDivID, useFadeOut );
					} );
	// show the spinning/loading animation while we wait.
	jQuery('div#'+loadingDivID).show();
}

// one lonely function for the content editor pages.
function dypo_insertShortcode( selectID ) {

	var win = window.dialogArguments || opener || parent || top;
	win.send_to_editor('['+jQuery('#'+selectID).val()+']');

	jQuery('#'+selectID).get(0).selectedIndex = 0;
}