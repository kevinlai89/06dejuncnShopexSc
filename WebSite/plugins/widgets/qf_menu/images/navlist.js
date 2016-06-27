var NavDemo = new Class({	
	Implements: [Options, Events],
	options: {
		'duration':200,
		'class': {
			'current':'current',
			'hook':'navHook'
		},
		'tagName':'li'
	},

	initialize: function(el, options){
		this.setOptions(options);
		this.container = document.id(el);
		this.items = this.container.getElements(this.options.tagName);
		this.bound = {
			'reset': this.reset.bind(this)
		}
		this.bg = null;
		this.current = null;
		this.moving = false;
		this.build();
	},

	build: function(){
		var ops = this.options, items = this.items;
		var bg = this.bg = new Element(ops.tagName,{
			'class': ops['class']['hook']
		}).inject(this.container);
		items.each(function(item){
			var css = ops['class']['current'];
			if(item.hasClass(css)){
				this.current = item;
				item.removeClass(css);
			}
		}, this);
		//this.diffLeft = items[0].getCoordinates().left;
		this.diffLeft = this.container.getCoordinates().left;
		this.reset(true);
		bg.set('morph',{
			'duration': ops.duration,
			'onComplete': function(){
				this.moving = false;
				if(this.out) {
					this.reset();
					this.out = false;
				}
			}.bind(this)
		})
		this.attach();
	},

	attach: function(){
		var _self = this;
		this.items.each(function(item){
			item.addEvent('mouseenter', function(e){_self.start(e)});
		});
		this.container.addEvent('mouseleave', function(){_self.reset()});
	},

	start: function(e){
		var ops = this.options, obj = document.id(e.target), coor = obj.getCoordinates();
		this.moving = true;
		this.bg.get('morph').start({
			'width': coor.width,
			'left': coor.left - this.diffLeft,
			'height': coor.height
		});
	},

	reset: function(){
		if(this.moving){
			this.out = true;
			return;
		}
		var coor = this.current.getCoordinates();
		this.bg.setStyles({
			'display': 'block',
			'width': coor.width,
			'height': coor.height,
			'left': coor.left - this.diffLeft
		});
	}
});