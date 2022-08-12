var appointmentsJS = {
	init: function() {
		$.ajax('./api/v1/order/appointments', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			appointmentsJS.displayAppointments(rs);
		});
	},

	displayAppointments: function(rs) {
		var viewCookie = Cookies.get("appointmentView");
		$('#appointments').fullCalendar({
			themeSystem: 'bootstrap4',
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			dayClick: function(date) {
                $('#startDate').val(date.format('YYYY-MM-DD'));
                if (date.format('hh:mm A') == "12:00 AM") {
                	$('#startTime').val("08:00 AM");
                	$('#startTimeHdn').val("08:00");
                } else {
	                $('#startTime').val(date.format('hh:mm A'));
	                $('#startTimeHdn').val(date.format('HH:mm'));
                }
                $('#newTicketModal').modal('show');
            },
            minTime: "08:00:00",
			maxTime: "18:00:00",
			allDaySlot: false,
			navLinks: true,
			weekNumbers: true,
			eventLimit: true,
			viewRender: function(view,element){
				Cookies.set('appointmentView', view.type+"|"+view.intervalStart.format()+"|"+view.intervalEnd.format(), { expires: 1, secure: false });
			},
			eventRender: function(eventObj, $el) {
				$el.popover({
					title: eventObj.poptitle,
			        content: eventObj.description,
			        trigger: 'hover',
			        html: true,
			        placement: 'top',
			        container: 'body'
				});
			},
			events: rs
		});
		
		if(viewCookie) {
			var view = viewCookie.split("|");
			$('#appointments').fullCalendar("changeView", view[0], view[1], view[2]);
		}
		
		
	}
};

$("#createNewTicketBtn").on('click', function(event) {
	event.preventDefault();
	event.stopPropagation();
	var startDate = $("#startDate").val();
	var startTime = $("#startTimeHdn").val();

	window.location = "workorder.php?startdate="+startDate+"&starttime="+startTime;
});