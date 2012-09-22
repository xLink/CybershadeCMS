
// Begin: Source/Behaviors/Behavior.BS.Alert.js
/*
---

name: Behavior.BS.Alert

description: This file just depends on the Fx.Reveal delegator in More-Behaviors to ensure you get it if you load the entire Bootstrap JS package.

license: MIT-style license.

authors: [Aaron Newton]

requires:
 - More-Behaviors/Delegator.Nix

provides: [Behavior.BS.Alert]

...
*/

// Begin: Source/Core/Clientcide.js
/*
---

name: Clientcide

description: The Clientcide namespace.

license: MIT-style license.

provides: Clientcide

...
*/
var Clientcide = {
	version: '3.0.10',
	assetLocation: "http://github.com/anutron/clientcide/raw/master/Assets",
	setAssetLocation: function(baseHref) {
		Clientcide.assetLocation = baseHref;
		if (Clientcide.preloaded) Clientcide.preLoadCss();
	},
	preLoadCss: function(){
		if (window.StickyWin && StickyWin.ui) StickyWin.ui();
		if (window.StickyWin && StickyWin.pointy) StickyWin.pointy();
		Clientcide.preloaded = true;
		return true;
	},
	preloaded: false
};
(function(){
	if (!window.addEvent) return;
	var preload = function(){
		if (window.dbug) dbug.log('preloading clientcide css');
		if (!Clientcide.preloaded) Clientcide.preLoadCss();
	};
	window.addEvent('domready', preload);
	window.addEvent('load', preload);
})();
setCNETAssetBaseHref = Clientcide.setAssetLocation;


// Begin: Source/Layout/MooScroller.js
/*
---
name: MooScroller

description: Recreates the standard scrollbar behavior for elements with overflow but using DOM elements so that the scroll bar elements are completely styleable by css.

license: MIT-Style License

requires: [Core/Class.Extras, Core/Element.Dimensions, Core/Element.Event, Core/Element.Style, Core/Slick.Finder, Clientcide]

provides: MooScroller

...
*/
var MooScroller = new Class({
	Implements: [Options, Events],
	options: {
		maxThumbSize: 10,
		mode: 'vertical',
		width: 0, //required only for mode: horizontal
		scrollSteps: 10,
		wheel: true,
		scrollLinks: {
			forward: 'scrollForward',
			back: 'scrollBack'
		},
		hideWhenNoOverflow: true
//		onScroll: function(){},
//		onPage: function(){}
	},

	initialize: function(content, knob, options){
		this.setOptions(options);
		this.horz = (this.options.mode == "horizontal");

		this.content = document.id(content).setStyle('overflow', 'hidden');
		this.knob = document.id(knob);
		this.track = this.knob.getParent();
		this.setPositions();

		if (this.horz && this.options.width) {
			this.wrapper = new Element('div');
			this.content.getChildren().each(function(child){
				this.wrapper.adopt(child);
			}, this);
			this.wrapper.inject(this.content).setStyle('width', this.options.width);
		}


		this.bound = {
			'start': this.start.bind(this),
			'end': this.end.bind(this),
			'drag': this.drag.bind(this),
			'wheel': this.wheel.bind(this),
			'page': this.page.bind(this)
		};

		this.position = {};
		this.mouse = {};
		this.update();
		this.attach();

		this.clearScroll = function (){
			clearInterval(this.scrolling);
		}.bind(this);
		['forward','back'].each(function(direction) {
			var lnk = document.id(this.options.scrollLinks[direction]);
			if (lnk) {
				lnk.addEvents({
					mousedown: function() {
						this.scrolling = this[direction].periodical(50, this);
					}.bind(this),
					mouseup: this.clearScroll.bind(this),
					click: this.clearScroll.bind(this)
				});
			}
		}, this);
		this.knob.addEvent('click', this.clearScroll.bind(this));
		window.addEvent('domready', function(){
			try {
				document.id(document.body).addEvent('mouseup', this.clearScroll);
			}catch(e){}
		}.bind(this));
	},
	setPositions: function(){
		[this.track, this.knob].each(function(el){
			if (el.getStyle('position') == 'static') el.setStyle('position','relative');
		});

	},
	toElement: function(){
		return this.content;
	},
	update: function(){
		var plain = this.horz?'Width':'Height';
		this.contentSize = this.content['offset'+plain];
		this.contentScrollSize = this.content['scroll'+plain];
		this.trackSize = this.track['offset'+plain];

		this.contentRatio = this.contentSize / this.contentScrollSize;

		this.knobSize = (this.trackSize * this.contentRatio).limit(this.options.maxThumbSize, this.trackSize);

		if (this.options.hideWhenNoOverflow) {
			this.hidden = this.knobSize == this.trackSize;
			this.track.setStyle('opacity', this.hidden?0:1);
		}

		this.scrollRatio = this.contentScrollSize / this.trackSize;
		this.knob.setStyle(plain.toLowerCase(), this.knobSize);

		this.updateThumbFromContentScroll();
		this.updateContentFromThumbPosition();
	},

	updateContentFromThumbPosition: function(){
		this.content[this.horz?'scrollLeft':'scrollTop'] = this.position.now * this.scrollRatio;
	},

	updateThumbFromContentScroll: function(){
		this.position.now = (this.content[this.horz?'scrollLeft':'scrollTop'] / this.scrollRatio).limit(0, (this.trackSize - this.knobSize));
		this.knob.setStyle(this.horz?'left':'top', this.position.now);
	},

	attach: function(){
		this.knob.addEvent('mousedown', this.bound.start);
		if (this.options.wheel) {
			this.content.addEvent('mousewheel', this.bound.wheel);
			this.track.addEvent('mousewheel', this.bound.wheel);
		}
		this.track.addEvent('mouseup', this.bound.page);
	},

	detach: function(){
		this.knob.removeEvent('mousedown', this.bound.start);
		if (this.options.wheel) {
			this.content.removeEvent('mousewheel', this.bound.wheel);
			this.track.removeEvent('mousewheel', this.bound.wheel);
		}
		this.track.removeEvent('mouseup', this.bound.page);
		document.id(document.body).removeEvent('mouseup', this.clearScroll);
	},

	wheel: function(event){
		if (this.hidden) return;
		this.scroll(-(event.wheel * this.options.scrollSteps));
		this.updateThumbFromContentScroll();
		event.stop();
	},

	scroll: function(steps){
		steps = steps||this.options.scrollSteps;
		this.content[this.horz?'scrollLeft':'scrollTop'] += steps;
		this.updateThumbFromContentScroll();
		this.fireEvent('onScroll', steps);
	},
	forward: function(steps){
		this.scroll(steps);
	},
	back: function(steps){
		steps = steps||this.options.scrollSteps;
		this.scroll(-steps);
	},

	page: function(event){
		var axis = this.horz?'x':'y';
		var forward = (event.page[axis] > this.knob.getPosition()[axis]);
		this.scroll((forward?1:-1)*this.content['offset'+(this.horz?'Width':'Height')]);
		this.updateThumbFromContentScroll();
		this.fireEvent('onPage', forward);
		event.stop();
	},


	start: function(event){
		var axis = this.horz?'x':'y';
		this.mouse.start = event.page[axis];
		this.position.start = this.knob.getStyle(this.horz?'left':'top').toInt();
		document.addEvent('mousemove', this.bound.drag);
		document.addEvent('mouseup', this.bound.end);
		this.knob.addEvent('mouseup', this.bound.end);
		event.stop();
	},

	end: function(event){
		document.removeEvent('mousemove', this.bound.drag);
		document.removeEvent('mouseup', this.bound.end);
		this.knob.removeEvent('mouseup', this.bound.end);
		event.stop();
	},

	drag: function(event){
		var axis = this.horz?'x':'y';
		this.mouse.now = event.page[axis];
		this.position.now = (this.position.start + (this.mouse.now - this.mouse.start)).limit(0, (this.trackSize - this.knobSize));
		this.updateContentFromThumbPosition();
		this.updateThumbFromContentScroll();
		event.stop();
	}

});


// Begin: Source/Core/dbug.js
/*
---

name: dbug

description: A wrapper for Firebug console.* statements.

license: MIT-style license.

authors:
- Aaron Newton

provides: dbug

...
*/
var dbug = {
	logged: [],
	timers: {},
	firebug: false,
	enabled: false,
	log: function() {
		dbug.logged.push(arguments);
	},
	nolog: function(msg) {
		dbug.logged.push(arguments);
	},
	time: function(name){
		dbug.timers[name] = new Date().getTime();
	},
	timeEnd: function(name){
		if (dbug.timers[name]) {
			var end = new Date().getTime() - dbug.timers[name];
			dbug.timers[name] = false;
			dbug.log('%s: %s', name, end);
		} else dbug.log('no such timer: %s', name);
	},
	enable: function(silent) {
		var con = window.firebug ? firebug.d.console.cmd : window.console;

		if((!!window.console && !!window.console.warn) || window.firebug) {
			try {
				dbug.enabled = true;
				dbug.log = function(){
						try {
							(con.debug || con.log).apply(con, arguments);
						} catch(e) {
							console.log(Array.slice(arguments));
						}
				};
				dbug.time = function(){
					con.time.apply(con, arguments);
				};
				dbug.timeEnd = function(){
					con.timeEnd.apply(con, arguments);
				};
				if(!silent) dbug.log('enabling dbug');
				for(var i=0;i<dbug.logged.length;i++){ dbug.log.apply(con, dbug.logged[i]); }
				dbug.logged=[];
			} catch(e) {
				dbug.enable.delay(400);
			}
		}
	},
	disable: function(){
		if(dbug.firebug) dbug.enabled = false;
		dbug.log = dbug.nolog;
		dbug.time = function(){};
		dbug.timeEnd = function(){};
	},
	cookie: function(set){
		var value = document.cookie.match('(?:^|;)\\s*jsdebug=([^;]*)');
		var debugCookie = value ? unescape(value[1]) : false;
		if((set == null && debugCookie != 'true') || (set != null && set)) {
			dbug.enable();
			dbug.log('setting debugging cookie');
			var date = new Date();
			date.setTime(date.getTime()+(24*60*60*1000));
			document.cookie = 'jsdebug=true;expires='+date.toGMTString()+';path=/;';
		} else dbug.disableCookie();
	},
	disableCookie: function(){
		dbug.log('disabling debugging cookie');
		document.cookie = 'jsdebug=false;path=/;';
	},
	conditional: function(fn, fnIfError) {
		if (dbug.enabled) {
			return fn();
		} else {
			try {
				return fn();
			} catch(e) {
				if (fnIfError) fnIfError(e);
			}
		}
	}
};

(function(){
	var fb = !!window.console || !!window.firebug;
	var con = window.firebug ? window.firebug.d.console.cmd : window.console;
	var debugMethods = ['debug','info','warn','error','assert','dir','dirxml'];
	var otherMethods = ['trace','group','groupEnd','profile','profileEnd','count'];
	function set(methodList, defaultFunction) {

		var getLogger = function(method) {
			return function(){
				con[method].apply(con, arguments);
			};
		};

		for(var i = 0; i < methodList.length; i++){
			var method = methodList[i];
			if (fb && con[method]) {
				dbug[method] = getLogger(method);
			} else {
				dbug[method] = defaultFunction;
			}
		}
	};
	set(debugMethods, dbug.log);
	set(otherMethods, function(){});
})();
if ((!!window.console && !!window.console.warn) || window.firebug){
	dbug.firebug = true;
	var value = document.cookie.match('(?:^|;)\\s*jsdebug=([^;]*)');
	var debugCookie = value ? unescape(value[1]) : false;
	if(window.location.href.indexOf("jsdebug=true")>0 || debugCookie=='true') dbug.enable();
	if(debugCookie=='true')dbug.log('debugging cookie enabled');
	if(window.location.href.indexOf("jsdebugCookie=true")>0){
		dbug.cookie();
		if(!dbug.enabled)dbug.enable();
	}
	if(window.location.href.indexOf("jsdebugCookie=false")>0)dbug.disableCookie();
}


// Begin: Source/UI/StyleWriter.js
/*
---
name: StyleWriter

description: Provides a simple method for injecting a css style element into the DOM if it's not already present.

license: MIT-Style License

requires: [Core/Class, Core/DomReady, Core/Element, dbug]

provides: StyleWriter

...
*/

var StyleWriter = new Class({
	createStyle: function(css, id) {
		window.addEvent('domready', function(){
			try {
				if (document.id(id) && id) return;
				var style = new Element('style', {id: id||''}).inject($$('head')[0]);
				if (Browser.ie) style.styleSheet.cssText = css;
				else style.set('text', css);
			}catch(e){dbug.log('error: %s',e);}
		}.bind(this));
	}
});

// Begin: Source/UI/StickyWin.js
/*
---

name: StickyWin

description: Creates a div within the page with the specified contents at the location relative to the element you specify; basically an in-page popup maker.

license: MIT-Style License

requires: [
  Core/DomReady,
  Core/Slick.Finder,
  More/Element.Position,
  More/Class.Binds,
  More/Element.Shortcuts,
  More/Element.Pin,
  More/IframeShim,
  More/Object.Extras,
  Clientcide,
  StyleWriter
]

provides: [StickyWin, StickyWin.Stacker]
...
*/


var StickyWin = new Class({
	Binds: ['destroy', 'hide', 'togglepin', 'esc'],
	Implements: [Options, Events, StyleWriter],
	options: {
//		onDisplay: function(){},
//		onClose: function(){},
//		onDestroy: function(){},
		closeClassName: 'closeSticky',
		pinClassName: 'pinSticky',
		content: '',
		zIndex: 10000,
		className: '',
//		id: ... set above in initialize function
/*  	these are the defaults for Element.position anyway
		************************************************
		edge: false, //see Element.position
		position: 'center', //center, corner == upperLeft, upperRight, bottomLeft, bottomRight
		offset: {x:0,y:0},
		relativeTo: document.body, */
		width: false,
		height: false,
		timeout: -1,
		allowMultipleByClass: true,
		allowMultiple: true,
		showNow: true,
		useIframeShim: true,
		iframeShimSelector: '',
		destroyOnClose: false,
		closeOnClickOut: false,
		closeOnEsc: false,
		getWindowManager: function(){ return StickyWin.WM; }
	},

	css: '.SWclearfix:after {content: "."; display: block; height: 0; clear: both; visibility: hidden;}'+
		 '.SWclearfix {display: inline-table;} * html .SWclearfix {height: 1%;} .SWclearfix {display: block;}',

	initialize: function(options){
		this.options.inject = this.options.inject || {
			target: document.body,
			where: 'bottom'
		};
		this.setOptions(options);
		this.windowManager = this.options.getWindowManager();
		this.id = this.options.id || 'StickyWin_'+new Date().getTime();
		this.makeWindow();
		if (this.windowManager) this.windowManager.add(this);

		if (this.options.content) this.setContent(this.options.content);
		if (this.options.timeout > 0) {
			this.addEvent('onDisplay', function(){
				this.hide.delay(this.options.timeout, this);
			}.bind(this));
		}
		//add css for clearfix
		this.createStyle(this.css, 'StickyWinClearFix');
		if (this.options.closeOnClickOut || this.options.closeOnEsc) this.attach();
		if (this.options.destroyOnClose) this.addEvent('close', this.destroy);
		if (this.options.showNow) this.show();
	},
	toElement: function(){
		return this.element;
	},
	attach: function(dettach){
		var method = dettach ? 'removeEvents' : 'addEvents';
		var events = {};
		if (this.options.closeOnClickOut) events.click = this.esc;
		if (this.options.closeOnEsc) events.keyup = this.esc;
		document[method](events);
	},
	esc: function(e) {
		if (e.key == "esc") this.hide();
		if (e.type == "click" && this.element != e.target && !this.element.contains(e.target)) this.hide();
	},
	makeWindow: function(){
		this.destroyOthers();
		if (!document.id(this.id)) {
			this.win = new Element('div', {
				id: this.id
			}).addClass(this.options.className).addClass('StickyWinInstance').addClass('SWclearfix').setStyles({
			 	display: 'none',
				position: 'absolute',
				zIndex: this.options.zIndex
			}).inject(this.options.inject.target, this.options.inject.where).store('StickyWin', this);
		} else this.win = document.id(this.id);
		this.element = this.win;
		if (this.options.width && typeOf(this.options.width.toInt())=="number") this.win.setStyle('width', this.options.width.toInt());
		if (this.options.height && typeOf(this.options.height.toInt())=="number") this.win.setStyle('height', this.options.height.toInt());
		return this;
	},
	show: function(suppressEvent){
		this.showWin();
		if (!suppressEvent) this.fireEvent('display');
		if (this.options.useIframeShim) this.showIframeShim();
		this.visible = true;
		return this;
	},
	showWin: function(){
		if (this.windowManager) this.windowManager.focus(this);
		if (!this.positioned) this.position();
		this.win.show();
	},
	hide: function(suppressEvent){
		if (typeOf(suppressEvent) == "event" || !suppressEvent) this.fireEvent('close');
		this.hideWin();
		if (this.options.useIframeShim) this.hideIframeShim();
		this.visible = false;
		return this;
	},
	hideWin: function(){
		this.win.setStyle('display','none');
	},
	destroyOthers: function() {
		if (!this.options.allowMultipleByClass || !this.options.allowMultiple) {
			$$('div.StickyWinInstance').each(function(sw) {
				if (!this.options.allowMultiple || (!this.options.allowMultipleByClass && sw.hasClass(this.options.className)))
					sw.retrieve('StickyWin').destroy();
			}, this);
		}
	},
	setContent: function(html) {
		if (this.win.getChildren().length>0) this.win.empty();
		if (typeOf(html) == "string") this.win.set('html', html);
		else if (document.id(html)) this.win.adopt(html);
		this.win.getElements('.'+this.options.closeClassName).each(function(el){
			el.addEvent('click', this.hide);
		}, this);
		this.win.getElements('.'+this.options.pinClassName).each(function(el){
			el.addEvent('click', this.togglepin);
		}, this);
		return this;
	},
	position: function(options){
		this.positioned = true;
		this.setOptions(options);
		this.win.position(
			Object.cleanValues({
				allowNegative: [this.options.allowNegative, this.options.relativeTo != document.body].pick(),
				relativeTo: this.options.relativeTo,
				position: this.options.position,
				offset: this.options.offset,
				edge: this.options.edge
			})
		);
		if (this.shim) this.shim.position();
		return this;
	},
	pin: function(pin) {
		if (!this.win.pin) {
			dbug.log('you must include element.pin.js!');
			return this;
		}
		this.pinned = pin != null && pin;
		this.win.pin(pin);
		return this;
	},
	unpin: function(){
		return this.pin(false);
	},
	togglepin: function(){
		return this.pin(!this.pinned);
	},
	makeIframeShim: function(){
		if (!this.shim){
			var el = (this.options.iframeShimSelector)?this.win.getElement(this.options.iframeShimSelector):this.win;
			this.shim = new IframeShim(el, {
				display: false,
				name: 'StickyWinShim'
			});
		}
	},
	showIframeShim: function(){
		if (this.options.useIframeShim) {
			this.makeIframeShim();
			this.shim.show();
		}
	},
	hideIframeShim: function(){
		if (this.shim) this.shim.hide();
	},
	destroy: function(){
		this.destroyed = true;
		this.attach(true);
		if (this.windowManager) this.windowManager.remove(this);
		if (this.win) this.win.destroy();
		if (this.options.useIframeShim && this.shim) this.shim.destroy();
		if (document.id('modalOverlay')) document.id('modalOverlay').destroy();
		this.fireEvent('destroy');
	}
});

StickyWin.Stacker = new Class({
	Implements: [Options, Events],
	Binds: ['click'],
	instances: [],
	options: {
		zIndexBase: 9000
	},
	initialize: function(options) {
		this.setOptions(options);
	},
	add: function(sw) {
		this.instances.include(sw);
		$(sw).addEvent('mousedown', this.click);
	},
	click: function(e) {
		this.instances.each(function(sw){
			var el = $(sw);
			if (el == e.target || el.contains($(e.target))) this.focus(sw);
		}, this);
	},
	focus: function(instance){
		if (this.focused == instance) return;
		this.focused = instance;
		if (instance) this.instances.erase(instance).push(instance);
		this.instances.each(function(current, i){
			$(current).setStyle('z-index', this.options.zIndexBase + i);
		}, this);
		this.focused = instance;
	},
	remove: function(sw) {
		this.instances.erase(sw);
		$(sw).removeEvent('click', this.click);
	}
});
StickyWin.WM = new StickyWin.Stacker();

// Begin: Source/UI/StickyWin.UI.js
 /*
---
name: StickyWin.UI

description: Creates an html holder for in-page popups using a default style.

license: MIT-Style License

requires: [Core/Element.Style, More/String.Extras, StyleWriter, StickyWin]

provides: StickyWin.UI
...
*/
StickyWin.UI = new Class({
	Implements: [Options, StyleWriter],
	options: {
		width: 300,
		css: "div.DefaultStickyWin {font-family:verdana; font-size:11px; line-height: 13px;position: relative;}"+
			"div.DefaultStickyWin div.top{-moz-user-select: none;-khtml-user-select: none;}"+
			"div.DefaultStickyWin div.top_ul{background:url({%baseHref%}full.png) top left no-repeat; height:30px; width:15px; float:left}"+
			"div.DefaultStickyWin div.top_ur{position:relative; left:0px !important; left:-4px; background:url({%baseHref%}full.png) top right !important; height:30px; margin:0px 0px 0px 15px !important; margin-right:-4px; padding:0px}"+
			"div.DefaultStickyWin h1.caption{clear: none !important; margin:0px !important; overflow: hidden; padding:0 !important; font-weight:bold; color:#555; font-size:14px !important; position:relative; top:8px !important; left:5px !important; float: left; height: 22px !important;}"+
			"div.DefaultStickyWin div.middle, div.DefaultStickyWin div.closeBody {background:url({%baseHref%}body.png) top left repeat-y; margin:0px 20px 0px 0px !important;	margin-bottom: -3px; position: relative;	top: 0px !important; top: -3px;}"+
			"div.DefaultStickyWin div.body{background:url({%baseHref%}body.png) top right repeat-y; padding:8px 23px 8px 0px !important; margin-left:5px !important; position:relative; right:-20px !important; z-index: 1;}"+
			"div.DefaultStickyWin div.bottom{clear:both;}"+
			"div.DefaultStickyWin div.bottom_ll{background:url({%baseHref%}full.png) bottom left no-repeat; width:15px; height:15px; float:left}"+
			"div.DefaultStickyWin div.bottom_lr{background:url({%baseHref%}full.png) bottom right; position:relative; left:0px !important; left:-4px; margin:0px 0px 0px 15px !important; margin-right:-4px; height:15px}"+
			"div.DefaultStickyWin div.closeButtons{text-align: center; background:url({%baseHref%}body.png) top right repeat-y; padding: 4px 30px 8px 0px; margin-left:5px; position:relative; right:-20px}"+
			"div.DefaultStickyWin a.button:hover{background:url({%baseHref%}big_button_over.gif) repeat-x}"+
			"div.DefaultStickyWin a.button {background:url({%baseHref%}big_button.gif) repeat-x; margin: 2px 8px 2px 8px; padding: 2px 12px; cursor:pointer; border: 1px solid #999 !important; text-decoration:none; color: #000 !important;}"+
			"div.DefaultStickyWin div.closeButton{width:13px; height:13px; background:url({%baseHref%}closebtn.gif) no-repeat; position: absolute; right: 0px; margin:10px 15px 0px 0px !important; cursor:pointer;top:0px}"+
			"div.DefaultStickyWin div.dragHandle {	width: 11px;	height: 25px;	position: relative;	top: 5px;	left: -3px;	cursor: move;	background: url({%baseHref%}drag_corner.gif); float: left;}",
		cornerHandle: false,
		cssClass: '',
		buttons: [],
		cssId: 'defaultStickyWinStyle',
		cssClassName: 'DefaultStickyWin',
		closeButton: true
/*	These options are deprecated:
		closeTxt: false,
		onClose: function(){},
		confirmTxt: false,
		onConfirm: function(){}	*/
	},
	initialize: function() {
		var args = this.getArgs(arguments);
		this.setOptions(args.options);
		this.legacy();
		var css = this.options.css.substitute({baseHref: this.options.baseHref || Clientcide.assetLocation + '/stickyWinHTML/'}, /\\?\{%([^}]+)%\}/g);
		if (Browser.ie) css = css.replace(/png/g, 'gif');
		this.createStyle(css, this.options.cssId);
		this.build();
		if (args.caption || args.body) this.setContent(args.caption, args.body);
	},
	toElement: function(){
		return this.element;
	},
	getArgs: function(){
		return StickyWin.UI.getArgs.apply(this, arguments);
	},
	legacy: function(){
		var opt = this.options; //saving bytes
		//legacy support
		if (opt.confirmTxt) opt.buttons.push({text: opt.confirmTxt, onClick: opt.onConfirm || function(){}});
		if (opt.closeTxt) opt.buttons.push({text: opt.closeTxt, onClick: opt.onClose || function(){}});
	},
	build: function(){
		var opt = this.options;

		var container = new Element('div', {
			'class': opt.cssClassName
		});
		if (opt.width) container.setStyle('width', opt.width);
		this.element = container;
		this.element.store('StickyWinUI', this);
		if (opt.cssClass) container.addClass(opt.cssClass);


		var bodyDiv = new Element('div').addClass('body');
		this.body = bodyDiv;

		var top_ur = new Element('div').addClass('top_ur');
		this.top_ur = top_ur;
		this.top = new Element('div').addClass('top').adopt(
				new Element('div').addClass('top_ul')
			).adopt(top_ur);
		container.adopt(this.top);

		if (opt.cornerHandle) new Element('div').addClass('dragHandle').inject(top_ur, 'top');

		//body
		container.adopt(new Element('div').addClass('middle').adopt(bodyDiv));
		//close buttons
		if (opt.buttons.length > 0){
			var closeButtons = new Element('div').addClass('closeButtons');
			opt.buttons.each(function(button){
				if (button.properties && button.properties.className){
					button.properties['class'] = button.properties.className;
					delete button.properties.className;
				}
				var properties = Object.merge({'class': 'closeSticky'}, button.properties);
				new Element('a').addEvent('click', button.onClick || function(){})
					.appendText(button.text).inject(closeButtons).set(properties).addClass('button');
			});
			container.adopt(new Element('div').addClass('closeBody').adopt(closeButtons));
		}
		//footer
		container.adopt(
			new Element('div').addClass('bottom').adopt(
					new Element('div').addClass('bottom_ll')
				).adopt(
					new Element('div').addClass('bottom_lr')
			)
		);
		if (this.options.closeButton) container.adopt(new Element('div').addClass('closeButton').addClass('closeSticky'));
		return this;
	},
	setCaption: function(caption) {
		this.caption = caption;
		if (!this.h1) {
			this.makeCaption(caption);
		} else {
			if (document.id(caption)) this.h1.adopt(caption);
			else this.h1.set('html', caption);
		}
		return this;
	},
	makeCaption: function(caption) {
		if (!caption) return this.destroyCaption();
		var opt = this.options;
		this.h1 = new Element('h1').addClass('caption');
		if (opt.width) this.h1.setStyle('width', (opt.width.toInt()-(opt.cornerHandle?55:40)-(opt.closeButton?10:0)));
		this.setCaption(caption);
		this.top_ur.adopt(this.h1);
		if (!this.options.cornerHandle) this.h1.addClass('dragHandle');
		return this;
	},
	destroyCaption: function(){
		if (this.h1) {
			this.h1.destroy();
			this.h1 = null;
		}
		return this;
	},
	setContent: function(){
		var args = this.getArgs.apply(this, arguments);
		var caption = args.caption;
		var body = args.body;
		this.setCaption(caption);
		if (document.id(body)) this.body.empty().adopt(body);
		else this.body.set('html', body);
		return this;
	}
});
StickyWin.UI.getArgs = function(){
	var input = typeOf(arguments[0]) == "arguments"?arguments[0]:arguments;
	if (Browser.opera && 1 === input.length) input = input[0];

	var cap = input[0], bod = input[1];
	var args = Array.link(input, {options: Type.isObject});
	if (input.length == 3 || (!args.options && input.length == 2)) {
		args.caption = cap;
		args.body = bod;
	} else if ((typeOf(bod) == 'object' || !bod) && cap && typeOf(cap) != 'object'){
		args.body = cap;
	}
	return args;
};

StickyWin.ui = function(caption, body, options){
	return document.id(new StickyWin.UI(caption, body, options));
};


// Begin: Source/UI/StickyWin.UI.Pointy.js
/*
---
name: StickyWin.UI.Pointy

description: Creates an html holder for in-page popups using a default style - this one including a pointer in the specified direction.

license: MIT-Style License

requires: [More/Element.Shortcuts, More/Element.Position, StickyWin.UI]

provides: [StickyWin.UI.Pointy, StickyWin.UI.pointy]
...
*/
StickyWin.UI.Pointy = new Class({
	Extends: StickyWin.UI,
	options: {
		theme: 'dark',
		themes: {
			dark: {
				bgColor: '#333',
				fgColor: '#ddd',
				imgset: 'dark'
			},
			light: {
				bgColor: '#ccc',
				fgColor: '#333',
				imgset: 'light'
			}
		},
		css: "div.DefaultPointyTip {vertical-align: auto; position: relative;}"+
		"div.DefaultPointyTip * {text-align:left !important}"+
		"div.DefaultPointyTip .pointyWrapper div.body{background: {%bgColor%}; color: {%fgColor%}; left: 0px; right: 0px !important;padding:  0px 10px !important;margin-left: 0px !important;font-family: verdana;font-size: 11px;line-height: 13px;position: relative;}"+
		"div.DefaultPointyTip .pointyWrapper div.top {position: relative;height: 25px; overflow: visible;}"+
		"div.DefaultPointyTip .pointyWrapper div.top_ul{background: url({%baseHref%}{%imgset%}_back.png) top left no-repeat;width: 8px;height: 25px; position: absolute; left: 0px;}"+
		"div.DefaultPointyTip .pointyWrapper div.top_ur{background: url({%baseHref%}{%imgset%}_back.png) top right !important;margin: 0 0 0 8px !important;height: 25px;position: relative;left: 0px !important;padding: 0;}"+
		"div.DefaultPointyTip .pointyWrapper h1.caption{color: {%fgColor%};left: 0px !important;top: 4px !important;clear: none !important;overflow: hidden;font-weight: 700;font-size: 12px !important;position: relative;float: left;height: 22px !important;margin: 0 !important;padding: 0 !important;}"+
		"div.DefaultPointyTip .pointyWrapper div.middle, div.DefaultPointyTip .pointyWrapper div.closeBody{background:  {%bgColor%};margin: 0 0px 0 0 !important;position: relative;top: 0 !important;}"+
		"div.DefaultPointyTip .pointyWrapper div.middle {min-height: 16px; background:  {%bgColor%};margin: 0 0px 0 0 !important;position: relative;top: 0 !important;}"+
		"div.DefaultPointyTip .pointyWrapper div.bottom {clear: both; width: 100% !important; background: none; height: 6px} "+
		"div.DefaultPointyTip .pointyWrapper div.bottom_ll{font-size:1; background: url({%baseHref%}{%imgset%}_back.png) bottom left no-repeat;width: 6px;height: 6px;position: absolute; left: 0px;}"+
		"div.DefaultPointyTip .pointyWrapper div.bottom_lr{font-size:1; background: url({%baseHref%}{%imgset%}_back.png) bottom right;height: 6px;margin: 0 0 0 6px !important;position: relative;left: 0 !important;}"+
		"div.DefaultPointyTip .pointyWrapper div.noCaption{ height: 6px; overflow: hidden}"+
		"div.DefaultPointyTip .pointyWrapper div.closeButton{width:13px; height:13px; background:url({%baseHref%}{%imgset%}_x.png) no-repeat; position: absolute; right: 0px; margin:0px !important; cursor:pointer; z-index: 1; top: 4px;}"+
		"div.DefaultPointyTip .pointyWrapper div.pointyDivot {background: url({%divot%}) no-repeat;}",
		divot: '{%baseHref%}{%imgset%}_divot.png',
		divotSize: 22,
		direction: 12,
		cssId: 'defaultPointyTipStyle',
		cssClassName: 'DefaultPointyTip'
	},
	initialize: function() {
		var args = this.getArgs(arguments);
		this.setOptions(args.options);
		Object.append(this.options, this.options.themes[this.options.theme]);
		this.options.baseHref = this.options.baseHref || Clientcide.assetLocation + '/PointyTip/';
		this.options.divot = this.options.divot.substitute(this.options, /\\?\{%([^}]+)%\}/g);
		if (Browser.ie) this.options.divot = this.options.divot.replace(/png/g, 'gif');
		this.options.css = this.options.css.substitute(this.options, /\\?\{%([^}]+)%\}/g);
		if (args.options && args.options.theme) {
			while (!this.id) {
				var id = Number.random(0, 999999999);
				if (!StickyWin.UI.Pointy[id]) {
					StickyWin.UI.Pointy[id] = this;
					this.id = id;
				}
			}
			this.options.css = this.options.css.replace(/div\.DefaultPointyTip/g, "div#pointy_"+this.id);
			this.options.cssId = "pointyTipStyle_" + this.id;
		}
		if (typeOf(this.options.direction) == 'string') {
			var map = {
				left: 9,
				right: 3,
				up: 12,
				down: 6
			};
			this.options.direction = map[this.options.direction];
		}

		this.parent(args.caption, args.body, this.options);
		if (this.id) document.id(this).set('id', "pointy_"+this.id);
	},
	build: function(){
		this.parent();
		var opt = this.options;
		this.pointyWrapper = new Element('div', {
			'class': 'pointyWrapper'
		}).inject(document.id(this));
		document.id(this).getChildren().each(function(el){
			if (el != this.pointyWrapper) this.pointyWrapper.grab(el);
		}, this);

		var w = opt.divotSize;
		var h = w;
		var left = (opt.width.toInt() - opt.divotSize)/2;
		var orient = function(){
			switch(opt.direction) {
				case 12: case 1: case 11:
					return {
						height: h/2
					};
				case 5: case 6: case 7:
					return {
						height: h/2,
						backgroundPosition: '0 -'+h/2+'px'
					};
				case 8: case 9: case 10:
					return {
						width: w/2
					};
				case 2: case 3: case 4:
					return {
						width: w/2,
						backgroundPosition: '100%'
					};
			};
		};
		this.pointer = new Element('div', {
			styles: Object.append({
				width: w,
				height: h,
				overflow: 'hidden'
			}, orient()),
			'class': 'pointyDivot pointy_'+opt.direction
		}).inject(this.pointyWrapper);
	},
	expose: function(){
		if (document.id(this).getStyle('display') != 'none' &&
		  document.body != document.id(this) &&
		  document.id(document.body).contains(document.id(this))) return function(){};
		document.id(this).setStyles({
			visibility: 'hidden',
			position: 'absolute'
		});
		var dispose;
		if (document.body != document.id(this) && !document.body.contains(document.id(this))) {
			document.id(this).inject(document.body);
			dispose = true;
		}
		return (function(){
			if (dispose) document.id(this).dispose();
			document.id(this).setStyles({
				visibility: 'visible',
				position: 'relative'
			});
		}).bind(this);
	},
	positionPointer: function(options){
		if (!this.pointer) return;
		var opt = options || this.options;
		var pos;
		var d = opt.direction;
		switch (d){
			case 12: case 1: case 11:
				pos = {
					edge: {x: 'center', y: 'bottom'},
					position: {
						x: d==12?'center':d==1?'right':'left',
						y: 'top'
					},
					offset: {
						x: (d==12?0:d==1?-1:1)*opt.divotSize,
						y: 1
					}
				};
				break;
			case 2: case 3: case 4:
				pos = {
					edge: {x: 'left', y: 'center'},
					position: {
						x: 'right',
						y: d==3?'center':d==2?'top':'bottom'
					},
					offset: {
						x: -1,
						y: (d==3?0:d==4?-1:1)*opt.divotSize
					}
				};
				break;
			case 5: case 6: case 7:
				pos = {
					edge: {x: 'center', y: 'top'},
					position: {
						x: d==6?'center':d==5?'right':'left',
						y: 'bottom'
					},
					offset: {
						x: (d==6?0:d==5?-1:1)*opt.divotSize,
						y: -1
					}
				};
				break;
			case 8: case 9: case 10:
				pos = {
					edge: {x: 'right', y: 'center'},
					position: {
						x: 'left',
						y: d==9?'center':d==10?'top':'bottom'
					},
					offset: {
						x: 1,
						y: (d==9?0:d==8?-1:1)*opt.divotSize
					}
				};
				break;
		};
		var putItBack = this.expose();
		this.pointer.position(Object.append({
			relativeTo: this.pointyWrapper,
			allowNegative: true
		}, pos, options));
		putItBack();
	},
	setContent: function(a1, a2){
		this.parent(a1, a2);
		this.top[this.h1?'removeClass':'addClass']('noCaption');
		if (Browser.ie) document.id(this).getElements('.bottom_ll, .bottom_lr').setStyle('font-size', 1); //IE6 bullshit
		if (this.options.closeButton) this.body.setStyle('margin-right', 6);
		this.positionPointer();
		return this;
	},
	makeCaption: function(caption){
		this.parent(caption);
		if (this.options.width && this.h1) this.h1.setStyle('width', (this.options.width.toInt()-(this.options.closeButton?25:15)));
	}
});

StickyWin.UI.pointy = function(caption, body, options){
	return document.id(new StickyWin.UI.Pointy(caption, body, options));
};
StickyWin.ui.pointy = StickyWin.UI.pointy;

// Begin: Source/UI/StickyWin.PointyTip.js
/*
---
name: StickyWin.PointyTip

description: Positions a pointy tip relative to the element you specify.

license: MIT-Style License

requires: StickyWin.UI.Pointy

provides: StickyWin.PointyTip

...
*/
StickyWin.PointyTip = new Class({
	Extends: StickyWin,
	options: {
		point: "left",
		pointyOptions: {}
	},
	initialize: function(){
		var args = this.getArgs(arguments);
		this.setOptions(args.options);
		var popts = this.options.pointyOptions;
		var d = popts.direction;
		if (!d) {
			var map = {
				left: 9,
				right: 3,
				up: 12,
				down: 6
			};
			d = map[this.options.point];
			if (!d) d = this.options.point;
			popts.direction = d;
		}
		if (!popts.width) popts.width = this.options.width;
		this.pointy = new StickyWin.UI.Pointy(args.caption, args.body, popts);
		this.options.content = null;
		this.setOptions(args.options, this.getPositionSettings());
		this.parent(this.options);
		this.win.empty().adopt(document.id(this.pointy));
		this.attachHandlers(this.win);
		if (this.options.showNow) this.position();
	},
	getArgs: function(){
		return StickyWin.UI.getArgs.apply(this, arguments);
	},
	getPositionSettings: function(){
		var s = this.pointy.options.divotSize;
		var d = this.options.point;
		var offset = this.options.offset || {};
		switch(d) {
			case "left": case 8: case 9: case 10:
				return {
					edge: {
						x: 'left',
						y: d==10?'top':d==8?'bottom':'center'
					},
					position: {x: 'right', y: 'center'},
					offset: {
						x: s + (offset.x || 0),
						y: offset.y || 0
					}
				};
			case "right": case 2:  case 3: case 4:
				return {
					edge: {
						x: 'right',
						y: (d==2?'top':d==4?'bottom':'center') + (offset.y || 0)
					},
					position: {x: 'left', y: 'center'},
					offset: {
						x: -s + (offset.x || 0),
						y: offset.y || 0
					}
				};
			case "up": case 11: case 12: case 1:
				return {
					edge: {
						x: d==11?'left':d==1?'right':'center',
						y: 'top'
					},
					position: {	x: 'center', y: 'bottom' },
					offset: {
						y: s + (offset.y || 0),
						x: (d==11?-s:d==1?s:0) + (offset.x || 0)
					}
				};
			case "down": case 5: case 6: case 7:
				return {
					edge: {
						x: (d==7?'left':d==5?'right':'center') + (offset.x || 0),
						y: 'bottom'
					},
					position: {x: 'center', y: 'top'},
					offset: {
						y: -s + (offset.y || 0),
						x: (d==7?-s:d==5?s:0) + (offset.x || 0)
					}
				};
		};
	},
	setContent: function() {
		var args = this.getArgs(arguments);
		this.pointy.setContent(args.caption, args.body);
		[this.pointy.h1, this.pointy.body].each(this.attachHandlers, this);
		if (this.visible) this.position();
		return this;
	},
	showWin: function(){
		this.parent();
		this.pointy.positionPointer();
	},
	position: function(options){
		this.parent(options);
		this.pointy.positionPointer();
	},
	attachHandlers: function(content) {
		if (!content) return;
		content.getElements('.'+this.options.closeClassName).addEvent('click', function(){ this.hide(); }.bind(this));
		content.getElements('.'+this.options.pinClassName).addEvent('click', function(){ this.togglepin(); }.bind(this));
	}
});

// Begin: Source/UI/Tips.Pointy.js
/*
---
name: Tips.Pointy

description: Defines Tips.Pointy, An extension to Tips that adds a pointy style to the tip.

license: MIT-Style License

requires: [More/Tips, StickyWin.PointyTip]

provides: Tips.Pointy

...
*/

Tips.Pointy = new Class({
	Extends: Tips,
	options: {
		onShow: function(tip, stickyWin){
			stickyWin.show();
		},
		onHide: function(tip, stickyWin){
			stickyWin.hide();
		},
		pointyTipOptions: {
			point: 11,
			width: 150,
			pointyOptions: {
				closeButton: false
			}
		}
	},
	initialize: function(){
		var params = Array.link(arguments, {options: Type.isObject, elements: function(arg){ return arg != null; }});
		this.setOptions(params.options);
		this.tip = new StickyWin.PointyTip(Object.append(this.options.pointyTipOptions, {
			showNow: false
		}));
		if (this.options.className) document.id(this.tip).addClass(this.options.className);
		if (params.elements) this.attach(params.elements);
	},
	elementEnter: function(event, element){

		var title = element.retrieve('tip:title');
		var text = element.retrieve('tip:text');
		this.tip.setContent(title, text);

		this.timer = clearTimeout(this.timer);
		this.timer = this.show.delay(this.options.showDelay, this);

		this.position(element);
	},

	elementLeave: function(event){
		clearTimeout(this.timer);
		this.timer = this.hide.delay(this.options.hideDelay, this);
	},

	elementMove: function(event){
		return; //always fixed
	},

	position: function(element){
		this.tip.setOptions({
			relativeTo: element
		});
		this.tip.position();
	},

	show: function(){
		this.fireEvent('show', [document.id(this.tip), this.tip]);
	},

	hide: function(){
		this.fireEvent('hide', [document.id(this.tip), this.tip]);
	},

	destroy: function(){
		this.detach();
		this.tip.destroy();
	}

});

// Begin: Source/Behaviors/Behavior.Tips.js
/*
---
name: Behavior.Tips
description: Attaches Tips.Pointy objects to elements with PointyTip in their data-behavior property and turns elements with HelpTip or InfoTip in their data-filters property into elements which show a Tips.Pointy object which contains their content, on rollover.
provides: [Behavior.Tips]
requires: [Behavior/Behavior, /Tips.Pointy]
script: Behavior.Tips.js
...
*/

(function() {

var createLink = function(element) {
	var isHelp = element.hasBehavior('HelpTip');
	var link = new Element('a', {
		'class': element.get('class'),
		'data-behavior': (isHelp ? 'HelpTip' : 'InfoTip'),
		'html': isHelp ? '?' : 'i'
	}).inject(element, 'after').store('tip:text', element.get('html'));
	//see where that text is supposed to have its pointer and group them by point
	var point = element.getData('help-direction', 1);
	return {point: point, link: link};
};

var createTip = function(link, point) {
	var tip = new Tips.Pointy(link, {
		pointyTipOptions: {
			destroyOnClose: false,
			width: 250,
			point: point.toInt()
		}
	});
	return tip;
};

Behavior.addGlobalFilters({

	PointyTip: function(element){
		var point = element.getData('tip-direction', 12);
		var tip = createTip(element, point);
		//destroy the tips on cleanup
		this.markForCleanup(element, function(){
			tip.destroy();
		});
		return tip;
	},

	//display help tips for users
	HelpTip: function(element) {
		var help = element.hide();
		var link = createLink(help);
		//for each point, create a new instance of Tips.Pointy (clientcide plugin)
		var tip = createTip(link.link, link.point);
		//destroy the tips on cleanup
		this.markForCleanup(element, function(){
			tip.destroy();
		});
		return tip;
	},

	InfoTip: function(element) {
		var info = element.hide();
		var link = createLink(info);
		var tip = createTip(link.link, link.point);
		this.markForCleanup(element, function(){
			tip.destroy();
		});
		return tip;
	}

});

})();


// Begin: Source/UI/Bootstrap.js
/*
---

name: Bootstrap

description: The BootStrap namespace.

authors: [Aaron Newton]

license: MIT-style license.

provides: [Bootstrap]

...
*/
var Bootstrap = {};

// Begin: Source/UI/Bootstrap.Dropdown.js
/*
---

name: Bootstrap.Dropdown

description: A simple dropdown menu that works with the Twitter Bootstrap css framework.

license: MIT-style license.

authors: [Aaron Newton]

requires:
 - /Bootstrap
 - Core/Element.Event
 - More/Element.Shortcuts

provides: Bootstrap.Dropdown

...
*/
Bootstrap.Dropdown = new Class({

	Implements: [Options, Events],

	options: {
		/*
			onShow: function(element){},
			onHide: function(elements){},
		*/
		ignore: 'input, select, label'
	},

	initialize: function(container, options){
		this.element = document.id(container);
		this.setOptions(options);
		this.boundHandle = this._handle.bind(this);
		document.id(document.body).addEvent('click', this.boundHandle);
	},

	hideAll: function(){
		var els = this.element.getElements('.open').removeClass('open');
		this.fireEvent('hide', els);
		return this;
	},

	show: function(subMenu){
		this.hideAll();
		this.fireEvent('show', subMenu);
		subMenu.addClass('open');
		return this;
	},

	destroy: function(){
		this.hideAll();
		document.body.removeEvent('click', this.boundHandle);
		return this;
	},

	// PRIVATE

	_handle: function(e){
		var el = e.target;
		var open = el.getParent('.open');
		if (!el.match(this.options.ignore) || !open) this.hideAll();
		if (this.element.contains(el)) {
			var parent = el.match('.dropdown-toggle') ? el.getParent() : el.getParent('.dropdown-toggle');
			if (parent) {
				e.preventDefault();
				if (!open) this.show(parent);
			}
		}
	}
});

// Begin: Source/Behaviors/Behavior.BS.Dropdown.js
/*
---

name: Behavior.BS.Dropdown

description: Instantiates Bootstrap.Dropdown based on HTML markup.

license: MIT-style license.

authors: [Aaron Newton]

requires:
 - Behavior/Behavior
 - Bootstrap.Dropdown

provides: [Behavior.BS.Dropdown]

...
*/
Behavior.addGlobalFilters({
	'BS.Dropdown': {
		returns: Bootstrap.Dropdown,
		setup: function(el, api){
			return new Bootstrap.Dropdown(el);
		}
	}
});

// Begin: Source/UI/Delegator.BS.ShowPopup.js
/*
---

name: Delegator.BS.ShowPopup

description: Shows a hidden popup.

authors: [Aaron Newton]

license: MIT-style license.

requires:
 - Behavior/Delegator
 - Behavior/Behavior

provides: [Delegator.BS.ShowPopup]

...
*/

(function(){



	Delegator.register('click', 'BS.showPopup', {

		require: ['target'],
		handler: function(event, link, api){
			var target = link.getElement(api.get('target'));
			event.preventDefault();
			if (!target) api.fail('Could not find target element to activate: ', api.get('target'));
			api.getBehavior().apply(target);
			target.getBehaviorResult('BS.Popup').show();
		}

	});

})();

// Begin: Source/UI/CSSEvents.js
/*
---

name: CSSEvents

license: MIT-style

authors: [Aaron Newton]

requires: [Core/DomReady]

provides: CSSEvents
...
*/

Browser.Features.getCSSTransition = function(){
	Browser.Features.cssTransition = (function () {
		var thisBody = document.body || document.documentElement
			, thisStyle = thisBody.style
			, support = thisStyle.transition !== undefined || thisStyle.WebkitTransition !== undefined || thisStyle.MozTransition !== undefined || thisStyle.MsTransition !== undefined || thisStyle.OTransition !== undefined;
		return support;
	})();

	// set CSS transition event type
	if ( Browser.Features.cssTransition ) {
		Browser.Features.transitionEnd = "TransitionEnd";
		if ( Browser.safari || Browser.chrome ) {
			Browser.Features.transitionEnd = "webkitTransitionEnd";
		} else if ( Browser.firefox ) {
			Browser.Features.transitionEnd = "transitionend";
		} else if ( Browser.opera ) {
			Browser.Features.transitionEnd = "oTransitionEnd";
		}
	}
	Browser.Features.getCSSTransition = Function.from(Browser.Features.transitionEnd);
};

window.addEvent("domready", Browser.Features.getCSSTransition);

// Begin: Source/UI/Bootstrap.Popup.js
/*
---

name: Popup

description: A simple Popup class for the Twitter Bootstrap CSS framework.

authors: [Aaron Newton]

license: MIT-style license.

requires:
 - Core/Element.Delegation
 - Core/Fx.Tween
 - Core/Fx.Transitions
 - More/Mask
 - More/Elements.From
 - More/Element.Position
 - More/Element.Shortcuts
 - More/Events.Pseudos
 - /CSSEvents
 - /Bootstrap

provides: [Bootstrap.Popup]

...
*/

Bootstrap.Popup = new Class({

	Implements: [Options, Events],

	options: {
		/*
			onShow: function(){},
			onHide: function(){},
			animate: function(){},
			destroy: function(){},
		*/
		persist: true,
		closeOnClickOut: true,
		closeOnEsc: true,
		mask: true,
		animate: true
	},

	initialize: function(element, options){
		this.element = document.id(element).store('Bootstrap.Popup', this);
		this.setOptions(options);
		this.bound = {
			hide: this.hide.bind(this),
			bodyClick: function(e){
				if (!this.element.contains(e.target)){
					this.hide();
				}
			}.bind(this),
			keyMonitor: function(e){
				if (e.key == 'esc') this.hide();
			}.bind(this),
			animationEnd: this._animationEnd.bind(this)
		};
		if ((this.element.hasClass('fade') && this.element.hasClass('in')) ||
		    (!this.element.hasClass('hide') && !this.element.hasClass('fade'))){
			if (this.element.hasClass('fade')) this.element.removeClass('in');
			this.show();
		}
	},

	_checkAnimate: function(){
		var check = this.options.animate !== false && Browser.Features.getCSSTransition() && (this.options.animate || this.element.hasClass('fade'));
		if (!check) {
			this.element.removeClass('fade').addClass('hide');
			this._mask.removeClass('fade').addClass('hide');
		} else if (check) {
			this.element.addClass('fade').removeClass('hide');
			this._mask.addClass('fade').removeClass('hide');
		}
		return check;
	},

	show: function(){
		if (this.visible || this.animating) return;
		this.element.addEvent('click:relay(.close, .dismiss)', this.bound.hide);
		if (this.options.closeOnEsc) document.addEvent('keyup', this.bound.keyMonitor);
		this._makeMask();
		this._mask.inject(document.body);
		this.animating = true;
		if (this._checkAnimate()){
			this.element.offsetWidth; // force reflow
			this.element.addClass('in');
			this._mask.addClass('in');
		} else {
			this.element.show();
			this._mask.show();
		}
		this.visible = true;
		this._watch();
	},

	_watch: function(){
		if (this._checkAnimate()) this.element.addEventListener(Browser.Features.getCSSTransition(), this.bound.animationEnd);
		else this._animationEnd();
	},

	_animationEnd: function(){
		if (Browser.Features.getCSSTransition()) this.element.removeEventListener(Browser.Features.getCSSTransition(), this.bound.animationEnd);
		this.animating = false;
		if (this.visible){
			this.fireEvent('show', this.element);
		} else {
			this.fireEvent('hide', this.element);
			if (!this.options.persist){
				this.destroy();
			} else {
				this._mask.dispose();
			}
		}
	},

	destroy: function(){
		this._mask.destroy();
		this.fireEvent('destroy', this.element);
		this.element.destroy();
		this._mask = null;
		this.destroyed = true;
	},

	hide: function(event, clicked){
		if (!this.visible || this.animating) return;
		this.animating = true;
		if (event && clicked && clicked.hasClass('stopEvent')){
			event.preventDefault();
		}
		document.id(document.body).removeEvent('click', this.bound.hide);
		document.removeEvent('keyup', this.bound.keyMonitor);
		this.element.removeEvent('click:relay(.close, .dismiss)', this.bound.hide);

		if (this._checkAnimate()){
			this.element.removeClass('in');
			this._mask.removeClass('in');
		} else {
			this.element.hide();
			this._mask.hide();
		}
		this.visible = false;
		this._watch();
	},

	// PRIVATE

	_makeMask: function(){
		if (this.options.mask){
			if (!this._mask){
				this._mask = new Element('div.modal-backdrop', {
					events: {
						click: this.bound.hide
					}
				});
				if (this._checkAnimate()){
					this._mask.addClass('fade');
				}
			}
		} else if (this.options.closeOnClickOut){
			document.id(document.body).addEvent('click', this.bound.hide);
		}
	}

});

// Begin: Source/Behaviors/Behavior.BS.Popup.js
/*
---

name: Behavior.Popup

description: Creates a bootstrap popup based on HTML markup.

license: MIT-style license.

authors: [Aaron Newton]

requires:
 - Behavior/Behavior
 - More/Object.Extras
 - Bootstrap.Popup

provides: [Behavior.BS.Popup]

...
*/

Behavior.addGlobalFilters({
	'BS.Popup': {
		defaults: {
			hide: false,
			animate: true,
			closeOnEsc: true,
			closeOnClickOut: true,
			mask: true,
			persist: true
		},
		returns: Bootstrap.Popup,
		setup: function(el, api){
			var popup = new Bootstrap.Popup(el,
				Object.cleanValues(
					api.getAs({
						persist: Boolean,
						animate: Boolean,
						closeOnEsc: Boolean,
						closeOnClickOut: Boolean,
						mask: Boolean
					})
				)
			);
			popup.addEvent('destroy', function(){
				api.cleanup(el);
			});
			if (!el.hasClass('hide') && !api.getAs(Boolean, 'hide') && (!el.hasClass('in') && !el.hasClass('fade'))) {
				popup.show();
			}
			return popup;
		}
	}
});

// Begin: Source/Behaviors/Behavior.BS.Popup.FormRequest.js
/*
---

name: Behavior.BS.Popup.FormRequest

description: Integrates FormRequest behavior into Popups.

license: MIT-style license.

authors: [Aaron Newton]

requires:
 - /Behavior.BS.Popup
 - More/Form.Request

provides: [Behavior.BS.Popup.FormRequest]

...
*/

Behavior.addGlobalPlugin("FormRequest", "Popup.FormRequest", {
	defaults: {
		closeOnSuccess: true
	},
	setup: function(element, api, instance){
		if (element.getParent('.modal')){
			var dismissed;
			var dismissals = element.getElements('input.dismiss, input.close').map(function(el){
				return el.addEvent('click', function(){
					dismissed = true;
				}).removeClass('dismiss').removeClass('close');
			});
			instance.addEvents({
				success: function(){
					var formRequestAPI = new BehaviorAPI(element, 'formrequest');
					if (formRequestAPI.getAs(Boolean, 'closeOnSuccess') !== false || api.get(Boolean, 'closeOnSuccess') !== false || dismissed){
						element.getParent('.modal').getBehaviorResult('BS.Popup').hide();
					}
				}
			});
		}
	}
});

// Begin: Source/Fx/Fx.Marquee.js
/*
---

name: Fx.Marquee

description: Defines Fx.Marquee, a marquee class for animated notifications.

License: MIT-Style License

requires: [Core/Fx.Morph, More/Element.Shortcuts]

provides: Fx.Marquee

...
*/
Fx.Marquee = new Class({
	Extends: Fx.Morph,
	options: {
		mode: 'horizontal', //or vertical
		message: '', //the message to display
		revert: true, //revert back to the previous message after a specified time
		delay: 5000, //how long to wait before reverting
		cssClass: 'msg', //the css class to apply to that message
		showEffect: { opacity: 1 },
		hideEffect: {opacity: 0},
		revertEffect: { opacity: [0,1] },
		currentMessage: null
/*	onRevert: function(){},
		onMessage: function(){} */
	},
	initialize: function(container, options){
		container = document.id(container);
		var msg = this.options.currentMessage || (container.getChildren().length == 1)?container.getFirst():'';
		var wrapper = new Element('div', {
				styles: { position: 'relative' },
				'class':'fxMarqueeWrapper'
			}).inject(container);
		this.parent(wrapper, options);
		this.current = this.wrapMessage(msg);
	},
	wrapMessage: function(msg){
		var wrapper;
		if (document.id(msg) && document.id(msg).hasClass('fxMarquee')) { //already set up
			wrapper = document.id(msg);
		} else {
			//create the wrapper
			wrapper = new Element('span', {
				'class':'fxMarquee',
				styles: {
					position: 'relative'
				}
			});
			if (document.id(msg)) wrapper.grab(document.id(msg)); //if the message is a dom element, inject it inside the wrapper
			else if (typeOf(msg) == "string") wrapper.set('html', msg); //else set it's value as the inner html
		}
		return wrapper.inject(this.element); //insert it into the container
	},
	announce: function(options) {
		this.setOptions(options).showMessage();
		return this;
	},
	showMessage: function(reverting){
		//delay the fuction if we're reverting
		(function(){
			//store a copy of the current chained functions
			var chain = this.$chain ? Array.clone(this.$chain) : [];
			//clear teh chain
			this.clearChain();
			this.element = document.id(this.element);
			this.current = document.id(this.current);
			this.message = document.id(this.message);
			//execute the hide effect
			this.start(this.options.hideEffect).chain(function(){
				//if we're reverting, hide the message and show the original
				if (reverting) {
					this.message.hide();
					if (this.current) this.current.show();
				} else {
					//else we're showing; remove the current message
					if (this.message) this.message.dispose();
					//create a new one with the message supplied
					this.message = this.wrapMessage(this.options.message);
					//hide the current message
					if (this.current) this.current.hide();
				}
				//if we're reverting, execute the revert effect, else the show effect
				this.start((reverting)?this.options.revertEffect:this.options.showEffect).chain(function(){
					//merge the chains we set aside back into this.$chain
					if (this.$chain) this.$chain.combine(chain);
					else this.$chain = chain;
					this.fireEvent((reverting)?'onRevert':'onMessage');
					//then, if we're reverting, show the original message
					if (!reverting && this.options.revert) this.showMessage(true);
					//if we're done, call the chain stack
					else this.callChain.delay(this.options.delay, this);
				}.bind(this));
			}.bind(this));
		}).delay((reverting)?this.options.delay:10, this);
		return this;
	}
});

// Begin: Source/UI/StickyWin.Modal.js
/*
---

name: StickyWin.Modal

description: This script extends StickyWin and StickyWin.Fx classes to add Mask functionality.

license: MIT-Style License

requires: [More/Mask, StickyWin]

provides: StickyWin.Modal
...
*/
StickyWin.Modal = new Class({

	Extends: StickyWin,

	options: {
		modalize: true,
		maskOptions: {
			style: {
				'background-color':'#333',
				opacity:0.8
			}
		},
		hideOnClick: true,
		getWindowManager: function(){ return StickyWin.ModalWM; }
	},

	initialize: function(options) {
		this.options.maskTarget = this.options.maskTarget || document.body;
		this.setOptions(options);
		this.mask = new Mask(this.options.maskTarget, this.options.maskOptions).addEvent('click', function() {
			if (this.options.hideOnClick) this.hide();
		}.bind(this));
		this.parent(options);
	},

	show: function(showModal){
		if ([showModal, this.options.modalize].pick()) this.mask.show();
		this.parent();
	},

	hide: function(hideModal){
		if ([hideModal, true].pick()) this.mask.hide();
		this.parent();
	},

	destroy: function(){
		this.mask.destroy();
		this.parent.apply(this, arguments);
	}

});

StickyWin.ModalWM = new StickyWin.Stacker({
	zIndexBase: 11000
});
if (StickyWin.Fx) StickyWin.Fx.Modal = StickyWin.Modal;

// Begin: Source/UI/StickyWin.Fx.js
/*
---

name: StickyWin.Fx

description: Extends StickyWin to create popups that fade in and out.

license: MIT-style license.

requires: [More/Class.Refactor, Core/Fx.Tween, StickyWin]

provides: StickyWin.Fx

...
*/
if (!Browser.ie){
	StickyWin = Class.refactor(StickyWin, {
		options: {
			//fadeTransition: 'sine:in:out',
			fade: true,
			fadeDuration: 150
		},
		hideWin: function(){
			if (this.options.fade) this.fade(0);
			else this.previous();
		},
		showWin: function(){
			if (this.options.fade) this.fade(1);
			else this.previous();
		},
		hide: function(){
			this.previous(this.options.fade);
		},
		show: function(){
			this.previous(this.options.fade);
		},
		fade: function(to){
			if (!this.fadeFx) {
				this.win.setStyles({
					opacity: 0,
					display: 'block'
				});
				var opts = {
					property: 'opacity',
					duration: this.options.fadeDuration
				};
				if (this.options.fadeTransition) opts.transition = this.options.fadeTransition;
				this.fadeFx = new Fx.Tween(this.win, opts);
			}
			if (to > 0) {
				this.win.setStyle('display','block');
				this.position();
			}
			this.fadeFx.clearChain();
			this.fadeFx.start(to).chain(function (){
				if (to == 0) {
					this.win.setStyle('display', 'none');
					this.fireEvent('onClose');
				} else {
					this.fireEvent('onDisplay');
				}
			}.bind(this));
			return this;
		}
	});
}
StickyWin.Fx = StickyWin;


// Begin: Source/UI/StickyWin.Drag.js
/*
---

name: StickyWin.Drag

description: Implements drag and resize functionaity into StickyWin.Fx. See StickyWin.Fx for the options.

license: MIT-Style License

requires: [More/Class.Refactor, More/Drag.Move, StickyWin]

provides: StickyWin.Drag

...
*/
StickyWin = Class.refactor(StickyWin, {
	options: {
		draggable: false,
		dragOptions: {
			onComplete: function(){}
		},
		dragHandleSelector: '.dragHandle',
		resizable: false,
		resizeOptions: {
			onComplete: function(){}
		},
		resizeHandleSelector: ''
	},
	setContent: function(){
		this.previous.apply(this, arguments);
		if (this.options.draggable) this.makeDraggable();
		if (this.options.resizable) this.makeResizable();
		return this;
	},
	makeDraggable: function(){
		var toggled = this.toggleVisible(true);
		if (this.options.useIframeShim) {
			this.makeIframeShim();
			var onComplete = (this.options.dragOptions.onComplete);
			this.options.dragOptions.onComplete = function(){
				onComplete();
				this.shim.position();
			}.bind(this);
		}
		if (this.options.dragHandleSelector) {
			var handle = this.win.getElement(this.options.dragHandleSelector);
			if (handle) {
				handle.setStyle('cursor','move');
				this.options.dragOptions.handle = handle;
			}
		}
		this.win.makeDraggable(this.options.dragOptions);
		if (toggled) this.toggleVisible(false);
	},
	makeResizable: function(){
		var toggled = this.toggleVisible(true);
		if (this.options.useIframeShim) {
			this.makeIframeShim();
			var onComplete = (this.options.resizeOptions.onComplete);
			this.options.resizeOptions.onComplete = function(){
				onComplete();
				this.shim.position();
			}.bind(this);
		}
		if (this.options.resizeHandleSelector) {
			var handle = this.win.getElement(this.options.resizeHandleSelector);
			if (handle) this.options.resizeOptions.handle = this.win.getElement(this.options.resizeHandleSelector);
		}
		this.win.makeResizable(this.options.resizeOptions);
		if (toggled) this.toggleVisible(false);
	},
	toggleVisible: function(show){
		if (!this.visible && show == null || show) {
			this.win.setStyles({
				display: 'block',
				opacity: 0
			});
			return true;
		} else if (show != null && !show){
			this.win.setStyles({
				display: 'none',
				opacity: 1
			});
			return false;
		}
		return false;
	}
});
StickyWin.Fx = StickyWin;

// Begin: Source/Behaviors/Behavior.StickyWin.js
/*
---
name: Behavior.StickyWin
description: Behaviors for StickyWin instances.
provides: [Behavior.StickyWin]
requires: [Behavior/Behavior, /StickyWin, /StickyWin.Modal, /StickyWin.Fx, /StickyWin.Drag, More/Array.Extras, More/Object.Extras]
script: Behavior.Tabs.js

...
*/

Behavior.addGlobalFilters({

	'StickyWin.Modal': {
		defaults: {
			destroyOnClose: true,
			closeOnClickOut: true,
			closeOnEsc: true,
			draggable: false,
			resizable: false
		},
		returns: StickyWin.Modal,
		setup: function(element, api) {
			var flex = element.getElement('.flex'),
			    height = api.getAs(Number, 'height') || (window.getSize().y * .9);

			if (flex){
				element.measure(function(){
					var tmp = new Element('span', { styles: { display: 'none' }}).replaces(flex),
					    remainder = element.getSize().y;
					var padding = ['padding-top', 'padding-bottom', 'margin-top', 'margin-bottom', 'border-top-width', 'border-bottom-width'].map(function(style){
						return flex.getStyle(style).toInt();
					}).sum();
					flex.setStyle('max-height', height - remainder - padding);
					flex.replaces(tmp);
				});
			}

			var options = Object.merge({
					content: element
				},
				Object.cleanValues(
					api.getAs({
						closeClassName: String,
						pinClassName: String,
						className: String,
						edge: String,
						position: String,
						offset: Object,
						relativeTo: String,
						width: Number,
						height: Number,
						timeout: Number,
						destroyOnClose: Boolean,
						closeOnClickOut: Boolean,
						closeOnEsc: Boolean,
						//modal options
						maskOptions: Object,
						//draggable options
						draggable: Boolean,
						dragHandleSelector: String,
						resizable: Boolean,
						resizeHandleSelector: String
					})
				)
			);

			if (options.mask) options.closeOnClickOut = false;

			var sw = new StickyWin.Modal(options);
			api.onCleanup(function(){
				if (!sw.destroyed) sw.destroy();
			});
			sw.addEvent('destroy', function(){
				api.cleanup(element);
			});
			return sw;
		}
	}

});


// Begin: Source/UI/StickyWin.Alert.js
/*
---

name: StickyWin.Alert

description: Defines StickyWin.Alert, a simple little alert box with a close button.

license: MIT-Style License

requires: [StickyWin.Modal, StickyWin.UI]

provides: [StickyWin.Alert, StickyWin.Error, StickyWin.alert, StickyWin.error]

...
*/
StickyWin.Alert = new Class({
	Implements: Options,
	Extends: StickyWin.Modal,
	options: {
		destroyOnClose: true,
		modalOptions: {
			modalStyle: {
				zIndex: 11000
			}
		},
		zIndex: 110001,
		uiOptions: {
			width: 250,
			buttons: [
				{text: 'Ok'}
			]
		},
		getWindowManager: function(){}
	},
	initialize: function(caption, message, options) {
		this.message = message;
		this.caption = caption;
		this.setOptions(options);
		this.setOptions({
			content: this.build()
		});
		this.parent(options);
	},
	makeMessage: function() {
		return new Element('p', {
			'class': 'errorMsg SWclearfix',
			styles: {
				margin: 0,
				minHeight: 10
			},
			html: this.message
		});
	},
	build: function(){
		return StickyWin.ui(this.caption, this.makeMessage(), this.options.uiOptions);
	}
});

StickyWin.Error = new Class({
	Extends: StickyWin.Alert,
	makeMessage: function(){
		var message = this.parent();
		new Element('img', {
			src: (this.options.baseHref || Clientcide.assetLocation + '/simple.error.popup') + '/icon_problems_sm.gif',
			'class': 'bang clearfix',
			styles: {
				'float': 'left',
				width: 30,
				height: 30,
				margin: '3px 5px 5px 0px'
			}
		}).inject(message, 'top');
		return message;
	}
});

StickyWin.alert = function(caption, message, options) {
	if (typeOf(options) == "string") options = {baseHref: options};
	return new StickyWin.Alert(caption, message, options);
};

StickyWin.error = function(caption, message, options) {
	return new StickyWin.Error(caption, message, options);
};

// Begin: Source/UI/StickyWin.Confirm.js
/*
---
name: StickyWin.Confirm

description: Defines StickyWin.Conferm, a simple confirmation box with an ok and a close button.

license: MIT-Style License

requires: StickyWin.Alert

provides: [StickyWin.Confirm, StickyWin.confirm]

...
*/
StickyWin.Confirm = new Class({
	Extends: StickyWin.Alert,
	options: {
		uiOptions: {
			width: 250
		}
	},
	build: function(callback){
		this.setOptions({
			uiOptions: {
				buttons: [
					{text: 'Cancel'},
					{
						text: 'Ok',
						onClick: callback || function(){
							this.fireEvent('confirm');
						}.bind(this)
					}
				]
			}
		});
		return this.parent();
	}
});

StickyWin.confirm = function(caption, message, callback, options) {
	return new StickyWin.Confirm(caption, message, options).addEvent('confirm', callback);
};

// Begin: Source/Forms/Form.Validator.Tips.js
/*
---
name: Form.Validator.Tips

description: Form.Validator using StickyWin.PointyTip.

license: MIT-Style License

requires: [More/Form.Validator.Inline, Clientcide, StickyWin.PointyTip]

provides: Form.Validator.Tips
...
*/
Form.Validator.Tips = new Class({
	Extends: Form.Validator.Inline,
	options: {
		pointyTipOptions: {
			point: "left",
			width: 250
		},
		tipCaption: ''
	},
	showAdvice: function(className, field){
		var advice = this.getAdvice(field);
		if (advice && !advice.visible){
			advice.show();
			advice.position();
			advice.pointy.positionPointer();
		}
	},
	hideAdvice: function(className, field){
		var advice = this.getAdvice(field);
		if (advice && advice.visible) advice.hide();
	},
	getAdvice: function(className, field) {
		var params = Array.link(arguments, {field: Type.isElement});
		return params.field.retrieve('PointyTip');
	},
	advices: [],
	makeAdvice: function(className, field, error, warn){
		if (!error && !warn) return;
		var advice = field.retrieve('PointyTip');
		if (!advice){
			var cssClass = warn?'warning-advice':'validation-advice';
			var msg = new Element('ul', {
				styles: {
					margin: 0,
					padding: 0,
					listStyle: 'none'
				}
			});
			var li = this.makeAdviceItem(className, field);
			if (li) msg.adopt(li);
			field.store('validationMsgs', msg);
			advice = new StickyWin.PointyTip(this.options.tipCaption, msg, Object.merge(this.options.pointyTipOptions, {
				showNow: false,
				relativeTo: field,
				inject: {
					target: this.element
				}
			}));
			this.advices.push(advice);
			advice.msgs = {};
			field.store('PointyTip', advice);
			document.id(advice).addClass(cssClass).set('id', 'advice-'+className+'-'+this.getFieldId(field));
		}
		field.store('advice-'+className, advice);
		this.appendAdvice(className, field, error, warn);
		advice.pointy.positionPointer();
		return advice;
	},
	validateField: function(field, force){
		var advice = this.getAdvice(field);
		var anyVis = this.advices.some(function(a){ return a.visible; });
		if (anyVis && this.options.serial) {
			if (advice && advice.visible) {
				var passed = this.parent(field, force);
				if (!field.hasClass('validation-failed')) advice.hide();
			}
			return passed;
		}
		var msgs = field.retrieve('validationMsgs');
		if (msgs) msgs.getChildren().hide();
		if ((field.hasClass('validation-failed') || field.hasClass('warning')) && advice) advice.show();
		if (this.options.serial) {
			var fields = this.element.getElements('.validation-failed, .warning');
			if (fields.length) {
				fields.each(function(f, i) {
					var adv = this.getAdvice(f);
					if (adv) adv.hide();
				}, this);
			}
		}
		return this.parent(field, force);
	},
	makeAdviceItem: function(className, field, error, warn){
		if (!error && !warn) return;
		var advice = this.getAdvice(field);
		var errorMsg = this.makeAdviceMsg(field, error, warn);
		if (advice && advice.msgs[className]) return advice.msgs[className].set('html', errorMsg);
		return new Element('li', {
			html: errorMsg,
			style: {
				display: 'none'
			}
		});
	},
	makeAdviceMsg: function(field, error, warn){
		var errorMsg = (warn)?this.warningPrefix:this.errorPrefix;
			errorMsg += (this.options.useTitles) ? field.title || error:error;
		return errorMsg;
	},
	appendAdvice: function(className, field, error, warn) {
		var advice = this.getAdvice(field);
		if (advice.msgs[className]) return advice.msgs[className].set('html', this.makeAdviceMsg(field, error, warn)).show();
		var li = this.makeAdviceItem(className, field, error, warn);
		if (!li) return;
		li.inject(field.retrieve('validationMsgs')).show();
		advice.msgs[className] = li;
	},
	insertAdvice: function(advice, field){
		//Check for error position prop
		var props = field.get('validatorProps');
		//Build advice
		if (!props.msgPos || !document.id(props.msgPos)) {
			switch (field.type.toLowerCase()) {
				case 'radio':
					var p = field.getParent().adopt(advice);
					break;
				default:
					document.id(advice).inject(document.id(field), 'after');
			};
		} else {
			document.id(props.msgPos).grab(advice);
		}
		advice.position();
	}
});

if (window.FormValidator) FormValidator.Tips = Form.Validator.Tips;

// Begin: Source/Layout/HoverGroup.js
/*
---

name: HoverGroup

description: Manages mousing in and out of multiple objects (think drop-down menus).

license: MIT-Style License

requires: [Core/Class.Extras, Core/Element.Event, More/Class.Binds]

provides: HoverGroup

...
*/

var HoverGroup = new Class({
	Implements: [Options, Events],
	Binds: ['enter', 'leave', 'remain'],
	options: {
		//onEnter: function(){},
		//onLeave: function(){},
		elements: [],
		delay: 300,
		start: ['mouseenter'],
		remain: [],
		end: ['mouseleave']
	},
	initialize: function(options) {
		this.setOptions(options);
		this.attachTo(this.options.elements);
		this.addEvents({
			leave: function(){
				this.active = false;
			},
			enter: function(){
				this.active = true;
			}
		});
	},
	elements: [],
	attachTo: function(elements, detach){
		var starters = {}, remainers = {}, enders = {};
		elements = Array.from(document.id(elements)||$$(elements));
		this.options.start.each(function(start) {
			starters[start] = this.enter;
		}, this);
		this.options.end.each(function(end) {
			enders[end] = this.leave;
		}, this);
		this.options.remain.each(function(remain){
			remainers[remain] = this.remain;
		}, this);
		if (detach) {
			elements.each(function(el) {
				el.removeEvents(starters).removeEvents(enders).removeEvents(remainers);
				this.elements.erase(el);
			}, this);
		} else {
			elements.each(function(el){
				el.addEvents(starters).addEvents(enders).addEvents(remainers);
			});
			this.elements.combine(elements);
		}
		return this;
	},
	detachFrom: function(elements){
		this.attachTo(elements, true);
	},
	enter: function(e){
		this.isMoused = true;
		this.assert(e);
	},
	leave: function(e){
		this.isMoused = false;
		this.assert(e);
	},
	remain: function(e){
		if (this.active) this.enter(e);
	},
	assert: function(e){
		clearTimeout(this.assertion);
		this.assertion = (function(){
			if (!this.isMoused && this.active) this.fireEvent('leave', e);
			else if (this.isMoused && !this.active) this.fireEvent('enter', e);
		}).delay(this.options.delay, this);
	}
});

// Begin: Source/Layout/MultipleOpenAccordion.js
/*
---
name: MultipleOpenAccordion

description: Creates a Mootools Fx.Accordion that allows the user to open more than one element.

license: MIT-Style License

requires: [Core/Element.Event, More/Fx.Reveal]

provides: MultipleOpenAccordion

...
*/
var MultipleOpenAccordion = new Class({
	Implements: [Options, Events, Chain],
	options: {
		togglers: [],
		elements: [],
		openAll: false,
		firstElementsOpen: [0],
		fixedHeight: null,
		fixedWidth: null,
		height: true,
		opacity: true,
		width: false
		//onActive: function(){},
		//onBackground: function(){}
	},
	togglers: [],
	elements: [],
	initialize: function(options){
		var args = Array.link(arguments, {options: Type.isObject, elements: Type.isElements});
		this.setOptions(args.options);
		elements = $$(this.options.elements);
		$$(this.options.togglers).each(function(toggler, idx){
			this.addSection(toggler, elements[idx], idx);
		}, this);
		if (this.togglers.length) {
			if (this.options.openAll) this.showAll();
			else this.toggleSections(this.options.firstElementsOpen, false, true);
		}
		this.openSections = this.showSections.bind(this);
		this.closeSections = this.hideSections.bind(this);
	},
	addSection: function(toggler, element){
		toggler = document.id(toggler);
		element = document.id(element);
		var test = this.togglers.contains(toggler);
		var len = this.togglers.length;
		this.togglers.include(toggler);
		this.elements.include(element);
		var idx = this.togglers.indexOf(toggler);
		var displayer = this.toggleSection.bind(this, idx);
		toggler.addEvent('click', displayer).store('multipleOpenAccordion:display', displayer);
		var mode;
		if (this.options.height && this.options.width) mode = "both";
		else mode = (this.options.height)?"vertical":"horizontal";
		element.store('moa:reveal', new Fx.Reveal(element, {
			transitionOpacity: this.options.opacity,
			mode: mode,
			heightOverride: this.options.fixedHeight,
			widthOverride: this.options.fixedWidth
		}));
		return this;
	},
	removeSection: function(toggler) {
		var idx = this.togglers.indexOf(toggler);
		var element = this.elements[idx];
		element.dissolve();
		this.togglers.erase(toggler);
		this.elements.erase(element);
		this.detach(toggler);
		return this;
	},
	detach: function(toggler){
		var remove = function(toggler) {
			toggler.removeEvent(this.options.trigger, toggler.retrieve('multipleOpenAccordion:display'));
		}.bind(this);
		if (!toggler) this.togglers.each(remove);
		else remove(toggler);
		return this;
	},
	onComplete: function(idx, callChain){
		this.fireEvent(this.elements[idx].isDisplayed()?'onActive':'onBackground', [this.togglers[idx], this.elements[idx]]);
		this.callChain();
		return this;
	},
	showSection: function(idx, useFx){
		this.toggleSection(idx, useFx, true);
	},
	hideSection: function(idx, useFx){
		this.toggleSection(idx, useFx, false);
	},
	toggleSection: function(idx, useFx, show, callChain){
		var method = show?'reveal':show != null?'dissolve':'toggle';
		callChain = [callChain, true].pick();
		var el = this.elements[idx];
		if (useFx != null ? useFx : true) {
			el.retrieve('moa:reveal')[method]().chain(
				this.onComplete.bind(this, idx, callChain)
			);
		} else {
				if (method == "toggle") el.toggle();
				else el[method == "reveal"?'show':'hide']();
				this.onComplete(idx, callChain);
		}
		return this;
	},
	toggleAll: function(useFx, show){
		var method = show?'reveal':(show!=null)?'disolve':'toggle';
		var last = this.elements.getLast();
		this.elements.each(function(el, idx){
			this.toggleSection(idx, useFx, show, el == last);
		}, this);
		return this;
	},
	toggleSections: function(sections, useFx, show) {
		last = sections.getLast();
		this.elements.each(function(el,idx){
			this.toggleSection(idx, useFx, sections.contains(idx)?show:!show, idx == last);
		}, this);
		return this;
	},
	showSections: function(sections, useFx){
		sections.each(function(i){
			this.showSection(i, useFx);
		}, this);
	},
	hideSections: function(sections, useFx){
		sections.each(function(i){
			this.hideSection(i, useFx);
		}, this);
	},
	showAll: function(useFx){
		return this.toggleAll(useFx, true);
	},
	hideAll: function(useFx){
		return this.toggleAll(useFx, false);
	}
});


// Begin: Source/Behaviors/Behavior.BS.FormValidator.js
/*
---

name: Behavior.BS.FormValidator

description: Integrates FormValidator behavior into Bootstrap.

license: MIT-style license.

authors: [Aaron Newton]

requires:
 - More-Behaviors/Behavior.FormValidator

provides: [Behavior.BS.FormValidator]

...
*/

Behavior.addGlobalPlugin("FormValidator", "BS.FormValidator", {
	setup: function(element, api, instance){
		var original = {
			showError: instance.showError,
			hideError: instance.hideError
		};
		instance.setOptions({
			showError: function(){},
			hideError: function(){}
		});
		instance.warningPrefix = '';
		instance.errorPrefix = '';
		instance.addEvents({
			showAdvice: function(field, advice, className){
				var inputParent = field.getParent('.input'),
				    clearfixParent = inputParent.getParent('.clearfix');
				if (!inputParent || !clearfixParent){
					original.showError(advice);
				} else {
					field.addClass('error');
					var help = inputParent.getElement('div.advice');
					if (!help){
						inputParent.getElements('span.help-inline').setStyle('display', 'none');
						help = new Element('span.help-inline.advice.auto-created', {
							html: advice.get('html')
						}).inject(inputParent);
					}
					help.removeClass('hide');
					help.set('title', advice.get('html'));
					clearfixParent.addClass('error');
				}
			},
			hideAdvice: function(field, advice, className){
				var inputParent = field.getParent('.input'),
				    clearfixParent = inputParent.getParent('.clearfix');
				if (!inputParent || !clearfixParent){
					original.hideError(advice);
				} else {
					field.removeClass('error');
					var help = inputParent.getElement('span.advice');
					if (help.hasClass('auto-created')) help.destroy();
					else help.set('html', '');
					inputParent.getElements('span.help-inline').setStyle('display', '');
					clearfixParent.removeClass('error');
				}
			}
		});
	}
});

// Begin: Source/3rdParty/Autocompleter.Observer.js
/*
---
name: Autocompleter.Observer

description: Observe formelements for changes

version: 1.0rc3

license: MIT-style license
author: Harald Kirschner <mail [at] digitarald.de>
copyright: Author

requires: [Core/Class.Extras, Core/Element.Event, Core/JSON]

provides: [Autocompleter.Observer, Observer]

...
 */
var Observer = new Class({

	Implements: [Options, Events],

	options: {
		periodical: false,
		delay: 1000
	},

	initialize: function(el, onFired, options){
		this.setOptions(options);
		this.addEvent('onFired', onFired);
		this.element = document.id(el) || $$(el);
		/* Clientcide change */
		this.boundChange = this.changed.bind(this);
		this.resume();
	},

	changed: function() {
		var value = this.element.get('value');
		if ($equals(this.value, value)) return;
		this.clear();
		this.value = value;
		this.timeout = this.onFired.delay(this.options.delay, this);
	},

	setValue: function(value) {
		this.value = value;
		this.element.set('value', value);
		return this.clear();
	},

	onFired: function() {
		this.fireEvent('onFired', [this.value, this.element]);
	},

	clear: function() {
		clearTimeout(this.timeout || null);
		return this;
	},
	/* Clientcide change */
	pause: function(){
		clearTimeout(this.timeout);
		clearTimeout(this.timer);
		this.element.removeEvent('keyup', this.boundChange);
		return this;
	},
	resume: function(){
		this.value = this.element.get('value');
		if (this.options.periodical) this.timer = this.changed.periodical(this.options.periodical, this);
		else this.element.addEvent('keyup', this.boundChange);
		return this;
	}

});

var $equals = function(obj1, obj2) {
	return (obj1 == obj2 || JSON.encode(obj1) == JSON.encode(obj2));
};

// Begin: Source/3rdParty/Autocompleter.js
/*
---
name: Autocompleter

description: An auto completer class from <a href=\"http://digitarald.de\">http://digitarald.de</a>.

version: 1.1.1

license: MIT-style license

author: Harald Kirschner <mail [at] digitarald.de>

copyright: Author

requires: [Core/Fx.Tween, More/Element.Shortcuts, More/Element.Forms, More/IframeShim, Observer, Clientcide]

provides: [Autocompleter, Autocompleter.Base]

...
*/
var Autocompleter = {};

var OverlayFix = IframeShim;

Autocompleter.Base = new Class({

	Implements: [Options, Events],

	options: {
		minLength: 1,
		markQuery: true,
		width: 'inherit',
		maxChoices: 10,
//		injectChoice: null,
//		customChoices: null,
		className: 'autocompleter-choices',
		zIndex: 42,
		delay: 400,
		observerOptions: {},
		fxOptions: {},
//		onSelection: function(){},
//		onShow: function(){},
//		onHide: function(){},
//		onBlur: function(){},
//		onFocus: function(){},
//		onChoiceConfirm: function(){},

		autoSubmit: false,
		overflow: false,
		overflowMargin: 25,
		selectFirst: false,
		filter: null,
		filterCase: false,
		filterSubset: false,
		forceSelect: false,
		selectMode: true,
		choicesMatch: null,

		multiple: false,
		separator: ', ',
		autoTrim: true,
		allowDupes: false,

		cache: true,
		relative: false
	},

	initialize: function(element, options) {
		this.element = document.id(element);
		this.setOptions(options);
		this.options.separatorSplit = new RegExp("\s*["+
		  this.options.separator == " " ? " " : this.options.separator.trim()+
		  "]\s*/");
		this.build();
		this.observer = new Observer(this.element, this.prefetch.bind(this), Object.merge({
			'delay': this.options.delay
		}, this.options.observerOptions));
		this.queryValue = null;
		if (this.options.filter) this.filter = this.options.filter.bind(this);
		var mode = this.options.selectMode;
		this.typeAhead = (mode == 'type-ahead');
		this.selectMode = (mode === true) ? 'selection' : mode;
		this.cached = [];
	},

	/**
	 * build - Initialize DOM
	 *
	 * Builds the html structure for choices and appends the events to the element.
	 * Override this function to modify the html generation.
	 */
	build: function() {
		if (document.id(this.options.customChoices)) {
			this.choices = this.options.customChoices;
		} else {
			this.choices = new Element('ul', {
				'class': this.options.className,
				'styles': {
					'zIndex': this.options.zIndex
				}
			}).inject(document.body);
			this.relative = false;
			if (this.options.relative || this.element.getOffsetParent() != document.body) {
				this.choices.inject(this.element, 'after');
				this.relative = this.element.getOffsetParent();
			}
			this.fix = new OverlayFix(this.choices);
		}
		if (!this.options.separator.test(this.options.separatorSplit)) {
			this.options.separatorSplit = this.options.separator;
		}
		this.fx = (!this.options.fxOptions) ? null : new Fx.Tween(this.choices, Object.merge({
			'property': 'opacity',
			'link': 'cancel',
			'duration': 200
		}, this.options.fxOptions)).addEvent('onStart', Chain.prototype.clearChain).set(0);
		this.element.setProperty('autocomplete', 'off')
			.addEvent((Browser.ie || Browser.chrome || Browser.safari) ? 'keydown' : 'keypress', this.onCommand.bind(this))
			.addEvent('click', this.onCommand.bind(this, false))
			.addEvent('focus', function(){
				this.toggleFocus.delay(100, this, [true]);
			}.bind(this));
			//.addEvent('blur', this.toggleFocus.create({bind: this, arguments: false, delay: 100}));
		document.addEvent('click', function(e){
			if (e.target != this.choices) this.toggleFocus(false);
		}.bind(this));
	},

	destroy: function() {
		if (this.fix) this.fix.dispose();
		this.choices = this.selected = this.choices.destroy();
	},

	toggleFocus: function(state) {
		this.focussed = state;
		if (!state) this.hideChoices(true);
		this.fireEvent((state) ? 'onFocus' : 'onBlur', [this.element]);
	},

	onCommand: function(e) {
		if (!e && this.focussed) return this.prefetch();
		if (e && e.key && !e.shift) {
			switch (e.key) {
				case 'enter': case 'tab':
					if (this.element.value != this.opted) return true;
					if (this.selected && this.visible) {
						this.choiceSelect(this.selected);
						this.fireEvent('choiceConfirm', this.selected);
						return !!(this.options.autoSubmit);
					}
					break;
				case 'up': case 'down':
					if (!this.prefetch() && this.queryValue !== null) {
						var up = (e.key == 'up');
						this.choiceOver((this.selected || this.choices)[
							(this.selected) ? ((up) ? 'getPrevious' : 'getNext') : ((up) ? 'getLast' : 'getFirst')
						](this.options.choicesMatch), true);
					}
					return false;
				case 'esc':
					this.hideChoices(true);
					break;
			}
		}
		return true;
	},

	setSelection: function(finish) {
		var input = this.selected.inputValue, value = input;
		var start = this.queryValue.length, end = input.length;
		if (input.substr(0, start).toLowerCase() != this.queryValue.toLowerCase()) start = 0;
		if (this.options.multiple) {
			var split = this.options.separatorSplit;
			value = this.element.value;
			start += this.queryIndex;
			end += this.queryIndex;
			var old = value.substr(this.queryIndex).split(split, 1)[0];
			value = value.substr(0, this.queryIndex) + input + value.substr(this.queryIndex + old.length);
			if (finish) {
				var space = /[^\s,]+/;
				var tokens = value.split(this.options.separatorSplit).filter(space.test, space);
				if (!this.options.allowDupes) tokens = [].combine(tokens);
				var sep = this.options.separator;
				value = tokens.join(sep) + sep;
				end = value.length;
			}
		}
		this.observer.setValue(value);
		this.opted = value;
		if (finish || this.selectMode == 'pick') start = end;
		this.element.selectRange(start, end);
		this.fireEvent('onSelection', [this.element, this.selected, value, input]);
	},

	showChoices: function() {
		var match = this.options.choicesMatch, first = this.choices.getFirst(match);
		this.selected = this.selectedValue = null;
		if (this.fix) {
			var pos = this.element.getCoordinates(this.relative), width = this.options.width || 'auto';
			this.choices.setStyles({
				'left': pos.left,
				'top': pos.bottom,
				'width': (width === true || width == 'inherit') ? pos.width : width
			});
		}
		if (!first) return;
		if (!this.visible) {
			this.visible = true;
			this.choices.setStyle('display', '');
			if (this.fx) this.fx.start(1);
			this.fireEvent('onShow', [this.element, this.choices]);
		}
		if (this.options.selectFirst || this.typeAhead || first.inputValue == this.queryValue) this.choiceOver(first, this.typeAhead);
		var items = this.choices.getChildren(match), max = this.options.maxChoices;
		var styles = {'overflowY': 'hidden', 'height': ''};
		this.overflown = false;
		if (items.length > max) {
			var item = items[max - 1];
			styles.overflowY = 'scroll';
			styles.height = item.getCoordinates(this.choices).bottom;
			this.overflown = true;
		};
		this.choices.setStyles(styles);
		if (this.fix){
			this.fix.show();
		}
	},

	hideChoices: function(clear) {
		if (clear) {
			var value = this.element.value;
			if (this.options.forceSelect) value = this.opted;
			if (this.options.autoTrim) {
				value = value.split(this.options.separatorSplit).filter(function(){ return arguments[0]; }).join(this.options.separator);
			}
			this.observer.setValue(value);
		}
		if (!this.visible) return;
		this.visible = false;
		this.observer.clear();
		var hide = function(){
			this.choices.setStyle('display', 'none');
			if (this.fix){
				this.fix.hide();
			}
		}.bind(this);
		if (this.fx) this.fx.start(0).chain(hide);
		else hide();
		this.fireEvent('onHide', [this.element, this.choices]);
	},

	prefetch: function() {
		var value = this.element.value, query = value;
		if (this.options.multiple) {
			var split = this.options.separatorSplit;
			var values = value.split(split);
			var index = this.element.getCaretPosition();
			var toIndex = value.substr(0, index).split(split);
			var last = toIndex.length - 1;
			index -= toIndex[last].length;
			query = values[last];
		}
		if (query.length < this.options.minLength) {
			this.hideChoices();
		} else {
			if (query === this.queryValue || (this.visible && query == this.selectedValue)) {
				if (this.visible) return false;
				this.showChoices();
			} else {
				this.queryValue = query;
				this.queryIndex = index;
				if (!this.fetchCached()) this.query();
			}
		}
		return true;
	},

	fetchCached: function() {
		if (!this.options.cache
			|| !this.cached
			|| !this.cached.length
			|| this.cached.length >= this.options.maxChoices
			|| this.queryValue) return false;
		this.update(this.filter(this.cached));
		return true;
	},

	update: function(tokens) {
		this.choices.empty();
		this.cached = tokens;
		if (!tokens || !tokens.length) {
			this.hideChoices();
		} else {
			if (this.options.maxChoices < tokens.length && !this.options.overflow) tokens.length = this.options.maxChoices;
			tokens.each(this.options.injectChoice || function(token){
				var choice = new Element('li', {'html': this.markQueryValue(token)});
				choice.inputValue = token;
				this.addChoiceEvents(choice).inject(this.choices);
			}, this);
			this.showChoices();
		}
	},

	choiceOver: function(choice, selection) {
		if (!choice || choice == this.selected) return;
		if (this.selected) this.selected.removeClass('autocompleter-selected');
		this.selected = choice.addClass('autocompleter-selected');
		this.fireEvent('onSelect', [this.element, this.selected, selection]);
		if (!selection) return;
		this.selectedValue = this.selected.inputValue;
		if (this.overflown) {
			var coords = this.selected.getCoordinates(this.choices), margin = this.options.overflowMargin,
				top = this.choices.scrollTop, height = this.choices.offsetHeight, bottom = top + height;
			if (coords.top - margin < top && top) this.choices.scrollTop = Math.max(coords.top - margin, 0);
			else if (coords.bottom + margin > bottom) this.choices.scrollTop = Math.min(coords.bottom - height + margin, bottom);
		}
		if (this.selectMode) this.setSelection();
	},

	choiceSelect: function(choice) {
		if (choice) this.choiceOver(choice);
		this.setSelection(true);
		this.queryValue = false;
		this.hideChoices();
	},

	filter: function(tokens) {
		return (tokens || this.tokens).filter(function(token) {
			return this.test(token);
		}, new RegExp(((this.options.filterSubset) ? '' : '^') + this.queryValue.escapeRegExp(), (this.options.filterCase) ? '' : 'i'));
	},

	/**
	 * markQueryValue
	 *
	 * Marks the queried word in the given string with <span class="autocompleter-queried">*</span>
	 * Call this i.e. from your custom parseChoices, same for addChoiceEvents
	 *
	 * @param		{String} Text
	 * @return		{String} Text
	 */
	markQueryValue: function(str) {
		if (!this.options.markQuery || !this.queryValue) return str;
		var regex = new RegExp('(' + ((this.options.filterSubset) ? '' : '^') + this.queryValue.escapeRegExp() + ')', (this.options.filterCase) ? '' : 'i');
		return str.replace(regex, '<span class="autocompleter-queried">$1</span>');
	},

	/**
	 * addChoiceEvents
	 *
	 * Appends the needed event handlers for a choice-entry to the given element.
	 *
	 * @param		{Element} Choice entry
	 * @return		{Element} Choice entry
	 */
	addChoiceEvents: function(el) {
		return el.addEvents({
			'mouseover': this.choiceOver.bind(this, el),
			'click': function(){
				var result = this.choiceSelect(el);
				this.fireEvent('choiceConfirm', this.selected);
				return result;
			}.bind(this)
		});
	}
});


// Begin: Source/3rdParty/Autocompleter.Local.js
/*
---
name: Autocompleter.Local

description: Allows Autocompleter to use an object in memory for autocompletion (instead of retrieving via ajax).

version: 1.1.1

license: MIT-style license
author: Harald Kirschner <mail [at] digitarald.de>
copyright: Author

requires: [Autocompleter.Base]

provides: [Autocompleter.Local]
...
 */
Autocompleter.Local = new Class({

	Extends: Autocompleter.Base,

	options: {
		minLength: 0,
		delay: 200
	},

	initialize: function(element, tokens, options) {
		this.parent(element, options);
		this.tokens = tokens;
	},

	query: function() {
		this.update(this.filter());
	}

});


// Begin: Source/3rdParty/Autocompleter.Clientcide.js
/*
---
name: Autocompleter.Clientcide

description: Adds Clientcide css assets to autocompleter automatically.

license: MIT-Style License

requires: [Autocompleter.Base]

provides: Autocompleter.Clientcide
...
*/
(function(){
	Autocompleter.Base = Class.refactor(Autocompleter.Base, {
		initialize: function(a1,a2,a3) {
			this.previous(a1,a2,a3);
			this.writeStyle();
		},
		writeStyle: function(){
			window.addEvent('domready', function(){
				if (document.id('AutocompleterCss')) return;
				new Element('link', {
					rel: 'stylesheet',
					media: 'screen',
					type: 'text/css',
					href: (this.options.baseHref || Clientcide.assetLocation + '/autocompleter')+'/Autocompleter.css',
					id: 'AutocompleterCss'
				}).inject(document.head);
			}.bind(this));
		}
	});
})();


// Begin: Source/UI/Bootstrap.Tooltip.js
/*
---

name: Bootstrap.Tooltip

description: A simple tooltip implementation that works with the Twitter Bootstrap css framework.

authors: [Aaron Newton]

license: MIT-style license.

requires:
 - /Bootstrap
 - /CSSEvents
 - More/Element.Position
 - More/Element.Shortcuts
 - Behavior/Behavior

provides: [Bootstrap.Twipsy, Bootstrap.Tooltip]

...
*/

Bootstrap.Tooltip = Bootstrap.Twipsy = new Class({

	Implements: [Options, Events],

	options: {
		location: 'above', //below, left, right, bottom, top
		animate: true,
		delayIn: 200,
		delayOut: 0,
		fallback: '',
		override: '',
		onOverflow: false,
		offset: 0,
		title: 'title', //element property
		trigger: 'hover', //focus, manual
		getContent: function(el){
			return el.get(this.options.title);
		}
	},

	initialize: function(el, options){
		this.element = document.id(el);
		this.setOptions(options);
		this._attach();
	},

	show: function(){
		this._clear();
		this._makeTip();
		var pos, edge, offset = {x: 0, y: 0};
		switch(this.options.location){
			case 'below': case 'bottom':
				pos = 'centerBottom';
				edge = 'centerTop';
				offset.y = this.options.offset;
				break;
			case 'left':
				pos = 'centerLeft';
				edge = 'centerRight';
				offset.x = this.options.offset;
				break;
			case 'right':
				pos = 'centerRight';
				edge = 'centerLeft';
				offset.x = this.options.offset;
				break;
			default: //top
				pos = 'centerTop';
				edge = 'centerBottom';
				offset.y = this.options.offset;
		}
		if (typeOf(this.options.offset) == "object") offset = this.options.offset;
		this.tip.inject(document.body).show().position({
			relativeTo: this.element,
			position: pos,
			edge: edge,
			offset: offset
		}).removeClass('out').addClass('in');
		this.visible = true;
		if (!Browser.Features.cssTransition || !this.options.animate) this._complete();
		this.fireEvent('show');
		return this;
	},

	hide: function(){
		this._makeTip();
		this.tip.removeClass('in').addClass('out');
		this.visible = false;
		if (!Browser.Features.cssTransition || !this.options.animate) this._complete();
		this.fireEvent('hide');
		return this;
	},

	destroy: function(){
		this._detach();
		if (this.tip) this.tip.destroy();
		this.destroyed = true;
		return this;
	},

	// PRIVATE METHODS

	_makeTip: function(){
		if (!this.tip){
			var location = this.options.location;
			if (location == 'above') location = 'top';    //bootstrap 2.0
			if (location == 'below') location = 'bottom'; //bootstrap 2.0
			this.tip = new Element('div.tooltip').addClass(location)
				 .adopt(new Element('div.tooltip-arrow'))
				 .adopt(
				   new Element('div.tooltip-inner', {
				     html: this.options.override || this.options.getContent.apply(this, [this.element]) || this.options.fallback
				   })
				 );
			if (this.options.animate) this.tip.addClass('fade');
			if (Browser.Features.cssTransition && this.tip.addEventListener){
				this.tip.addEventListener(Browser.Features.transitionEnd, this.bound.complete);
			}
			this.element.set('alt', '').set('title', '');
		}
		return this.tip;
	},

	_attach: function(method){
		method = method || 'addEvents';
		this.bound = {
			enter: this._enter.bind(this),
			leave: this._leave.bind(this),
			complete: this._complete.bind(this)
		};

		if (this.options.trigger == 'hover') {
			this.element[method]({
				mouseenter: this.bound.enter,
				mouseleave: this.bound.leave
			});
		} else if (this.options.trigger == 'focus'){
			this.element[method]({
				focus: this.bound.enter,
				blur: this.bound.leave
			});
		}
	},

	_detach: function(){
		this._attach('removeEvents');
	},

	_clear: function(){
		clearTimeout(this._inDelay);
		clearTimeout(this._outDelay);
	},

	_enter: function(){
		if (this.options.onOverflow){
			var scroll = this.element.getScrollSize(),
			    size = this.element.getSize();
			if (scroll.x <= size.x && scroll.y <= size.y) return;
		}
		this._clear();
		if (this.options.delayIn){
			this._inDelay = this.show.delay(this.options.delayIn, this);
		} else {
			this.show();
		}
	},

	_leave: function(){
		this._clear();
		if (this.options.delayOut){
			this._outDelay = this.hide.delay(this.options.delayOut, this);
		} else {
			this.hide();
		}
	},

	_complete: function(){
		if (!this.visible){
			this.tip.dispose();
		}
		this.fireEvent('complete', this.visible);
	}

});

// Begin: Source/Behaviors/Behavior.BS.Tooltip.js
/*
---

name: Behavior.BS.Tooltip

description: Instantiates Bootstrap.Tooltip based on HTML markup.

license: MIT-style license.

authors: [Aaron Newton]

requires:
 - /Bootstrap.Tooltip
 - Behavior/Behavior
 - More/Object.Extras

provides: [Behavior.BS.Twipsy, Behavior.BS.Tooltip]

...
*/
(function(){
	var filter = {
		defaults: {
			location: 'above', //below, left, right
			animate: true,
			delayIn: 200,
			delayOut: 0,
			onOverflow: false,
			offset: 0,
			trigger: 'hover' //focus, manual
		},
		delayUntil: 'mouseover,focus',
		returns: Bootstrap.Tooltip,
		setup: function(el, api){
			var options = Object.cleanValues(
				api.getAs({
					onOverflow: Boolean,
					location: String,
					animate: Boolean,
					delayIn: Number,
					delayOut: Number,
					fallback: String,
					override: String,
					html: Boolean,
					offset: Number,
					trigger: String
				})
			);
			options.getTitle = Function.from(api.get('content') || el.get('title'));
			var tip = new Bootstrap.Tooltip(el, options);
			api.onCleanup(tip.destroy.bind(tip));
			if (api.event) tip.show();
			return tip;
		}
	};
	Behavior.addGlobalFilters({
		'BS.Tooltip': filter,
		'BS.Twipsy': filter
	});
})();

// Begin: Source/Layout/TabSwapper.js
/*
---

name: TabSwapper

description: Handles the scripting for a common UI layout; the tabbed box.

license: MIT-Style License

requires: [Core/Element.Event, Core/Fx.Tween, Core/Fx.Morph, Core/Element.Dimensions, More/Element.Shortcuts, More/Element.Measure]

provides: TabSwapper

...
*/
var TabSwapper = new Class({
	Implements: [Options, Events],
	options: {
		// initPanel: null,
		// smooth: false,
		// smoothSize: false,
		// maxSize: null,
		// onActive: function(){},
		// onActiveAfterFx: function(){},
		// onBackground: function(){}
		// cookieName: null,
		preventDefault: true,
		selectedClass: 'tabSelected',
		mouseoverClass: 'tabOver',
		deselectedClass: '',
		rearrangeDOM: true,
		effectOptions: {
			duration: 500
		},
		cookieDays: 999
	},
	tabs: [],
	sections: [],
	clickers: [],
	sectionFx: [],
	initialize: function(options){
		this.setOptions(options);
		var prev = this.setup();
		if (prev) return prev;
		if (this.options.initPanel != null) this.show(this.options.initPanel);
		else if (this.options.cookieName && this.recall()) this.show(this.recall().toInt());
		else this.show(0);

	},
	setup: function(){
		var opt = this.options,
		    sections = $$(opt.sections),
		    tabs = $$(opt.tabs);
		if (tabs[0] && tabs[0].retrieve('tabSwapper')) return tabs[0].retrieve('tabSwapper');
		var clickers = $$(opt.clickers);
		tabs.each(function(tab, index){
			this.addTab(tab, sections[index], clickers[index], index);
		}, this);
	},
	addTab: function(tab, section, clicker, index){
		tab = document.id(tab); clicker = document.id(clicker); section = document.id(section);
		//if the tab is already in the interface, just move it
		if (this.tabs.indexOf(tab) >= 0 && tab.retrieve('tabbered')
			 && this.tabs.indexOf(tab) != index && this.options.rearrangeDOM) {
			this.moveTab(this.tabs.indexOf(tab), index);
			return this;
		}
		//if the index isn't specified, put the tab at the end
		if (index == null) index = this.tabs.length;
		//if this isn't the first item, and there's a tab
		//already in the interface at the index 1 less than this
		//insert this after that one
		if (index > 0 && this.tabs[index-1] && this.options.rearrangeDOM) {
			tab.inject(this.tabs[index-1], 'after');
			section.inject(this.tabs[index-1].retrieve('section'), 'after');
		}
		this.tabs.splice(index, 0, tab);
		clicker = clicker || tab;

		tab.addEvents({
			mouseout: function(){
				tab.removeClass(this.options.mouseoverClass);
			}.bind(this),
			mouseover: function(){
				tab.addClass(this.options.mouseoverClass);
			}.bind(this)
		});

		clicker.addEvent('click', function(e){
			if (this.options.preventDefault) e.preventDefault();
			this.show(index);
		}.bind(this));

		tab.store('tabbered', true);
		tab.store('section', section);
		tab.store('clicker', clicker);
		this.hideSection(index);
		return this;
	},
	removeTab: function(index){
		var now = this.tabs[this.now];
		if (this.now == index){
			if (index > 0) this.show(index - 1);
			else if (index < this.tabs.length) this.show(index + 1);
		}
		this.now = this.tabs.indexOf(now);
		return this;
	},
	moveTab: function(from, to){
		var tab = this.tabs[from];
		var clicker = tab.retrieve('clicker');
		var section = tab.retrieve('section');

		var toTab = this.tabs[to];
		var toClicker = toTab.retrieve('clicker');
		var toSection = toTab.retrieve('section');

		this.tabs.erase(tab).splice(to, 0, tab);

		tab.inject(toTab, 'before');
		clicker.inject(toClicker, 'before');
		section.inject(toSection, 'before');
		return this;
	},
	show: function(i){
		if (this.now == null) {
			this.tabs.each(function(tab, idx){
				if (i != idx)
					this.hideSection(idx);
			}, this);
		}
		this.showSection(i).save(i);
		return this;
	},
	save: function(index){
		if (this.options.cookieName)
			Cookie.write(this.options.cookieName, index, {duration:this.options.cookieDays});
		return this;
	},
	recall: function(){
		return (this.options.cookieName) ? Cookie.read(this.options.cookieName) : false;
	},
	hideSection: function(idx) {
		var tab = this.tabs[idx];
		if (!tab) return this;
		var sect = tab.retrieve('section');
		if (!sect) return this;
		if (sect.getStyle('display') != 'none') {
			this.lastHeight = sect.getSize().y;
			sect.setStyle('display', 'none');
			tab.swapClass(this.options.selectedClass, this.options.deselectedClass);
			this.fireEvent('onBackground', [idx, sect, tab]);
		}
		return this;
	},
	showSection: function(idx) {
		var tab = this.tabs[idx];
		if (!tab) return this;
		var sect = tab.retrieve('section');
		if (!sect) return this;
		var smoothOk = this.options.smooth && !Browser.ie;
		if (this.now != idx) {
			if (!tab.retrieve('tabFx'))
				tab.store('tabFx', new Fx.Morph(sect, this.options.effectOptions));
			var overflow = sect.getStyle('overflow');
			var start = {
				display:'block',
				overflow: 'hidden'
			};
			if (smoothOk) start.opacity = 0;
			var effect = false;
			if (smoothOk) {
				effect = {opacity: 1};
			} else if (sect.getStyle('opacity').toInt() < 1) {
				sect.setStyle('opacity', 1);
				if (!this.options.smoothSize) this.fireEvent('onActiveAfterFx', [idx, sect, tab]);
			}
			if (this.options.smoothSize) {
				var size = sect.getDimensions().height;
				if (this.options.maxSize != null && this.options.maxSize < size)
					size = this.options.maxSize;
				if (!effect) effect = {};
				effect.height = size;
			}
			if (this.now != null) this.hideSection(this.now);
			if (this.options.smoothSize && this.lastHeight) start.height = this.lastHeight;
			sect.setStyles(start);
			var finish = function(){
				this.fireEvent('onActiveAfterFx', [idx, sect, tab]);
				sect.setStyles({
					height: this.options.maxSize == effect.height ? this.options.maxSize : "auto",
					overflow: overflow
				});
				sect.getElements('input, textarea').setStyle('opacity', 1);
			}.bind(this);
			if (effect) {
				tab.retrieve('tabFx').start(effect).chain(finish);
			} else {
				finish();
			}
			this.now = idx;
			this.fireEvent('onActive', [idx, sect, tab]);
		}
		tab.swapClass(this.options.deselectedClass, this.options.selectedClass);
		return this;
	}
});


// Begin: Source/Behaviors/Behavior.Tabs.js
/*
---
name: Behavior.Tabs
description: Adds a tab interface (TabSwapper instance) for elements with .css-tab_ui. Matched with tab elements that are .tabs and sections that are .tab_sections.
provides: [Behavior.Tabs]
requires: [Behavior/Behavior, /TabSwapper, More/String.QueryString, More/Object.Extras]
script: Behavior.Tabs.js

...
*/

Behavior.addGlobalFilters({

	Tabs: {
		defaults: {
			'tabs-selector': '.tabs>li',
			'sections-selector': '.tab_sections>li',
			smooth: true,
			smoothSize: true,
			rearrangeDOM: false,
			preventDefault: true
		},
		setup: function(element, api) {
			var tabs = element.getElements(api.get('tabs-selector'));
			var sections = element.getElements(api.get('sections-selector'));
			if (tabs.length != sections.length || tabs.length == 0) {
				api.fail('warning; sections and sections are not of equal number. tabs: ' + tabs.length + ', sections: ' + sections.length);
			}
			var getHash = function(){
				return window.location.hash.substring(1, window.location.hash.length).parseQueryString();
			};

			var ts = new TabSwapper(
				Object.merge(
					{
						tabs: tabs,
						sections: sections,
						initPanel: api.get('hash') ? getHash()[api.get('hash')] : null
					},
					Object.cleanValues(
						api.getAs({
							smooth: Boolean,
							smoothSize: Boolean,
							rearrangeDOM: Boolean,
							selectedClass: String,
							initPanel: Number,
							preventDefault: Boolean
						})
					)
				)
			);
			ts.addEvent('active', function(index){
				if (api.get('hash')) {
					var hash = getHash();
					hash[api.get('hash')] = index;
					window.location.hash = Object.cleanValues(Object.toQueryString(hash));
				}
				api.fireEvent('layout:display', sections[0].getParent());
			});
			element.store('TabSwapper', ts);
			return ts;
		}
	}
});


// Begin: Source/UI/Bootstrap.Popover.js
/*
---

name: Bootstrap.Popover

description: A simple tooltip (yet larger than Bootstrap.Tooltip) implementation that works with the Twitter Bootstrap css framework.

authors: [Aaron Newton]

license: MIT-style license.

requires:
 - /Bootstrap.Tooltip

provides: Bootstrap.Popover

...
*/

Bootstrap.Popover = new Class({

	Extends: Bootstrap.Tooltip,

	options: {
		location: 'right',
		offset: 10,
		getTitle: function(el){
			return el.get(this.options.title);
		},
		content: 'data-content',
		getContent: function(el){
			return el.get(this.options.content);
		}
	},

	_makeTip: function(){
		if (!this.tip){
			this.tip = new Element('div.popover').addClass(this.options.location)
				 .adopt(new Element('div.arrow'))
				 .adopt(
				   new Element('div.popover-inner').adopt(
				     new Element('h3.popover-title', { html: this.options.getTitle.apply(this, [this.element]) || this.options.fallback })
				   ).adopt(
				     new Element('div.popover-content').adopt(
				       new Element('p', { html: this.options.getContent.apply(this, [this.element])})
				     )
				   )
				 );
			if (this.options.animate) this.tip.addClass('fade');
			if (Browser.Features.cssTransition && this.tip.addEventListener){
				this.tip.addEventListener(Browser.Features.transitionEnd, this.bound.complete);
			}
			this.element.set('alt', '').set('title', '');
		}
		return this.tip;
	}

});

// Begin: Source/Behaviors/Behavior.BS.Popover.js
/*
---

name: Behavior.BS.Popover

description: Instantiates Bootstrap.Popover based on HTML markup.

license: MIT-style license.

authors: [Aaron Newton]

requires:
 - /Bootstrap.Popover
 - Behavior/Behavior
 - More/Object.Extras

provides: [Behavior.BS.Popover]

...
*/
Behavior.addGlobalFilters({
	'BS.Popover': {
		defaults: {
		  onOverflow: false,
			location: 'right', //below, left, right
			animate: true,
			delayIn: 200,
			delayOut: 0,
			offset: 10,
			trigger: 'hover' //focus, manual
		},
		delayUntil: 'mouseover,focus',
		returns: Bootstrap.Popover,
		setup: function(el, api){
			var options = Object.cleanValues(
				api.getAs({
					onOverflow: Boolean,
					location: String,
					animate: Boolean,
					delayIn: Number,
					delayOut: Number,
					html: Boolean,
					offset: Number,
					trigger: String
				})
			);
			options.getContent = Function.from(api.get('content'));
			options.getTitle = Function.from(api.get('title') || el.get('title'));
			var tip = new Bootstrap.Popover(el, options);
			if (api.event) tip._enter();
			api.onCleanup(tip.destroy.bind(tip));
			return tip;
		}
	}
});

// Begin: Source/Layout/Collapsible.js
/*
---
name: Collapsible

description: Enables a dom element to, when clicked, hide or show (it toggles) another dom element. Kind of an Accordion for one item.

license: MIT-Style License

requires: [Core/Element.Event, More/Fx.Reveal]

provides: Collapsible
...
*/
var Collapsible = new Class({
	Extends: Fx.Reveal,
	initialize: function(clicker, section, options) {
		this.clicker = document.id(clicker);
		this.section = document.id(section);
		this.parent(this.section, options);
		this.boundtoggle = this.toggle.bind(this);
		this.attach();
	},
	attach: function(){
		this.clicker.addEvent('click', this.boundtoggle);
	},
	detach: function(){
		this.clicker.removeEvent('click', this.boundtoggle);
	}
});
//legacy, this class originated w/ a typo. nice!
var Collapsable = Collapsible;

// Begin: Source/Behaviors/Behavior.Collapsible.js
/*
---
name: Behavior.Collapsible
description: Instantiates a Collapsible class.
provides: [Behavior.Collapsible]
requires: [Behavior/Behavior, /Collapsible]
script: Behavior.Collapsible.js

...
*/

Behavior.addGlobalFilters({

	Collapsible: {
		defaults: {
			target: '+'
		},
		setup: function(element, api) {
			var target = element.getElement(api.get('target'));
			var col = new Collapsible(element, target);
			col.addEvent('reveal', function(){
				api.fireEvent('layout:display', target);
			});
			api.onCleanup(col.detach.bind(col));
			return col;
		},
		returns: Collapsible
	}

});

// Begin: Source/3rdParty/Autocompleter.Remote.js
/*
---
name: Autocompleter.Remote

version: 1.1.1

description: Autocompleter extensions that enable requests for JSON/XHTML data for input suggestions.

license: MIT-style license
author: Harald Kirschner <mail [at] digitarald.de>
copyright: Author

requires: [Autocompleter.Base, Core/Request.HTML, Core/Request.JSON]

provides: [Autocompleter.Remote, Autocompleter.Ajax, Autocompleter.Ajax.Base, Autocompleter.Ajax.Json, Autocompleter.Ajax.Xhtml]

...
 */

Autocompleter.Ajax = {};

Autocompleter.Ajax.Base = new Class({

	Extends: Autocompleter.Base,

	options: {
		// onRequest: function(){},
		// onComplete: function(){},
		postVar: 'value',
		postData: {},
		ajaxOptions: {}
	},

	initialize: function(element, options) {
		this.parent(element, options);
		var indicator = document.id(this.options.indicator);
		if (indicator) {
			this.addEvents({
				'onRequest': indicator.show.bind(indicator),
				'onComplete': indicator.hide.bind(indicator)
			}, true);
		}
	},

	query: function(){
		var data = Object.clone(this.options.postData);
		data[this.options.postVar] = this.queryValue;
		this.fireEvent('onRequest', [this.element, this.request, data, this.queryValue]);
		this.request.send({'data': data});
	},

	/**
	 * queryResponse - abstract
	 *
	 * Inherated classes have to extend this function and use this.parent(resp)
	 *
	 * @param		{String} Response
	 */
	queryResponse: function() {
		this.fireEvent('onComplete', [this.element, this.request, this.response]);
	}

});

Autocompleter.Ajax.Json = new Class({

	Extends: Autocompleter.Ajax.Base,

	initialize: function(el, url, options) {
		this.parent(el, options);
		this.request = new Request.JSON(Object.merge({
			'url': url,
			'link': 'cancel'
		}, this.options.ajaxOptions)).addEvent('onComplete', this.queryResponse.bind(this));
	},

	queryResponse: function(response) {
		this.parent();
		this.update(response);
	}

});

Autocompleter.Ajax.Xhtml = new Class({

	Extends: Autocompleter.Ajax.Base,

	initialize: function(el, url, options) {
		this.parent(el, options);
		this.request = new Request.HTML(Object.merge({
			'url': url,
			'link': 'cancel',
			'update': this.choices
		}, this.options.ajaxOptions)).addEvent('onComplete', this.queryResponse.bind(this));
	},

	queryResponse: function(tree, elements) {
		this.parent();
		if (!elements || !elements.length) {
			this.hideChoices();
		} else {
			this.choices.getChildren(this.options.choicesMatch).each(this.options.injectChoice || function(choice) {
				var value = choice.innerHTML;
				choice.inputValue = value;
				this.addChoiceEvents(choice.set('html', this.markQueryValue(value)));
			}, this);
			this.showChoices();
		}

	}

});


// Begin: Source/Behaviors/Behavior.BS.Tabs.js
/*
---

name: Behavior.BS.Tabs

description: Instantiates Bootstrap.Tabs based on HTML markup.

license: MIT-style license.

authors: [Aaron Newton]

requires:
 - Behavior/Behavior
 - Clientcide/Behavior.Tabs

provides: [Behavior.BS.Tabs]

...
*/
(function(){

	var tabs = Object.clone(Behavior.getFilter('Tabs'));

	Behavior.addGlobalFilters({
		'BS.Tabs': tabs.config
	});

	Behavior.setFilterDefaults('BS.Tabs', {
		'tabs-selector': 'a:not(.dropdown-toggle)',
		'sections-selector': '+.tab-content >',
		'selectedClass': 'active',
		smooth: false,
		smoothSize: false
	});

	Behavior.addGlobalPlugin('BS.Tabs', 'BS.Tabs.CSS', function(el, api, instance){
		instance.addEvent('active', function(index, section, tab){
			el.getElements('.active').removeClass('active');
			tab.getParent('li').addClass('active');
			var dropdown = tab.getParent('.dropdown');
			if (dropdown) dropdown.addClass('active');
		});
	});

})();

// Begin: Source/UI/StickyWin.Ajax.js
/*
---

name: StickyWin.Ajax

description: Adds ajax functionality to all the StickyWin classes.

license: MIT-Style License

requires: [Core/Request, StickyWin, StickyWin.UI, StickyWin.PointyTip]

provides: [StickyWin.Ajax, StickyWin.Modal.Ajax, StickyWin.PointyTip.Ajax]

...
*/
(function(){
	var SWA = function(extend){
		return {
			Extends: extend,
			options: {
				//onUpdate: function(){},
				url: '',
				showNow: false,
				cacheRequest: false,
				requestOptions: {
					method: 'get',
					evalScripts: true
				},
				wrapWithUi: false,
				caption: '',
				uiOptions:{},
				cacheRequest: false,
				handleResponse: function(response){
					if(this.options.cacheRequest) {
						this.element.store(this.Request.options.url, response);
					}
					var responseScript = "";
					this.Request.response.text.stripScripts(function(script){	responseScript += script; });
					if (this.options.wrapWithUi) response = StickyWin.ui(this.options.caption, response, this.options.uiOptions);
					this.setContent(response);
					this.show();
					if (this.evalScripts) Browser.exec(responseScript);
					this.fireEvent('update');
				}
			},
			initialize: function(options){
				var showNow;
				if (options && options.showNow) {
					showNow = true;
					options.showNow = false;
				}
				this.parent(options);
				this.evalScripts = this.options.requestOptions.evalScripts;
				this.options.requestOptions.evalScripts = false;
				this.createRequest();
				if (showNow) this.update();
			},
			createRequest: function(){
				this.Request = new Request(this.options.requestOptions).addEvent('onSuccess',
					this.options.handleResponse.bind(this));
			},
			update: function(url, options){
				this.Request.options.url = url || options.url;
				var cachedResponse;
				if(this.options.cacheRequest) {
					cachedResponse = this.element.retrieve(url);
				}
				if(!cachedResponse) {
					this.Request.setOptions(options).send({url: url||this.options.url});
					return this;
				} else {
					this.Request.fireEvent('onSuccess', cachedResponse);
					return this;
				}
			}
		};
	};
	try { StickyWin.Ajax = new Class(SWA(StickyWin)); } catch(e){}
	try { StickyWin.Modal.Ajax = new Class(SWA(StickyWin.Modal)); } catch(e){}
	try { StickyWin.PointyTip.Ajax = new Class(SWA(StickyWin.PointyTip)); } catch(e){}
})();

// Begin: Source/UI/StickyWin.Prompt.js
/*
---

name: StickyWin.Prompt

description: Defines StickyWin.Prompt, a little prompt box with an input as well as ok close buttons.

license: MIT-Style License

requires: StickyWin.Confirm

provides: [StickyWin.Prompt, StickyWin.prompt]

...
*/

StickyWin.Prompt = new Class({
	Extends: StickyWin.Confirm,
	options: {
		defaultValue: ''
	},
	initialize: function(message, header, options){
		this.addEvent('display', function(){
			this.input.select();
		}.bind(this));
		this.parent.apply(this, arguments);
	},
	makeMessage: function(){
		this.input = new Element('input', {
			value: this.options.defaultValue,
			type: 'text',
			id: 'foo',
			styles: {
				width: '100%'
			},
			events: {
				keyup: function(e) {
					if (e.key == 'enter') {
						this.fireEvent('confirm', this.input.get('value'));
						this.hide();
					}
				}.bind(this)
			}
		});
		return new Element('div').adopt(this.parent()).adopt(this.input);
	},
	build: function(){
		return this.parent(function(){
			this.fireEvent('confirm', this.input.get('value'));
		}.bind(this));
	}
});

StickyWin.prompt = function(caption, message, callback, options) {
	return new StickyWin.Prompt(caption, message, options).addEvent('confirm', callback);
};

// Begin: Source/3rdParty/Autocompleter.JSONP.js
/*
---
name: Autocompleter.JSONP

description: Implements Request.JSONP support for the Autocompleter class.

license: MIT-Style License

requires: [More/Request.JSONP, Autocompleter.Remote]

provides: [Autocompleter.JSONP]
...
*/

Autocompleter.JSONP = new Class({

	Extends: Autocompleter.Ajax.Json,

	options: {
		postVar: 'query',
		jsonpOptions: {},
//		onRequest: function(){},
//		onComplete: function(){},
//		filterResponse: function(){},
		minLength: 1
	},

	initialize: function(el, url, options) {
		this.url = url;
		this.setOptions(options);
		this.parent(el, options);
	},

	query: function(){
		var data = Object.clone(this.options.jsonpOptions.data||{});
		data[this.options.postVar] = this.queryValue;
		this.jsonp = new Request.JSONP(Object.merge({url: this.url, data: data},	this.options.jsonpOptions));
		this.jsonp.addEvent('onComplete', this.queryResponse.bind(this));
		this.fireEvent('onRequest', [this.element, this.jsonp, data, this.queryValue]);
		this.jsonp.send();
	},

	queryResponse: function(response) {
		this.parent();
		var data = (this.options.filter)?this.options.filter.apply(this, [response]):response;
		this.update(data);
	}

});
Autocompleter.JsonP = Autocompleter.JSONP;

// Begin: Source/Behaviors/Behavior.Autocompleter.js
/*
---
name: Behavior.Autocompleter
description: Adds support for Autocompletion on form inputs.
provides: [Behavior.Autocomplete, Behavior.Autocompleter]
requires: [Behavior/Behavior, /Autocompleter.Local, /Autocompleter.Remote, More/Object.Extras]
script: Behavior.Autocomplete.js

...
*/

Behavior.addGlobalFilters({

	/*
		takes elements (inputs) with the data filter "Autocomplete" and creates a autocompletion ui for them
		that either completes against a list of terms supplied as a property of the element (dtaa-autocomplete-tokens)
		or fetches them from a server. In both cases, the tokens must be an array of values. Example:

		<input data-behavior="Autocomplete" data-autocomplete-tokens="['foo', 'bar', 'baz']"/>

		Alternately, you can specify a url to submit the current typed token to get back a list of valid values in the
		same format (i.e. a JSON response; an array of strings). Example:

		<input data-behavior="Autocomplete" data-autocomplete-url="/some/API/for/autocomplete"/>

		When the values ar fetched from the server, the server is sent the current term (what the user is typing) as
		a post variable "term" as well as the entire contents of the input as "value".

		An additional data property for autocomplete-options can be specified; this must be a JSON encoded string
		of key/value pairs that configure the Autocompleter instance (see documentation for the Autocompleter classes
		online at http://www.clientcide.com/docs/3rdParty/Autocompleter but also available as a markdown file in the
		clientcide repo fetched by hue in the thirdparty directory).

		Note that this JSON string can't include functions as callbacks; if you require amore advanced usage you should
		write your own Behavior filter or filter plugin.

	*/

	Autocomplete: {
		defaults: {
			minLength: 1,
			selectMode: 'type-ahead',
			overflow: true,
			selectFirst: true,
			multiple: true,
			separator: ' ',
			allowDupes: true,
			postVar: 'term'
		},
		setup: function(element, api){
			var options = Object.cleanValues(
				api.getAs({
					minLength: Number,
					selectMode: String,
					overflow: Boolean,
					selectFirst: Boolean,
					multiple: Boolean,
					separator: String,
					allowDupes: Boolean,
					postVar: String
				})
			);

			if (element.getData('autocomplete-url')) {
				var aaj = new Autocompleter.Ajax.Json(element, element.getData('autocomplete-url'), options);
				aaj.addEvent('request', function(el, req, data, value){
					data['value'] = el.get('value');
				});
				return aaj;
			} else {
				var tokens = api.getAs(Array, 'tokens');
				if (!tokens) {
					dbug.warn('Could not set up autocompleter; no local tokens found.');
					return;
				}
				return new Autocompleter.Local(element, tokens, options);
			}
		}
	}

});


