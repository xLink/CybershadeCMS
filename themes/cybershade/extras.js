window.addEvent('domready', function() {

    if( $$('[data-toggle="tooltip"]').length ){
        (function($) {
            console.log($('[data-toggle="tooltip"]'));
            $('[data-toggle="tooltip"]').tooltip();
        })(jQuery);
    }

});