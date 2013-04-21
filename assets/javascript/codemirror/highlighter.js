window.addEvent('domready', function() {

    $codeBlocks = $$('div.code');

    if( $codeBlocks.length ){

        // replace the code blocks with codemirror instances
        $codeBlocks.getElements('[data-codemirror="true"]').each(function(ele, index){
            ele = ele[0];
            var editor = CodeMirror(
                function(node){
                    ele.parentNode.replaceChild(node, ele);
                }, {
                    value:              ele.textContent,
                    lineNumbers:        true,
                    mode:               ele.get('data-lang') || 'text/plain',
                    readOnly:           'nocursor',
                    theme:              'monokai',
                    //styleActiveLine:    true,
                    lineWrapping:       true,
                    viewportMargin:     Infinity
                }
            );
        });

    }

});
