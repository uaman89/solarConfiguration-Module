$(document).ready(function(){
    next = 0;
    prew = 0;

    $("#foo2").carouFredSel({
        responsive	: true,
        scroll		: {
            fx			: "crossfade"
        },
        items		: {
            visible		: 1,
            width		: 1000,
            height		: "30%"
        }
    });
    $('#mycarousel').jcarousel({
        vertical: true,
        scroll: 2
    });

        $("a.fancy").fancybox({
            'zoomSpeedIn': 300,
            'zoomSpeedOut': 300,
            'overlayShow': false
        });

    $(".videoNewTitle").click(function() {
        var vid = '<div class="full-vid">'+$(this).parent().children('.videoData').html()+'</div>';
        $.fancybox(vid);
    });

    //$('iframe').attr('src', $('iframe').attr('src')+"?wmode=opaque");

    $(".item").hover(
        function () {
            if($(this).find("ul").height()!= null){
                var heightUL = $(this).find("ul").height();
                // alert(heightUL);
                var blockHeight = $(this).height();
                var heightSum = heightUL + blockHeight + 25;
                $(this).css("height", heightSum);
            }
        },
        function () {
            $(this).css("height", '292');
        }
    );
    $(".min-slide").click(function() {
        var source = $(this).data('img');
        $(".full-prop-img img").attr('src', source);
    });

    $(".pages-full-content img").click(
        function(){
            var imgF = $(this).attr('src');
            $.fancybox('<img src="'+imgF+'">');
        }
    );


});