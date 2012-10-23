



 /* Dropdown
  * bootstrap-dropdown.js v2.1.1
  * http://twitter.github.com/bootstrap/javascript.html#dropdowns
  * ============================================================ */

var Dropdown = new Class({

    Implements: [Options, Events],

    initialize: function( ){
        console.log(['initd']);
        this.element = $$('[dropdown-toggle="dropdown"]');
        this.boundHandle = this._handle.bind(this);
        $$('body').addEvent('click', this.boundHandle);



        /*
        $$('[data-toggle*=dropdown]').addEvent('click', this.toggleMenu.bind(this));
        $$('html').addEvent('click', this.clearMenus);*/
    },

    hideAll: function(){
        var ele = this.element.getElements('.open').removeClass('open');
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
        $$('body').removeEvent('click', this.boundHandle);

        return this;
    },

    _handle: function(e){
        var el = e.target;
        var open = el.getParent('.open');

        if(!el.match('input, select, label') || !open){
            this.hideAll();
        }

        if(this.element.contains(el)){
            var parent = el.match('.dropdown-toggle')
                            ? el.getParent()
                            : el.getParent('.dropdown-toggle');
            console.log(parent);
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

        //this.clearMenus();

        if(!isActive){
            eleParent.toggleClass('open');
            console.log(['toggling ', eleParent]);
            //ele.focus();
        }

        return;
    },

    clearMenus: function(){
        console.log(['clear class', $$('[data-toggle*=dropdown]')]);
        $$('[data-toggle*=dropdown]').getParent().removeClass('open');
    }

});

