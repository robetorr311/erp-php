    <footer class="sticky-footer d-print-none">
      <div class="container">
        <div class="text-center">
          <small>Copyright &copy; 2018 - 2021 FluxShopManager.com v<?php echo FLUX_VERSION; ?></small>
        </div>
      </div>
    </footer>
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded d-print-none" href="#page-top">
      <i class="fa fa-angle-up"></i>
    </a>
    
	<div class="modal fade" id="reportBugModal" tabindex="-1" role="dialog" aria-labelledby="reportBugModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Report Bug</h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-row">
						<div class="form-group col-md-12">
							Create a new support ticket and include the following details:
							<ul><li><strong>ID</strong> (of customer, work order, contact, or vehicle)</li>
							<li><strong>Description of Problem and Result</strong></li>
							<li><strong>Expected Result</strong></li>
							<li><strong>Steps to Reproduce the Issue</strong></li>
							<li><strong>Console View:</strong>&nbsp;<span id="consoleViewSpan"></span></li>
							</ul>
						</div>
					</div>
					<div class="container-fluid">
						<div class="form-group col-md-12">
							<a class="btn btn-primary w-100" id="reportBugCreateBtn" target="_blank" href="https://fluxshopmanager.freshdesk.com/support/tickets/new">Create Ticket</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Log In</h5>
				</div>
				<div class="modal-body">
					<div id="loginMessageContainer"></div>
			        <form id="loginFrm">
			          <div class="form-group">
			            <label for="exampleInputEmail1">Email address</label>
			            <input class="form-control" id="inputEmail1" name="username" type="email" aria-describedby="emailHelp" placeholder="Enter email">
			          </div>
			          <div class="form-group">
			            <label for="exampleInputPassword1">Password</label>
			            <input class="form-control" id="inputPassword1" name="password" type="password" placeholder="Password">
			          </div>
			          <!-- <div class="form-group">
			            <div class="form-check">
			              <label class="form-check-label">
			                <input class="form-check-input" type="checkbox"> Remember Password</label>
			            </div>
			          </div> -->
			          <a class="btn btn-primary btn-block" href="#" id="loginBtn">Login</a>
			        </form>
				</div>
			</div>
		</div>
	</div>
		
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="vendor/datatables/datatables.min.js"></script>
    <script src="vendor/bootpag/jquery.bootpag.min.js"></script>
    <script src="vendor/datepicker/bootstrap-datepicker.min.js"></script>
    <script src="vendor/easyautocomplete/jquery.easy-autocomplete.min.js"></script>
    <script src="vendor/select2/js/select2.min.js"></script>
    <script src="vendor/fullcalendar/moment.min.js"></script>
    <script src="vendor/fullcalendar/fullcalendar.min.js"></script>
    <script src="js/fluxshopmanager.min.js?v=<?php echo FLUX_VERSION; ?>"></script>
    <script src="js/reportbug.js?v=<?php echo FLUX_VERSION; ?>"></script>
    <script src="js/utilities.js?v=<?php echo FLUX_VERSION; ?>"></script>
    <script src="js/js.cookie.js?v=<?php echo FLUX_VERSION; ?>"></script>
    <script src="js/user.js?v=<?php echo FLUX_VERSION; ?>"></script>