void function(){finderGroup={};finderDestory=function(){for(var f in finderGroup){delete finderGroup[f]}};Element.Events.ListDrag={base:"mousedown",condition:function(f){return(f.control==true)}};var e=new Class({Extends:Drag,attach:function(){this.handles.addEvent("ListDrag",this.bound.start);return this},detach:function(){this.handles.removeEvent("ListDrag",this.bound.start);return this}});var b=new Class({Extends:Drag,start:function(f){if(this.element.getParent(".finder-header").scrollLeft>0){return}this.parent(f)}});var a=function(f,g,i,h){h=h||{x:0,y:0};var p=(i||window).getSize(),m=(i||window).getScroll();var o={x:f.offsetWidth,y:f.offsetHeight};var k={x:"left",y:"top"};for(var j in k){var l=g.page[j]+h[j];if((l+o[j]-m[j])>p[j]){l=g.page[j]-h[j]-o[j]}f.setStyle(k[j],l)}};var d=function(g,f){var h=$("msgStorage").retrieve("events",{finder_colset:{}});(h.finder_colset[g]=h.finder_colset[g]||{})[f[0]]=f[1]};Finder=new Class({Implements:[Events],options:{},initialize:function(g,f){$extend(this.options,f);this.id=g;this.initStaticView();this.initView();this.attachStaticEvents();this.attachEvents()},initStaticView:function(){$each(["action","form","search","tip","detail","header","footer"],function(f){this[f]=$("finder-"+f+"-"+this.id)},this)},initView:function(){this.list=$("finder-list-"+this.id).store("visibility",true);this.listContainer=this.list.getContainer()},isVisibile:function(){if(!this.list.retrieve){return false}return $chk(this.list.retrieve("visibility"))},attachStaticEvents:function(){var f=this;if(f.search){f.search.addEvent("submit",function(i){i.stop();var h=this;var g=h.getElement(".finder-search-input");f.filter.reset().push({value:g.value,name:h.get("current_key"),label:h.getElement("label").innerHTML+"为:<b>"+g.value+"&nbsp;</b>"});g.value=""});new DropMenu($E(".finder-search-select",f.action),{offset:{y:12,x:-5},relative:f.action})}if(f.tip){f.tip.addEvents({show:function(i,g){$$(this.childNodes).hide();var h=this.getElement("."+i);h.innerHTML=h.innerHTML.replace(/<em>([\s\S]*?)<\/em>/ig,function(){return"<em>"+g+"</em>"});h.setStyle("display","block");if(this.retrieve("show")){return}this.store("show",true);this.show();window.fireEvent("resize")},hide:function(){if(!this.retrieve("show")){return}this.store("show",false);$$(this.childNodes).hide();this.hide();window.fireEvent("resize")}})}if(f.action){f.action.getElements("*[submit]").addEvent("click",function(m){$list=$$("ul.finder-filter-info li.finder-filter-item input[type=hidden]");$list.each(function(q){q.set("disabled",true)});var o=this.get("target");var k=this.get("submit");var l=f.form.retrieve("rowselected");var h=new Element("div");for(n in l){if(n){$A(l[n]).each(function(q){h.adopt(new Element("input",{type:"hidden",name:n,value:q}))})}}var g=o,j={};if(o){if(o.contains("::")){g=o.split("::")[0];j=JSON.decode(o.split("::")[1]);if($type(j)!="object"){j={}}}}else{g="refresh"}if(!h.getFirst()){return MessageBox.error("请选择要操作的数据项.")}var i=this.getProperty("confirm");if(i){if(!window.confirm(i)){return}}if(!$(m.target).get("only_id")){h.adopt(f.form.toQueryString().toFormElements())}switch(g){case"refresh":W.page(k,$extend({data:h,method:"post",update:"messagebox",onComplete:f.refresh.bind(f)},j));break;case"dialog":var p=g.contains("::")?JSON.decode(g.split("::")[1]):{};window.finderDialog=new Dialog(k,$extend({title:this.get("dialogtitle")||this.get("text"),ajaxoptions:{data:h,method:"post"},onClose:f.refresh.bind(f)},j));break;case"_blank":new Element("form",{action:k,name:g,target:"_blank",method:"post"}).adopt(h).inject("elTempBox").submit();break;default:new Element("form",{action:k,name:g,method:"post"}).adopt(h).inject("elTempBox").submit();break}$list.each(function(q){q.set("disabled",false)})})}if(f.header){f.header.addEvent("click",function(h){var g=$(h.target);if(!g.hasClass("orderable")){g=g.getParent(".orderable")}if(!g){return}var i=[("desc"==g.get("order"))?"asc":"desc",g.get("key")].link({"_finder[orderType]":String.type,"_finder[orderBy]":String.type});f.fillForm(i).refresh();h.stopPropagation();return})}if(f.form){if(f.options.filterInit){new c(f.form.getElement(".finder-filter")).attach(f.options.filterInit);updateSize()}f.filter=new c(f.form.getElement(".finder-filter"),{onChange:function(){f.unselectAll();f.refresh()}})}if(f.detail){f.detail.retrieve("content",f.detail.getElement(".finder-detail-content"));f.detail.addEvents({slideIn:function(){var g=Array.flatten(arguments);if(this.retrieve("slideIn")){this.store("slideIn",g);return this.fireEvent("load",g)}this.store("slideIn",g);this.fireEvent("load",g);this.setStyle("height",f.listContainer.getSize().y*0.5);this.setStyle("width",f.listContainer.getSize().x);updateSize()},slideOut:function(){if(!this.retrieve("slideIn")){return}this.store("slideIn",false);this.retrieve("content").empty();this.setStyle("height",0);var g=this.retrieve("currow");if(g.removeClass){g.removeClass("view-detail")}this.store("item-id",null);updateSize()},load:function(j,i,l,k){if(!k){if(!$chk(l)||l.hasClass("view-detail")){return}if($type(this.retrieve("currow"))=="element"){$(this.retrieve("currow")).removeClass("view-detail")}this.store("currow",l.addClass("view-detail"));this.store("item-id",l.get("item-id"))}this.addClass("loading-content");var h=this;var g={update:h.retrieve("content"),onComplete:function(){h.fireEvent("complete",l)}};$extend(g,i);W.page(j+"&_finder_name="+f.id,g)},complete:function(g){if(g&&(g.getPosition().y>this.getPosition().y)){f.listContainer.retrieve("fxscroll",new Fx.Scroll(f.listContainer,{link:"cancel"})).toElement(g)}this.removeClass("loading-content")}})}},selectAll:function(){var f=this.header.getElement(".sellist");f.set("checked",true).fireEvent("change").set("checked",false);(this.form.retrieve("rowselected")[f.name]||[]).empty().push("_ALL_");this.tip.fireEvent("show","selectedall")},unselectAll:function(){var f=this.header.getElement(".sellist");f.set("checked",false).fireEvent("change");(this.form.retrieve("rowselected")[f.name]||[]).empty();this.tip.fireEvent("hide")},attachEvents:function(){var j=this;if(j.header){j.header.getElements("col").each(function(m,l){var l=l.toInt();var p=l+1;var o=j.header.getElement("td:nth-child("+p+")").getElement(".finder-col-resizer");if(!o){return}new b(m,{modifiers:{x:"width"},limit:{x:[15,1000]},handle:o,unit:0,onBeforeStart:function(q){q.store("lc",j.list.getElements("col")[l])},onDrag:function(r){if(!j.header.hasClass("col-resizing")){j.header.addClass("col-resizing")}var q=r.getStyle("width").toInt();$(r.retrieve("lc")).setStyle("width",q);$(r).addClass("resizing");if(window.webkit){j.header.getElement("td:nth-child("+p+")").setStyle("width",q);j.list.getElement("td:nth-child("+p+")").setStyle("width",q)}},onComplete:function(r){j.header.removeClass("col-resizing");$(r).removeClass("resizing");var q=j.list.getElement("td:nth-child("+p+")").get("key");d(j.options.controller,[q,r.getStyle("width").toInt()])}})})}var h=j.list.retrieve("eventInfo",{});var k=j.form.retrieve("rowselected",{});j.list.addEvents({selectrow:function(l){l.getParent(".row").addClass("selected")},unselectrow:function(l){l.getParent(".row").removeClass("selected")}});var g=j.list.getElements(".sel");j.rowCount=g.length;if(j.header&&j.header.getElement(".sellist")){j.header.getElement(".sellist").addEvent("change",function(){g.set("checked",this.checked);g.fireEvent("change")})}g.each(function(l){if(window.ie){l.addEvent("click",function(){this.fireEvent("change")});l.addEvent("focus",function(){this.blur()})}l.addEvent("change",function(){k[this.name]=k[this.name]||[];k[this.name][this.checked?"include":"erase"](this.value);if(!this.checked&&k[this.name].contains("_ALL_")){k[this.name].erase("_ALL_");return j.unselectAll()}var m=k[this.name].length;if(m>1){j.tip.fireEvent("show",["selected",m]);if(m==j.tip.get("count").toInt()||k[l.name].contains("_ALL_")){j.tip.fireEvent("show",["selectedall",m])}}else{j.tip.fireEvent("hide")}j.list.fireEvent(this.checked?"selectrow":"unselectrow",this)});if(k[l.name]&&k[l.name].push&&k[l.name].contains(l.value)){l.set("checked",true).fireEvent("change")}else{if(k[l.name]&&k[l.name].contains("_ALL_")){l.set("checked",true).fireEvent("change")}}if(row=l.getParent(".row")){if(row.get("item-id")==j.detail.retrieve("item-id")){row.addClass("view-detail");j.detail.store("currow",row);j.listContainer.retrieve("fxscroll",new Fx.Scroll(j.listContainer,{link:"cancel"})).toElement(row)}}});var i=j.list.getLast();if(i){i.addEvents({show:function(l){if(!l){return}var o=i.getSize().size;i.setStyles({visibility:"visible",opacity:0});i.retrieve("fx",new Fx.Styles(i,{link:"cancel",duration:400,transition:Fx.Transitions.Quint.easeOut})).start({opacity:1});var m=i.getElement(".cell-edit-action");m.addEvent("submet",function(){this.addClass("cell-edit-action-remote");i.store("requesting",true);var p=new Request({onSuccess:function(q){if(q.substring(0,3)=="ok:"){(l.getElement(".cell")||l).innerHTML=q.substring(3)}else{MessageBox.error(q)}i.fireEvent("hide",l);i.store("requesting",false)}});p.post(this.get("action"),this);this.store("request",p)});m.getElements("button").addEvent("click",function(p){if(this.hasClass("x-select-btn")){return}if(this.get("type")=="submit"){return m.fireEvent("submet",l)}i.fireEvent("hide",l)})},hide:function(l){this.setStyle("visibility","hidden");var m=this.getElement(".cell-edit-action")?this.getElement(".cell-edit-action").retrieve("request"):null;if(m&&"cancel" in m){m.cancel()}if(!l){return}["edit-ready","edit-begin","edit-ing"].each(l.removeClass,l);this.empty()}})}j.list.addEvent("click",function(m){var l=$(m.target);if(!l){return}if(l.match("img")){l=$(l.parentNode)}if(l.match("a")&&l.get("target")=="_blank"){var o=l.getParent(".row").getElement("input[class=sel]");o.set("checked",true);j.list.fireEvent("selectrow",o);o.fireEvent("change");return}if(_detail=l.get("detail")){m.stopPropagation();if(l.hasClass("btn-detail-open")&&l.getParent(".view-detail")){return j.hideDetail(_detail,{},l.getParent(".row"))}else{return j.showDetail(_detail,{},l.getParent(".row"))}}if(l.hasClass("cell")||l.hasClass("cell-inside")){l=l.getParent("td")}if(l.match("td")){if(j.detail.retrieve("slideIn")){if((detailbtn=l.getParent(".row").getElement("*[detail]"))&&!l.getParent(".row").hasClass("view-detail")){return j.showDetail(detailbtn.get("detail"),{},l.getParent(".row"))}}if(l.hasClass("editable")){if(l.hasClass("edit-ing")||l.hasClass("edit-begin")){return}if(l.hasClass("edit-ready")){l.addClass("edit-begin");j.editCell(l,m,function(p){l.addClass("edit-ing")});return}if(h.curcell){if(_editPanel=h.curcell.retrieve("editPanel")){if(_editPanel.retrieve("requesting")){return}_editPanel.fireEvent("hide")}h.curcell.removeClass("edit-ready").removeClass("edit-begin").removeClass("edit-ing")}l.addClass("edit-ready");h.curcell=l}}});attachEsayCheck(j.list,"td:nth-child(first)");var f=j.list.getContainer();if(j.header.getStyle("overflow")!="visible"){new e(j.header,{handle:j.list,style:false,modifiers:{x:"scrollLeft"},invert:true,preventDefault:true,onDrag:function(){f.scrollTo(j.header.getScrollLeft())},onStart:function(l){l.setStyle("cursor","move");j.list.addEvent("contextmenu",function(m){m.stop()})},onComplete:function(l){l.setStyle("cursor","default");(function(){j.list.removeEvents("contextmenu")}).delay(200)}})}},editCell:function(f,i,g){if(!f){return}var k=this;f.addClass("edit-begin");var j=k.list.getLast().setStyle("visibility","hidden");var h="index.php?ctl="+this.options.controller+"&act=cell_editor";new Request({onSuccess:function(l){j.set("html",l);a(j,i);j.fireEvent("show",f);f.store("editPanel",j);(g||$empty)(j)}}).post(h,{id:f.getParent(".row").get("item-id"),key:f.get("key").trim()})},fillForm:function(g){if(!g||"object"!=$type(g)){return}g=$H(g);var f=this;g.each(function(i,h){var j=(f.form.getElement("input[name^="+h.slice(0,-1)+"]")||new Element("input",{type:"hidden",name:h}).inject(f.form));j.set("value",i)});return f},eraseFormElement:function(){var f=Array.flatten(arguments);var g=this;$each(f,function(h){g.form.getElement("input[name="+h+"]").remove()});return g},showDetail:function(g,f,h){this.detail.fireEvent("slideIn",Array.flatten(arguments))},hideDetail:function(g,f,h){this.detail.fireEvent("slideOut",Array.flatten(arguments))},getQueryStringByForm:function(){return this.form.toQueryString()},page:function(f){this.form.store("page",f||1);this.request({method:this.form.method||"post"})},refresh:function(){this.request({method:this.form.method||"post",onComplete:function(){if(sinargs=this.detail.retrieve("slideIn")){sinargs.push("refresh");sinargs[2]=false;this.detail.fireEvent("load",sinargs)}}})},request:function(){var k=Array.flatten(arguments);var j=k.link({options:Object.type,action:String.type});j.action=j.action||this.form.action+"&page="+(this.form.retrieve("page")||1);j.options=j.options||{};var h=j.options.onComplete;if($type(h)!="function"){h=$empty}j.options=$extend(j.options,{elMap:{".mainHead":this.header,".mainFoot":this.footer},onComplete:function(){this.initView();this.attachEvents();h.apply(this,Array.flatten(arguments))}.bind(this)});var i=this.getQueryStringByForm();var g=j.options.data;switch($type(g)){case"string":j.options.data=[i,g].join("&");break;case"object":case"hash":j.options.data=[i,Hash.toQueryString(g)].join("&");break;case"element":j.options.data=[i,$(g).toQueryString()].join("&");break;default:j.options.data=i}var f=$("msgStorage").retrieve("events");if($H(f.finder_colset).getValues().length){new Request().post("index.php?ctl=default&act=status",{events:$("msgStorage").retrieve("events")}).chain(function(){f.finder_colset={};W.page(j.action,j.options)}.bind(this));return}W.page(j.action,j.options)}});var c=new Class({Implements:[Events,Options],options:{onPush:$empty,onRemove:$empty,onChange:$empty},initialize:function(g,f){var h=this;this.fp=$(g);this.fpInfo=this.fp.getElement(".finder-filter-info");this.fp.removeEvents("click").addEvent("click",function(j){var i=$(j.target);if(i&&i.hasClass("remove")){h.remove(i.getParent(".finder-filter-item"));return j.stopPropagation()}});this.setOptions(f)},push:function(g,h){if(this.fpInfo.getElement("input[name="+g.name+"]")){return this}var f=this.fp.getElement(".ffitpl").clone();if("array"==typeof(g.value)){g.name+="[]";g.each(function(i){new Element("input",{type:"hidden"}).set({name:g.name,value:g.value}).inject(f)})}else{new Element("input",{type:"hidden"}).set({name:g.name,value:g.value}).inject(f)}if(mg=g.merge){$splat(mg).each(function(j){var i;if($type(j)=="object"){i=new Element("input",{type:"hidden"}).set({name:j.name,value:j.value})}if($type(j)=="string"){i=j.toFormElements()}f.adopt(i)});delete (g.merge)}f.getElement("span").set("html",g.label);f.className="finder-filter-item";this.fpInfo.adopt(f);if(h){return this}this.change();return this},attach:function(f){f.each(function(h,g){this.push(h,true);if(g==(f.length-1)){this.change()}},this)},remove:function(f){$(f).remove();this.change();return this},reset:function(){this.fpInfo.getElements(".finder-filter-item").each(Element.remove);return this},change:function(){if(!this.fpInfo.getElements(".finder-filter-item").length){this.fp.hide()}else{this.fp.show()}this.fireEvent("change")}})}();