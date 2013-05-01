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

    // submit forms that are loaded into a modal via ajax
    if( $$('form[data-async]').length ){
        $$('form[data-async]').each(function(form){
            form.addEvent('submit', function(e){
                e.stop();
                var target = form.get('data-target');

                var asyncForm = new Request({
                    url: form.get('action'),
                    method: form.get('method'),

                    onRequest: function(){
                        target.set('text', 'loading...');
                    },
                    onSuccess: function(responseText){
                        target.set('html', responseText);
                    },
                    onFailure: function(){
                        target.set('text', 'Sorry, your request failed.');
                    }
                });

                asyncForm.send();
                return;
            });
        });
    }

    // if we have any forms that are being loaded via ajax into a modal, then we need to load em here
    if( $$('a[data-async][data-toggle="modal"]').length ){
        $$('a[data-async][data-toggle="modal"]').each(function(ele){
            ele.addEvent('click', function(e){
                //e.preventDefault();
                var target = $$(ele.get('href'))[0];

                var asyncForm = new Request({
                    url: ele.get('data-load'),
                    method: 'get',

                    onRequest: function(){
                        target.set('text', 'loading...');
                    },
                    onSuccess: function(responseText){
                        target.set('html', responseText);
                    },
                    onFailure: function(){
                        target.set('text', 'Sorry, your request failed.');
                    }
                });

                asyncForm.send();
                return;
            });
        });
    }
});