
//Random helper functions and globals

var dayNames = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];

function formatTime(time) {
	if(!time) return;
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
	var displayTime;
	if(event.all_day === "1") {
		displayTime = "All Day";	
	} else {
		displayTime = formatTime(event.start_time);
		if(event.end_time) {
			displayTime += " - " + formatTime(event.end_time);
		}
	}
	return displayTime;					
}

//format the date like "August 25"
function getDisplayDate(event) {
	if(event && event.date) {
		var parts = event.date.match(/(\d+)/g);
		var displayDate = new Date(parts[0], parts[1]-1, parts[2]); //JS Date month is indexed 0-11
		return dayNames[displayDate.getDay()] + ", " + monthNames[displayDate.getMonth()] + " " + displayDate.getDate();	
	}
}

