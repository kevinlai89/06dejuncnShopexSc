var countdown_task = new Class({
    begin: 0,
	end: 0,
	tasktimeBox: null,
    initialize: function(id, begin, end, store, widgets){
	    this.begin = begin;
		this.end = end;
		this.tasktimeBox = $('countdown_'+id);
		this.item = $('pdt_'+id);
		this.id = id;
		this.li = this.item.getElement('li.gp');
		if(store<1){
			this.set_box(5);
			return this.tasktimeBox.set('html','<span class="timeof">团购已结束</span>');
		}
		if(begin>0){
			this.set_box(1);
		}
		this.auto_trackTime();
	},
	auto_trackTime: function(){
		var str,sec,day,hour,minute,hour,second,cd=3;
		if(this.begin >= 0){
			sec = this.begin--;
			if(this.begin<0){
			    this.set_box(2);
			    sec = this.end--;
			    this.li.set('html','<span class="price0">团购价：</span><span class="price1">'+this.li.get('price')+'</span>');
				try{$('btn_'+this.id).removeClass('addcart1');}catch(e){}
			}
		}else{
			sec = this.end--;
		}
		if(this.end<0) {
			this.set_box(5);
			return this.tasktimeBox.set('html','<span class="timeof">团购已结束</span>');
		}
		if(this.begin<0){
			if(this.end<3600) cd=4;
			else if(this.begin>-3600) cd=2;
			this.set_box(cd);
		}
		str='<span class="timeon">'+(this.begin>=0 ? '' : ' ');
		day = (sec/(3600*24)).toInt();sec-=day*3600*24;
		hour=(sec/3600).toInt();sec-=hour*3600;
		minute=(sec/60).toInt();
		second=sec%60;
		if (day>0){
			str +='<em >'+day+'</em>天';
			str +='<em >'+hour+'</em>时';
			str +='<em >'+minute+'</em>分';
			str+='<em >'+second+'</em>秒';
			str+= this.begin>=0 ? '后开始</span>' : '</span>';
		}else{
			str +='<em >'+hour+'</em>时';
			str +='<em >'+minute+'</em>分';
			str+='<em >'+second+'</em>秒';
			str+= this.begin>=0 ? '后开始</span>' : '</span>';	
		}
		this.tasktimeBox.set('html',str);
		var _this = this;
		(function(){_this.auto_trackTime()}).delay(1000);
	},
	set_box: function(i){
	}
});