//preloader
$(window).on('load', function() {
    $(".load-status").fadeOut();
    $(".preloader").delay(1000).fadeOut("slow");
});
$(document).ready(function() {
    //google login after storing the data and that data fill up when page is ready.
    /** global: localStorage */
    if(typeof localStorage.alb_arr !== 'undefined'){
        if(localStorage.alb_arr.length > 0) {
            var ls_alb_obj = jQuery.parseJSON(localStorage.alb_arr);
            $.each(ls_alb_obj, function (key, val) {
                $(":checkbox[value='" + val + "']").attr('checked', 'checked');
            });
            if ($('.ischecked:checked').length) {
                $(".dwn-slct-alb, .move-slct-alb").removeAttr('disabled');
            }
        }
    }
    //tooltip
    $('[data-toggle="tooltip"]').tooltip();

    //Scroll to top
    var scrollTop = $(".scroll-top");
    $(window).scroll(function() {
        //declare variable
        var topPos = $(this).scrollTop();
        //user scrolls down - show scroll to top button
        if (topPos > 100) {
            $(scrollTop).css("opacity", "1");
        } else {
            $(scrollTop).css("opacity", "0");
        }
    });
    //click event to scroll to top
    $(scrollTop).click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 800);
        return false;
    });

    //click event for show album
    $(".usr-alb").click(function() {
        $(".overlay").css("width", "100%");
        $(".alb-status,.alb-preloader").fadeIn();
        var albumId = $(this).attr('alb-id');
        $.ajax({
            url : 'ajax.php',
            type : 'POST',
            data : {"method":"getAlbumImages","albumId":albumId},
            success : function(data) {
                $(".alb-status").fadeOut();
                $(".alb-preloader").fadeOut("slow");
                $(".alb-images-slider").html(data);
                $(".mySlides").first().css( "display", "block" );
            }
        });
    });

    //if checkbox is not checked then disabled download selected album button.
    $('.ischecked').change(function() {
        if ($('.ischecked:checked').length) {
            $(".dwn-slct-alb, .move-slct-alb").removeAttr('disabled');
        } else {
            $(".dwn-slct-alb, .move-slct-alb").attr('disabled', 'disabled');
        }
    });

    //Download Album Zip
    $(".download-alb-btn, .dwn-slct-alb, .dwn-all-alb").click(function() {
        var downloadType = $(this).attr('dwn_type');
        $(".overlay-process").css("width", "100%");
        $(".zip-process-bar").css("opacity", "1");
        $(".overlay-closebtn").css("opacity", "0");
        $(".zip-process-bar span").text("Download process may take time.");
        var alb_arr = {};
        var key = 0;
        if(downloadType === '1'){ //Download selected album
            alb_arr[0] = $(this).attr('alb-id');
        }else if(downloadType === '2'){ //Download selected album
            $('input[name="slct-alb"]:checked').each(function() {
                alb_arr[key++] = this.value;
            });
        }else if(downloadType === '3'){ //Download all album
            $('input[name="slct-alb"]').each(function() {
                alb_arr[key++] = this.value;
            });
        }
        $.ajax({
            url : 'ajax.php',
            type : 'POST',
            data : {"method":"generateAlbumZip","albumsId":JSON.stringify(alb_arr)},
            success : function(data) {
                $(".zip-process-bar").css("opacity", "0");
                $(".overlay-closebtn").css("opacity", "1");
                $(".overlay-content-process").append(data);
            }
        });
    });

    //Download Album after remove zip & album directory user image files
    $(".rm-dwn-zip, .overlay-closebtn").click(function() {
        $.ajax({
            url : 'ajax.php',
            type : 'POST',
            data : {"method":"deleteZipAndDir"},
            success : function() {
                $(".overlay-process").css("width", "0%");
                $(".dwn-zip").css("opacity", "0");
            }
        });
    });

    //Move album to Google Drive.
    $(".move-alb-btn, .move-slct-alb, .move-all-alb").click(function() {

        var downloadType = $(this).attr('move_type');
        var alb_arr = {};
        var key = 0;
        if(downloadType === '1'){ //Move selected album
            alb_arr[0] = $(this).attr('alb-id');
        }else if(downloadType === '2'){ //Move selected album
            $('input[name="slct-alb"]:checked').each(function() {
                alb_arr[key++] = this.value;
            });
        }else if(downloadType === '3'){ //Move all album
            $('input[name="slct-alb"]').each(function() {
                alb_arr[key++] = this.value;
            });
        }
        /** global: localStorage */
        localStorage.alb_arr =  JSON.stringify(alb_arr);
        $.ajax({
            url : 'ajax.php',
            type : 'POST',
            data : {"method":"isGoogleLogin"},
            success : function(data) {
                if(data != 1){
                    //if user is not Logged In
                    $(".overlay-process").css("width", "100%");
                    $(".zip-process-bar").css("opacity", "0");
                    $(".overlay-closebtn").css("opacity", "1");
                    $.ajax({
                        url : 'ajax.php',
                        type : 'POST',
                        data : {"method":"GoogleLoginURL"},
                        success : function(data) {
                            $(".overlay-content-process").append(data);
                        }
                    });
                }else{
                    //if user is Logged In & Ready to move albums into google drive.
                    $(".overlay-process").css("width", "100%");
                    $(".zip-process-bar").css("opacity", "1");
                    $(".overlay-closebtn").css("opacity", "0");
                    $(".zip-process-bar span").text("Moving process may take time.");
                    $.ajax({
                        url : 'ajax.php',
                        type : 'POST',
                        data : {"method":"AlbumMoveToDrive","albumsId":JSON.stringify(alb_arr)},
                        success : function(data) {
                            $(".zip-process-bar").css("opacity", "0");
                            $(".overlay-closebtn").css("opacity", "1");
                            $(".overlay-process").css("width", "0%");
                        }
                    });
                    /** global: localStorage */
                    localStorage.alb_arr = [];
                }
            }
        });
    });

    //close the album overlay screen
    $(".closebtn").click(function() {
        $(".overlay").css("width", "0%");
        $(".alb-status").fadeOut();
        $(".alb-preloader").fadeOut("slow");
    });
});

//album slider Pure JS Script
var slideIndex = 1;
var alb_flag = 0;
var count = 0;
showSlides(slideIndex);
plusSlides(1);
function plusSlides(n) {
    showSlides(slideIndex += n);
};
setInterval(function autoSlider() {
    var slides = document.getElementsByClassName("mySlides");
    showSlides(slideIndex = count);
    count++;
    if(count > slides.length){
        slideIndex = 1;
        count = 0;
    }
},5000);
function showSlides(n) {
    var i;
    var slides = document.getElementsByClassName("mySlides");
    var caption = document.getElementsByClassName("text");
    if (n > slides.length) {slideIndex = 1}
    if (n < 1) {slideIndex = slides.length}
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    if((alb_flag == 1) || (slideIndex - 1 > 0)) {
        slides[slideIndex - 1].style.display = "block";
        if (caption[slideIndex - 1].textContent.length > 1) {
            caption[slideIndex - 1].style.backgroundColor = "black";
        }
        alb_flag = 1;
    }
};