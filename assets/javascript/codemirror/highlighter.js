window.addEvent('domready', function() {

    $code = $$('[data-codemirror="true"]');
    if( $code.length ){
        console.log($code);

        $code.each(function(ele){
            editor = CodeMirror.runMode(ele.get('html'), ele.get('data-language'), ele);

        });

    }

});