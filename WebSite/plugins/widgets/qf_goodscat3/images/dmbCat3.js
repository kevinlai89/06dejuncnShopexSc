function dmbCat3OnSide(){
	$E('.dmbCat3 .dmbCat3_ob').addClass('indexShow');
	var floatBox=$E('.dmbCat3 .dmbCat3_sort');
	return floatBox.getSize().y+floatBox.getBorderWidth().top+floatBox.getBorderWidth().bottom
}
(function(){
	$('dmbCat-3-style').inject($E('link'),'before');
	var dmbCat3_items=$ES('.dmbCat3 .dmbCat3_dh1');
	if(!dmbCat3_items[0])return;
	var mDm3=$E('.dmbCat3 .dmbCat3_ob');
	if(!mDm3.hasClass('hover')){
		var mFloat=$E('.dmbCat3 .dmbCat3_sort');
		mDm3.addEvents({
			'mouseenter':function(){
				this.addClass('onShow')
			},
			'mouseleave':function(){
				this.removeClass('onShow')
			}
		})
	}
	dmbCat3_items.each(function(cat,i){
		var popup=cat.getElement('.dmbCat3_popup');
		var show_sort_l = cat.getElements('.show_sort_l dl');
		if(popup){
			cat.addEvents({
				'mouseenter':function(){
					var mtop=-i*33,r;
					if(popup.retrieve('top')){
						mtop=popup.retrieve('top');r=true
					}
					popup.setStyles({
						'display':'block',
						'margin-top':mtop
					});
					if(!r&&i&&popup.getSize().y<(i+1)*33){
						var turetop=mtop+((i+1)*33-popup.getSize().y)-1;
						popup.store('top',turetop);
						popup.setStyle('margin-top',turetop)
					}
					this.addClass('hover')
				},
				'mouseleave':function(){
					popup.setStyle('display','none');
					this.removeClass('hover')
				}
			})
		}
		if(show_sort_l){
			show_sort_l.each(function(cat2,i){
				cat2.addEvents({
					'mouseenter':function(){
						this.addClass('dl_cur')
					},
					'mouseleave':function(){
						this.removeClass('dl_cur')
					}
				})
			})
		}
	})
})()