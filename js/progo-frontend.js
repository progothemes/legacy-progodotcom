// js for front end of ProGo.com
var progo_cycle, progo_timing;


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