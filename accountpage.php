<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Account | Flux Shop Manager</title>
    <?php include "includes/head.php"; ?>
</head>

<body class="fixed-nav sticky-footer bg-light" id="page-top">
<?php include "includes/navigation.php"; ?>
<div class="content-wrapper">
    <div class="container-fluid">

        <div class="row d-print-none">
            <div class="col-12">
                <h1>My Account page</h1>
            </div>
        </div>

        <div class="row d-print-none">
            <div class="col-lg-6">
                <div class="card mb-3" id="customerCard">
                    <div class="card-header">
                        Change Password
                    </div>
                    <div class="card-body">
                    <div id="accountMessageContainer"></div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="input-group">
                                    <input type="password" id="OldPassword" class="form-control form-control-lg" placeholder="Old Password" name="oldpassword">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="input-group">
                                    <input type="password" id="newPassword" class="form-control form-control-lg" placeholder="New Password" name="newpassword">
                                </div>
                            </div>
                        </div>
                        <p></p>
                        <div class="row">
                            <div class="col-lg-6">
                                <a href="#" id="changePassword" class="btn btn-primary form-control form-control-lg">Change</a>
                            </div>
                        </div>
                        <p></p>
                        <div id="customerSearchResults">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "includes/footer.php"; ?>
<script src="js/account.js?v=<?php echo FLUX_VERSION; ?>"></script>
<script>
    (function($) {
        utilitiesJS.sessionCheck();
        accountJs.init();
    })(jQuery);
</script>
</body>

</html>