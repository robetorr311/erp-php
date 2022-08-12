var vendorsJS = {
		init: function() {
			$.ajax('./api/v1/vendor/all', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {vendorsJS.displayVendors(rs);});
			
			$(document).on("click", ".vendorEdit", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var vId = $(this).data("vendor-id");
				$("#txtVendorname").val($("#vendor-vendorname-"+vId).val());
				$("#txtFirstname").val($("#vendor-firstname-"+vId).val());
				$("#txtLastname").val($("#vendor-lastname-"+vId).val());
				$("#txtEmail").val($("#vendor-email-"+vId).val());
				$("#txtPhone1").val($("#vendor-phone1-"+vId).val());
				$("#txtPhone2").val($("#vendor-phone2-"+vId).val());
				$("#txtAddress1").val($("#vendor-address1-"+vId).val());
				$("#txtAddress2").val($("#vendor-address2-"+vId).val());
				$("#txtCity").val($("#vendor-city-"+vId).val());
				$("#selectState").val($("#vendor-state-"+vId).val());
				$("#txtZip").val($("#vendor-zip-"+vId).val());
				$("#hiddenVendorId").val($("#vendor-id-"+vId).val());
				if($("#vendor-active-"+vId).val() == 1) {
					$("#chkActive").prop("checked",true);
				} else {
					$("#chkActive").prop("checked",false);
				}
				$('#vendorInfoModal').modal('show');
			});
			
			$(document).on("click", "#addVendorBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				$("#txtVendorname").val("");
				$("#txtFirstname").val("");
				$("#txtLastname").val("");
				$("#txtEmail").val("");
				$("#txtPhone1").val("");
				$("#txtPhone2").val("");
				$("#txtAddress1").val("");
				$("#txtAddress2").val("");
				$("#txtCity").val("");
				$("#selectState").val("");
				$("#txtZip").val("");
				$("#hiddenVendorId").val(-1);
				$("#chkActive").prop("checked",true);
				$('#vendorInfoModal').modal('show');
			});
			
			$(document).on("click", "#vendorInfoSaveBtn", function(event) {
				var form = document.getElementById("vendorForm");
				if (form.checkValidity() === false) {
					event.preventDefault();
			        event.stopPropagation();
			        form.classList.add('was-validated');
			        return;
				}

				var submitType = "POST";
				var vendorObj = {};
				vendorObj['vendorname'] = $("#txtVendorname").val();
				vendorObj['firstname'] = $("#txtFirstname").val();
				vendorObj['lastname'] = $("#txtLastname").val();
				vendorObj['phone1'] = $("#txtPhone1").val().replace(/\D/g,'');
				vendorObj['phone2'] = $("#txtPhone2").val().replace(/\D/g,'');
				vendorObj['email'] = $("#txtEmail").val();
				vendorObj['address1'] = $("#txtAddress1").val();
				vendorObj['address2'] = $("#txtAddress2").val();
				vendorObj['zip'] = $("#txtZip").val();
				vendorObj['city'] = $("#txtCity").val();
				vendorObj['state'] = $("#selectState").val();
				
				if($("#chkActive").prop("checked")) {
					vendorObj['active'] = 1;
				} else {
					vendorObj['active'] = 0;
				}
				
				if($("#hiddenVendorId").val() > 0) {
					vendorObj['vendor_id'] = $("#hiddenVendorId").val();
					submitType = "PUT";
				}

				this.classList.add("disabled");
				$.ajax({
			          url: './api/v1/vendor/', // url where to submit the request
			          type : submitType, // type of action POST || GET
			          dataType : 'json', // data type
			          data : JSON.stringify(vendorObj), // post data || get data
			          contentType: "application/json",
			          headers:{"Authorization":"Bearer " + Cookies.get('token')},
			          success : function(result) {
			              if(result.id) {
			            	  $.ajax('./api/v1/vendor/all/1', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {vendorsJS.displayVendors(rs);});
			            	  var form = document.getElementById("vendorForm");
			            	  form.classList.remove('was-validated');
			            	  $('#vendorInfoModal').modal('hide');
			            	  $('#vendorInfoFormMessageContainer').html("");
			              } else {
			            	  $('#vendorInfoFormMessageContainer').html("");
			            	  $('#vendorInfoFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Vendor<br />'+JSON.stringify(result)+'</div>');
			              }
			              $("#vendorInfoSaveBtn").removeClass("disabled");
			          },
			          error: function(xhr, resp, text) {
			        	  $('#vendorInfoFormMessageContainer').html("");
			        	  $('#vendorInfoFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Vendor<br />'+text+'</div>');
			        	  $("#vendorInfoSaveBtn").removeClass("disabled");
			          }
			      })
			});
			
			$(document).on("click", ".showVendorHistory", function(event) {
				var vid = $(this).data("vendor-id");
				if($("#vendorHistory"+vid).hasClass("show")) {
					$("#vendorHistory"+vid).collapse("hide");
				} else {
					if($("#vendorHistory"+vid).data("fetched-history") != true) {
						if(vid != "") {
							$.ajax('./api/v1/vendor/history/'+vid, {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {vendorsJS.displayHistory(rs);});
							$("#vendorHistory"+vid).data("fetched-history",true);
						}
					}
					$("#vendorHistory"+vid).collapse("show");
				}
				event.preventDefault();
				event.stopPropagation();
			});
		},
		
		displayVendors: function(rs) {
			var vendors = "";
			$.each(rs, function(i, v) {
				if(!v.email) {v.email = "";}
				if(!v.phone2) {v.phone2 = "";}
				if(!v.address2) {v.address2 = "";}
				var rowClass = "";
				if(v.active != "1") {
					rowClass = "table-secondary";
				}
				vendors += '<tr class="'+rowClass+'"><td><strong><a href="#" class="vendorEdit" data-vendor-id="'+v.id+'" >'+v.vendorname+'</a></strong><br />'+v.firstname+' '+v.lastname+'</td>';
				vendors += '<td>'+v.phone1+'</td>';
				vendors += '<td>'+v.email+'</td>';
				vendors += '<td>'+v.address1+'</td>';
				vendors += '<td>'+v.city+'</td>';
				vendors += '<td>'+v.state+'</td>';
				vendors += '<td>'+v.zip;
				vendors += '<input type="hidden" value="'+v.id+'" id="vendor-id-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.vendorname+'" id="vendor-vendorname-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.firstname+'" id="vendor-firstname-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.lastname+'" id="vendor-lastname-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.phone1+'" id="vendor-phone1-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.phone2+'" id="vendor-phone2-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.email+'" id="vendor-email-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.address1+'" id="vendor-address1-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.address2+'" id="vendor-address2-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.zip+'" id="vendor-zip-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.city+'" id="vendor-city-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.state+'" id="vendor-state-'+v.id+'" />';
				vendors += '<input type="hidden" value="'+v.active+'" id="vendor-active-'+v.id+'" />';
				vendors += '</td><td><a href="#" data-vendor-id="'+v.id+'" class="font-weight-bold showVendorHistory">History</a></td></tr>';
				vendors += '<tr class="collapse" id="vendorHistory'+v.id+'"><td colspan="8">No History Found</td></tr>';
			});
			
			$("#vendorTableBody").html(vendors);
		},
		
		displayHistory: function(rs) {
			var invoices = "";
			var history = "";
			$.each(rs, function(i, o) {
				if(!o.total) {o.total = 0;}
				var paid = '<span class="text-danger font-weight-bold">Unpaid</span>';
				if(o.paid == 1) {
					paid = '<span class="text-success font-weight-bold">Paid</span>';
				}
				invoices += '<tr><td>' + o.date + '</td><td><a class="font-weight-bold" href="invoicehistory.php?id=' + o.id + '">'+o.number+'</a></td><td>'+Number(o.total).toFixed(2)+'</td><td>' + paid + '</td></tr>';
			});
						
			if(invoices != "") {
				history += '<td colspan="8"><table class="table">';
				history += '<tr><th>Date</th><th>Number</th><th>Total</th><th>Paid</th></tr>';
				history += invoices + '</table></td>';
			}
			
			if(history != "") {
				$("#vendorHistory"+rs[0].vendor_id).html(history);
			}
			
		}
};