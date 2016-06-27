window.addEvent('domready',function(e){
	var popular = $ES('.grid .items-gallery');
	popular.each(function(el){
		el.addEvents({
			'mouseover':function(){
			this.addClass("current")
			},
			'mouseout':function(){
			this.removeClass("current")
			}
		})
	});
	
})
