/*
---

name: MooTools Bootstrap plugins

...
*/

(function(){

	// Slick pseudos
	Slick.definePseudo('visible', function(){
		return ((!this.offsetHeight && !this.offsetWidth) || this.style.display == 'none');
	});


	window.addEvent('domready', function(){

		// Global switch
		// @see  http://twitter.github.com/bootstrap/javascript.html
		var $body = document.id(document.body);

		$body.on = function(namespace) {
			this.store(namespace, true);

			return;
		}

		$body.off = function(namespace) {
			this.store(namespace, false);

			return;
		}
	});

}())




/* ==========================================================
 * bootstrap-affix.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#affix
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
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
 * ========================================================== */


!function ($, $$) {

  "use strict"; // jshint ;_;


 /* AFFIX CLASS DEFINITION
  * ====================== */

  var Affix = function (element, options) {
    var self = this

    this.options = Object.append({}, Element.prototype.affix.defaults, options)
    this.$window = $(window)
      .addEvent('scroll', function(){ self.checkPosition.apply(self, arguments) })
      .addEvent('click', function(){ setTimeout( self.checkPosition.apply(self, arguments), 1) })
    this.$element = $(element)
    this.checkPosition()
  }

  Affix.prototype.checkPosition = function () {
    if ((!this.$element.offsetHeight && !this.$element.offsetWidth) || this.$element.style.display == 'none') return

    var scrollHeight = $(document).getSize().y
      , scrollTop = this.$window.getScroll().y
      , position = this.$element.getPosition() // {x,y}
      , offset = this.options.offset
      , offsetBottom = offset.bottom
      , offsetTop = offset.top
      , reset = 'affix affix-top affix-bottom'
      , affix

    if (typeof offset != 'object') offsetBottom = offsetTop = offset
    if (typeof offsetTop == 'function') offsetTop = offset.top()
    if (typeof offsetBottom == 'function') offsetBottom = offset.bottom()

    affix = this.unpin != null && (scrollTop + this.unpin <= position.top) ?
      false    : offsetBottom != null && (position.y + this.$element.getSize().y >= scrollHeight - offsetBottom) ?
      'bottom' : offsetTop != null && scrollTop <= offsetTop ?
      'top'    : false

    if (this.affixed === affix) return

    this.affixed = affix
    this.unpin = affix == 'bottom' ? position.y - scrollTop : null

    this.$element.removeClass(reset).addClass('affix' + (affix ? '-' + affix : ''))
  }


 /* AFFIX PLUGIN DEFINITION
  * ======================= */

  var old = Element.prototype.affix

  Element.implement('affix', function (option) {
    var $this = $(this)
      , data = $this.retrieve('affix')
      , options = typeof option == 'object' && option
    if (!data) $this.store('affix', (data = new Affix(this, options)))
    if (typeof option == 'string') data[option]()
    return $this
  })

  Element.prototype.affix.Constructor = Affix

  Element.prototype.affix.defaults = {
    offset: 0
  }


 /* AFFIX NO CONFLICT
  * ================= */

  Element.affix.noConflict = function () {
    Element.affix = old
    return this
  }


 /* AFFIX DATA-API
  * ============== */

  window.addEvent('load', function () {
    document.getElements('[data-spy="affix"]').each(function () {
      var $spy = $(this)
        , rawdata = $spy.getProperties('data-offset', 'data-offset-bottom', 'data-offset-top')
        , data = {}

      data.offset = rawdata['data-offset'] || {}
      rawdata['data-offset-bottom'] && (data.offset.bottom = rawdata['data-offset-bottom'])
      rawdata['data-offset-top'] && (data.offset.top = rawdata['data-offset-top'])

      $spy.affix(data)
    })
  })


}(document.id, document.getElements);


/* ==========================================================
 * bootstrap-alert.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#alerts
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
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
 * ========================================================== */


!function ($, $$) {

  "use strict"; // jshint ;_;


 /* ALERT CLASS DEFINITION
  * ====================== */

  var dismiss = '[data-dismiss="alert"]'
    , Alert = function (el) {
        $(el).addEvent('click:relay(' + dismiss + ')', this.close)
      }

  Alert.prototype.close = function (e) {
    var $this = $(e && e.target || this)
      , selector = $this.get('data-target')
      , $parent

    if (!selector) {
      selector = $this.get('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
    }

    $parent = document.getElement(selector)

    e && e.preventDefault()

    $parent || ($parent = $this.hasClass('alert') ? $this : $this.getParent());

    // Create dummy event
    e = { 
      preventDefault: function(){ this.isDefaultPrevented = true
    }}

    $parent.fireEvent('close', e);
//  $parent.trigger(e = $.Event('close'))

    if (e.isDefaultPrevented) return

    $parent.removeClass('in')

    function removeElement() {
      $parent
        .fireEvent('closed', e)
        .dispose()
    }

    Browser.support.transition && $parent.hasClass('fade') ?
      $parent.addEvent(Browser.support.transition.end, removeElement) :
      removeElement()
  }


 /* ALERT PLUGIN DEFINITION
  * ======================= */

  var old = Element.prototype.alert

  Element.implement('alert', function (option) {
      var $this = $(this)
        , data = $this.retrieve('alert')
      if (!data) $this.store('alert', (data = new Alert(this)))
      if (typeof option == 'string') data[option].call($this)
      return $this
  })

  Element.prototype.alert.Constructor = Alert


 /* ALERT NO CONFLICT
  * ================= */

  Element.alert.noConflict = function () {
    Element.alert = old
    return this
  }


 /* ALERT DATA-API
  * ============== */

  $(document).addEvent('click:relay(' + dismiss + ')', Alert.prototype.close);

}(document.id, document.getElements);


/* ============================================================
 * bootstrap-button.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#buttons
 * ============================================================
 * Copyright 2012 Twitter, Inc.
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


!function ($, $$) {

  "use strict"; // jshint ;_;


 /* BUTTON PUBLIC CLASS DEFINITION
  * ============================== */

  var Button = function (element, options) {
    this.$element = $(element)
    this.options = Object.append({}, Element.prototype.button.defaults, options)
  }

  Button.prototype.setState = function (state) {
    var d = 'disabled'
      , $el = this.$element
      , attrName = 'data-' + state + '-text' // Mimic jQuery mixed storage
      , attrResetText = 'data-reset-text'
      , data = $el.getProperties(attrName, attrResetText)
      , val = $el.match('input') ? 'value' : 'html'

    state = state + 'Text'
    data[attrResetText] || $el.setProperty(attrResetText, $el.get(val))

    $el.set(val, data[attrName] || this.options[state])

    // push to event loop to allow forms to submit
    setTimeout(function () {
      state == 'loadingText' ?
        $el.addClass(d).setProperty(d, d) :
        $el.removeClass(d).removeProperty(d)
    }, 0)
  }

  Button.prototype.toggle = function () {
    var $parent = this.$element.getParent('[data-toggle="buttons-radio"]')

    $parent && $parent
      .getElements('.active')
      .removeClass('active')

    this.$element.toggleClass('active')
  }


 /* BUTTON PLUGIN DEFINITION
  * ======================== */

  var old = Element.prototype.button

  Element.implement('button', function (option) {
    var $this = $(this)
      , data = $this.retrieve('button')
      , options = typeof option == 'object' && option
    if (!data) $this.store('button', (data = new Button(this, options)))
    if (option == 'toggle') data.toggle()
    else if (option) data.setState(option)
    return $this
  })

  Element.prototype.button.defaults = {
    loadingText: 'loading...'
  }

  Element.prototype.button.Constructor = Button


 /* BUTTON NO CONFLICT
  * ================== */

  Element.button.noConflict = function () {
    Element.button = old
    return this
  }


 /* BUTTON DATA-API
  * =============== */

  $(document).addEvent('click:relay([data-toggle^="button"])', function (e) { // OR ~=
    if ($(document.body).retrieve('.button.data-api') === false) return
    var $btn = $(e.target)
    if (!$btn.hasClass('btn')) $btn = $btn.getParent('.btn')
    $btn.button('toggle')
  })

}(document.id, document.getElements);


/* ==========================================================
 * bootstrap-carousel.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#carousel
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
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
 * ========================================================== */


!function ($, $$) {

  "use strict"; // jshint ;_;

 /* CAROUSEL CLASS DEFINITION
  * ========================= */

  var Carousel = function (element, options) {
    this.$element = $(element)
    this.$indicators = this.$element.getElement('.carousel-indicators')
    this.options = options
    this.options.pause == 'hover' && this.$element
      .addEvent('mouseenter', this.pause.bind(this)) // $.proxy(this.pause, this)
      .addEvent('mouseleave', this.cycle.bind(this))
  }

  Carousel.prototype = {

    cycle: function (e) {
      if (!e) this.paused = false
      if (this.interval) clearInterval(this.interval);
      this.options.interval
        && !this.paused
        && (this.interval = setInterval(this.next.bind(this), this.options.interval))
      return this
    }

  , getActiveIndex: function () {
      this.$active = this.$element.getElement('.item.active')
      this.$items = this.$active.getParent().getChildren()
      return Array.from(this.$items).indexOf(this.$active)
    }

  , to: function (pos) {
      var activeIndex = this.getActiveIndex()
        , that = this

      if (pos > (this.$items.length - 1) || pos < 0) return

      if (this.sliding) {
        return this.$element.addEvent('slid:once', function () {
          that.to(pos)
        })
      }

      if (activeIndex == pos) {
        return this.pause().cycle()
      }

      return this.slideX(pos > activeIndex ? 'next' : 'prev', $(this.$items[pos]))
    }

  , pause: function (e) {
      if (!e) this.paused = true
      if (this.$element.getElements('.next, .prev').length && Browser.support.transition.end) {
        this.$element.fireEvent(Browser.support.transition.end)
        this.cycle(true)
      }
      clearInterval(this.interval)
      this.interval = null
      return this
    }

  , next: function () {
      if (this.sliding) return
      return this.slideX('next')
    }

  , prev: function () {
      if (this.sliding) return
      return this.slideX('prev')
    }

  , slideX: function (type, next) {
      var $active = this.$element.getElement('.item.active')
        , $next = next || (!$active ? null : type == 'next' ? $active.getNext() : $active.getPrevious())
        , isCycling = this.interval
        , direction = type == 'next' ? 'left' : 'right'
        , fallback  = type == 'next' ? '* ^' : '* !^' // first or last
        , that = this
        , e

      this.sliding = true

      isCycling && this.pause()

      if (!$next) $next = this.$element.getElement(fallback + ' .item')
      if (!$next) return this // Sorry

      e = {
        relatedTarget: $next
      , direction: direction
      , preventDefault: function(){ this.preventedDefault = true }
      , isDefaultPrevented: function(){ return this.preventedDefault }
      }

      if ($next.hasClass('active')) return

      if (this.$indicators && this.$indicators.getElement('.active')) {  // Fix for when clicking too fast on arrows: `Uncaught TypeError: Cannot call method 'removeClass' of null`
        this.$indicators.getElement('.active').removeClass('active')
        this.$element.addEvent('slid:once', function () {
          var $nextIndicator = $(that.$indicators.getChildren()[that.getActiveIndex()])
          $nextIndicator && $nextIndicator.addClass('active')
        })
      }

      if (Browser.support.transition && this.$element.hasClass('slide')) {
        this.$element.fireEvent('slide', e)
        if (e.isDefaultPrevented()) return
        $next.addClass(type)
        $next.offsetWidth // force reflow
        $active.addClass(direction)
        $next.addClass(direction)
        this.$element.addEvent(Browser.support.transition.end + ':once', function () {
          $next.removeClass(type).removeClass(direction).addClass('active')
          $active.removeClass('active').removeClass(direction)
          that.sliding = false
          setTimeout(function () { that.$element.fireEvent('slid') }, 0)
        })
      } else {
        this.$element.fireEvent('slide', e)
        if (e.isDefaultPrevented()) return
        $active.removeClass('active')
        $next.addClass('active')
        this.slididng = false
        this.$element.fireEvent('slid')
      }

      isCycling && this.cycle()

      return this
    }

  }


 /* CAROUSEL PLUGIN DEFINITION
  * ========================== */

  var old = Element.carousel

  Element.implement( 'carousel', function (option) {
    var $this = this
      , data = $this.retrieve('carousel')
      , options = Object.append({}, Element.prototype.carousel.defaults, typeof option == 'object' && option)
      , action = typeof option == 'string' ? option : options.slide
    if (!data) $this.store('carousel', (data = new Carousel(this, options)))
    if (typeof option == 'number') data.to(option)
    else if (action) data[action]()
    else if (options.interval) data.pause().cycle()
    return this
  })

  Element.prototype.carousel.defaults = {
    interval: 5000
  , pause: 'hover'
  }

  Element.prototype.carousel.Constructor = Carousel


 /* CAROUSEL NO CONFLICT
  * ==================== */

  Element.carousel.noConflict = function () {
    Element.carousel = old
    return this
  }

 /* CAROUSEL DATA-API
  * ================= */

  $(document).addEvent('click:relay([data-slide], [data-slide-to])', function(e) {
    var $this = $(this), href
      , $target = document.getElement($this.get('data-target') || (href = $this.get('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
      , options = Object.merge({}, dataset($target), dataset($this)) // TODO: dataset for IE7
      , slideIndex

    $target.carousel(options)

    if (slideIndex = $this.get('data-slide-to')) {
      $target.retrieve('carousel').pause().to(slideIndex).cycle()
    }

    e.preventDefault()
  })


  // Helper. Relies on dataset support (IE8+)
  function dataset ($el) {
    var data = {};

    // jQuery auto typecast & camelcase (haha)
    for (var k in $el.dataset) {
      data[k.camelCase()] = (!isNaN($el.dataset[k]))
        ? Number($el.dataset[k])
        : $el.dataset[k]
      ;
    }

    return data;

    // Or
//  return $el.dataset;
  }

}(document.id, document.getElements);


/* ============================================================
 * bootstrap-dropdown.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#dropdowns
 * ============================================================
 * Copyright 2012 Twitter, Inc.
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


!function ($, $$) {

  "use strict"; // jshint ;_;


 /* DROPDOWN CLASS DEFINITION
  * ========================= */

  var toggle = '[data-toggle=dropdown]'
    , Dropdown = function (element) {
        var $el = $(element).addEvent('click', this.toggle)
        $(document).addEvent('click', function () { // if `document.getElement('html')`, event not fired
          var $parent = $el.getParent()
          if ($parent) $parent.removeClass('open');
        })
      }

  Dropdown.prototype = {

    constructor: Dropdown

  , toggle: function (e) {
      var $this = $(this)
        , $parent
        , isActive

      if ($this.match('.disabled, :disabled')) return

      $parent = getParent($this)

      isActive = $parent.hasClass('open')

      clearMenus()

      if (!isActive) {
        $parent.toggleClass('open')
      }

      $this.focus()

      return false
    }

  , keydown: function (e) {
      var $this
        , $items
        , $active
        , $parent
        , isActive
        , index

      if (!/(38|40|27)/.test(e.keyCode)) return

      $this = $(this)

      e.preventDefault()
      e.stopPropagation()

      if ($this.match('.disabled, :disabled')) return

      $parent = getParent($this)

      isActive = $parent.hasClass('open')

      if (!isActive || (isActive && e.keyCode == 27)) {
        if (e.which == 27) $parent.getElements(toggle).focus()
        return $this.fireEvent('click')
      }

      $items = $parent.getElements('[role=menu] li:not(.divider):visible a')

      if (!$items.length) return

      index = $items.indexOf( $parent.getElement('[role=menu] li:not(.divider):visible a:focus'));
//		items.each(function($item, i){ if ($item.match(':focus')) index = i });

      if (e.keyCode == 38 && index > 0) index--                                        // up
      if (e.keyCode == 40 && index < $items.length - 1) index++                        // down
      if (!~index) index = 0

      $items[index]
        .focus()
    }

  }

  function clearMenus() {
    $$(toggle).each(function ($this, i, arr) {
	  getParent($this).removeClass('open')
    })
  }

  function getParent($this) {
    var selector = $this.getProperty('data-target')
      , $parent

    if (!selector) {
      selector = $this.getProperty('href')
      selector = selector && /#/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
    }

    $parent = selector && $(selector)

    if (!$parent) $parent = $this.getParent()

    return $parent
  }


  /* DROPDOWN PLUGIN DEFINITION
   * ========================== */

  var old = Element.prototype.dropdown

  Element.implement('dropdown', function (option) {
    var $this = $(this)
      , data = $this.retrieve('dropdown')
    if (!data) $this.store('dropdown', (data = new Dropdown(this)))
    if (typeof option == 'string') data[option].call($this)
    return $this
  })

  Element.prototype.dropdown.Constructor = Dropdown


 /* DROPDOWN NO CONFLICT
  * ==================== */

  Element.dropdown.noConflict = function () {
    Element.dropdown = old
    return this
  }


  /* APPLY TO STANDARD DROPDOWN ELEMENTS
   * =================================== */

  $(document)
    .addEvent('click', clearMenus)
    .addEvent('click:relay(.dropdown form)', function(e) { e && e.stopPropagation() })
    .addEvent('click', function(e) { e || e.stopPropagation && e.stopPropagation() })
    .addEvent('click:relay(' + toggle + ')', Dropdown.prototype.toggle)
    .addEvent('keydown:relay(' + toggle + ', [role=menu])', Dropdown.prototype.keydown)

}(document.id, document.getElements);



/* =============================================================
 * bootstrap-scrollspy.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#scrollspy
 * =============================================================
 * Copyright 2012 Twitter, Inc.
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
 * ============================================================== */


!function ($, $$) {

  "use strict"; // jshint ;_;


 /* SCROLLSPY CLASS DEFINITION
  * ========================== */

  function ScrollSpy(element, options) {
    var process = this.process.bind(this, arguments)
      , $element = $(element).match('body') ? $(window) : $(element)
      , href
    this.options = Object.append({}, Element.prototype.scrollspy.defaults, options)
    this.$scrollElement = $element.addEvent('scroll', process)
    this.selector = (this.options.target
      || ((href = $(element).getAttribute('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
      || '') + ' .nav li > a'
    this.$body = document.getElement('body')
    this.refresh()
	this.process()
  }

  ScrollSpy.prototype = {

      constructor: ScrollSpy

    , refresh: function () {
        var self = this
          , $targets

        this.offsets = []
        this.targets = []

        $targets = this.$body
          .getElements(this.selector) // nothing
          .map(function ($el) {
            var href = $el.retrieve('target') || $el.get('href')
              , $href = /^#\w/.test(href) && document.getElement(href)
            return ( $href
              && [ $href.getPosition().y + (!(window == self.$scrollElement) && self.$scrollElement.getScroll().y), href ] ) || null
          })
          .sort(function (a, b) { return a[0] - b[0] })
          .each(function ($this) {
            self.offsets.push($this[0])
            self.targets.push($this[1])
          })
      }

    , process: function () {
        var scrollTop = this.$scrollElement.getScroll().y + this.options.offset
          , scrollHeight = this.$scrollElement.scrollHeight || this.$body.scrollHeight
          , maxScroll = scrollHeight - this.$scrollElement.getSize().y
          , offsets = this.offsets
          , targets = this.targets
          , activeTarget = this.activeTarget
          , i

        if (scrollTop >= maxScroll) {
          return activeTarget != (i = Array.from(targets).getLast())
            && this.activate ( i )
        }

        for (i = offsets.length; i--;) {
          activeTarget != targets[i]
            && scrollTop >= offsets[i]
            && (!offsets[i + 1] || scrollTop <= offsets[i + 1])
            && this.activate( targets[i] )
        }
      }

    , activate: function (target) {
        var active
          , selector

        this.activeTarget = target

        var $parent = document.getElement(this.selector).getParent('.active')
        if ($parent) $parent.removeClass('active')

        selector = this.selector
          + '[data-target="' + target + '"],'
          + this.selector + '[href="' + target + '"]'

        active = document.getElement(selector)
          .getParent('li')
          .addClass('active')

        if (active.getParent('.dropdown-menu'))  {
          active = active.getParent('li.dropdown').addClass('active')
        }

        active.fireEvent('activate')
      }

  }


 /* SCROLLSPY PLUGIN DEFINITION
  * =========================== */

  var old = Element.prototype.scrollspy

  Element.implement( 'scrollspy', function (option) {
    var $this = $(this)
      , data = $this.retrieve('scrollspy')
      , options = typeof option == 'object' && option
    if (!data) $this.store('scrollspy', (data = new ScrollSpy(this, options)))
    if (typeof option == 'string') data[options]()
    return $this
  })

  Element.prototype.scrollspy.Constructor = ScrollSpy

  Element.prototype.scrollspy.defaults = {
    offset: 10
  }


 /* SCROLLSPY NO CONFLICT
  * ===================== */

  Element.scrollspy.noConflict = function () {
    Element.scrollspy = old
    return this
  }


 /* SCROLLSPY DATA-API
  * ================== */

  window.addEvent('load', function () {
    document.getElements('[data-spy="scroll"]').each(function () {
      var $spy = $(this)
      $spy.scrollspy($spy.getProperties('data-target')) // TODO $spy.scrollspy($spy.data())
    })
  })

}(document.id, document.getElements);


/* ========================================================
 * bootstrap-tab.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#tabs
 * ========================================================
 * Copyright 2012 Twitter, Inc.
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
 * ======================================================== */


!function ($, $$) {

  "use strict"; // jshint ;_;


 /* TAB CLASS DEFINITION
  * ==================== */

  var Tab = function (element) {
    this.element = element
  }

  Tab.prototype = {

    constructor: Tab

  , show: function () {


      var $this = this.element
        , $ul = $this.match('ul:not(.dropdown-menu)') ? $this : $this.getParent('ul:not(.dropdown-menu)')
        , selector = $this.get('data-target')
        , previous
        , $target
        , e

      if (!selector) {
        selector = $this.get('href')
        selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
      }

      // Problem: cannot test document fragment (no parent)
      if ($this.getParent('li').hasClass('active')) return

      previous = $ul.getLast('.active a');

      // Simple custom event
      e = {
        relatedTarget: previous
      , target: $this
      , preventDefault: function(){ this.isDefaultPrevented = true }
      }

      // Create & Fire new Event
      $this.fireEvent('show', e, 1) // when no delay, elements get mixed around

      if (e.isDefaultPrevented) return; // No access to event

      $target = document.getElement(selector);
      if (!$target) throw ('cannot find related target');

      this.activate($this.getParent('li'), $ul)
      this.activate($target, $target.getParent(), function() {
        $this.fireEvent('shown', e, 1)
      })
    }

  , activate: function ( element, container, callback) {
      var $active = container.getElements('> .active')
        , transition = callback
            && Browser.support.transition
            && container.getElements('> .active.fade').length

      function next() {

        // V1: Reversed order
/* */
        container.getElements('> .active .dropdown-menu > .active')
          .removeClass('active')
        $active
          .removeClass('active')
/* *
        // V2
        $active
            .removeClass('active')
            .each(function($sublevel){ $sublevel.getElements('> .dropdown-menu > .active')
              .removeClass('active') })
*/
        element.addClass('active');

        if (transition) {
          element.offsetWidth // reflow for transition
          element.addClass('in')
        } else {
          element.removeClass('fade')
        }

        if ( element.getParent('.dropdown-menu') ) {
          element.getParent('li.dropdown').addClass('active')
        }

        callback && callback()
      }

      transition ?
        $active.addEvent(Browser.support.transition.end + ':once', next) :
        next()

      $active.removeClass('in')
    }
  }


 /* TAB PLUGIN DEFINITION
  * ===================== */

  var old = Element.tab

  Element.implement('tab', function ( option ) {
    // Applies to single Element as well to Elements collection
      var $this = this
        , data = $this.retrieve('tab')
      if (!data) $this.store('tab', (data = new Tab(this)))
      if (typeof option == 'string') data[option]()
      return $this
  })

  Element.prototype.tab.Constructor = Tab


 /* TAB NO CONFLICT
  * =============== */

  Element.tab.noConflict = function () {
    Element.tab = old
    return this
  }


 /* TAB DATA-API
  * ============ */

  $(document).addEvent('click:relay([data-toggle="tab"], [data-toggle="pill"])', function(e) {
    if ($(document.body).retrieve('.tab.data-api') === false) return
    e.preventDefault()
    $(this).tab('show')
  })

}(document.id, document.getElements);


/* ===================================================
 * bootstrap-transition.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#transitions
 * ===================================================
 * Copyright 2012 Twitter, Inc.
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
 * ========================================================== */


!function ($, $$) {

  "use strict"; // jshint ;_;


  /* CSS TRANSITION SUPPORT (http://www.modernizr.com/)
   * ======================================================= */

    Browser.extend('support', {
//  Object.merge(Browser, { support: {

    transition: (function () {

      var transitionEnd = (function () {

        var el = document.createElement('bootstrap')
          , transEndEventNames = {
               'WebkitTransition' : 'webkitTransitionEnd'
            ,  'MozTransition'    : 'transitionend'
            ,  'OTransition'      : 'otransitionend' // 'oTransitionEnd otransitionend'
            ,  'transition'       : 'transitionend'
            }
          , name

        for (name in transEndEventNames){
          if (el.style[name] !== undefined) {
            return transEndEventNames[name]
          }
        }

      }())

	  // Add to MooTools NativeEvents
	  // See https://github.com/mootools/mootools-core/pull/2053
	  Element.NativeEvents[transitionEnd] = 2

      return transitionEnd && {
        end: transitionEnd
      }

    })()

  })

}(document.id, document.getElements);


