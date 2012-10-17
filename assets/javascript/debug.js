window.addEvent('domready',function() {

    var options = {
        tabSelector: '.tab',
        contentSelector: '.content'
    };
    myTabPane = new TabPane('debug-tabs', options, function(){
        if(window.location.hash && $$('li'+window.location.hash)[0]){
            return $$('ul.nav-tabs li'+ window.location.hash)[0].get('data-index').toInt();
        }
        return 0;
    });

});