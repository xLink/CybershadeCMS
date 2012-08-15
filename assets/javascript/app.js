// http://forrst.com/posts/Refining_my_choice_of_JavaScript_App_Structure-22t

var AppName = AppName || (function() {

    var utils = {};      // Your Toolbox
    var app = {};        // Your Global Logic
    var bootstrap = {};  // Your Page by Page Logic

    utils = {
        cache: {
            window: $(window),
            document: $(document),
            body: $('body')
        },
        home_url: function(path){
            if(typeof path=="undefined"){
                path = '';
            }
            return utils.settings.homeURL+path;            
        },
        settings: {
            debug: true,
            currentUser: -1,
            primus: -1,
            secundus: -1,
            homeURL: -1,
            init: function() {
                _log('Initializing Settings');
                utils.settings.primus = $("meta[name=primus]").attr("content");
                utils.settings.secundus = $("meta[name=secundus]").attr("content");

                // The following are just useful features
                utils.settings.currentUser = $("meta[name=userid]").attr("content");
                utils.settings.homeURL = $("meta[name=url]").attr("content");
            }
        },
        callbacks: {
            doSomething: function() {
                dump('Doing Something');
            }
        },
        log: function(what) {
            if (utils.settings.debug) {
                console.log(what);
            }
        },
        bootstrap: function(){

            var controller = utils.settings.primus;
            var action = utils.settings.secundus;

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
    dump = utils.log;

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
    bootstrap = {
        dashboard: {
            init: function(){
                _log('Auto Loading Dashboard Init Function');
            },
            following: function() {
                _log('Doing code for the Following Page');
            }
        }
    };

    // Your Global Logic
    app = {
        demoLogs: function() {
            if(utils.settings.currentUser == -1){
                _log('User is not logged in');
            }
            _log('Dynamic Links: ' + utils.home_url('some/link'));
        },
        init: function() {

            _log('My App Initializing');

            utils.settings.init();
            utils.bootstrap();
            app.demoLogs();

            _log('My App Initialized');
            _log(utils);

        }
    };

    // Public Functions
    return {
        init: app.init
    };

})();

// MKAY GO
$(document).ready(AppName.init);?
