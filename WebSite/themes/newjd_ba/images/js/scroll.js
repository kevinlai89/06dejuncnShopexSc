/*滚动展示*/
var Scroll_Script=new Class({
	initialize:function(id,direct){
		this.adshow_box=$('Scroll_'+id);
		var items;
		if(items=this.adshow_box.getElements('li')){
			this.direct=direct>0?1:0;
			this.adshow_size=this.adshow_box.getSize();
			var n=items.length;
			var wrapwidth=this.direct?this.adshow_size.y:this.adshow_size.x;
			this.item_size=this.direct?items[0].getSize().y:(items[0].getSize().x+5);
			if(this.item_size*n>wrapwidth){
				var btn=[['prev','next'],['up','down']];
				var _this=this;
				this.adshow_box.adopt(new Element('span',{
					'class':'span_'+btn[this.direct][0]
				}).setOpacity(0.15));
				this.adshow_box.adopt(new Element('b',{
					'class':'b_'+btn[this.direct][0],'events':{
						'mouseenter':function(){_this.hand=true},
						'click':function(){_this.scrollto(-1)},
						'mouseleave':function(){_this.hand=false}
					}
				}));						
				this.adshow_box.adopt(
					new Element('span',{
						'class':'span_'+btn[this.direct][1]
					}).setOpacity(0.15)
				);
				this.adshow_box.adopt(new Element('b',{
					'class':'b_'+btn[this.direct][1],'events':{
						'mouseenter':function(){_this.hand=true},
						'click':function(){_this.scrollto(1)},
						'mouseleave':function(){_this.hand=false}
					}
				}));					
				this.style=!this.direct?'left':'top';
				this.container=this.adshow_box.getElement('.scroll-box');
				this.fn=new Fx.Style(this.container,this.style,{
					duration:500,
					fps:100,
					onComplete:function(){
						if(_this.op<0)_this.container.adopt(_this.container.getFirst()).setStyle(_this.style,0)
					}
				});
				this.auto()
			}
		}
	},
	auto:function(){
		this.timer=(function(){
			if(this.hand)return;
			this.scrollto(-1)
		}).periodical(3500,this)
	},
	scrollto:function(op){
		if((this.op=op)<0){
			this.fn.start(0,this.item_size*op)
		}else{
			this.container.setStyle(this.style,-this.item_size*op).getLast().injectTop(this.container);
			this.fn.start(-this.item_size*op,0)
		}
	}
})



