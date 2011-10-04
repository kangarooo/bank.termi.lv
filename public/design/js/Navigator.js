var Navigator = new Class({
	Implements: [Options, Events]
//	, active: []
	, options: {
		el: document.body
	}
	, block: null//block of element
	, active: {}//active elements
	, links:{}//set of links
	, types:[]//types ids
	, initialize: function(options){
		this.setOptions(this.options, options);
		this.container=this.options.el;
		this.createNavigation();
	}
	, createNavigation: function(){
		this.block = (new Element('div', {
			'id':'navigation'
			, 'styles': {
				'position': 'absolute'
				, 'top': '30%'
				, 'right': '5%'
				, 'opacity': 0.7
			}
			, 'events':{
				'mouseenter':function(){
					this.setStyle('opacity',1);
				}
				, 'mouseleave':function(){
					this.setStyle('opacity',0.7);
				}
			}
		})).inject(this.container);
	}
	, addLinks: function(b, type, head){
		this.types[this.types.length]=type;
		this.active[type]=[];
		new Element('h2',{
			'html':head
		}).inject(this.block);
		this.links[type] = new Element('ul', {
			'id':type+'List'
		}).inject(this.block);
		$each(b, function(item, id){
			(new Element('li', {
				'html':item.name+' <span class="count">'+(item.count ? '('+item.count+')' : '')+'</span> '+(item.logo ? ' <img src="'+item.logo+'" />' : '')
				, 'id':type+'Link'+'-'+id
				, 'events':{
					'click':function(e){
						this.activate(id, this.types.indexOf(type), e.target, true);
						this.change();
					}.bind(this)
				}
			})).inject(this.links[type]);
			if(item.active){
				this.activate(id, this.types.indexOf(type));
			}
		}.bind(this));
	}
	, activate: function(id, typeId, link, fire){
		var type = this.types[typeId];
		link = link==undefined ? this.links[type].getElement('#'+type+'Link'+'-'+id) : link;
		id = id.toInt();
		if(this.active[type].contains(id)){
			this.active[type].erase(id);
			link.removeClass('active');
		} else {
			this.active[type].include(id);
			link.addClass('active');
		}
	}
	, update: function(active){
		if(active==this.getActive()){
			return;
		}
		active.split(';').each(function(ids, typeId){
			ids = ids=='' ? [] : ids.split(',').map(function(i){ return i.toInt(); });
			var type = this.types[typeId];
			if(this.active[type]){
				this.active[type].each(function(id){
					if(ids&&!ids.contains(id)){
						this.activate(id, typeId);
					}
				}.bind(this));
			}
			ids.each(function(id){
				if(id&&!(this.active[type]&&this.active[type].contains(id))){
					this.activate(id, typeId);
				}
			}.bind(this));
		}.bind(this));
		this.change()
	}
	, change: function(){
		this.fireEvent('change');
	}
	, updateCount: function(id, type, count){
		var counter = this.links[type].getElement('#'+type+'Link'+'-'+id).getElement('span.count');
		if(counter){
			if(count>0){
				counter.set('html', '('+count+')');
			} else {
				counter.set('html', '');
			}
		}
	}
	, getActive: function(){
		var s = [];
		this.types.each(function(t){
			s[s.length]=this.active[t].sort().join(',');
		}.bind(this));
		return s.join(';');
	}
});