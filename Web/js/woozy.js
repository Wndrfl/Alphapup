$('document').ready(function() {
	function wooz(el,amt,speed) {
		var el = $(el);
		if(el.hasClass('woozed')) {
			el.removeClass('woozed');
			el.animate({
				'right' : '0px'
			},speed);
		} else {
			el.addClass('woozed');
			el.animate({
				'right' : amt+'px'
			},speed);
		}
	}

	setInterval(function() {
		wooz('h1',15,200);
	},200);
	setTimeout(function() {
		setInterval(function() {
			wooz('h2',15,200);
		},150);
	},200);
});