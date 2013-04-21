window.addEvent('domready', function() {

//     $code = $$('[data-codemirror="true"]');
//     if( $code.length ){

//         $code.each(function(ele){
//             var editor = CodeMirror(
//                 function(node){
//                     ele.parentNode.replaceChild(node, ele);
//                 }, {
//                     value:              ele.textContent,
//                     lineNumbers:        true,
//                     mode:               ele.get('data-lang') || 'text/plain',
//                     readOnly:           'nocursor',
//                     theme:              'monokai',
//                     //styleActiveLine:    true,
//                     lineWrapping:       true,
//                     viewportMargin:     Infinity
//                 }
//             );
// console.log(ele.getPrevious('ul'));
//             // var clip = new ZeroClipboard( ele.getLast('ul.nav-tabs').getChildren('li.copy-code a')[0], {
//             //   moviePath: cscms.root('libaries/zeroclipboard/ZeroClipboard.swf')
//             // } );

//             // clip.setText(ele.textContent);
//             // clip.on('onMouseDown', function(){});
//             // clip.on('wrongflash', function ( client, args ) {});
//         });

//     }

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
