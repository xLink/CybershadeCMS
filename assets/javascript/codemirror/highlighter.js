window.addEvent('domready', function() {

    $code = $$('[data-codemirror="true"]');
    if( $code.length ){

        $code.each(function(ele){
            var editor = CodeMirror(
                function(node){
                    ele.parentNode.replaceChild(node, ele);
                }, {
                  value:            ele.textContent,
                  mode:             ele.get('data-lang') || 'text/plain',
                  readOnly:         'nocursor',
                  theme:            'monokai',
                  lineNumbers:      true,
                  styleActiveLine:  true,
                  lineWrapping:     true,
                  viewportMargin:   Infinity
                }
            );
        });

    }

});