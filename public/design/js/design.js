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
	//aktīvīs punkts uz kartis - suokuma punkts
	var activePoint = l('activePoint');
	//viesturis klase, kas rūpejas, lai struodotu saitis (links) i pūgys - back, forward, refresh
	//tukšīs html vādzeigs prīkš vacīm IE
	HistoryManager.initialize({
		iframeSrc: path+'/design/html/blank.html'
	});
	//klase, kas rūpejās par bankomatu pīvīnuošonu kartei
	var atmOnMap = new AtmOnMap({
		'activePoint': activePoint
		, el: container
		, onChange: function(coordinates){
			setActive();
		}
	});
	// paraugi, kas izvītoj bankomatus
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
    //izveidoj pūdzeņis kartej pa viersu, tai saucamos kategorejis
	var navigator = new Navigator({el:container});
	// pīprasiejuma AJAX turietojs - vīna konkreto pīprasiejuma. Tiks īluodietys kategorejis
	var req = new Request.JSON({
		'url': path+'/load/?l=type&j'
		// davīnojam kategorejis pūdzeņis, kuros jer saņimtys nu servera
		//pīmāram "[{"name":"Rubļa svaidiejs","active":true},{"name":"Klientu servisi","active":false}]"
		, onComplete: function(r){
			if(r){
				navigator.addLinks(r, 'type', l('type'));
			}
		}
	});
	//davīnoj ajax pīprasiejumu rindā - vysi pīprasiejumu teik izpildieti pa vīnam
	requestQueue.addRequest('typeRequest', req);
	//izpildam pīprasiejumu
	req.send();
	//pīprasiejums AJAX turietojs - bankys
	var reqBank = new Request.JSON({
		'url': path+'/load/?l=bank&j'
		//saņemam "{"1":{"name":"lkb","logo":"design\/images\/lkb.png","active":true},"2":{"name":"swb","logo":"design\/images\/swb.png","active":true}}"
		, onComplete: function(r){
			if(r){
			    //davīnojam ikonu
				atmOnMap.addIcons(r);
				//davīnojam navigacejai banku
				navigator.addLinks(r, 'bank', l('bank'), 'bankList');
				//aktivizejam viesturis klasi, kas sekoj pūgom (back, forward utt) i navigacejai (#_ zeimis adresis jūslā)
				HistoryManager.start();
				//aktivizejam izmaiņis (poša fuknceja pakšā, kas nav pareizi :))
				setActive();
			}
		}
	});
	//davīnojam pīprasiejumu ryndai
	requestQueue.addRequest('bankRequest', reqBank);
	//izpildam pīprasiejumu
	reqBank.send();
	//davīnojam eventu (kas klausās) uz izmaiņom navigacejī i aktivizejam funkceju setActive, ka teik fiksietys izmaiņis
	navigator.addEvent('change', function(){
		setActive();
	});
	// pīprasiejumi, kas luodej bankys kartī
	var url = new Url({
		wwwLink: path+'/load/?l=mark'
		, error404: l('error404')
		, onAddRequest: function(req){
		    //davīnojam pīprasiejumu ryndai (to poša īmasla, lai nabītu vīnlaiceigi vaira par vīnu pīprasiejumu)
			requestQueue.addRequest('req', req);
		}
		//izpildās, ka teik fiksiets izmaiņis navigacejī
		, onChangeActive: function(url){
			var r = url.split(';');
			atmOnMap.setActive(r[0]);
			navigator.update(r.erase(r[0]).join(';'));
//			d('activeUrl:'+url);
		}
		, onNewContent: function(r){
			if(r.c){
			    //ka teik saņimts jauns saturs, tod davīnojam jaunys ikoneņis ar bankom
				atmOnMap.addMarkers(r.c);
			} else {
			    //ka nav satura, tod dziešam
				atmOnMap.removeAllMarkers();
			}
		}
	}, getActive());
	//saņem aktīvu saiti nu navigacejis
	function getActive(){
		return atmOnMap.getActive()+';'+navigator.getActive()
	}
	var delay = 0;
	//uzlīk aktīvu saiti
	function setActive(){
		$clear(delay);
		delay = url.setActive.delay(1000, url, getActive());
	}
//	HistoryManager.start();
});

