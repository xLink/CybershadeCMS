var JSCMS = new Class({

    Implements: [Options],

    options: {
        cache: {
            window          : $(window),
            document        : $(document),
            body            : $('body'),
            metas           : $$(document.getElementsByTagName('meta'))
        },
        settings: {
            currentUser     : -1,
            root            : -1,
            rootUrl         : -1,
            fullPath        : -1,

            module          : -1,
            method          : -1,

            debug           : true
        }
    },

    initialize: function(){
        this.options.settings = {
            currentUser     : this.getMeta('user_id'),
            root            : this.getMeta('root'),
            rootUrl         : this.getMeta('rootUrl'),
            fullPath        : this.getMeta('fullPath'),

            module          : this.getMeta('module'),
            method          : this.getMeta('method'),

            debug           : true
        };

    },

    root: function(path){
        if(typeof path=="undefined"){
            path = '';
        }

        return this.options.settings.rootUrl+path;
    },

    getMeta: function(name){
        var value = this.options.cache.metas.filter(function(item, index){
            if( item.match('[name="'+name+'"]') ){ return item; }

            return false;
        });

        if( value.length === 0 ){
            return false;
        }

        if( value[0].get('content').length > 0 ){
            return value[0].get('content');
        }

        return false;
    },

    dump: function(what) {
        if (this.options.settings.debug) {
            console.log(what);
        }
    },

    notify: function(msg, options){
        options = options || {
            sticky:         false,
            customClass:    null,
            visibleTime:    2500,
            title:          'Notification'
        };

        options.message = msg || '';

        notifications.show(options);
    }
});

// MKAY GO
window.addEvent('domready', function(){
    cscms = new JSCMS();

    notifications = new Notimoo({
        locationVtype: 'top',
        locationHType: 'right'
    });

});