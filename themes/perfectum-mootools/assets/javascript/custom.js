window.addEvent('domready', function() {

    /** ACP Menu Dropdowns **/
    $$('li > ul.dropmenu').show();
    var myAccordion = new Fx.Accordion($$('li > a.dropmenu'), $$('li > ul.dropmenu'), {
        display: -1,
        alwaysHide: true
    });

});