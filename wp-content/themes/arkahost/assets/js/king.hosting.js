(function($){
	"use strict";
	$(document).ready(function($){						
		$('.serch_area .input_submit').on( 'click', function(e){

			var domain = $('#domain_input').val();
			var search_domain_form = $(this).parent(".search_domain_form");

			//is diretly method
			var data = search_domain_form.data("form");
			
			if( data == 'direct')
			{

				search_domain_form.children('.domain').val( domain );
				search_domain_form.children('.domains').val( domain );
				search_domain_form.children('.domainsregperiod').attr( 'name', 'domainsregperiod[' + domain + ']' );
				search_domain_form.submit();
			}
			else
			{
				e.preventDefault();
				if($.active < 1){
					$('#domain_input').addClass('loading');
					$.ajax({
						url: king_hosting_params.ajax_url,
						type: 'POST',
						dataType: 'json',
						data: {
							'domain': domain,
							'action': 'king_search_domain',
							'security': $('#security').val()
						},
						success: function(data){
							$('#domain_search_results').html(data.results_html);
							$('#domain_input').removeClass('loading');	
							$.getScript(king_hosting_params.hosting_js);
						}
					});
				}
			}
						
		});


		$('.domain_input').keypress(function (e) {
			if (e.which == 13) {
				$(this).closest('form').find( ".input_submit" ).trigger( "click" );
				e.preventDefault();
				return false;
			}
		});

		
		
		$('.select_this_domain').on( 'click', function(){
			var o_this = $(this);
			var domain = o_this.attr('data-domain');
			$('#select_this_domain input.domains_val').val(domain);
			$('#select_this_domain .domainsregperiod_val').attr("name", "domainsregperiod[" + domain + "]");
			$('#select_this_domain').submit();
		});
		
		$('.whois_view').on( 'click', function(){
			var o_this = $(this);
			var domain = o_this.attr('data-domain');
			
			if($.active < 1){
				if($('#whois_view_result').length > 0) $('#whois_view_result').remove();
				
				$('#domain_search_results .content-result').append('<div id="whois_view_result"><a class="close" href="javascript:;"><i class="fa fa-times"></i></a><div class="content"></div></div>');
				$('#whois_view_result').hide();
				o_this.text('Loading...');
						
				$.ajax({
					url: king_hosting_params.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {
						'domain': domain,
						'action': 'king_get_whois'
					},
					success: function(data){
						$('#domain_search_results .suggest_domain').slideUp('fast');
						$('#whois_view_result .content').html(data.results_html);
						$('#whois_view_result').slideDown('fast');
						o_this.text('Whois');

						$.getScript(king_hosting_params.hosting_js);
					}
				});
			}
		});
		
		$('#whois_view_result .close').on( 'click', function(){
			$('#whois_view_result').slideUp('fast');
			$('#domain_search_results .suggest_domain').slideDown('fast');
		});
		
	});
	
})(jQuery);	
