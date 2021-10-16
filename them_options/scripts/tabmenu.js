jQuery(document).ready(function ($) {
    $(".osa-settings-wrapper .osa-setting-tabs li").on("click", "a", function (e) {
        e.preventDefault();
        var contentId = $(this).attr("rel");
        $(".osa-setting-content-item").hide();
        var selector = ".osa-settings-wrapper .osa-setting-contents " + "#" + contentId;
        console.log(selector);
        $(selector).show();
        $(".osa-setting-tabs li").removeClass("active");
        $(this).parent("li").addClass("active");
    });
});