$(document).ready(function(){

    /*Галерея  зображень, прокрутка*/ 
    $("#carousel").jcarousel({
        scroll: 6,
        auto: 0
    });
    $("#carousel").removeClass("vhidden");
    $('.fancybox').fancybox();

 });

// Ф-ыя вивиоду зображення в детальному перегляді галереї. 
function showImage (path, path_org, alt, title) {
      $("#imageLarge").html( '<a href="'+path_org+'" class="fancybox"><img align="middle" src="'+path+'" alt="'+alt+'" title="'+title+'"/></a>' );
}