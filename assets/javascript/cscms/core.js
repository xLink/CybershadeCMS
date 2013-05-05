window.addEvent('domready', function() {

    // Bootstrap stuff
    if( $$('[data-toggle="tooltip"]').length ){
        jQuery('[data-toggle="tooltip"]').tooltip();
    }

    if( $$('[data-toggle="popover"]').length ){
        jQuery('[data-toggle="popover"]').popover();
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

                    onSuccess: function(responseText){
                        target.set('html', responseText);

                        // once the output in in the DOM, attach a submit handler on the form if we can grab it :)
                        if( $$('form[data-async]').length ){
                            $$('form[data-async]').each(function(form){
                                new Form.Request(form, target);
                            });
                        }

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
