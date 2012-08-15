/* ============================================================
 * Copyright 2012 Twitter, Inc.
 * Ported to Prototype.js By Dan Aldridge (xLink) 2012
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */

//  /* Transition 
//   * bootstrap-transition.js v2.0.1
//   * http://twitter.github.com/bootstrap/javascript.html#transitions
//   * ============================================================ */

// var Transition = function(){
// 	var thisBody = document.body || document.documentElement,
// 		thisStyle = thisBody.style,
// 		support = Modernizr.csstransitions;

// 	return support && {
// 		end: function(){
// 			var transitionEnd = "TransitionEnd";
// 			if ( Prototype.Browser.webkit ) {
// 				transitionEnd = "webkitTransitionEnd";
// 			} else if ( Prototype.Browser.mozilla ) {
// 				transitionEnd = "transitionend";
// 			} else if ( Prototype.Browser.opera ) {
// 				transitionEnd = "oTransitionEnd";
// 			}
// 			return transitionEnd;
// 		}
// 	}
// }

//  /* Alert 
//   * bootstrap-alert.js v2.0.1
//   * http://twitter.github.com/bootstrap/javascript.html#alerts
//   * ============================================================ */
// var Alert = Class.create({
// 	initialize: function() {
// 		$$('body, [data-dismiss="modal"]').invoke('observe', 'click', this.close.bind(this));
// 	},

// 	close: function(e){
// 		e.stop();

// 		var $this = e.originalTarget,
// 			selector = $this.readAttribute('data-target'),
// 			$parent;

// 		if(!selector){
// 			selector = $this.readAttribute('href');
//         	selector = selector && selector.replace(/.*(?=#[^\s]*$)/, ''); //strip for ie7
// 		}

// 		$parent = $$(selector)[0];
// 		console.log(parent);
// 		$parent.simulate('close');

// 		if(!$parent.length){
// 			$parent = $this.hasClassName('alert') ? $this : $this.up();
// 		}

// 		$parent.simulate('close').removeClassName('in');

// 		function removeElement(){
// 			$parent.simulate('closed').remove();
// 		}

// 		if(Transition && $parent.hasClassName('fade')){
// 			$parent.invoke(Transition.end, removeElement);
// 		}else{
// 			removeElement();
// 		}
// 	}
// }); new Alert();

//  /* Collapse 
//   * bootstrap-collapse.js v2.0.1
//   * http://twitter.github.com/bootstrap/javascript.html#collapse
//   * ============================================================ */

// var Collapse = Class.create({
// 	initialize: function(element, options) {
// 		this.$element = $$('[data-toggle][data-target]')[0];
// 		this.options = Object.extend({
// 			toggle: true
// 		}, options);

// 		if(this.options['parent']){
// 			this.$parent = $$(this.options['parent'])[0];
// 		}

// 		if(this.options.toggle){
// 			this.toggle();
// 		}
// 		//console.log(this.options);
// 	},

// 	dimension: function () {
// 		return this.$element.hasClassName('width') ? 'width' : 'height';
//     },

//     show: function(){
//     	var dimension = this.dimension(),
//     		scroll = $w(this.toTitleCase(['scroll', dimension].join(' '))).join('-'),
//     		actives = this.$parent && this.$parent.findAll('.in'),
//     		hasData;

//     	if(actives && actives.length){
//     		hasData = actives.readAttribute('data');
//     	}
//     	//console.log(['show', scroll, dimension]);
//     },

//     hide: function(){

//     },

//     toggle: function(){
//     	this[this.$element.hasClassName('in') ? 'hide' : 'show']();
//     },


// 	toTitleCase: function (str) {
// 	    return str.replace(/(?:^|\s)\w/g, function(match) {
// 	        return match.toUpperCase();
// 	    });
// 	}


// }); new Collapse();

 /* Dropdown
  * bootstrap-dropdown.js v2.0.1
  * http://twitter.github.com/bootstrap/javascript.html#dropdowns
  * ============================================================ */

var Dropdown = Class.create({
	initialize: function() {
		var dropdown = $$('[data-toggle="dropdown"]');

		dropdown.invoke('observe', 'click', this.toggle.bind(this));

		$$('html').invoke('observe', 'click', this.clearmenus);
	},

	toggle: function(e){
		e.stop();

		var $this = e.originalTarget;
		var selector = $this.readAttribute('data-toggle') || false;
		if(!selector){
			selector = $this.readAttribute('href');
        	selector = selector && selector.replace(/.*(?=#[^\s]*$)/, ''); //strip for ie7
		}

		var parent = $this.up();

		isActive = parent.hasClassName('open');

		this.clearmenus();

		if(!isActive){
			parent.toggleClassName('open');
		}
	},

	clearmenus: function(){
		$$('[data-toggle="dropdown"]').each(function(ele){
			$(ele).up().removeClassName('open');
		});
	}
}); new Dropdown();

