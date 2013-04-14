window.addEvent('domready', function() {

    $code = $$('[data-codemirror="true"]');
    if( $code.length ){
        console.log($code);

        $code.each(function(ele){
            //editor = CodeMirror.runMode(ele.get('html'), ele.get('data-language'), ele);

            CodeMirror.fromTextArea(ele.get('html'), {
                lineNumbers:    true,
                mode:           ele.get('data-language'),
                theme:          'monakai'
            });
        });

    }

});