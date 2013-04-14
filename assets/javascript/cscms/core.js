window.addEvent('domready', function() {

    // Bootstrap stuff
    if( $$('[data-toggle="tooltip"]').length ){
        (function($) {
            $('[data-toggle="tooltip"]').tooltip();
        })(window.jQuery);
    }

    if( $$('[data-toggle="popover"]').length ){
        (function($) {
            $('[data-toggle="popover"]').popover();
        })(window.jQuery);
    }

});