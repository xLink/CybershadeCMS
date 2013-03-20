window.addEvent('domready', function() {

    /** ACP Menu Dropdowns **/
    $$('li > a.dropmenu').each(function(ele){
        // get the nav
        var ul = ele.getParent().getChildren('ul')[0];

        // show it to counteract the css & then hide it with the FX 
        ul.show();
        new Fx.Slide( ul ).hide();
    });

    $$('.dropmenu').each(function(ele){
        var ul = ele.getParent().getChildren('div> ul')[0];
        var fx = new Fx.Slide( ul );

        // add a click event to toggle the ul via the FX
        ele.addEvent('click', function(){
            fx.toggle();
        });

    });


});