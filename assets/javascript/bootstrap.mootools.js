



 /** Replicating Bootstrap Functionality
  ** Tabs
  ** bootstrap-tab.js v2.1.1
  ** http://twitter.github.com/bootstrap/javascript.html#tabs
  ** ============================================================ */


var Tabs = new Class({

    Implements: [Events, Options],

    options: {
        tabSelector: '.tab',
        contentSelector: '.content',
        activeClass: 'active'
    },

    container: null,

    initialize: function(container, options, showNow) {
        this.setOptions(options);

        this.container = document.id(container);
        this.container.getElements(this.options.contentSelector).setStyle('display', 'none');

        this.container.addEvent('click:relay('+this.options.tabSelector+')', function(event, tab) {
            event.preventDefault();
            this.show(tab);
        }.bind(this));

        if (typeOf(showNow) == 'function') {
            showNow = showNow();
        } else {
            showNow = showNow || 0;
        }

        this.show(showNow);
    },

    get: function(index) {
        if (typeOf(index) == 'element') {
            return this.get(this.indexOf(index));
        } else {
            var tab = this.container.getElements(this.options.tabSelector)[index];
            var content = this.container.getElements(this.options.contentSelector)[index];
            return [tab, content];
        }
    },

    indexOf: function(element) {
        if (element.match(this.options.tabSelector)) {
            return this.container.getElements(this.options.tabSelector).indexOf(element);
        } else if (element.match(this.options.contentSelector)) {
            return this.container.getElements(this.options.contentSelector).indexOf(element);
        } else {
            return -1;
        }
    },

    show: function(what) {
        if (typeOf(what) != 'number') {
            what = this.indexOf(what);
        }

        var items = this.get(what);
        var tab = items[0];
        var content = items[1];

        if (tab) {
            this.container.getElements(this.options.tabSelector).removeClass(this.options.activeClass);
            this.container.getElements(this.options.contentSelector).setStyle('display', 'none');
            tab.addClass(this.options.activeClass);
            content.setStyle('display', 'block');
            this.fireEvent('change', what);
        }
    }

});

window.addEvent('domready', function(){
    $$('[data-tabs="true"]').each(function(i, idx){
        var active = i.getElement('ul.nav-tabs li.active');

        new Tabs(i, {
            tabSelector:        'ul.nav-tabs li',
            contentSelector:    '.tab-content .tab-pane',
            activeClass:        'active'
        }, active);
    });
});



 /** Replicating Bootstrap Functionality
  ** Dropdown
  ** bootstrap-dropdown.js v2.1.1
  ** http://twitter.github.com/bootstrap/javascript.html#dropdowns
  ** ============================================================ */

var Dropdown = new Class({

    Implements: [Options, Events],

    initialize: function(){
        this.element = $$('[data-toggle="dropdown"]');
        this.boundHandle = this._handle.bind(this);
        $$('html').addEvent('click', this.boundHandle);
    },

    hideAll: function(){
        var ele = this.element.getParent().removeClass('open');
        this.fireEvent('hide', ele);

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
        $$('html').removeEvent('click', this.boundHandle);

        return this;
    },

    _handle: function(e){
        var el = e.target;
        var open = el.getParent('.open');

        if(!el.match('input, select, label') || !open){
            this.hideAll();
        }

        if(this.element.contains(el)){
            var parent = el.match('.dropdown-toggle') ? el.getParent() : el.getParent('.dropdown-toggle');

            if(parent){
                e.preventDefault();
                if(!open){
                    this.show(parent);
                }
            }
        }
    },

    toggleMenu: function( e ){
        e.preventDefault();

        ele = e.target;

        if(ele.match('.disabled', ':disabled')){
            return;
        }

        eleParent = ele.getParent();
        isActive = eleParent.hasClass('open');
        if(!isActive){
            eleParent.toggleClass('open');
        }

        return;
    },

    clearMenus: function(){
        $$('[data-toggle*=dropdown]').getParent().removeClass('open');
    }

}); new Dropdown();
