jQuery(document).ready(function($){
    var lineChart = $("#osa-report-daily-postviews-chart");
    var lineChartData = {
        labels: ReportOBJ.viewsPeriod,
        datasets: [{
                label: ReportOBJ.singleViewLabel,
                data: ReportOBJ.viewsCount,
                fill: false,
                borderColor: 'rgba(0, 156, 208, 1)',
                lineTension: 0,
                pointBorderColor: 'rgba(0, 156, 208, 1)',
                pointBackgroundColor: 'rgba(0, 156, 208, 1)',
                pointRadius: 3,
            }]
    };
    lineChartOptions = {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                },
                    gridLines: {},
                }],
            xAxes: [{
                    gridLines: {},
                    ticks: {
                        minRotation: 60
                    }
                }]
        },
        legend: false
    };
    if (lineChart.length) {
        var dayChart = new Chart(lineChart, {
            type: 'line',
            data: lineChartData,
            options: lineChartOptions
        });
    }
    // online viewer pagination
    $(".osa-report-viewers-pagination-item").on("click",function(e){
        e.preventDefault();
        var pagedValue = $(this).data('paged');
        $(".osa-report-viewers-pagination-item").removeClass("disabled");
        $(this).addClass("active disabled");
        $.ajax({
            url : ReportOBJ.adminAjax,
            type : "POST",
            data : {
                action : ReportOBJ.viewerAction,
                paged : pagedValue
            },
            beforeSend: function(){
                $(".osa-preloader").show();
            },
            success : function(respons){
                console.log(respons);
                if(false == respons.status){
                    console.error(respons.message);
                    return;
                }
                $("#osa-report-viewer-rows").html(respons.html);
                $(".osa-preloader").hide();
            },
            error: function (request, status, error) {
                console.error({
                    "action" : ReportOBJ.viewerAction,
                    "url" : ReportOBJ.adminAjax,
                    "error" : error,
                    "status" : status
                });
            },
        });

    });
    //modal notification panel
	var modal = $(".osa-modal-content");
    $("#osa-report-viewer-rows").on("click" ,".osa-modal-button", function(e){
        e.preventDefault();
        var modal = $(this).data("modal");
        $("#" + modal).show( 0 , function(){
            $(this).addClass("open-modal");
        });
    });
    $("#osa-report-viewer-rows").on("click",".modal-close" , function(e){
        e.preventDefault();
        var modal = $(this).closest(".osa-modal-content");
        modal.hide();
    });

    $("#osa-report-viewer-rows").on("click",".osa-modal-content" , function(event){
        var modal_content = $(".modal-content").find("*");
        var target = $( event.target );
        if(!target.is(modal_content)){
            $(this).hide();
        }
    });
    //wp media uploader
    var wpFileFrame;
    $(document).on('click', '.woap-icon-uploader', function (event){
        event.preventDefault();
        window.prev_element = $(this).prev('.woap-notification-icon-input');
        if (wpFileFrame) {
            wpFileFrame.open();
            return;
        }
        wpFileFrame = wp.media.frames.wpFileFrame = wp.media();
        wpFileFrame.on('select', function () {
            attachment = wpFileFrame.state().get('selection').first().toJSON();
            prev_element.val(attachment.url);
        });
        wpFileFrame.open();
    });
    //send notification
    $(document).on("submit",".osa-report-notification-form",function(e){
        e.preventDefault(e);
        var resultOutput = $(this).find('.osa-report-notification-form-result');
        var formData = new FormData(this);
        formData.append('action',ReportOBJ.notificationAction);
        var id = e.currentTarget[0].value;
        var authentication = e.currentTarget[1].value;
        var title = e.currentTarget[2].value;
        var body = e.currentTarget[3].value;
        $.ajax({
            url : ReportOBJ.adminAjax,
            type : "POST",
            processData: false,
            contentType: false,
            data : formData,
            beforeSend: function(){
                $(".osa-report-notification-form-preloader").show();
            },
            success : function(respons){
                if(false == respons.status){
                    console.error(respons.result);
                }
                resultOutput.html(respons.message);
                $(".osa-report-notification-form-preloader").hide();
            },
            error: function (request, status, error) {
                console.error({
                    "request" : request,
                    "action" : ReportOBJ.notificationAction,
                    "url" : ReportOBJ.adminAjax,
                    "error" : error,
                    "status" : status
                });
            },
        });
    });
});