
//Random helper functions and globals

var dayNames = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];


function initToolbarBootstrapBindings() {
  $('a[title]').tooltip({container:'body'});
	$('.dropdown-menu input').click(function() {return false;})
	    .change(function () {$(this).parent('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle');})
    .keydown('esc', function () {this.value='';$(this).change();});

  $('[data-role=magic-overlay]').each(function () { 
    var overlay = $(this), target = $(overlay.data('target')); 
    overlay.css('opacity', 0).css('position', 'absolute').offset(target.offset()).width(target.outerWidth()).height(target.outerHeight());
  });
};
	
function formatTime(time) {
	if(!time) return;
	
	//Check to see if the time is already formatted. (Most likely the case.)
	var _time = time.toLowerCase();
	if(_time.indexOf("am") != -1 || _time.indexOf("pm") != -1) {		
		return time; //already formatted appropriately
	}
	
	//strip leading zeros first
	if(_time.substring(0,1) === "0") {
		time = time.substring(1);
	}
	
	var parts = time.split(":");
	var hh = parts[0];
	var m = parts[1];	
	var dd = "AM";
    var h = hh;
    if (h >= 12) {
        h = hh-12;
        dd = "PM";
    }
    if (h == 0) {
        h = 12;
    }
    return h + ":" + m + " " + dd;
}

function getDisplayTime(event) {
	if(!event) return;
	var displayTime = "";
	if(event.all_day === "1") {
		displayTime = "All Day";	
	} else {
		if(event.start_time) {
			displayTime = formatTime(event.start_time);	
		}
		if(event.end_time) {
			displayTime += " - " + formatTime(event.end_time);
		}
		//Remove :00 for cleaner times
		displayTime = displayTime.replace(/:00/g, '');
	}
	return displayTime;					
}

// input example: "2013-01-30"
function getShortDate(dateString) {
	if(dateString) {
		var parts = dateString.match(/(\d+)/g);
		if(parts && parts.length > 2) {
			var displayDate = new Date(parts[0], parts[1]-1, parts[2]); //JS Date month is indexed 0-11
			var month = displayDate.getMonth() + 1; //JS date month indexed 0-11
			return month + "/" + displayDate.getDate();
		}
	}
}

function getDisplayDate(event) {
	if(!event) return "";
	
	var displayString = "";
	if(event.date) {
		var parts = event.date.match(/(\d+)/g);
		var displayDate = new Date(parts[0], parts[1]-1, parts[2]); //JS Date month is indexed 0-11
		displayString += monthNames[displayDate.getMonth()] + " <span title=\"" + dayNames[displayDate.getDay()] + "\">" + displayDate.getDate() + "</span>";	
	}
	if(event.end_date) {
		var parts = event.end_date.match(/(\d+)/g);
		var displayEndDate = new Date(parts[0], parts[1]-1, parts[2]);
		//If the end date is in the same month, omit the month name.
		var dayWithHover = "<span title=\"" + dayNames[displayEndDate.getDay()] + "\">" + displayEndDate.getDate() + "</span>";
		if(displayDate.getMonth() == displayEndDate.getMonth()) {
			displayString += "–" + dayWithHover;
		} else {
			displayString += " – " + monthNames[displayEndDate.getMonth()] + " " + dayWithHover;
		}
	} 
	return displayString;
}

function getQueryParam(key) {
   var query = window.location.search.substring(1);
   var vars = query.split("&");
   for (var i=0; i < vars.length; i++) {
	   var pair = vars[i].split("=");
	   if(pair[0] == key) return pair[1];
   }
   return false;
}

var SITE = SITE || {};
SITE.fileInputs = function() {
  var $this = $(this),
      $val = $this.val(),
      valArray = $val.split('\\'),
      newVal = valArray[valArray.length-1],
      $button = $this.siblings('.button'),
      $fakeFile = $this.siblings('.file-holder');
  if(newVal !== '') {
//    $button.text('Photo Chosen');
    if($fakeFile.length === 0) {
      $button.after('<span class="file-holder">' + newVal + '</span>');
    } else {
      $fakeFile.text(newVal);
    }
  }
};

$(document).ready(function() {
  $('.file-wrapper input[type=file]').bind('change focus click', SITE.fileInputs);
});