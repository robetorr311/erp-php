<!DOCTYPE html>
<html lang="en">

<head>
	<title>Inspection Report | Flux Shop Manager</title>
	<?php include "includes/head.php"; ?>
</head>

<body class="fixed-nav sticky-footer bg-light" id="page-top">
	<?php include "includes/navigation.php"; ?>

	<div class="content-wrapper">
		<form id="inspectionReportForm">
			<div class="container-fluid">
				<!-- Breadcrumbs-->
				<ol class="breadcrumb d-print-none">
					<li class="breadcrumb-item">
						<a href="/">Dashboard</a>
					</li>
					<li class="breadcrumb-item" id="workOrderBc">
					</li>
					<li class="breadcrumb-item active">Inspection Report</li>
				</ol>

				<div class="row d-print-none">
					<div id="inspectionReportMessageContainer" class="col-12"></div>
				</div>
				<div class="row d-print-none">
					<div class="col-6">
						<h1>Inspection Report</h1>
					</div>
					<div class="col-6">
						<button class="btn btn-primary float-right" id="saveReport" type="button">Save Report</button>
					</div>
				</div>

				<div id="accordion" class="accordion">
					<!-- Start Vehicle Details .row -->
					<div class="row d-print-none">
						<div class="col">
							<div class="card mb-3">
								<div class="card-header collapsed" id="headerOne" data-toggle="collapse" data-target="#vehicleDetails" aria-expanded="true" aria-controls="vehicleDetails">
									Vehicle Details <span class="fa fa-chevron-up float-right"></span>
								</div>
								<div class="card-body collapse show" id="vehicleDetails" aria-labelledby="headerOne" data-parent="#accordion">
									<div class="card-text">
										<div class="row">
											<div class="form-group col-md-4">
												<div class="row">
													<div class="form-group col-md-12">
														<label for="license">License Plate</label> 
														<input name="license" id="license" type="text" class="form-control" readonly>
														<input name="inspectionreport_id" id="inspectionreport_id" type="hidden" value="-1">
													</div>
												</div>

												<div class="row">
													<div class="form-group col-md-12">
														<label for="fleetnum">Fleet Number</label>
														<input name="fleetnum" id="fleetnum" type="text" class="form-control" readonly>
													</div>
												</div>
											</div><!-- end .col-md-4 -->

											<div class="col-md-4">
												<div class="row">
													<div class="form-group col-md-12">
														<label for="vin">Vin</label> 
														<input name="vin" id="vin" type="text" class="form-control" readonly>
													</div>
												</div>

												<div class="row">
													<div class="form-group col-md-12">
														<label for="trimengine">Trim/Engine</label>
														<input name="trimengine" id="trimengine" type="text" class="form-control" readonly>
													</div>
												</div>
											</div><!-- end .col-md-4 -->

											<div class="col-md-4">
												<div class="row">
													<div class="form-group col-md-12">
														<label for="mileage">Mileage</label> 
														<input name="mileage" id="mileage" type="text" class="form-control" readonly>
													</div>
												</div>
											</div><!-- end .col-md-4 -->

										</div><!-- end .row-->
									</div>
								</div>
							</div><!-- end of .card -->
						</div><!-- end of .col -->
					</div><!-- end of Vehicle Details .row -->

					<!-- Start Preliminary Inspection .row -->
					<div class="row d-print-none">
						<div class="col">
							<div class="card mb-3" >
								<div class="card-header" id="headerTwo" data-toggle="collapse" data-target="#preliminaryInspection" aria-expanded="false" aria-controls="preliminaryInspection">
									Preliminary Inspection <span class="fa fa-chevron-down float-right"></span>
								</div>
								<div class="card-body collapse" id="preliminaryInspection" aria-labelledby="headerTwo" data-parent="#accordion">
									<div class="card-text">
										<div class="row">
											<div class="form-group col-md-12">
												<div class="row">
													<div class="form-group col-md-6">
														<label for="extras">(EX. Dents, Scratches, Missing/Damaged Hubcaps)</label>
														<select name="extrasSug" id="extrasSug" class="form-control preliminaryInspectionSerialize">
															<option value="yes">Yes</option>
															<option value="no">No</option>
														</select>
													</div>
													<div class="form-group col-md-6">
														<label for="extras">&nbsp;</label>
														<input name="extras" id="extras" type="text" class="form-control preliminaryInspectionSerialize">
													</div>
												</div>
											</div><!-- end .col-md-12 -->

											<div class="col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="headlights">Headlights</label>
														<select name="headlightsSug" id="headlightsSug" class="form-control preliminaryInspectionSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="headlights">&nbsp;</label>
														<input name="headlights" id="headlights" type="text" class="form-control preliminaryInspectionSerialize">
													</div>
												</div>
											</div><!-- end .col-md-6 -->

											<div class="col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="otherlighting">Other Lighting</label>
														<select name="otherlightingSug" id="otherlightingSug" class="form-control preliminaryInspectionSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="otherlighting">&nbsp;</label>
														<input name="otherlighting" id="otherlighting" type="text" class="form-control preliminaryInspectionSerialize">
													</div>
												</div>
											</div><!-- end .col-md-6 -->

											<div class="col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="wipers">Wipers</label>
														<select name="wipersSug" id="wipersSug" class="form-control preliminaryInspectionSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<optionPreliminary Inspection value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="wipers">&nbsp;</label>
														<input name="wipers" id="wipers" type="text" class="form-control preliminaryInspectionSerialize">
													</div>
												</div>
											</div><!-- end .col-md-6 -->

										</div><!-- end .row-->
									</div>
								</div>
							</div><!-- end of .card -->
						</div><!-- end of .col -->
					</div><!-- end of Preliminary Inspection .row -->

					<!-- Start Cluster .row -->
					<div class="row d-print-none">
						<div class="col">
							<div class="card mb-3">
								<div class="card-header" id="headerEight" data-toggle="collapse" data-target="#cluster" aria-expanded="false" aria-controls="cluster">
									Cluster <span class="fa fa-chevron-down float-right"></span>
								</div>
								<div class="card-body collapse" id="cluster" aria-labelledby="headerEight" data-parent="#accordion">
									<div class="card-text">
										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="engineLight">Engine Light</label>
														<select name="engineLightSug" id="engineLightSug" class="form-control clusterSerialize">
															<option value="yes">Yes</option>
															<option value="no">No</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="engineLight">&nbsp;</label>
														<input name="engineLight" id="engineLight" type="text" class="form-control clusterSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="tmpsLight">TPMS Light</label>
														<select name="tmpsLightSug" id="tmpsLightSug" class="form-control clusterSerialize">
															<option value="yes">Yes</option>
															<option value="no">No</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="tmpsLight">&nbsp;</label>
														<input name="tmpsLight" id="tmpsLight" type="text" class="form-control clusterSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="absLight">ABS Light</label>
														<select name="absLightSug" id="absLightSug" class="form-control clusterSerialize">
															<option value="yes">Yes</option>
															<option value="no">No</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="absLight">&nbsp;</label>
														<input name="absLight" id="absLight" type="text" class="form-control clusterSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

									</div>
								</div>
							</div><!-- end of .card -->
						</div><!-- end of .col -->
					</div><!-- end of Cluster .row -->

					<!-- Start Under Hood .row -->
					<div class="row d-print-none">
						<div class="col">
							<div class="card mb-3">
								<div class="card-header" id="headerThree" data-toggle="collapse" data-target="#underHood" aria-expanded="false" aria-controls="underHood">
									Under Hood <span class="fa fa-chevron-down float-right"></span>
								</div>
								<div class="card-body collapse" id="underHood" aria-labelledby="headerThree" data-parent="#accordion">
									<div class="card-text">
										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="washerfluid">Washer Fluid</label>
														<select name="washerfluidoksug" id="washerfluidoksug" class="form-control underHoodSerialize">
															<option value="yes">Yes</option>
															<option value="no">No</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="washerfluid">&nbsp;</label>
														<input name="washerfluid" id="washerfluid" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="engineoil">Engine Oil</label>
														<select name="engineoilsug" id="engineoilsug" class="form-control underHoodSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="engineoil">&nbsp;</label>
														<input name="engineoil" id="engineoil" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="coolantcond">Coolant Cond.</label>
														<select name="coolantcondsug" id="coolantcondsug" class="form-control underHoodSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="coolantcond">&nbsp;</label>
														<input name="coolantcond" id="coolantcond" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="transfluid">Trans. Fluid</label>
														<select name="transfluidsug" id="transfluidsug" class="form-control underHoodSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="transfluid">&nbsp;</label>
														<input name="transfluid" id="transfluid" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="steeringfluid">Steering Fluid</label>
														<select name="steeringfluidsug" id="steeringfluidsug" class="form-control underHoodSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="steeringfluid">&nbsp;</label>
														<input name="steeringfluid" id="steeringfluid" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="airfilters">Air Filters</label>
														<select name="airfilterssug" id="airfilterssug" class="form-control underHoodSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="airfilters">&nbsp;</label>
														<input name="airfilters" id="airfilters" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="otherfilters">Other Filters</label>
														<select name="otherfilterssug" id="otherfilterssug" class="form-control underHoodSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="otherfilters">&nbsp;</label>
														<input name="otherfilters" id="otherfilters" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="coolanthoses">Coolant Hoses</label>
														<select name="coolanthosessug" id="coolanthosessug" class="form-control underHoodSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="coolanthoses">&nbsp;</label>
														<input name="coolanthoses" id="coolanthoses" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="belts">Belts</label>
														<select name="beltssug" id="beltssug" class="form-control underHoodSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="belts">&nbsp;</label>
														<input name="belts" id="belts" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="batterycables">Battery /Cables</label>
														<select name="batterycablessug" id="batterycablessug" class="form-control underHoodSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="batterycables">&nbsp;</label>
														<input name="batterycables" id="batterycables" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="alternator">Alternator</label>
														<select name="alternatorsug" id="alternatorsug" class="form-control underHoodSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="alternator">&nbsp;</label>
														<input name="alternator" id="alternator" type="text" class="form-control underHoodSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

									</div>
								</div>
							</div><!-- end of .card -->
						</div><!-- end of .col -->
					</div><!-- end of Under Hood .row -->

					<!-- Start UNDERCAR .row -->
					<div class="row d-print-none">
						<div class="col">
							<div class="card mb-3">
								<div class="card-header" id="headerFour" data-toggle="collapse" data-target="#underCar" aria-expanded="false" aria-controls="underCar">
									Under Car <span class="fa fa-chevron-down float-right"></span>
								</div>
								<div class="card-body collapse" id="underCar" aria-labelledby="headerFour" data-parent="#accordion">
									<div class="card-text">
										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="driveLine">Drive Line</label>
														<select name="driveLineSug" id="driveLineSug" class="form-control underCarSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="driveLine">&nbsp;</label>
														<input name="driveLine" id="driveLine" type="text" class="form-control underCarSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="driveShaft">U‐Joint /Driveshaft</label>
														<select name="driveShaftSug" id="driveShaftSug" class="form-control underCarSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="driveShaft">&nbsp;</label>
														<input name="driveShaft" id="driveShaft" type="text" class="form-control underCarSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="diffFluid">Diff. Fluid</label>
														<select name="diffFluidSug" id="diffFluidSug" class="form-control underCarSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="diffFluid">&nbsp;</label>
														<input name="diffFluid" id="diffFluid" type="text" class="form-control underCarSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="exhaustSystem">Exhaust System</label>
														<select name="exhaustSystemSug" id="exhaustSystemSug" class="form-control underCarSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="exhaustSystem">&nbsp;</label>
														<input name="exhaustSystem" id="exhaustSystem" type="text" class="form-control underCarSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

									</div>
								</div>
							</div><!-- end of .card -->
						</div><!-- end of .col -->
					</div><!-- end of UNDERCAR .row -->

					<!-- Start Steering & Suspension .row -->
					<div class="row d-print-none">
						<div class="col">
							<div class="card mb-3">
								<div class="card-header" id="headerFive" data-toggle="collapse" data-target="#steeringSuspension" aria-expanded="false" aria-controls="steeringSuspension">
									Steering & Suspension <span class="fa fa-chevron-down float-right"></span>
								</div>
								<div class="card-body collapse" id="steeringSuspension" aria-labelledby="headerFive" data-parent="#accordion">
									<div class="card-text">
										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="alignment">Alignment</label>
														<select name="alignmentSug" id="alignmentSug" class="form-control steeringSuspensionSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="alignment">&nbsp;</label>
														<input name="alignment" id="alignment" type="text" class="form-control steeringSuspensionSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="linkagesGears">Linkages & Gears</label>
														<select name="linkagesGearsSug" id="linkagesGearsSug" class="form-control steeringSuspensionSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="linkagesGears">&nbsp;</label>
														<input name="linkagesGears" id="linkagesGears" type="text" class="form-control steeringSuspensionSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="jointsSprings">Ball Joints /Springs</label>
														<select name="jointsSpringsSug" id="jointsSpringsSug" class="form-control steeringSuspensionSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="jointsSprings">&nbsp;</label>
														<input name="jointsSprings" id="jointsSprings" type="text" class="form-control steeringSuspensionSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="shocksStruts">Shocks /Struts</label>
														<select name="shocksStrutsSug" id="shocksStrutsSug" class="form-control steeringSuspensionSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="shocksStruts">&nbsp;</label>
														<input name="shocksStruts" id="shocksStruts" type="text" class="form-control steeringSuspensionSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

									</div>
								</div>
							</div><!-- end of .card -->
						</div><!-- end of .col -->
					</div><!-- end of Steering & Suspension .row -->

					<!-- Start Tires .row -->
					<div class="row d-print-none">
						<div class="col">
							<div class="card mb-3">
								<div class="card-header collapsed" id="headerSeven" data-toggle="collapse" data-target="#tires" aria-expanded="false" aria-controls="tires">
									Tires <span class="fa fa-chevron-down float-right"></span>
								</div>
								<div class="card-body collapse" id="tires" aria-labelledby="headerSeven" data-parent="#accordion">
									<div class="card-text">
										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-12">
														<label for="tireSizeFront">Size Front</label> 
														<input name="tireSizeFront" id="tireSizeFront" type="text" class="form-control tiresSerialize">
													</div>
												</div>
											</div><!-- end .col-md-6 -->

											<div class="col-md-6">
												<div class="row">
													<div class="form-group col-md-12">
														<label for="tireSizeRear">Size Rear</label> 
														<input name="tireSizeRear" id="tireSizeRear" type="text" class="form-control tiresSerialize">
													</div>
												</div>
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-1">
														<label for="tireLf">&nbsp;</label>
														<label for="tireLf">LF</label>
													</div>
													<div class="form-group col-md-2">
														<label for="tireLf">PSI</label>
														<input name="tireLf" id="tireLf" type="text" class="form-control tiresSerialize">
													</div>
													<div class="form-group col-md-4">
														<label for="tireLfTread">Tread</label>
														<div class="input-group">
															<input name="tireLfTread" id="tireLfTread" type="text" class="form-control tiresSerialize">
															<span class="input-group-addon">/32”</span>
														</div>
													</div>
													<div class="form-group col-md-5">
														<label for="tireLfDes">&nbsp;</label>
														<input name="tireLfDes" id="tireLfDes" type="text" class="form-control tiresSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-1">
														<label for="tireRf">&nbsp;</label>
														<label for="tireRf">RF</label>
													</div>
													<div class="form-group col-md-2">
														<label for="tireRf">PSI</label>
														<input name="tireRf" id="tireRf" type="text" class="form-control tiresSerialize">
													</div>
													<div class="form-group col-md-4">
														<label for="tireRfTread">Tread</label>
														<div class="input-group">
															<input name="tireRfTread" id="tireRfTread" type="text" class="form-control tiresSerialize">
															<span class="input-group-addon">/32”</span>
														</div>
													</div>
													<div class="form-group col-md-5">
														<label for="tireRfDes">&nbsp;</label>
														<input name="tireRfDes" id="tireRfDes" type="text" class="form-control tiresSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-1">
														<label for="tireRr">&nbsp;</label>
														<label for="tireRr">RR</label>
													</div>
													<div class="form-group col-md-2">
														<label for="tireRr">PSI</label>
														<input name="tireRr" id="tireRr" type="text" class="form-control tiresSerialize">
													</div>
													<div class="form-group col-md-4">
														<label for="tireRrTread">Tread</label>
														<div class="input-group">
															<input name="tireRrTread" id="tireRrTread" type="text" class="form-control tiresSerialize">
															<span class="input-group-addon">/32”</span>
														</div>
													</div>
													<div class="form-group col-md-5">
														<label for="tireRrDes">&nbsp;</label>
														<input name="tireRrDes" id="tireRrDes" type="text" class="form-control tiresSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-1">
														<label for="tireLr">&nbsp;</label>
														<label for="tireLr">LR</label>
													</div>
													<div class="form-group col-md-2">
														<label for="tireLr">PSI</label>
														<input name="tireLr" id="tireLr" type="text" class="form-control tiresSerialize">
													</div>
													<div class="form-group col-md-4">
														<label for="tireLrTread">Tread</label>
														<div class="input-group">
															<input name="tireLrTread" id="tireLrTread" type="text" class="form-control tiresSerialize">
															<span class="input-group-addon">/32”</span>
														</div>
													</div>
													<div class="form-group col-md-5">
														<label for="tireLrDes">&nbsp;</label>
														<input name="tireLrDes" id="tireLrDes" type="text" class="form-control tiresSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-1">
														<label for="tireSp">&nbsp;</label>
														<label for="tireSp">SP</label>
													</div>
													<div class="form-group col-md-2">
														<label for="tireSp">PSI</label>
														<input name="tireSp" id="tireSp" type="text" class="form-control tiresSerialize">
													</div>
													<div class="form-group col-md-4">
														<label for="tireSpTread">Tread</label>
														<div class="input-group">
															<input name="tireSpTread" id="tireSpTread" type="text" class="form-control tiresSerialize">
															<span class="input-group-addon">/32”</span>
														</div>
													</div>
													<div class="form-group col-md-5">
														<label for="tireSpDes">&nbsp;</label>
														<input name="tireSpDes" id="tireSpDes" type="text" class="form-control tiresSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

									</div>
								</div>
							</div><!-- end of .card -->
						</div><!-- end of .col -->
					</div><!-- end of Tires .row -->

					<!-- Start Brakes .row -->
					<div class="row d-print-none">
						<div class="col">
							<div class="card mb-3">
								<div class="card-header collapsed" id="headerNine" data-toggle="collapse" data-target="#brakes" aria-expanded="false" aria-controls="brakes">
									Brakes <span class="fa fa-chevron-down float-right"></span>
								</div>
								<div class="card-body collapse" id="brakes" aria-labelledby="headerNine" data-parent="#accordion">
									<div class="card-text">
										<div class="row">
											<div class="form-group col-md-12">
												<div class="row">
													<div class="form-group col-md-1">
														<div>&nbsp;</div>
														<label for="brakesLf">LF</label>
													</div>
													<div class="form-group col-md-2">
														<label for="brakesLfPads">Pads/Shoes</label>
														<div class="input-group">
															<input name="brakesLfPads" id="brakesLfPads" type="text" class="form-control brakesSerialize">
															<span class="input-group-addon">/mm</span>
														</div>
													</div>
													<div class="form-group col-md-3">
														<label for="brakesLf">&nbsp;</label>
														<select name="brakesLfSug" id="brakesLfSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-6">
														<label for="brakesLf">&nbsp;</label>
														<input name="brakesLf" id="brakesLf" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-12 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-12">
												<div class="row">
													<div class="form-group col-md-1">
														<div>&nbsp;</div>
														<label for="brakesRf">RF</label>
													</div>
													<div class="form-group col-md-2">
														<label for="brakesRfPads">Pads/Shoes</label>
														<div class="input-group">
															<input name="brakesRfPads" id="brakesRfPads" type="text" class="form-control brakesSerialize">
															<span class="input-group-addon">/mm</span>
														</div>
													</div>
													<div class="form-group col-md-3">
														<label for="brakesRf">&nbsp;</label>
														<select name="brakesRfSug" id="brakesRfSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-6">
														<label for="brakesRf">&nbsp;</label>
														<input name="brakesRf" id="brakesRf" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-12 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-12">
												<div class="row">
													<div class="form-group col-md-1">
														<div>&nbsp;</div>
														<label for="brakesRr">RR</label>
													</div>
													<div class="form-group col-md-2">
														<label for="brakesRrPads">Pads/Shoes</label>
														<div class="input-group">
															<input name="brakesRrPads" id="brakesRrPads" type="text" class="form-control brakesSerialize">
															<span class="input-group-addon">/mm</span>
														</div>
													</div>
													<div class="form-group col-md-3">
														<label for="brakesRr">&nbsp;</label>
														<select name="brakesRrSug" id="brakesRrSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-6">
														<label for="brakesRr">&nbsp;</label>
														<input name="brakesRr" id="brakesRr" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-12 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-12">
												<div class="row">
													<div class="form-group col-md-1">
														<div>&nbsp;</div>
														<label for="brakesLr">LR</label>
													</div>
													<div class="form-group col-md-2">
														<label for="brakesLrPads">Pads/Shoes</label>
														<div class="input-group">
															<input name="brakesLrPads" id="brakesLrPads" type="text" class="form-control brakesSerialize">
															<span class="input-group-addon">/mm</span>
														</div>
													</div>
													<div class="form-group col-md-3">
														<label for="brakesLr">&nbsp;</label>
														<select name="brakesLrSug" id="brakesLrSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-6">
														<label for="brakesLr">&nbsp;</label>
														<input name="brakesLr" id="brakesLr" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-12 -->
										</div><!-- end .row-->

										<hr>

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="lfRotorDrum">LF Rotor /Drum</label>
														<select name="lfRotorDrumSug" id="lfRotorDrumSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="lfRotorDrum">&nbsp;</label>
														<input name="lfRotorDrum" id="lfRotorDrum" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="rfRotorDrum">RF Rotor /Drum</label>
														<select name="rfRotorDrumSug" id="rfRotorDrumSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="rfRotorDrum">&nbsp;</label>
														<input name="rfRotorDrum" id="rfRotorDrum" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="rrRotorDrum">RR Rotor /Drum</label>
														<select name="rrRotorDrumSug" id="rrRotorDrumSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="rrRotorDrum">&nbsp;</label>
														<input name="rrRotorDrum" id="rrRotorDrum" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="lrRotorDrum">LR Rotor /Drum</label>
														<select name="lrRotorDrumSug" id="lrRotorDrumSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="lrRotorDrum">&nbsp;</label>
														<input name="lrRotorDrum" id="lrRotorDrum" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="brakeFluidCondition">Brake Fluid Condition</label>
														<select name="brakeFluidConditionSug" id="brakeFluidConditionSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="brakeFluidCondition">&nbsp;</label>
														<input name="brakeFluidCondition" id="brakeFluidCondition" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="masterCylinder">Master Cylinder</label>
														<select name="masterCylinderSug" id="masterCylinderSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="masterCylinder">&nbsp;</label>
														<input name="masterCylinder" id="masterCylinder" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="brakeHosesLines">Brake Hoses /Lines</label>
														<select name="brakeHosesLinesSug" id="brakeHosesLinesSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="brakeHosesLines">&nbsp;</label>
														<input name="brakeHosesLines" id="brakeHosesLines" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="wheelBearings">Wheel Bearings</label>
														<select name="wheelBearingsSug" id="wheelBearingsSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="wheelBearings">&nbsp;</label>
														<input name="wheelBearings" id="wheelBearings" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="brakeCalipers">Brake Calipers</label>
														<select name="brakeCalipersSug" id="brakeCalipersSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="brakeCalipers">&nbsp;</label>
														<input name="brakeCalipers" id="brakeCalipers" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->

											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="wheelCylinders">Wheel Cylinders</label>
														<select name="wheelCylindersSug" id="wheelCylindersSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="wheelCylinders">&nbsp;</label>
														<input name="wheelCylinders" id="wheelCylinders" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

										<div class="row">
											<div class="form-group col-md-6">
												<div class="row">
													<div class="form-group col-md-4">
														<label for="brakeCables">Brake Cables</label>
														<select name="brakeCablesSug" id="brakeCablesSug" class="form-control brakesSerialize">
															<option value="">Not Inspected</option>
															<option value="good">Good</option>
															<option value="replace_soon">Replace Soon</option>
															<option value="needs_replaced_now">Needs Replaced Now</option>
															<option value="ok">OK</option>
															<option value="sug">SUG</option>
														</select>
													</div>
													<div class="form-group col-md-8">
														<label for="brakeCables">&nbsp;</label>
														<input name="brakeCables" id="brakeCables" type="text" class="form-control brakesSerialize">
													</div>
												</div><!-- end .row -->
											</div><!-- end .col-md-6 -->
										</div><!-- end .row-->

									</div>
								</div>
							</div><!-- end of .card -->
						</div><!-- end of .col -->
					</div><!-- end of Brakes .row -->

					<!-- Start Technician Initials .row -->
					<div class="row d-print-none">
						<div class="col">
							<div class="card mb-3">
								<div class="card-header collapsed" id="headerSix" data-toggle="collapse" data-target="#technicianInitials" aria-expanded="false" aria-controls="technicianInitials">
									Technician Initials <span class="fa fa-chevron-down float-right"></span>
								</div>
								<div class="card-body collapse" id="technicianInitials" aria-labelledby="headerSix" data-parent="#accordion">
									<div class="card-text">
										<div class="row">
											<div class="form-group col-md-4">
												<div class="row">
													<div class="form-group col-md-12">
														<label for="qts">Qts</label> 
														<input name="qts" id="qts" type="text" class="form-control technicianInitialsSerialize">
													</div>
												</div>

												<div class="row">
													<div class="form-group col-md-12">
														<label for="filter">Filter</label>
														<input name="filter" id="filter" type="text" class="form-control technicianInitialsSerialize">
													</div>
												</div>
											</div><!-- end .col-md-4 -->

											<div class="col-md-4">
												<div class="row">
													<div class="form-group col-md-12">
														<label for="oilType">Oil Type</label> 
														<input name="oilType" id="oilType" type="text" class="form-control technicianInitialsSerialize">
													</div>
												</div>

												<div class="row">
													<div class="form-group col-md-12">
														<label for="wheelTorque">Wheel Torque</label>
														<input name="wheelTorque" id="wheelTorque" type="text" class="form-control technicianInitialsSerialize">
													</div>
												</div>
											</div><!-- end .col-md-4 -->

											<div class="col-md-4">
												<div class="row">
													<div class="form-group col-md-12">
														<label for="techComments">Comments</label> 
														<textarea name="techComments" rows="5" id="techComments" type="text" class="form-control pb-0 technicianInitialsSerialize"></textarea>
													</div>
												</div>
											</div><!-- end .col-md-4 -->

										</div><!-- end .row-->
									</div>
								</div>
							</div><!-- end of .card -->
						</div><!-- end of .col -->
					</div><!-- end of Technician Initials .row -->

				</div><!-- end #accordian -->

			</div><!-- end of .container-fluid -->
		</form>
	</div><!-- end of .content-wrapper -->

	<?php include "includes/footer.php"; ?>
	<script src="js/inspectionreport.js?v=<?php echo FLUX_VERSION; ?>"></script>
	<script>
		(function($) {
			utilitiesJS.sessionCheck();
			inspectionReport.init();
		})(jQuery);
	</script>
</body>

</html>
