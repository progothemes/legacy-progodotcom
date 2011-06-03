// js for front end of ProGo.com
var progo_cycle, progo_timing;


function progo_homecycle( lnk ) {
	if( lnk == false ) {
		lnk = jQuery('#pagetop .ar a:last');
	}
	if( lnk.hasClass('off') == false ) {
		lnk.add(lnk.siblings('a')).addClass('off');
		clearTimeout(progo_cycle);
		var onn = jQuery('#pagetop .slide.on');
		var nex = onn.next();
		var dir = '-=994px';
		if( lnk.hasClass('n') ) {
			if(nex.hasClass('slide') == false) {
				nex = onn.prevAll('.slide:first-child');
			}
			nex.css({'left':'1000px'});
		} else {
			nex = onn.prev();
			if(nex.size() == 0) {
				nex = onn.nextAll('.ar').prev();
			}
			nex.css({'left':'-988px'});
			dir = '+=994px';
		}
		onn.add(nex).animate({
			left: dir
		}, 450, function() {
			jQuery(this).toggleClass('on');
			jQuery('#pagetop .ar a').removeClass('off');
			progo_scrollcheck();
		});
	}
	return false;
}

function progo_scrollcheck() {
	var ptop = jQuery('#pagetop');
	var fset = ptop.offset();
	var wscrolltop = jQuery(window).scrollTop();
	clearTimeout(progo_cycle);
	if( ( progo_timing > 0 ) && ( wscrolltop < fset.top ) ) {
		progo_cycle = setTimeout("progo_homecycle(false)",progo_timing);
		//console.log('scrollon');
	} else {
		//console.log('noscroll');
	}
}

jQuery(function($) {
	var progo_ptop = $('#pagetop');
	if(progo_ptop.hasClass('slides')) {
		progo_ptop.children('div.ar').children('a').click(function() { return progo_homecycle($(this)); });//.next().click(function() { return progo_homecycle(false); });
		progo_ptop.addClass('sliding');
		$(window).bind('scroll.progo',progo_scrollcheck).trigger('scroll.progo');
	}
	
	$('#hdr .cartcollapse').hide().parent().parent().prev().click(function() {
		$('#hdr .cartcollapse').stop().slideDown();
	});
	
	$('#nav ul.sub-menu').wrap('<div class="sub" />').parent().prev().addClass('flink').bind('mouseover',function() {
		$(this).parent().addClass('over');
	}).parent().bind('mouseleave',function() {
		$(this).removeClass('over');
	});
	
	Cufon.replace('#slogan, #main h2.banner', { fontFamily: 'TitilliumText', fontWeight: '400' });
	Cufon.replace('#main h2', { fontFamily: 'TitilliumText', fontWeight: '600' });
	Cufon.replace('h3, .meter strong', { fontFamily: 'TitilliumText', fontWeight: '800' });
	Cufon.replace('h1.page-title, #htop h1', { fontFamily: 'TitilliumText', fontWeight: '800', textShadow: '3px 3px rgba(160, 72, 0, 1.0)' });
	Cufon.now();
});