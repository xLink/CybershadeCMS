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

    if( $$('form[data-async]').length ){
        (function($) {
            /** https://gist.github.com/havvg/3226804 **/
            $('form[data-async]').live('submit', function(event) {
                var $form = $(this);
                var $target = $($form.attr('data-target'));

                $.ajax({
                    type: $form.attr('method'),
                    url: $form.attr('action'),
                    data: $form.serialize(),

                    success: function(data, status) {
                        $target.html(data);
                    }
                });

                event.preventDefault();
            });

        })(window.jQuery);
    }

});