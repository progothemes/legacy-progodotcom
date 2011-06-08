// js for front end of ProGo.com
var progo_cycle, progo_timing;

function proGoTwitterCallback(twitters) {
  for (var i=0; i<twitters.length; i++){
    var status = twitters[i].text.replace(/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;'">\:\s\<\>\)\]\!])/g, function(url) {
      return '<a href="'+url+'">'+url+'</a>';
    }).replace(/\B@([_a-z0-9]+)/ig, function(reply) {
      return  reply.charAt(0)+'<a href="http://twitter.com/'+reply.substring(1)+'">'+reply.substring(1)+'</a>';
    });
    jQuery('.tweets p.last').before('<p>&ldquo;'+status+'&rdquo;<br /><span class="tstamp"><a href="http://twitter.com/'+twitters[i].user.screen_name+'/status/'+twitters[i].id_str+'" target="_blank">'+relative_time(twitters[i].created_at)+'</a> via '+ twitters[i].source +'</span></p>');
  }
}

function relative_time(time_value) {
  var values = time_value.split(" ");
  time_value = values[1] + " " + values[2] + ", " + values[5] + " " + values[3];
  var parsed_date = Date.parse(time_value);
  var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
  var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
  delta = delta + (relative_to.getTimezoneOffset() * 60);

  if (delta < 60) {
    return 'less than a minute ago';
  } else if(delta < 120) {
    return 'about a minute ago';
  } else if(delta < (60*60)) {
    return (parseInt(delta / 60)).toString() + ' minutes ago';
  } else if(delta < (120*60)) {
    return 'about an hour ago';
  } else if(delta < (24*60*60)) {
    return 'about ' + (parseInt(delta / 3600)).toString() + ' hours ago';
  } else if(delta < (48*60*60)) {
    return '1 day ago';
  } else {
    return (parseInt(delta / 86400)).toString() + ' days ago';
  }
}

function progo_set_shipping_country(html_form_id, form_id){
	var shipping_region = '';
	country = jQuery(("div#"+html_form_id+" select[class=current_country]")).val();

	if(country == 'undefined'){
		country =  jQuery("select[title='billingcountry']").val();
	}

	region = jQuery(("div#"+html_form_id+" select[class=current_region]")).val();
	if(/[\d]{1,}/.test(region)) {
		shipping_region = "&shipping_region="+region;
	}

	form_values = {
		wpsc_ajax_action: "change_tax",
		form_id: form_id,
		shipping_country: country,
		shipping_region: region
	}
	
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
		jQuery('.statelabel').each(function() {
			jQuery(this).hide();
			if(jQuery(this).next().html() != '') {
				//jQuery(this).show();//.parents().show();
				if(jQuery(this).parent().next().hasClass('zip')) {
					 jQuery(this).next().children().css('width','70px');
				}
			}
		});
	});
	
}

function progo_set_billing_country(html_form_id, form_id){
	var billing_region = '';
	country = jQuery(("div#"+html_form_id+" select[class=current_country]")).val();
	region = jQuery(("div#"+html_form_id+" select[class=current_region]")).val();
	if(/[\d]{1,}/.test(region)) {
		billing_region = "&billing_region="+region;
	}

	form_values = "wpsc_ajax_action=change_tax&form_id="+form_id+"&billing_country="+country+billing_region;
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
		jQuery('.statelabel').each(function() {
			jQuery(this).hide();
			if(jQuery(this).next().html() != '') {
				//jQuery(this).show();//.parents().show();
				if(jQuery(this).parent().next().hasClass('zip')) {
					 jQuery(this).next().children().css('width','70px');
				}
			}
		});
	});
}

function progo_selectcheck( id, disabled ) {
//	console.log('progo_selectcheck : ' + id + ', '+ disabled==true? 'true' : 'false');
	var wpsc_checkout_table = jQuery('#region_select_'+ id).parents('.wpsc_checkout_table');
	
	if(disabled == true ) {
		wpsc_checkout_table.find('input.billing_region').attr('disabled', 'disabled');
		wpsc_checkout_table.find('input.shipping_region').attr('disabled', 'disabled');
		wpsc_checkout_table.find('.billing_region').parent().hide();
		wpsc_checkout_table.find('.shipping_region').parent().hide();
	} else {
		wpsc_checkout_table.find('input.billing_region').removeAttr('disabled');
		wpsc_checkout_table.find('input.shipping_region').removeAttr('disabled');
		wpsc_checkout_table.find('.billing_region').parent().show();
		wpsc_checkout_table.find('.shipping_region').parent().show();
	}
	var countrysel = jQuery('#wpsc_checkout_form_'+id);
	if( countrysel.children().size() < 2 ) {
		countrysel.hide().parent().prev().prev().hide();
	}
}

function progo_homecycle( lnk ) {
	if( lnk == false ) {
		lnk = jQuery('#pagetop .ar a:last');
	}
	if( ( lnk.hasClass('off') == false ) && ( lnk.hasClass('here') == false ) ) {
		var onn, nex, slide1, slide2;
		if( lnk.hasClass('s') ) { // specific slide clicked
			lnk.add(lnk.siblings('a')).addClass('off');
			
			onn = lnk.siblings('.here');
			nex = lnk;
			nex.addClass('on here');
			onn.removeClass('on here');
			slide1 = jQuery('#pagetop .slide:eq('+ onn.data('tar') +')');
			slide2 = jQuery('#pagetop .slide:eq('+ nex.data('tar') +')');
			slide2.css({'left':'1000px'});
			
			slide2.add(slide1).animate({
				left: '-=994px'
			}, 450, function() {
				jQuery('#pagetop .ar a').removeClass('off');
				if( progo_timing > 0 ) {
					clearTimeout(progo_cycle);
					clearTimeout("progo_cycle");
					progo_cycle = setTimeout("progo_homecycle(false)",progo_timing);
				}
			});
		} else { // arrow clicked - just click next/prev slide
			onn = lnk.siblings('.here');
			nex = onn.next();
			if( lnk.hasClass('n') ) {
				if(nex.hasClass('n')) {
					nex = onn.prevAll('.r').next();
				}
			} else {
				nex = onn.prev();
				if(nex.hasClass('r')) {
					nex = onn.nextAll('.n').prev();
				}
			}
			progo_homecycle(nex);
		}
	}
	return false;
}

jQuery(function($) {
	var progo_ptop = $('#pagetop');
	if(progo_ptop.hasClass('slides')) {
		progo_ptop.children('div.ar').children('a').click(function() { return progo_homecycle($(this)); })
			.filter('a.s').bind({
				mouseover: function() {
					$(this).addClass('on');
				},
				mouseleave: function() {
					if($(this).hasClass('here') == false) {
						$(this).removeClass('on');
					}
				}
			}).each(function(i) {
				$(this).data('tar',i);
			});
		progo_ptop.addClass('sliding');
		progo_cycle = setTimeout("progo_homecycle(false)",progo_timing);
	}
	
	$('#hdr .cartcollapse').hide().parent().parent().prev().click(function() {
		$('#hdr .cartcollapse').stop().slideDown();
	});
	
	$('#nav ul.sub-menu').wrap('<div class="sub" />').parent().prev().addClass('flink').bind('mouseover',function() {
		$(this).parent().addClass('over');
	}).parent().bind('mouseleave',function() {
		$(this).removeClass('over');
	});
});