$(document).ready(function() {

    //Overlay
    Overlay.initialiseOverlay();

    $("#login-button").button();
    $("#login-button").click(function() {
        auth();
    });
    
    check();
    
});

function check() {
    $.get(
        "index.php?action=check",
        function(data) {
            
            if (data.re_auth) {
                $("#info, #login-form").show();
                $("#loading, #authenticated, #loading-text").hide();
            }
            else if (data.importing) {
                $("#loading, #loading-text").show();
                $("#info, #login-form, #authenticated").hide();
                setTimeout(function() { check(); }, 5000);
            }
            else if (data.imported) {
                $("#authenticated").show();
                $("#info, #login-form, #loading, #loading-text").hide();
                setTimeout(function() { window.location = "http://lan.lsucs.org.uk/index.php?page=account"; }, 5000);
            }
        },
        'json');
}

function auth() {
    Overlay.loadingOverlay();
    $.post(
        "index.php?action=auth",
        { username: $("#username").val(), password: $("#password").val(), seat: $("#seat").val() },
        function (data) {
            if (data && data.error) {
                Overlay.openOverlay(true, data.error);
                return;
            }
            check();
            setTimeout(function() { check(); }, 3000);
            Overlay.closeOverlay();
        },
        'json');
}



//Overlay object
var Overlay = {
    
    initialiseOverlay: function() {
        $(window).resize(function() { Overlay.resizeScreen(); });
        $(document).scroll(function() { Overlay.resizeScreen(); });
        $("#close-overlay").live("click", function() { Overlay.closeOverlay(); });
    },
    openOverlay: function(showButton, text, timeout) {

        if (showButton) this.showCloseButton();
        else this.hideCloseButton();
        
        if (text.length > 0) $("#overlay-content").html(text);
        
        this.resizeScreen();
        this.adjustOverlay();
        $("#screen").fadeIn("300");
        $("#overlay").fadeIn("300");
        
        if (timeout > 0) {
            setTimeout(function () { Overlay.closeOverlay(); }, timeout);
        }
        
    },
    loadingOverlay: function() {
        this.openOverlay(false, '<img src="images/loading.gif" />');
    },
    closeOverlay: function() {
        $("#screen").fadeOut("300");
        $("#overlay").fadeOut("300");
    },
    adjustOverlay: function() {
        $("#overlay").css('margin-top', - $("#overlay").height()/2 -50);
        $("#overlay").css('margin-left', - ($("#overlay").width()/2 + 75));
    },
    resizeScreen: function() {
        $("#screen").css('width', $(document).width());
        $("#screen").css('height', $(document).height());
        this.adjustOverlay();
    },
    hideCloseButton: function() {
        $("#close-overlay").css('display', 'none');
    },
    showCloseButton: function() {
        $("#close-overlay").css('display', 'block');
    }

}
