var AtmOnMap = new Class({
//	Binds: [],
	Implements: [Options, Events],
	options: {
		'activePoint': ''
		, el: document.body
	},
	active: '',
	icons: {},
	markers: {},
	initialize: function(options){
		this.setOptions(this.options, options);
		this.activePoint = this.options.activePoint;
		this.map = new google.maps.Map((new Element('div', {
			'id': 'mapCanvas'
		})).inject(this.options.el), {
			zoom: this.getZoom()
			, center: this.getLatLng()
			, mapTypeId: google.maps.MapTypeId.ROADMAP
		});
		this.mapEvents();
	}
	, getLatLng: function(str){
		var p = str ? str.split(',') : this.activePoint.split(',');
		return new google.maps.LatLng(p[1],p[2]);
	}
	, getZoom: function(str){
		var p = str ? str.split(',') : this.activePoint.split(',');
		return p[0].toInt();
	}
	, setActive: function(str){
		var zoom = this.getZoom(str);
		var latLng = this.getLatLng(str);
		var newPoint = false;
		if(zoom!=this.getZoom()){
			this.map.setZoom(zoom);
			newPoint = true;
		}
		if(!latLng.equals(this.getLatLng())){
			this.map.setCenter(latLng);
			newPoint = true;
		}
		if(newPoint){
			this.activePoint = this.getActive();
			this.change();
		}
	}
	, change: function(){
		this.fireEvent('change', this.getActive());
	}
	, getActive: function(){
		return this.map.getZoom()+','+this.map.getCenter().toUrlValue();
	}
	, mapEvents: function(){
		google.maps.event.addListener(this.map, 'dragend', function(){
			this.change();
		}.bind(this));
		google.maps.event.addListener(this.map, 'zoom_changed', function(){
			this.change();
		}.bind(this));
	}
	, addIcons: function(ic){
		var prevMarkers = this.getMarkers();
		$each(ic, function(item, i){
			this.icons[i]=new google.maps.MarkerImage(
				item.logo
				, new google.maps.Size(20, 22)
				, new google.maps.Point(0,0)
				, new google.maps.Point(0, 22)
			);
		}.bind(this));
	}
	, addMarkers: function(mr){
		var markers = this.getMarkers();
		$each(mr, function(item, i){
			i=item.id.toInt();
			if(!markers.contains(i)){
				var marker=this.markers[i]=new google.maps.Marker({
					map: this.map
					, icon: this.icons[item.bank]
					, title: item.name
					, position: new google.maps.LatLng(item.lat,item.lng)
				});
				google.maps.event.addListener(marker, 'click', function() {
					var infowindow = new google.maps.InfoWindow({
						content: this.formatInfo(item)
					});
					infowindow.open(this.map,marker);
				}.bind(this));
			} else {
				markers.erase(i);
			}
//			this.markers[i].setDraggable(true);
		}.bind(this));
		markers.each(function(i){
			this.removeMarker(i);
		}.bind(this));
	}
	, getMarkers: function(){
		var m = [];
		$each(this.markers, function(item, i){
			i=i.toInt();
			if(item){
				m.include(i);
			}
		});
		return m;
	}
	, removeMarker: function(i){
		this.markers[i].setVisible(false);
		this.markers[i] = undefined;
	}
	, removeAllMarkers: function(){
		var markers = this.getMarkers();
		markers.each(function(i){
			this.removeMarker(i);
		}.bind(this));
	}
	, formatInfo: function(p){
		var c = '';
		$each({
			'address':'Adrese'
			,'working':'Darba laiks'
			,'comment':'Paskaidrojums'
		}, function(item,index){
			if(p[index]&&p[index]!=''){
				c+='<p><strong>'+item+'</strong>: '+p[index]+'</p>'
			}
		});
		return c;
	}
});