jQuery(document).ready(function($){
	/** wp color picker */
	$('.wp-color-picker').wpColorPicker();

    /** upload button wp media */
    $(document).on('click', '.upload-button', function(e){
        var elm = $(this).parent().find('.target-line');
        e.preventDefault();
        var image = wp.media({
            title: 'Upload Image',
            multiple: false
        }).open().on('select', function(e){
                var uploaded_image = image.state().get('selection').first();
                var image_url = uploaded_image.toJSON().url;
                elm.val(image_url);
            });
    });
    /** clean url and add protocol */
    function setUrlProtocolLogic(url,protocol){
        let regexExp = /https*:\/\//g;
        let replaceStr = '';
        let pureUrl = url.replace(regexExp,replaceStr);
        return protocol + pureUrl;
    }

    /** implement url protocol on current nodes */
    function setUrlProtocol(){
        let url = $(".wp-installation-url").val();
        let protocol = $(".website-protocol").find(":selected").val();
        let complateUrl = setUrlProtocolLogic(url,protocol);
        let finalUrl = (url.length > 3)? complateUrl : '';
        $(".wp-installation-url").val(finalUrl);
    }

	/** show modal content **/
	var modal = $(".modal-wrapper");
	var btn = $(".modal-button");
	if(modal.length){
		btn.on("click" , function(e){
            e.preventDefault();
			var modal = $(this).data("modal");
			$("#" + modal).show( 0 , function(){
				$(this).addClass("open-modal");
			});
		});
		$(".modal-close").on("click" , function(){
			var modal = $(this).closest(".modal-wrapper");
			modal.hide();
		});
		modal.click(function(event) {
			var modal_content = $(".modal-content").find("*");
			var target = $( event.target );
		    if(!target.is(modal_content)){
		    	$(this).hide();
		    }
		});
	}
	
    /** set protocol url */
    $(".wp-installation-url").on("input", setUrlProtocol);
	$(".website-protocol").on("change", setUrlProtocol);
	
	/** request builder */
	$("#woap-build-form").on("submit",function(e){
		e.preventDefault();
		var builderData = new FormData(this);
		builderData.append('action',BUILDObj.action.queueAdd);
		let requestMessage = $(".request-message");
		$.ajax({
			type: 'POST',
			processData: false,
			contentType: false,
			url: BUILDObj.ajaxUrl,
			data: builderData,
			beforeSend: function () {
				requestMessage.text("request send...");
				console.log('before send');
			},
			error: function (request, status, error) { console.log(request,status,error); },
			success: function (respons) {
				var paragraph = ($(".woap-builder-message").length)? $(".woap-builder-message") : $(document.createElement('p')).addClass('woap-builder-message');
				console.log(paragraph);
				if(false == respons.status.length){
					requestMessage.text("error");
					return;
				}
				paragraph.text(respons.message);
				let requestMessageContent = (true == respons.status)? "request complate" : "request fail";
				requestMessage.text(requestMessageContent);
				requestMessage.after(paragraph);
			}
		});
	});
});
