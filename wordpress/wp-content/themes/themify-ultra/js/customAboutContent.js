var $ = jQuery.noConflict();

$(function(){

   $(".who-we-are-text, .our-vision-text, .become-a-partner-text, .investors-text").hide();
   // $(".who-we-are-image").mouseenter(function () {
   //      $(".who-we-are-text").css("color", "blue");
   // }).mouseleave()


    $(document).on('click','.who-we-are-image', function (event) {
        $('.who-we-are-text').show();
        $(this).css("background-color", "#fcd404");
        $('.our-vision-image, .become-a-partner-image, .investors-image').css("background-color", "#ffffff");
        $(".our-vision-text, .become-a-partner-text, .investors-text").hide();
    })

    $(document).on('click','.our-vision-image', function (event) {
        $('.our-vision-text').show();
        $(this).css("background-color", "#fcd404");
        $('.who-we-are-image, .become-a-partner-image, .investors-image').css("background-color", "#ffffff");
        $(".who-we-are-text, .become-a-partner-text, .investors-text").hide();
    })

    $(document).on('click','.become-a-partner-image', function (event) {
        $('.become-a-partner-text').show();
        $(this).css("background-color", "#fcd404");
        $('.who-we-are-image, .our-vision-image, .investors-image').css("background-color", "#ffffff");
        $(".who-we-are-text, .our-vision-text, .investors-text").hide();
    });

    $(document).on('click','.investors-image', function (event) {
        $('.investors-text').show();
        $(this).css("background-color", "#fcd404");
        $('.who-we-are-image, .our-vision-image, .become-a-partner-image').css("background-color", "#ffffff");
        $(".who-we-are-text, .our-vision-text, .become-a-partner-text").hide();
    });


});