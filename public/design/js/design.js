//activizejam domReady funkceju, kod lopys HTML jer īlodiets
window.addEvent('domready', function domReady(){
    //html>body elements
	var container = document.body;
	//body uzlīkam tukšu HTML, sanuok <body></body>
	container.set('html','');
	//configuracejis parametrs prīkš ceļa nu kurīņa luodēt aplikācejis datus (/aplikaceja/)
	//parametri saglobuoti īkš lang.js datnis
	var path = l('path');
	//ajax pīprasiejumu turietojs, lai nabītu tai, ka vīnlaiceigi jer vairuoki pīprasiejumu (dažim puorlūkim tys napateik)
	var requestQueue = new Request.Queue();
	//
	var activePoint = l('activePoint');
	HistoryManager.initialize({
		iframeSrc: path+'/design/html/blank.html'
	});
	var atmOnMap = new AtmOnMap({
		'activePoint': activePoint
		, el: container
		, onChange: function(coordinates){
			setActive();
		}
	});
//	atmOnMap.addMarkers({
//		'0': {
//			'bank': '1'
//			, 'lat': '56.93'
//			, 'lng': '24.11'
//			, 'name': 'super'
//			, 'address': 'asdf'
//		},
//		'1': {
//			'bank': '1'
//			, 'lat': '56.931'
//			, 'lng': '24.111'
//			, 'name': 'super'
//		}
//	});
	var navigator = new Navigator({el:container});
	var req = new Request.JSON({
		'url': path+'/load/?l=type&j'
		, onComplete: function(r){
			if(r){
				navigator.addLinks(r, 'type', l('type'));
			}
		}
	});
	requestQueue.addRequest('typeRequest', req);
	req.send();
	var reqBank = new Request.JSON({
		'url': path+'/load/?l=bank&j'
		, onComplete: function(r){
			if(r){
				atmOnMap.addIcons(r);
				navigator.addLinks(r, 'bank', l('bank'), 'bankList');
				HistoryManager.start();
				setActive();
			}
		}
	});
	requestQueue.addRequest('bankRequest', reqBank);
	reqBank.send();
	navigator.addEvent('change', function(){
		setActive();
	});
	var url = new Url({
		wwwLink: path+'/load/?l=mark'
		, error404: l('error404')
		, onAddRequest: function(req){
			requestQueue.addRequest('req', req);
		}
		, onChangeActive: function(url){
			var r = url.split(';');
			atmOnMap.setActive(r[0]);
			navigator.update(r.erase(r[0]).join(';'));
//			d('activeUrl:'+url);
		}
		, onNewContent: function(r){
			if(r.c){
				atmOnMap.addMarkers(r.c);
			} else {
				atmOnMap.removeAllMarkers();
			}
		}
	}, getActive());
	function getActive(){
		return atmOnMap.getActive()+';'+navigator.getActive()
	}
	var delay = 0;
	function setActive(){
		$clear(delay);
		delay = url.setActive.delay(1000, url, getActive());
	}
//	HistoryManager.start();
});

