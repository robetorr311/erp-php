var settingsJS = {
		init: function() {
			$.ajax('./api/v1/store/checkcache', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (cached_version) {
				$.ajax('./api/v1/store/details?_=' + cached_version, {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displaySettings(rs);});
				$.ajax('./api/v1/store/paymentmethods?_=' + cached_version, {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displayPaymentMethods(rs);});
			});

			$(document).on("click", "#storeEdit", function(event) {
				event.preventDefault();
				event.stopPropagation();
				$("#txtSEditIdentifier").val($("#store-identifier").text());
				$("#txtSEditName").val($("#store-name").text());
				$("#txtSEditAddress1").val($("#store-address1").text());
				$("#txtSEditAddress2").val($("#store-address2").text());
				$("#txtSEditCity").val($("#store-city").text());
				$("#txtSEditState").val($("#store-state").text());
				$("#txtSEditZip").val($("#store-zip").text());
				$("#txtSEditPhone").val($("#store-phone").text());
				$("#txtSEditFax").val($("#store-fax").text());
				$("#txtSEditEmail").val($("#store-email").text());
				$("#txtSEditLaborRate").val($("#store-laborrate").text());
				$("#txtSEditTimezone").val($("#store-timezone").text());
				
				if ($("#show-reference-no").text() == 'Yes') {
					$("#txtSEditShowReferenceNo").prop('checked', true);
					$("#txtSEditReferenceNumber").prop('disabled', false);
				}

				var emailReminderTxt = $("#email-reminder").text();
				if (emailReminderTxt == 'Enabled') { 
					$("#txtSEditEmailReminder-1").attr('checked', true);
				} else {
					$("#txtSEditEmailReminder-0").attr('checked', true);
				}

				$("#txtSEditReferenceNumber").val($("#reference-number").text());
				$('#storeEditModal').modal('show');
			});

			$(document).on("change", "#txtSEditUploadLogo", function(event) {
				if (this.files && this.files[0]) {
					var reader = new FileReader();
					reader.onload = function (e) {
						$('#txtSEditLogo')
							.attr('src', e.target.result)
							.width(80)
							.height(80);
					};
					reader.readAsDataURL(this.files[0]);
				}
			});

			$(document).on("click", "#SEditCancelBtn, button.close", function(event) {
				var form = document.getElementById("storeEditForm");
				form.classList.remove('was-validated');
				$(form).find(".numeric_only").removeClass("btn-outline-danger");
				$('#storeEditModal').modal('hide');
				$('#storeEditFormMessageContainer').html("");
			});

			$(document).on("click", "#SEditUpdateBtn", function(event) {
				var form = document.getElementById("storeEditForm");
				var error = false;
				if (form.checkValidity() === false) {
					error = true;
				}
				
				var numeric_fields = $(form).find(".numeric_only");	
				numeric_fields.each(function() {	
					if(isNaN($( this ).val())) {	
						error = true;	
						$(this).addClass("btn-outline-danger");	
					}	
					else {	
						$(this).removeClass("btn-outline-danger");	
					}	
				});

				if(error) {
					form.classList.add('was-validated');
					event.preventDefault();
			        event.stopPropagation();
			        return;
				}
				var formData = utilitiesJS.serializeObject("storeEditForm","SEditSerialize");
				var submitType = "PUT";
				
				var showReferenceNo = $('#txtSEditShowReferenceNo').prop('checked');
				if(showReferenceNo) {
					formData["show_reference_no"] = 1;
				}

				var emailReminder = $("input[name='email_reminder']:checked").val();
				formData["email_reminder"] = emailReminder;
				
				this.classList.add("disabled");

				var formDataLogo = new FormData();
				formDataLogo.append('file', $('#txtSEditUploadLogo')[0].files[0]);
				$.ajax({
					url: 'uploadfile.php',
					type: 'post',
					data: formDataLogo,
					contentType: false,
					processData: false,
					success: function(response){
						if(response != 0) {
							$('#storeEditFormMessageContainer').html("");
					   } else {
							$('#storeEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Image not uploaded</div>');
					   }
					},
				 });

				$.ajax({
			          url: './api/v1/store/details/', // url where to submit the request
			          type : submitType, // type of action POST || GET
			          dataType : 'json', // data type
			          data : JSON.stringify(formData), // post data || get data
			          contentType: "application/json",
			          cache: false,
			          headers:{"Authorization":"Bearer " + Cookies.get('token')},
			          success : function(result) {
			              if(result.id) {
			            	  $.ajax('./api/v1/store/details', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displaySettings(rs);});
			            	  var form = document.getElementById("storeEditForm");
			            	  form.classList.remove('was-validated');
			            	  $('#storeEditModal').modal('hide');
			            	  $('#storeEditFormMessageContainer').html("");
			              } else {
			            	  $('#storeEditFormMessageContainer').html("");
			            	  $('#storeEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Store Detailsr<br />'+JSON.stringify(result)+'</div>');
			              }
			              $("#SEditUpdateBtn").removeClass("disabled");
			          },
			          error: function(xhr, resp, text) {
			        	  $('#storeEditFormMessageContainer').html("");
			        	  $('#storeEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Store Details<br />'+text+'</div>');
			        	  $("#SEditUpdateBtn").removeClass("disabled");
			          }
			      });
			});

			$(document).on("click", ".taxrateEdit", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var trId = $(this).data("taxrate-id");
				$("#taxrateModalTitle").html("Edit Tax Rate");
				$("#txtTREditName").val($("#taxrate-name-"+trId).val());
				$("#txtTREditRate").val($("#taxrate-rate-"+trId).val());
				$("#txtTREditCategory").val($("#taxrate-category-"+trId).val());
				$("#txtTREditExemptionAmount").val($("#taxrate-exemption-"+trId).val());
				$("#txtTREditId").val(trId);
				$("#TREditUpdateBtn").text("Update");
				$('#taxrateEditModal').modal('show');
			});

			$(document).on("click", "#TREditCancelBtn, button.close", function(event) {
				var form = document.getElementById("taxrateEditForm");
				form.classList.remove('was-validated');
				$(form).find(".numeric_only").removeClass("btn-outline-danger");
				$('#taxrateEditModal').modal('hide');
				$('#taxrateEditFormMessageContainer').html("");
			});

			$(document).on("click", "#addTaxRateBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				$("#taxrateModalTitle").html("Add Tax Rate");
				$("#txtTREditName").val("");
				$("#txtTREditRate").val("");
				$("#txtTREditCategory").val("part");
				$("#txtTREditExemptionAmount").val("");
				$("#txtTREditId").val("-1");
				$("#TREditUpdateBtn").text("Add");
				$('#taxrateEditModal').modal('show');
			});

			$(document).on("click", "#TREditUpdateBtn", function(event) {
				var form = document.getElementById("taxrateEditForm");
				var error = false;
				if (form.checkValidity() === false) {
					error = true;
				}

				var numeric_fields = $(form).find(".numeric_only");	
				numeric_fields.each(function() {	
					if(isNaN($( this ).val())) {	
						error = true;	
						$(this).addClass("btn-outline-danger");	
					} else if ($( this ).val() > 100) {
						error = true;	
						$(this).addClass("btn-outline-danger");
						$('#taxrateEditFormMessageContainer').html("");
			            $('#taxrateEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Rate and Exemption Amount must be less than or equal to 100.</div>');
					}	
					else {	
						$('#taxrateEditFormMessageContainer').html("");
						$(this).removeClass("btn-outline-danger");	
					}	
				});
				
				if(error) {
					form.classList.add('was-validated');
					event.preventDefault();
			        event.stopPropagation();
			        return;
				}
				var formData = utilitiesJS.serializeObject("taxrateEditForm","TREditSerialize");
				var submitType = "PUT";
				if(formData.taxrate_id == -1) {
					submitType = "POST";
				}
				this.classList.add("disabled");
				$.ajax({
			          url: './api/v1/store/taxrate/', // url where to submit the request
			          type : submitType, // type of action POST || GET
			          dataType : 'json', // data type
			          data : JSON.stringify(formData), // post data || get data
			          contentType: "application/json",
			          cache: false,
			          headers:{"Authorization":"Bearer " + Cookies.get('token')},
			          success : function(result) {
			              if(result.id) {
			            	  $.ajax('./api/v1/store/details', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displaySettings(rs);});
			            	  var form = document.getElementById("taxrateEditForm");
			            	  form.classList.remove('was-validated');
			            	  $('#taxrateEditModal').modal('hide');
			            	  $('#taxrateEditFormMessageContainer').html("");
			              } else {
			            	  $('#taxrateEditFormMessageContainer').html("");
			            	  $('#taxrateEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Tax Rate<br />'+JSON.stringify(result)+'</div>');
			              }
			              $("#TREditUpdateBtn").removeClass("disabled");
			          },
			          error: function(xhr, resp, text) {
			        	  $('#taxrateEditFormMessageContainer').html("");
			        	  $('#taxrateEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Tax Rate<br />'+text+'</div>');
			        	  $("#TREditUpdateBtn").removeClass("disabled");
			          }
			      });
			});

			$(document).on("click", ".taxrateDelete", function(event){
				event.preventDefault();
				event.stopPropagation();
				var trId = $(this).data("taxrate-id");
				var result = confirm("Are you sure you want to delete?");
				if (result) {
					$.ajax('./api/v1/store/taxrateDelete/'+trId, {cache: false, type: 'PUT', headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
	            	  	$.ajax('./api/v1/store/details', {headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displaySettings(rs);});
						alert("Tax Rate deleted successfully.");
					});
				}
			});

			$(document).on("click", ".teammemberEdit", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var tmId = $(this).data("employee-id");
				$("#taxrateModalTitle").html("Edit Team Member");
				$("#txtTMEditName").val($("#teammember-name-"+tmId).val());
				$("#txtTMEditRole").val($("#teammember-role-"+tmId).val());
				$("#txtTMEditId").val(tmId);
				$("#TMEditUpdateBtn").text("Update");
				$('#teammemberEditModal').modal('show');
			});

			$(document).on("click", "#TMEditCancelBtn, button.close", function(event) {
				var form = document.getElementById("teammemberEditForm");
				form.classList.remove('was-validated');
				$('#teammemberEditModal').modal('hide');
				$('#teammemberEditFormMessageContainer').html("");
			});

			$(document).on("click", "#addTeamMemberBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				$("#taxrateModalTitle").html("Add Team Member");
				$("#txtTMEditName").val("");
				$("#txtTMEditRole").val("1");
				$("#txtTMEditId").val("-1");
				$("#TMEditUpdateBtn").text("Add");
				$('#teammemberEditModal').modal('show');
			});

			$(document).on("click", "#TMEditUpdateBtn", function(event) {
				var form = document.getElementById("teammemberEditForm");
				var error = false;
				if (form.checkValidity() === false) {
					error = true;
				}
				
				if(error) {
					form.classList.add('was-validated');
					event.preventDefault();
			        event.stopPropagation();
			        return;
				}
				var formData = utilitiesJS.serializeObject("teammemberEditForm","TMEditSerialize");
				var submitType = "PUT";
				if(formData.employee_id == -1) {
					submitType = "POST";
				}
				this.classList.add("disabled");
				$.ajax({
			          url: './api/v1/store/teammember/', // url where to submit the request
			          type : submitType, // type of action POST || GET
			          dataType : 'json', // data type
			          data : JSON.stringify(formData), // post data || get data
			          contentType: "application/json",
			          cache: false,
			          headers:{"Authorization":"Bearer " + Cookies.get('token')},
			          success : function(result) {
			              if(result.id) {
			            	  $.ajax('./api/v1/store/details', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displaySettings(rs);});
			            	  var form = document.getElementById("teammemberEditForm");
			            	  form.classList.remove('was-validated');
			            	  $('#teammemberEditModal').modal('hide');
			            	  $('#teammemberEditFormMessageContainer').html("");
			              } else {
			            	  $('#teammemberEditFormMessageContainer').html("");
			            	  $('#teammemberEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Team Member<br />'+JSON.stringify(result)+'</div>');
			              }
			              $("#TMEditUpdateBtn").removeClass("disabled");
			          },
			          error: function(xhr, resp, text) {
			        	  $('#teammemberEditFormMessageContainer').html("");
			        	  $('#teammemberEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Team Member<br />'+text+'</div>');
			        	  $("#TMEditUpdateBtn").removeClass("disabled");
			          }
			      });
			});

			$(document).on("click", ".teammemberDelete", function(event){
				event.preventDefault();
				event.stopPropagation();
				var tmId = $(this).data("employee-id");
				var result = confirm("Are you sure you want to delete?");
				if (result) {
					$.ajax('./api/v1/store/teammemberDelete/'+tmId, {cache: false, type: 'PUT', headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
	            	  	$.ajax('./api/v1/store/details', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displaySettings(rs);});
						alert("Team Member deleted successfully.");
					});
				}
			});

			//-- User Account
			$(document).on("click", "#addUserAccountBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var inputForChangePassword = $("#txtUAChangePassword");

				inputForChangePassword.removeAttr("placeholder");
				inputForChangePassword.attr("required", "");
				inputForChangePassword.val("");

				$("#userAccountModalTitle").html("Add a User");
				$("#txtUAEditEmail").val("");
				$("#txtUAEditAdmin").val("0");
				$("#txtUAEditId").val("-1");
				$("#linkUAChangePassword").addClass("d-none");
				$("#divUAChangePassword").removeClass("d-none");
				$("#lblUAChangePassword").removeClass("d-none");
				$("#UAEditUpdateBtn").text("Add");
				$("#userAccountEditModal").modal("show");
			});

			$(document).on("click", "#userAccountEdit", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var uaId = $(this).data("id");
				var inputForChangePassword = $("#txtUAChangePassword");

				inputForChangePassword.attr("placeholder", "Change Password");
				inputForChangePassword.removeAttr("required");
				inputForChangePassword.val("");

				$("#userAccountModalTitle").html("Edit a User");
				$("#txtUAEditEmail").val($("#useraccount-email-"+uaId).val());
				$("#txtUAEditAdmin").val($("#useraccount-admin-"+uaId).val());
				$("#txtUAEditId").val(uaId);
				$("#linkUAChangePassword").removeClass("d-none")
				$("#divUAChangePassword").addClass("d-none");
				$("#lblUAChangePassword").addClass("d-none");
				$("#UAEditUpdateBtn").text("Update");
				$("#userAccountEditModal").modal("show");
			});

			$(document).on("click", "#linkUAChangePassword", function(e){
				e.preventDefault();
				e.stopPropagation();
				var changePasswordDiv = $("#divUAChangePassword");
				var changePasswordLink = $(this);
				if (changePasswordDiv.hasClass("d-none")) {
					changePasswordLink.text("Hide Change Password");
					changePasswordDiv.removeClass("d-none");
				} else {
					changePasswordLink.text("Change Password");
					changePasswordDiv.addClass("d-none");
					$("#txtUAChangePassword").val("");
				}
			});

			$(document).on("click", "#chkShowPassword", function() {
				var passwordInput = $("#txtUAChangePassword");
				if (passwordInput.attr("type") == "password") {
					passwordInput.attr("type", "text");
				} else {
					passwordInput.attr("type", "password");
				}
			});

			$(document).on("click", "#UAEditCancelBtn, button.close", function(event) {
				var form = document.getElementById("userAccountEditForm");
				form.classList.remove('was-validated');

				$("#linkUAChangePassword").text("Change Password");
				$("#divUAChangePassword").addClass("d-none");
				$("#txtUAChangePassword").val("");
				$('#userAccountEditModal').modal('hide');
				$('#userAccountEditFormMessageContainer').html("");
			});

			$(document).on("click", "#UAEditUpdateBtn", function(event) {
				var form = document.getElementById("userAccountEditForm");
				var error = false;
				if (form.checkValidity() === false) {
					error = true;
				}
				
				if(error) {
					form.classList.add('was-validated');
					event.preventDefault();
			        event.stopPropagation();
			        return;
				}
				var formData = utilitiesJS.serializeObject("userAccountEditForm","UAEditSerialize");
				var submitType = "PUT";
				if(formData.userid == -1) {
					submitType = "POST";
				}
				this.classList.add("disabled");
				$.ajax({
					url: './api/v1/store/users/',
					type : submitType,
					dataType : 'json',
					data : JSON.stringify(formData),
					contentType: "application/json",
					cache: false,
					headers:{"Authorization":"Bearer " + Cookies.get('token')},
					success : function(result) {
						if(result.id) {
							$.ajax('./api/v1/store/details', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displaySettings(rs);});
							var form = document.getElementById("userAccountEditForm");
							form.classList.remove('was-validated');
							$('#userAccountEditModal').modal('hide');
							$('#userAccountEditFormMessageContainer').html("");
						} else {
							$('#userAccountEditFormMessageContainer').html("");
							$('#userAccountEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving User Account<br />'+JSON.stringify(result)+'</div>');
						}
						$("#UAEditUpdateBtn").removeClass("disabled");
					},
					error: function(xhr, resp, text) {
						$('#userAccountEditFormMessageContainer').html("");
						$('#userAccountEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving User Account<br />'+text+'</div>');
						$("#UAEditUpdateBtn").removeClass("disabled");
					}
				});
			});

			$(document).on("click", "#userAccountDelete", function(event){
				event.preventDefault();
				event.stopPropagation();
				var uaId = $(this).data("id");
				var result = confirm("Are you sure you want to delete?");
				if (result) {
					$.ajax('./api/v1/store/userDelete/'+uaId, {cache: false, type: 'PUT', headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
	            	  	$.ajax('./api/v1/store/details', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displaySettings(rs);});
						alert("User deleted successfully.");
					});
				}
			});

			//--------//

			$(document).on("click", ".paymentmethodEdit", function(event) {
				event.preventDefault();
				event.stopPropagation();
				var pmId = $(this).data("paymentmethod-id");
				$("#paymentmethodModalTitle").html("Edit Payment Method");
				$("#txtPMEditName").val($("#paymentmethod-name-"+pmId).val());
				$("#txtPMEditPaymentType").val($("#paymentmethod-paymenttype-"+pmId).val());
				
				if ($("#paymentmethod-open-"+pmId).val() == 1) {
					$("#txtPMEditOpen").prop('checked', true);
				}
				else {
					$("#txtPMEditOpen").prop('checked', false);
				}
				if ($("#paymentmethod-default-"+pmId).val() == 1) {
					$("#txtPMEditDefault").prop('checked', true);
				}
				else {
					$("#txtPMEditDefault").prop('checked', false);
				}

				$("#txtPMEditId").val(pmId);
				$("#PMEditUpdateBtn").text("Update");
				$('#paymentmethodEditModal').modal('show');
			});

			$(document).on("click", "#PMEditCancelBtn, button.close", function(event) {
				var form = document.getElementById("paymentmethodEditForm");
				form.classList.remove('was-validated');
				$('#paymentmethodEditModal').modal('hide');
				$('#paymentmethodEditFormMessageContainer').html("");
			});


			$(document).on("click", "#addPaymentMethodBtn", function(event) {
				event.preventDefault();
				event.stopPropagation();
				$("#paymentmethodModalTitle").html("Add Payment Method");
				$("#txtPMEditName").val("");
				$("#txtPMEditPaymentType").val("1");
				$("#txtPMEditOpen").prop('checked', false);
				$("#txtPMEditDefault").prop('checked', false);
				$("#txtPMEditId").val("-1");
				$("#PMEditUpdateBtn").text("Add");
				$('#paymentmethodEditModal').modal('show');
			});

			$(document).on("click", "#PMEditUpdateBtn", function(event) {
				var form = document.getElementById("paymentmethodEditForm");
				var error = false;
				if (form.checkValidity() === false) {
					error = true;
				}
				
				if(error) {
					form.classList.add('was-validated');
					event.preventDefault();
			        event.stopPropagation();
			        return;
				}

				var isOpen = $("#txtPMEditOpen").prop('checked');
				var isDefault = $("#txtPMEditDefault").prop('checked');
				var formData = utilitiesJS.serializeObject("paymentmethodEditForm","PMEditSerialize");

				if(isOpen) {
					formData["open"] = 1;
				}
				if(isDefault) {
					formData["default"] = 1;
				} 
				
				var submitType = "PUT";
				if(formData.paymentmethod_id == -1) {
					submitType = "POST";
				}
				this.classList.add("disabled");
				$.ajax({
			          url: './api/v1/store/paymentmethod/', // url where to submit the request
			          type : submitType, // type of action POST || GET
			          dataType : 'json', // data type
			          data : JSON.stringify(formData), // post data || get data
			          contentType: "application/json",
			          cache: false,
			          headers:{"Authorization":"Bearer " + Cookies.get('token')},
			          success : function(result) {
			              if(result.id) {
			            	  $.ajax('./api/v1/store/paymentmethods', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displayPaymentMethods(rs);});
			            	  var form = document.getElementById("paymentmethodEditForm");
			            	  form.classList.remove('was-validated');
			            	  $('#paymentmethodEditModal').modal('hide');
			            	  $('#paymentmethodEditFormMessageContainer').html("");
			              } else {
			            	  $('#paymentmethodEditFormMessageContainer').html("");
			            	  $('#paymentmethodEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Payment Method<br />'+JSON.stringify(result)+'</div>');
			              }
			              $("#PMEditUpdateBtn").removeClass("disabled");
			          },
			          error: function(xhr, resp, text) {
			        	  $('#paymentmethodEditFormMessageContainer').html("");
			        	  $('#paymentmethodEditFormMessageContainer').html('<div class="alert alert-danger" role="alert">Error Saving Payment Method<br />'+text+'</div>');
			        	  $("#PMEditUpdateBtn").removeClass("disabled");
			          }
			      });
			});

			$(document).on("click", ".paymentmethodDelete", function(event){
				event.preventDefault();
				event.stopPropagation();
				var pmId = $(this).data("paymentmethod-id");
				var result = confirm("Are you sure you want to delete?");
				if (result) {
					$.ajax('./api/v1/store/paymentmethodDelete/'+pmId, {cache: false, type: 'PUT', headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {
						$.ajax('./api/v1/store/paymentmethods', {cache: false, headers:{"Authorization":"Bearer " + Cookies.get('token')}}).then(function (rs) {settingsJS.displayPaymentMethods(rs);});
						alert("Payment Method deleted successfully.");
					});
				}
			});

			$(document).on("click", "#txtSEditShowReferenceNo", function(event){
				var showReferenceNo = $(this).prop('checked');
				if (showReferenceNo) {
					$("#txtSEditReferenceNumber").prop("disabled", false);
				} else {
					$("#txtSEditReferenceNumber").val('').prop("disabled", true);
				}
			});
		},
		
		displaySettings: function(rs) {
			var store = "";
			var timezone = (!rs.store.hasOwnProperty("timezone"))? 'America/New_York' : rs.store.timezone;
			if(!rs.store.hasOwnProperty('address2') || !rs.store.address2) {rs.store.address2 = "";}
			if(!rs.store.hasOwnProperty('fax') || !rs.store.fax) {rs.store.fax = "";}
			store += '<h3>Store Details</h3>';
			if (Cookies.get('fluxur') == 1) {
				store += '<button class="btn btn-primary float-right mb-3" id="storeEdit" type="button"><i class="fa fa-fw fa-pencil"></i> Edit Store Details</button>';
			}
			store += '<table class="table table-striped">';
			store += '	<thead>';
			store += '		<tr><th>Setting</th><th>Value</th></tr>';
			store += '	</thead>';
			store += '	<tbody>';
			store += '		<tr><td>Identifier</td><td id="store-identifier">'+rs.store.identifier+'</td></tr>';
			store += '		<tr><td>Name</td><td id="store-name">'+rs.store.name+'</td></tr>';
			store += '		<tr><td>Address 1</td><td id="store-address1">'+rs.store.address1+'</td></tr>';
			store += '		<tr><td>Address 2</td><td id="store-address2">'+rs.store.address2+'</td></tr>';
			store += '		<tr><td>City</td><td id="store-city">'+rs.store.city+'</td></tr>';
			store += '		<tr><td>State</td><td id="store-state">'+rs.store.state+'</td></tr>';
			store += '		<tr><td>Zip</td><td id="store-zip">'+rs.store.zip+'</td></tr>';
			store += '		<tr><td>Phone</td><td id="store-phone">'+rs.store.phone+'</td></tr>';
			store += '		<tr><td>Fax</td><td id="store-fax">'+rs.store.fax+'</td></tr>';
			store += '		<tr><td>Email</td><td id="store-email">'+rs.store.email+'</td></tr>';
			store += '		<tr><td>Logo</td><td><img src="logo.jpg?t='+new Date().getTime()+'" style="height:50px;" /></td></tr>';
			store += '		<tr><td>Labor Rate</td><td id="store-laborrate">'+parseFloat(rs.store.laborrate).toFixed(2)+'</td></tr>';
			store += '		<tr><td>Timezone</td><td id="store-timezone">'+timezone+'</td></tr>';
			store += '		<tr><td>Show Reference #</td><td id="show-reference-no">'+((rs.store.show_reference_no == 1)? 'Yes': 'No')+'</td></tr>';
			store += '		<tr><td>Reference Number</td><td id="reference-number">'+((rs.store.show_reference_no == 1)? rs.store.reference_number: '')+'</td></tr>';
			store += '		<tr><td>Email Missing Reminder</td><td id="email-reminder">'+((rs.store.email_reminder == 1)? 'Enabled': 'Disabled')+'</td></tr>';
			store += '	</tbody>';
			store += '</table>';
			
			store += '<h3>Tax Rates</h3>';
			if (Cookies.get('fluxur') == 1) {
				store += '<button class="btn btn-primary float-right mb-3" id="addTaxRateBtn" type="button"><i class="fa fa-fw fa-plus"></i> Add Tax Rate</button>';
			}
			store += '<table class="table table-striped">';
			store += '	<thead>';
			if (Cookies.get('fluxur') == 1) {
				store += '	<tr><th>Name</th><th>Rate</th><th>Category</th><th>Exemption Amount</th><th>Edit</th></tr>';
			}
			else {
				store += '	<tr><th>Name</th><th>Rate</th><th>Category</th><th>Exemption Amount</th></tr>';
			}
			store += '	</thead>';
			store += '	<tbody>';
			$.each(rs.rates, function(i, m) {
				if (Cookies.get('fluxur') == 1) {
					store += '	<tr>'+
									'<td>'+m.name+'</td>'+
									'<td>'+m.rate+'</td>'+
									'<td>'+m.category+'</td>'+
									'<td>'+m.exemption+'</td>'+
									'<td>'+
										'<div class="btn-group" role="group">'+
											'<button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle form-control" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Edit</button>'+
											'<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">'+
												'<a data-taxrate-id="'+m.id+'" class="dropdown-item taxrateEdit" href="#">Edit</a>'+
												'<a data-taxrate-id="'+m.id+'" class="dropdown-item taxrateDelete" href="#">Delete</a>'+
											'</div>'+
										'</div>'+
									'</td>'+	
									'<input type="hidden" id="taxrate-name-'+m.id+'" value="'+m.name+'" />'+
									'<input type="hidden" id="taxrate-rate-'+m.id+'" value="'+m.rate+'" />'+
									'<input type="hidden" id="taxrate-category-'+m.id+'" value="'+m.category+'" />'+
									'<input type="hidden" id="taxrate-exemption-'+m.id+'" value="'+m.exemption+'" />'+
								'</tr>';
				}
				else {
					store += '	<tr><td>'+m.name+'</td><td>'+m.rate+'</td><td>'+m.category+'</td><td>'+m.exemption+'</td></tr>';
				}
			});
			store += '	</tbody>';
			store += '</table>';
			
			store += '<h3>Team Members</h3>';
			if (Cookies.get('fluxur') == 1) {
				store += '<button class="btn btn-primary float-right mb-3" id="addTeamMemberBtn" type="button"><i class="fa fa-fw fa-plus"></i> Add Team Member</button>';
			}
			store += '<table class="table table-striped">';
			store += '	<thead>';
			if (Cookies.get('fluxur') == 1) {
				store += '	<tr><th>Name</th><th>Role</th><th>Edit</th></tr>';
			}
			else {
				store += '	<tr><th>Name</th><th>Role</th></tr>';
			}
			store += '	</thead>';
			store += '	<tbody>';
			$.each(rs.team, function(i, m) {
				if (Cookies.get('fluxur') == 1) {
					store += '	<tr>'+
									'<td>'+m.name+'</td>'+
									'<td>'+m.role_name+'</td>'+
									'<td>'+
										'<div class="btn-group" role="group">'+
											'<button id="btnGroupDrop2" type="button" class="btn btn-secondary dropdown-toggle form-control" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Edit</button>'+
											'<div class="dropdown-menu" aria-labelledby="btnGroupDrop2">'+
												'<a data-employee-id="'+m.employee_id+'" class="dropdown-item teammemberEdit" href="#">Edit</a>'+
												'<a data-employee-id="'+m.employee_id+'" class="dropdown-item teammemberDelete" href="#">Delete</a>'+
											'</div>'+
										'</div>'+
									'</td>'+	
									'<input type="hidden" id="teammember-name-'+m.employee_id+'" value="'+m.name+'" />'+
									'<input type="hidden" id="teammember-role-'+m.employee_id+'" value="'+m.role_id+'" />'+
								'</tr>';
				}
				else {
					store += '	<tr><td>'+m.name+'</td><td>'+m.role_name+'</td></tr>';
				}
			});
			store += '	</tbody>';
			store += '</table>';

			// init role dropdown
			$('#txtTMEditRole').html("");
			$.each(rs.roles, function(i, r) {
				$('#txtTMEditRole').append('<option value="' + r.id +'">' + r.name + '</option>');
			});


			//-- user accounts
			store += '<h3>User Accounts</h3>';
			if (Cookies.get('fluxur') == 1) {
				store += '<button class="btn btn-primary float-right mb-3" id="addUserAccountBtn" type="button"><i class="fa fa-fw fa-plus"></i> Add a User</button>';
			}
			store += '<table class="table table-striped">';
			store += '	<thead>';
			if (Cookies.get('fluxur') == 1) {
				store += '	<tr><th>Email</th><th>Admin</th><th>Edit</th></tr>';
			}
			else {
				store += '	<tr><th>Email</th><th>Admin</th></tr>';
			}
			store += '	</thead>';
			store += '	<tbody>';
			$.each(rs.user, function(i, u) {
				if (Cookies.get('fluxur') == 1) {
					var ifAdmin = (u.isadmin == 1) ? 'Yes' : 'No';
					store += '	<tr>'+
									'<td>'+u.email+'</td>'+
									'<td>'+ifAdmin+'</td>'+
									'<td>'+
										'<div class="btn-group" role="group">'+
											'<button id="btnGroupDrop2" type="button" class="btn btn-secondary dropdown-toggle form-control" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Edit</button>'+
											'<div class="dropdown-menu" aria-labelledby="btnGroupDrop2">'+
												'<a data-id="'+u.id+'" class="dropdown-item" id="userAccountEdit" href="#">Edit</a>'+
												'<a data-id="'+u.id+'" class="dropdown-item" id="userAccountDelete" href="#">Delete</a>'+
											'</div>'+
										'</div>'+
									'</td>'+
									'<input type="hidden" id="useraccount-email-'+u.id+'" value="'+u.email+'" />'+	
									'<input type="hidden" id="useraccount-admin-'+u.id+'" value="'+u.isadmin+'" />'+
								'</tr>';
				}
				else {
					store += '	<tr><td>'+u.email+'</td><td>'+u.isadmin+'</td></tr>';
				}
			});
			store += '	</tbody>';
			store += '</table>';
			
			$("#settingsContainer").html(store);
		},
		
		displayPaymentMethods: function(rs) {
			var pays = "";
			pays += '<h3>Payment Methods</h3>';
			if (Cookies.get('fluxur') == 1) {
				pays += '<button class="btn btn-primary float-right mb-3" id="addPaymentMethodBtn" type="button"><i class="fa fa-fw fa-plus"></i> Add Payment Method</button>';
			}
			pays += '<table class="table table-striped">';
			pays += '	<thead>';
			if (Cookies.get('fluxur') == 1) {
				pays += '	<tr><th>Name</th><th>Payment Type</th><th>Open</th><th>Default</th><th>Edit</th></tr>';
			}
			else {
				pays += '	<tr><th>Name</th><th>Payment Type</th><th>Open</th><th>Default</th></tr>';
			}
			pays += '	</thead>';
			pays += '	<tbody>';
			$.each(rs.paymentmethods, function(i, m) {
				if (Cookies.get('fluxur') == 1) {
					pays += '	<tr>'+
									'<td>'+m.name+'</td>'+
									'<td>'+m.paymenttype_name+'</td>'+
									'<td>'+((m.open == 1)? "Open" : "")+'</td>'+
									'<td>'+((m.default == 1)? 'Default' : "")+'</td>'+
									'<td>'+
										'<div class="btn-group" role="group">'+
											'<button id="btnGroupDrop3" type="button" class="btn btn-secondary dropdown-toggle form-control" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Edit</button>'+
											'<div class="dropdown-menu" aria-labelledby="btnGroupDrop3">'+
												'<a data-paymentmethod-id="'+m.id+'" class="dropdown-item paymentmethodEdit" href="#">Edit</a>'+
												'<a data-paymentmethod-id="'+m.id+'" class="dropdown-item paymentmethodDelete" href="#">Delete</a>'+
											'</div>'+
										'</div>'+
									'</td>'+	
									'<input type="hidden" id="paymentmethod-name-'+m.id+'" value="'+m.name+'" />'+
									'<input type="hidden" id="paymentmethod-paymenttype-'+m.id+'" value="'+m.paymenttype_id+'" />'+
									'<input type="hidden" id="paymentmethod-open-'+m.id+'" value="'+m.open+'" />'+
									'<input type="hidden" id="paymentmethod-default-'+m.id+'" value="'+m.default+'" />'+
								'</tr>';
				}
				else {
					pays += '	<tr><td>'+m.name+'</td><td>'+m.paymenttype_name+'</td><td>'+((m.open == 1)? "Open" : "")+'</td><td>'+((m.default == 1)? 'Default' : "")+'</td></tr>';
				}
			});
			pays += '	</tbody>';
			pays += '</table>';

			// init payment type dropdown
			$('#txtPMEditPaymentType').html("");
			$.each(rs.paymenttypes, function(i, p) {
				$('#txtPMEditPaymentType').append('<option value="' + p.id +'">' + p.name + '</option>');
			});
			
			$("#paymentMethodContainer").html(pays);
		}
};