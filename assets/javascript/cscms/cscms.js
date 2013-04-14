var cscms = cscms || (function() {

    var utils     = {};      // Your Toolbox
    var bootstrap = {};      // Your Toolbox
    var app       = {};      // Your Toolbox

    utils = {
        cache: {
            window:     $(window),
            document:   $(document),
            body:       $('body'),
            metas:      $$(document.getElementsByTagName('meta'))
        },
        root: function(path){
            if(typeof path=="undefined"){
                path = '';
            }
            return utils.settings.rootUrl+path;
        },
        getMeta: function(name){
            var value = utils.cache.metas.filter(function(item, index){
                if( item.match('[name="'+name+'"]') ){ return item; }

                return false;
            });

            if( value.length > 0 ){
                return value[0].get('content');
            }
            return false;
        },
        settings: {
            debug: true,
            init: function() {
                utils.settings.currentUser = utils.getMeta('user_id')   || -1;
                utils.settings.root        = utils.getMeta('root')      || -1;
                utils.settings.rootUrl     = utils.getMeta('rootUrl')   || -1;
                utils.settings.fullPath    = utils.getMeta('fullPath')  || -1;

                utils.settings.module      = utils.getMeta('module')    || -1;
                utils.settings.method      = utils.getMeta('method')    || -1;
            }
        },

        dump: function(what) {
            if (utils.settings.debug) {
                console.log(what);
            }
        },
        bootstrap: function(){

            var controller = utils.settings.module;
            var action = utils.settings.method;

            if(typeof bootstrap[controller] != 'undefined'){
                if(typeof bootstrap[controller].init != 'undefined'){
                    bootstrap[controller].init.call();
                }

                if(typeof bootstrap[controller][action] != 'undefined'){
                    bootstrap[controller][action].call();
                }
            }
        }
    };

    /*
     * Your Page by Page Logic
     *
     * Use the following object to store page-specific code. if your controller
     * is dashboard, it would look like the following below. If there is an init
     * function within your controller object, it will be called before any other
     * function is called within that object. Then if your action is set, and that
     * action exists, it will also call that function. Again, these are values based
     * from the meta values dynamically provided to you in the head of your doc.
     */
    modules = {
        dashboard: {
            init: function(){
                utils.dump('Auto Loading Dashboard Init Function');
            },
            following: function() {
                utils.dump('Doing code for the Following Page');
            }
        }
    };

    // Your Global Logic

    app = {
        demoLogs: function() {
            if(utils.settings.currentUser == -1){
                utils.dump('User is not logged in');
            }
            utils.dump('Dynamic Links: ' + utils.root('some/link'));
        },
        init: function() {

            utils.dump('My App Initializing');

            utils.settings.init();
            utils.bootstrap();
            app.demoLogs();

            utils.dump('My App Initialized');
            utils.dump(utils);

        }
    };

    // Public Functions
    return {
        init: app.init
    };

})();

// MKAY GO
window.addEvent('domready', cscms.init);