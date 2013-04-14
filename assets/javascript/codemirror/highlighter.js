window.addEvent('domready', function() {

    $code = $$('[data-codemirror="true"]');
    if( $code.length ){
        console.log($code);

        $code.each(function(ele){
            editor = CodeMirror.runMode(ele.getChildren('pre')[0].get('html'), ele.get('data-lang'), ele.getChildren('pre')[0]);

            // var editor = CodeMirror(
            //     function(node){
            //         ele.parentNode.replaceChild(node, ele);
            //     }, {
            //       value:            ele.innerHTML,
            //       lineNumbers:      true,
            //       mode:             ele.get('data-lang') || 'text/plain',
            //       readOnly:         'nocursor',
            //       theme:            'monokai',
            //       styleActiveLine:  true,
            //       lineWrapping:     true
            //     }
            // );
        });

    }

});