<!DOCTYPE html>
<html lang="en">

<head>
  <title>Settings | Flux Shop Manager</title>
  <?php include "includes/head.php"; ?>
</head>

<body class="fixed-nav sticky-footer bg-light" id="page-top">
  <?php include "includes/navigation.php"; ?>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb d-print-none">
      	<li class="breadcrumb-item">
          <a href="/">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">Settings</li>
      </ol>
      <div class="row d-print-none">
        <div class="col-12">
          <h1>Settings</h1>
        </div>
      </div>
      
      <div class="row d-print-none" id="inventoryList">
        <div class="col">
			<div class="card mb-3">
			  <div class="card-header">
			    Settings
			  </div>
			  <div class="card-body">
			    <div class="card-text">
					<div class="row">
						<div class="form-group col-md-12">
							<div id="settingsContainer"></div>
							<div id="paymentMethodContainer"></div>
						</div>
					</div>
				</div>
			  </div>
			</div>
        </div>
      </div>
            
	 </div>
    </div>
    
    <div class="modal fade" id="storeEditModal" tabindex="-1" role="dialog" aria-labelledby="storeEditModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="storeModalTitle">Edit Store Details</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="storeEditFormMessageContainer"></div>
                    <form id="storeEditForm" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="txtSEditIdentifier">Identifier</label> 
                                <input type="text" class="form-control SEditSerialize" name="identifier" id="txtSEditIdentifier" required="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="txtSEditName">Name</label> 
                                <input type="text" class="form-control SEditSerialize" name="name" id="txtSEditName" required="">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="txtSEditAddress1">Address 1</label> 
                                <input type="text" class="form-control SEditSerialize" name="address1" id="txtSEditAddress1" required="">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="txtSEditAddress2">Address 2</label> 
                                <input type="text" class="form-control SEditSerialize" name="address2" id="txtSEditAddress2">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="txtSEditCity">City</label> 
                                <input type="text" class="form-control SEditSerialize" name="city" id="txtSEditCity" required="">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="txtSEditState">State</label> 
                                <input type="text" class="form-control SEditSerialize" name="state" id="txtSEditState" required="">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="txtSEditZip">Zip</label> 
                                <input type="text" class="form-control SEditSerialize" name="zip" id="txtSEditZip" required="">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="txtSEditPhone">Phone</label> 
                                <input type="text" class="form-control SEditSerialize" name="phone" id="txtSEditPhone" required="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="txtSEditFax">Fax</label> 
                                <input type="text" class="form-control SEditSerialize" name="fax" id="txtSEditFax">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="txtSEditEmail">Email</label> 
                                <input type="text" class="form-control SEditSerialize" name="email" id="txtSEditEmail" required="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="txtSEditLaborRate">Labor Rate</label> 
                                <div class="input-group mb-2 mb-sm-0">
                                    <div class="input-group-addon d-print-none">$</div>
                                    <input type="text" class="form-control SEditSerialize numeric_only" name="laborrate" id="txtSEditLaborRate" required="">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="txtSEditTimezone">Timezone</label> 
                                <select name="timezone" id="txtSEditTimezone" class="form-control SEditSerialize">
                                    <option value="America/New_York">America/New_York</option>
                                    <option value="America/Chicago">America/Chicago</option>
                                    <option value="America/Denver">America/Denver</option>
                                    <option value="America/Phoenix">America/Phoenix</option>
                                    <option value="America/Los_Angeles">America/Los_Angeles</option>
                                    <option value="America/Anchorage">America/Anchorage</option>
                                    <option value="America/Adak">America/Adak</option>
                                    <option value="Pacific/Honolulu">Pacific/Honolulu</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" name="show_reference_no" class="custom-control-input SEditSerialize" id="txtSEditShowReferenceNo" value="0">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">Reference Number</span>
                                </label>
                                <select name="reference_number" id="txtSEditReferenceNumber" class="form-control SEditSerialize" required disabled>
                                    <option value="" selected>Select Reference Number</option>
                                    <option value="{date}-{id}-{margin}">{date}-{id}-{margin}</option>
                                    <option value="{date}-{margin}-{id}">{date}-{margin}-{id}</option>
                                    <option value="{margin}-{date}-{id}">{margin}-{date}-{id}</option>
                                    <option value="{margin}-{id}-{date}">{margin}-{id}-{date}</option>
                                    <option value="{id}-{date}-{margin}">{id}-{date}-{margin}</option>
                                    <option value="{id}-{margin}-{date}">{id}-{margin}-{date}</option>
                                    <option value="{id}-{date}-{tax}">{id}-{date}-{tax}</option>
                                    <option value="{id}-{tax}-{date}">{id}-{tax}-{date}</option>
                                    <option value="{date}-{id}-{tax}">{date}-{id}-{tax}</option>
                                    <option value="{date}-{tax}-{id}">{date}-{tax}-{id}</option>
                                    <option value="{tax}-{date}-{id}">{tax}-{date}-{id}</option>
                                    <option value="{tax}-{id}-{date}">{tax}-{id}-{date}</option>
                                    <option value="{total}-{id}-{date}">{total}-{id}-{date}</option>
                                    <option value="{total}-{date}-{id}">{total}-{date}-{id}</option>
                                    <option value="{date}-{total}-{id}">{date}-{total}-{id}</option>
                                    <option value="{date}-{id}-{total}">{date}-{id}-{total}</option>
                                    <option value="{id}-{date}-{total}">{id}-{date}-{total}</option>
                                    <option value="{id}-{total}-{date}">{id}-{total}-{date}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="txtSEditUploadLogo">Change Logo</label>
                                <input type="file" name="uploadlogo" class="form-control-file" id="txtSEditUploadLogo" accept=".jpg">
                            </div>
                            <div class="form-group col-md-4">
                                <img src="logo.jpg" id="txtSEditLogo" style="height:50px;" />
                            </div>
                        </div>
                        <div class="form-row">	
                            <div class="form-group col-md-12">	
                                <label for="txtSEditEmailReminder">Email Missing Reminder</label><br/>
                                <input type="radio" id="txtSEditEmailReminder-1" name="email_reminder" value="1"> Enabled<br/>
                                <input type="radio" id="txtSEditEmailReminder-0" name="email_reminder" value="0"> Disabled
                            </div>	
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="SEditCancelBtn" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="SEditUpdateBtn">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="taxrateEditModal" tabindex="-1" role="dialog" aria-labelledby="taxrateEditModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taxrateModalTitle">Add Tax Rate</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="taxrateEditFormMessageContainer"></div>
                    <form id="taxrateEditForm">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="txtTREditName">Name</label> 
                                <input type="text" class="form-control TREditSerialize" name="name" id="txtTREditName" required="">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="txtTREditRate">Rate</label>
                                <div class="input-group mb-2 mb-sm-0">
                                    <div class="input-group-addon d-print-none">%</div>
                                    <input type="text" class="form-control TREditSerialize numeric_only" name="rate" id="txtTREditRate" required="">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="txtTREditCategory">Category</label> 
                                <select name="category" id="txtTREditCategory" class="form-control TREditSerialize">
                                    <option value="part">part</option>
                                    <option value="labor">labor</option>
                                    <option value="fee">fee</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="txtTREditExemptionAmount">Exemption Amount</label> 
                                <div class="input-group mb-2 mb-sm-0">
                                    <div class="input-group-addon d-print-none">%</div>
                                    <input type="text" class="form-control TREditSerialize numeric_only" name="exemption" id="txtTREditExemptionAmount" required="">
                                </div>
                                <input type="hidden" class="TREditSerialize" name="taxrate_id" id="txtTREditId">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="TREditCancelBtn" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="TREditUpdateBtn">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="teammemberEditModal" tabindex="-1" role="dialog" aria-labelledby="teammemberEditModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="teammemberModalTitle">Add Team Member</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="teammemberEditFormMessageContainer"></div>
                    <form id="teammemberEditForm">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="txtTMEditName">Name</label> 
                                <input type="text" class="form-control TMEditSerialize" name="name" id="txtTMEditName" required="">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="txtTMEditRole">Role</label> 
                                <select name="role" id="txtTMEditRole" class="form-control TMEditSerialize">
                                    
                                </select>
                                <input type="hidden" class="TMEditSerialize" name="employee_id" id="txtTMEditId">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="TMEditCancelBtn" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="TMEditUpdateBtn">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userAccountEditModal" tabindex="-1" role="dialog" aria-labelledby="userAccountEditModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userAccountModalTitle">Edit a User</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="userAccountEditFormMessageContainer"></div>
                    <form id="userAccountEditForm">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="txtUAEditEmail">Email</label> 
                                <input type="email" class="form-control UAEditSerialize" name="email" id="txtUAEditEmail" required="">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="txtUAEditAdmin">Admin</label> 
                                <select name="ifadmin" id="txtUAEditAdmin" class="form-control UAEditSerialize">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>   
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <a href="#" id="linkUAChangePassword">Change Password</a>
                            </div>
                        </div>
                        <div class="form-row d-none" id="divUAChangePassword">
                            <div class="form-group col-md-12">
                                <label class="d-none" for="txtUAChangePassword" id="lblUAChangePassword">Password</label>
                                <input type="password" class="form-control UAEditSerialize" name="password" id="txtUAChangePassword" placeholder="Change Password">
                                <input type="checkbox" name="chkShowPassword" id="chkShowPassword">  Show Password
                                <input type="hidden" class="UAEditSerialize" name="userid" id="txtUAEditId">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="UAEditCancelBtn" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="UAEditUpdateBtn">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentmethodEditModal" tabindex="-1" role="dialog" aria-labelledby="paymentmethodEditModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentmethodModalTitle">Add Payment Method</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="paymentmethodEditFormMessageContainer"></div>
                    <form id="paymentmethodEditForm">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="txtPMEditName">Name</label> 
                                <input type="text" class="form-control PMEditSerialize" name="name" id="txtPMEditName" required="">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="txtPMEditPaymentType">Payment Type</label> 
                                <select name="paymenttype" id="txtPMEditPaymentType" class="form-control PMEditSerialize">
                                    
                                </select>
                                <input type="hidden" class="PMEditSerialize" name="paymentmethod_id" id="txtPMEditId">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="custom-control custom-checkbox">
                                  <input type="checkbox" name="open" class="custom-control-input PMEditSerialize" id="txtPMEditOpen" value="0">
                                  <span class="custom-control-indicator"></span>
                                  <span class="custom-control-description">Open</span>
                                </label>
                            </div>
                             <div class="form-group col-md-6">
                                <label class="custom-control custom-checkbox">
                                  <input type="checkbox" name="default" class="custom-control-input PMEditSerialize" id="txtPMEditDefault" value="0">
                                  <span class="custom-control-indicator"></span>
                                  <span class="custom-control-description">Default</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="PMEditCancelBtn" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="PMEditUpdateBtn">Update</button>
                </div>
            </div>
        </div>
    </div>

	<?php include "includes/footer.php"; ?>
    <script src="js/settings.js?v=<?php echo FLUX_VERSION; ?>"></script>
    <script>
    (function($) {
    	utilitiesJS.sessionCheck();
    	settingsJS.init();
    })(jQuery);
    </script>
</body>

</html>

