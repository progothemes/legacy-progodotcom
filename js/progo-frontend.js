// js for front end of ProGo.com
var progo_homecycling, progo_timing;

function progo_homecycle( prev ) {
	clearTimeout('progo_homecycling');
	var onn = jQuery('#pagetop .slide.on');
	var nex = onn.next();
	var dir = '-=960px';
	if( prev == true ) {
		nex = onn.prev();
		if(nex.size() == 0) {
			nex = onn.nextAll('.ar').prev();
		}
		nex.css({'left':'-960px'});
		dir = '+=960px';
	} else {
		if(nex.hasClass('slide') == false) {
			nex = onn.prevAll('.slide:first-child');
		}
		nex.css({'left':'960px'});
	}
	onn.add(nex).animate({
		left: dir
	}, 600, function() {
		jQuery(this).toggleClass('on');
	});
	jQuery('#pagetop').animate({
		height: nex.outerHeight(true)
	}, 600 );
	
	if( progo_timing > 0 ) {
		progo_homecycling = setTimeout("progo_homecycle(false)",progo_timing); 
	}
	return false;
}

jQuery(function($) {
	var progo_ptop = $('#pagetop');
	if(progo_ptop.hasClass('slides')) {
		progo_ptop.children('div.ar').children('a:first').click(function() { return progo_homecycle(true); }).next().click(function() { return progo_homecycle(false); });
		progo_ptop.height(progo_ptop.children('.slide.on').height()).addClass('sliding');
		if(progo_timing > 0) {
			progo_homecycling = setTimeout("progo_homecycle(false)",progo_timing);
		}
	}
	
	$('#cartcollapse').hide().parent().parent().prev().click(function() {
		$('#cartcollapse').stop().slideDown();
	});
	
	$('#nav ul.sub-menu').wrap('<div class="sub" />').parent().prev().addClass('flink').bind('mouseover',function() {
		$(this).parent().addClass('over');
	}).parent().bind('mouseleave',function() {
		$(this).removeClass('over');
	});
	
	Cufon.replace('#slogan, #main h2.banner', { fontFamily: 'TitilliumText', fontWeight: '400' });
	Cufon.replace('#main h2', { fontFamily: 'TitilliumText', fontWeight: '600' });
	Cufon.replace('h3', { fontFamily: 'TitilliumText', fontWeight: '800' });
	Cufon.replace('h1.page-title, #htop h1', { fontFamily: 'TitilliumText', fontWeight: '800', textShadow: '3px 3px rgba(160, 72, 0, 1.0)' });
	Cufon.now();
});