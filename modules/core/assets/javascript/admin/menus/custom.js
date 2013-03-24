document.addEvent('domready', function(){

    if ( $('tree') ){
        var tree = new Tree('tree', {

            checkDrop: function(element, drop){
                if (drop.target.getParent('ul').match('[id="tree"]')===false && drop.isSubnode){
                    return false;
                }
                return true;
            }

        });

        var dispose = new Element('span.dispose[text=(remove)]').addEvents({
            mousedown: function(event){
                event.preventDefault();
            },

            click: function(){
                this.getParent('li').dispose();
            }
        });

        $('tree').addEvents({
            'mouseover:relay(li)': function(){
                this.getElement('span').adopt(dispose);
            },

            mouseleave: function(){
                dispose.dispose();
            }
        });

    }


    var ajax = new Request({
        url: str_replace('/edit/', '/save/', document.location),
        method: 'post',
        onSuccess: function(responseText){
            $$('div#updates')[0].set('html', responseText);
            $$('div#updates')[0].addClass('alert-info').removeClass('alert-error');
        },
        onFailure: function(responseText){
            $$('div#updates')[0].set('html', responseText);
            $$('div#updates')[0].addClass('alert-error').removeClass('alert-info');
        }
    });
    $$('a.btn-custom')[0].addEvent('click', function(){
        var stree = tree.serialize();
        var json = JSON.encode( stree );

        ajax.send('menu=' + json );
    });

});