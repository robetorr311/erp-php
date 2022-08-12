var accountJs = {
    init: function() {
        $(document).on("click", "#changePassword", function(event) {
            event.preventDefault();
            event.stopPropagation();
            var params = {};
            params.oldpass = $("#OldPassword").val();
            params.newpass = $("#newPassword").val();
            this.classList.add("disabled");
            $('#accountMessageContainer').html("");
            $.ajax({
                url: './api/v1/account/password',
                type : "PUT",
                dataType : 'json',
                data : JSON.stringify(params),
                contentType: "application/json",
                headers:{"Authorization":"Bearer " + Cookies.get('token')},
                success : function(result) {
                    $("#changePassword").removeClass("disabled");
                    
                    $('#accountMessageContainer').html('<div class="alert alert-success" role="alert">Password changed successfully.</div>');
                },
                error: function(xhr, resp, text) {
                    var message = "An error occurred while trying to log in";
                    if(xhr.hasOwnProperty("responseJSON") && xhr.responseJSON.hasOwnProperty("error")) {
                        message = xhr.responseJSON.error;
                    }

                    $('#accountMessageContainer').html('<div class="alert alert-danger" role="alert">'+message+'</div>');
                    $("#changePassword").removeClass("disabled");
                }
            });
        });

        $(document).on("keyup", "#OldPassword, #newPassword", function(event) {
            if (event.keyCode === 13) {
                $("#changePassword").click();
            }
        });
    }
}