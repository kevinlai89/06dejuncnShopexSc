$ES('.fav').addEvent('click', function(e){
	e.stop();
	if(document.all) window.external.addFavorite($_config['favlink']['host'],$_config['favlink']['title']);
	else if(window.sidebar) window.sidebar.addPanel($_config['favlink']['title'],$_config['favlink']['host'], "");
});

var myquick=$ES('.headerMenu .myquick');window.addEvent('domready',function(){if(!myquick[0])return;myquick.each(function(nav,i){var myquickpop=nav.getElement('.quick_pop');var menuHd=nav.getElement('.menuHd');if(myquickpop){nav.addEvents({'mouseenter':function(){myquickpop.setStyle('display','block');menuHd.addClass('hover');},'mouseleave':function(){myquickpop.setStyle('display','none');menuHd.removeClass('hover');}})}});})

function setTab(name,cursel,n){
	for(i=1;i<=n;i++){
	var menu=document.getElementById(name+i);
	var con=document.getElementById("con_"+name+"_"+i);
	menu.className=i==cursel?"hover":"";
	con.style.display=i==cursel?"block":"none";
   }
}

window.addEvent('domready', function(){
	var menu = $E(".MenuList");
	var subMenu = menu.getElements("li");
	var currentPage = document.location.href.toString();
	currentPage = currentPage.substr(currentPage.lastIndexOf("/") + 1, currentPage.length);
	subMenu.each(function(el){
		var page = el.getElement('a').get('href');
		page = page.substr(page.lastIndexOf("/") + 1, page.length);
		if (page == currentPage){
			el.addClass('current');
		}
	});
	if (menu.getElements(".current").length == 0){
	 	menu.getElement("li").addClass('current');
	}
});
