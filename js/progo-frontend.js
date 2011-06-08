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

function progo_homecycle( lnk ) {
	if( lnk == false ) {
		lnk = jQuery('#pagetop .ar a:last');
	}
	if( ( lnk.hasClass('off') == false ) && ( lnk.hasClass('here') == false ) ) {
		var onn, nex, slide1, slide2;
		if( lnk.hasClass('s') ) { // specific slide clicked
			lnk.add(lnk.siblings('a')).addClass('off');
			clearTimeout(progo_cycle);
			
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
			nex.click();
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