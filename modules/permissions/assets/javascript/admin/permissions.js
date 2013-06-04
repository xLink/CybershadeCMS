window.addEvent('domready', function() {

    $$('.nav-pills li').each(function(ele){
        var label = ele.getElements('label')[0];
        var input = ele.getElements('input')[0];
        var inputBox = ele.getParents('ul')[0].getElements('li input')[0];

        label.set('data-value', input.get('value'));
        inputBox.value = input.getParents('ul')[0].getElements('li.active label')[0].get('data-value');
        if( input.get('id').substr( input.get('id').length-1, 1) == '0' ){
            inputBox.type = 'hidden';
        }else{
            input.destroy();
        }

        label.addEvent('click', function(){
            inputBox.value = label.get('data-value');

            // reset all the options
            label.getParent('ul').getChildren('li').removeClass('active').addClass('muted');
            label.getParent('ul').getElements('i').set('class', 'icon-check-empty');


            label.getParent('li').addClass('active').removeClass('muted');
            label.getParent('li').getElement('i').set('class', 'icon-check');

        });

    });

});
