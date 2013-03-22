document.addEvent('domready', function(){
    var tree = new Tree('tree', {
        indicatorOffset: 1,
        checkDrag: function(element){
            return !element.hasClass('nodrag');
        },

        checkDrop: function(element){
            return !element.hasClass('nodrop');
        }

    });

    tree.addEvent('change', function(){
        var stree = tree.serialize();
        var json = JSON.encode( stree );

        $$('pre')[0].set('html', json);
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


});