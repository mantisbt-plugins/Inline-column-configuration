
function write_result( data, result_div ) {
	result_div.html(data).show().delay(4000).fadeOut();
}

jQuery(document).ready(function($) {

	var callback_button_submit = function(e) {
		e.preventDefault();
		var form = $(this.form);
		var post_url = (form.attr("action"));
		var result_div = $(this.form).find(".text_result");
		var post_data = $(this.form).serialize();
		$.post(post_url, post_data)
			.done( function(data){ write_result(data,result_div) } )
			.fail( function(){ write_result('ERROR while posting form',result_div) } );
			
	};

	var callback_dialog_on_load = function (response, status, xhr) {
		if ( status == "error" ) {
			$("#column_config_dialog").html('<span class="error-msg">' + status + ': ' + xhr.status + " " + xhr.statusText + '</span>');
		}
		$(".jqui_tabs").tabs();
		$(".sortable").sortable({
			axis: "y",
			});
		$(".button_submit").click(callback_button_submit);
		$("#column_config_dialog").dialog({
			modal: true,
			width: "auto",
			height: "auto",
			});
	};
	
	var click_open_dialog = function(ev){
		ev.preventDefault();
		var url = $(".columns_form_trigger").data("remote");
		$("#column_config_dialog").load(url,callback_dialog_on_load);
	}
	
	$("a.columns_form_trigger").click(click_open_dialog);

});