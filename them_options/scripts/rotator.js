jQuery(document).ready(function ($) {
    /** update input name order number - using in adc */
    function updateAdcNameIndex(root){
        if(false == root.find("tr.woap-adc-row").length){
            return;
        }
        root.find("tr.woap-adc-row").each(function (rowIndex) {
            var inputs = $(this).find('input[type=text]');
            selectors = $(this).find('select').add(inputs);
            selectors.each(function(index){
                let attrName = $(this).attr("name");
                let validation = attrName.search("\\[\\]");
                if(-1 == validation){
                    return false;
                }
                let regex = /\[[0-9]\]/i;
                $(this).attr("name",attrName.replace(regex,"[" + rowIndex + "]"));
            });
        });
    }

    /** upload button wp media */
    $(document).on('click', '.upload-btn', function(e){
        var elm = jQuery(this).parent().find('.target_line');
        e.preventDefault();
        var image = wp.media({
            title: 'Upload Image',
            multiple: false
        }).open()
            .on('select', function(e){
                var uploaded_image = image.state().get('selection').first();
                console.log(uploaded_image);
                var image_url = uploaded_image.toJSON().url;
                elm.val(image_url);
            });
    });

    /** tbody sortable */
    $('tbody').sortable({
        update: function(event,ui){
            var root = $(".osa-setting-content-item:visible");
            updateAdcNameIndex(root);
        },
    });

    /** delete row - using in adc */
    $('tbody').on('click', '.delete_row', function () {
        $(this).closest('tr').remove();
        var root = $(".osa-setting-content-item:visible");
        updateAdcNameIndex(root);
    });

    /** add row */
    $('.add_row').on('click', function () {
        var $this = $(this);
        var $lastEML = $this.parent('.osa-submit-wrapper-table').prev('table').find('tr:last').clone(true);
        $lastEML.find('.element_id').remove();
        var elementIdInput = $("#osa_custom_option_id_template").html();
        var randomNumber = Math.floor((Math.random() * 10000) + 1);
        var elementIdNew = 'ID_' + randomNumber;
        var elementIdHtml = elementIdInput.replace("ELEMENT_ID",elementIdNew);
        var $lastEMLHTML = '<tr>' + elementIdHtml + $lastEML.html() + '</tr>';
        $this.parent('.osa-submit-wrapper-table').prev('table').find('tbody').append($lastEMLHTML);
        //console.log($lastEML.html());
        return false;
    });

    /** add new banner row - using in adc */
    $(document).on('change',".woap-adc-banner-column",function(){
        var request = $(this).val();
        var current = $(this).parent("td").prev(".adc-banner-wrapper").children(".d-flex").length;
        if(request > current){
            var htmlContent = '';
            var element = $(this).closest("tr").find(".adc-banner-wrapper").children(".d-flex")[0].outerHTML;
            for(var i=current;i<request;i++){
                htmlContent += element;
            }
            $(this).closest("tr").find(".adc-banner-wrapper").append(htmlContent);
        }else if(request < current){
            for(var i=current;i>request;i--){
                $(this).closest("tr").find(".adc-banner-wrapper").children(".d-flex")[i-1].remove();
            }
        }
    });

    /** add new adc row - using in adc */
    $(".add-adc-row").on("click",function(e){
        e.preventDefault();
        var root = $(this).closest(".osa-setting-content-item");
        var rowCount = root.find(".woap-adc-row").length;
        var htmlContent = root.find("#adc-row-template").html();
        htmlContent = htmlContent.replaceAll("COUNTER_CONST",rowCount);
        $(".woap-adc-tbody").append(htmlContent);
    });

});