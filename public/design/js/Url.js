var Url = new Class({
	Binds: ['setNewContent'],
	Implements: [Options, Events],
	options: {
		wwwLink: '',
		requestQueue: null,
		error404: 'page not found'
	},
	active: '',
	links: [],
	history: null,
	initialize: function(options, active){
		this.setOptions(this.options, options);
		var req = this.lastRequest = new Request.JSON({
			'url': this.options.wwwLink+(this.active.split('?').length>1|this.options.wwwLink.split('?').length>1 ? '&' : '?')+'j'
			, 'method': 'post'
			, onComplete: this.setNewContent
		});
		this.fireEvent('urlRequest', req);
		this.active = $pick(active, this.active);
		this.historyRegister();
	}
	, historyRegister: function(){
		var historyKey = '_';
		this.history = HistoryManager.register(
			historyKey,
			[this.active],
			function(values) {
				this.to(values[0]);
			}.bind(this),
			function(values) {
				return [historyKey, values[0]].join('');
			}.bind(this),
			historyKey + '(.+)'
		);
	}
	, setNewContent: function(c){
		this.fireEvent('newContent', {
			'c':c
		});
	}
	, setActive: function(url, fromHistory){
		var anchors = url.split('#');
		if(anchors[1]){
			this.fireEvent('anchor', anchors[1]);
		}
		url = anchors[0];
		if(this.active==url){
			return;
		}
		if(this.lastRequest){
			this.lastRequest.cancel();
		}
		this.active=url;
		this.fireEvent('changeActive', url);
		this.fireEvent('request');
		this.lastRequest.send({data:{
			'b':this.active
		}});
		if(!fromHistory){
			this.history.setValue(0, this.active);
		}
	}
	, to: function(url){
		this.setActive(url, true);
	}
});