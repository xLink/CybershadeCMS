window.addEvent('domready', function() {

    $code = $$('[data-codemirror="true"]');
    if( $code.length ){

        $code.each(function(ele){
        console.log(ele.get('html'));
            var editor = CodeMirror(
                function(node){
                    ele.parentNode.replaceChild(node, ele);
                }, {
                  value:            jQuery(ele).text(),
                  lineNumbers:      true,
                  mode:             ele.get('data-lang') || 'text/plain',
                  readOnly:         'nocursor',
                  theme:            'monokai',
                  styleActiveLine:  true,
                  lineWrapping:     true,
                  viewportMargin: Infinity
                }
            );
        });

    }

});