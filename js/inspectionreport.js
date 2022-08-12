var unsaved = false;
function confirmExit() {
	if (unsaved) {
		return unsaved;
	}
}

function toggleChevron(e) {
	$(e.target)
	.prev('.card-header')
	.find("span.fa")
	.toggleClass('fa-chevron-down fa-chevron-up');
}

var orderId = utilitiesJS.getQueryVariable("orderId");

var inspectionReport = {
	init: function() {
		$('#accordion').on('hide.bs.collapse show.bs.collapse', toggleChevron);

		$.ajax('./api/v1/inspectionReport/'+orderId, {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
			inspectionReport.displayVehicleInfo(rs.vehicle);
			if(rs.hasOwnProperty("inspectionreport")) {
				inspectionReport.displayReport(rs.inspectionreport);
			}
		});

		window.onbeforeunload = confirmExit;
		$("#workOrderBc").html('<a href="/workorderedit.php?orderId='+orderId+'">Work Order</a>');
	},

	displayVehicleInfo: function(rs) {
		$('#vin').val(rs.vin);
		$('#mileage').val(rs.mileage);
		$('#license').val(rs.license);
		$('#trimengine').val(rs.trim);
		$('#fleetnum').val(rs.fleetnum);
	},

	displayReport: function(rs) {
		$('#saveReport').text('Update Report');
		$('#inspectionreport_id').val(rs.id);

		inspectionReport.displayPreliminaryInspection(rs.preliminary_inspection);
		inspectionReport.displayCluster(rs.cluster);
		inspectionReport.displayUnderHood(rs.under_hood);
		inspectionReport.displayUnderCar(rs.under_car);
		inspectionReport.displaySteeringSuspension(rs.steering_suspension);
		inspectionReport.displayTires(rs.tires);
		inspectionReport.displayBrakes(rs.brakes);
		inspectionReport.displayTechnicianInitials(rs.tech_initials);
	},

	displayPreliminaryInspection: function(rs) {
		$.each(rs, function(i, m) {
			$('#'+i).val(m);
		});
	},

	displayCluster: function(rs) {
		$.each(rs, function(i, m) {
			$('#'+i).val(m);
		});
	},

	displayUnderHood: function(rs) {
		$.each(rs, function(i, m) {
			$('#'+i).val(m);
		});
	},

	displayUnderCar: function(rs) {
		$.each(rs, function(i, m) {
			$('#'+i).val(m);
		});
	},

	displaySteeringSuspension: function(rs) {
		$.each(rs, function(i, m) {
			$('#'+i).val(m);
		});
	},

	displayTires: function(rs) {
		$.each(rs, function(i, m) {
			$('#'+i).val(m);
		});
	},

	displayBrakes: function(rs) {
		$.each(rs, function(i, m) {
			$('#'+i).val(m);
		});
	},

	displayTechnicianInitials: function(rs) {
		$.each(rs, function(i, m) {
			$('#'+i).val(m);
		});
	},

	generateReportObject: function() {
		var reportObj = {};
		reportObj.preliminaryInspection = utilitiesJS.serializeObject("inspectionReportForm", "preliminaryInspectionSerialize");
		reportObj.cluster = utilitiesJS.serializeObject("inspectionReportForm", "clusterSerialize");
		reportObj.underHood = utilitiesJS.serializeObject("inspectionReportForm", "underHoodSerialize");
		reportObj.underCar = utilitiesJS.serializeObject("inspectionReportForm", "underCarSerialize");
		reportObj.steeringSuspension = utilitiesJS.serializeObject("inspectionReportForm", "steeringSuspensionSerialize");
		reportObj.tires = utilitiesJS.serializeObject("inspectionReportForm", "tiresSerialize");
		reportObj.brakes = utilitiesJS.serializeObject("inspectionReportForm", "brakesSerialize");
		reportObj.technicianInitials = utilitiesJS.serializeObject("inspectionReportForm", "technicianInitialsSerialize");
		return reportObj;
	},
};

$(document).on("click", "#saveReport", function(event){
	event.preventDefault();
	event.stopPropagation();
	var isError = false;

	var obj = inspectionReport.generateReportObject();
	var submitType = "POST";

	var inspectionreport_id = $('#inspectionreport_id').val();
	if(typeof inspectionreport_id != 'undefined' && inspectionreport_id > 0) {
		submitType = "PUT";
		obj.id = inspectionreport_id;
	}

	if(obj != null && !isError) {
		obj.orderId = orderId;
		this.classList.add("disabled");
		unsaved = false;
		$.ajax({
			url: './api/v1/inspectionReport/',
			type : submitType,
			dataType : 'json',
			data : JSON.stringify(obj),
			contentType: "application/json",
			headers:{"Authorization":"Bearer " + Cookies.get('token')},
			success : function(result) {
				if(result.id) {
					window.location = "workorderedit.php?orderId=" + orderId;
				} else {
					$('#inspectionReportMessageContainer').html("");
					$('#inspectionReportMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Report<br />'+JSON.stringify(result)+'</div>');
				}
				$("#saveReport").removeClass("disabled");
			},
			error: function(xhr, resp, text) {
				$('#inspectionReportMessageContainer').html("");
				$('#inspectionReportMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Report<br />'+text+'</div>');
				$("#saveReport").removeClass("disabled");
			}
		});
	}
});

$(document).on("change", "#inspectionReportForm", function(event) {
	unsaved = true;
});
