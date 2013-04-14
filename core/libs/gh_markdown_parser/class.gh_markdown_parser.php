<?php

if( !class_exists('MarkdownExtra_Parser', false) ){
    Core_Classes_coreObj::getLib('markdown_parser');
}

/**
 * Add a few extras from GitHub's Markdown implementation
 * https://github.com/github/github-flavored-markdown
 */
class gh_markdown_parser extends MarkdownExtra_Parser {
    /**
     * Overload to enable single-newline paragraphs
     * https://github.com/github/github-flavored-markdown/blob/gh-pages/index.md#newlines
     */
    function formParagraphs( $text ) {
        // Treat single linebreaks as double linebreaks
        $text = preg_replace('#([^\n])\n([^\n])#', "$1\n\n$2", $text );
        return parent::formParagraphs( $text );
    }


    function _doFencedCodeBlocks_callback($matches) {
        $classname =& $matches[2];
        $attrs     =& $matches[3];
        $codeblock = $matches[4];
        $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
        $codeblock = preg_replace_callback('/^\n+/', array(&$this, '_doFencedCodeBlocks_newlines'), $codeblock);

        if ($classname != "") {
            if ($classname{0} == '.'){
                $classname = substr($classname, 1);
            }
            $attr_str = ' class="'.$this->code_class_prefix.$classname.'"';
        } else {
            $attr_str = $this->doExtraAttributes($this->code_attr_on_pre ? "pre" : "code", $attrs);
        }
        $pre_attr_str  = $this->code_attr_on_pre ? $attr_str : '';
        $code_attr_str = $this->code_attr_on_pre ? '' : $attr_str;
        //$codeblock  = "<pre$pre_attr_str><code$code_attr_str>$codeblock</code></pre>";
        $codeblock = $this->_codemirrorHighlight($codeblock, $classname);

        return "\n\n".$this->hashBlock($codeblock)."\n\n";
    }

    function _geshiHighlight($content, $language=''){
        $langauge = ( is_empty($language) ? 'text' : strtolower($language) );

        $langInfo = grabLangInfo($language);
        $ext      = doArgs('ext', null, $langInfo);
        $language = doArgs('lang', null, $langInfo);
        $geshiExt = doArgs('geshi', null, $langInfo);

        if( is_empty($content) ){
            return false;
        }

        $content = trim($content);
        $content = htmlspecialchars_decode($content, ENT_NOQUOTES);


        $geshi = Core_Classes_coreObj::getLib('GeSHi', array($content, $geshiExt));
        $geshi->set_header_type(GESHI_HEADER_PRE);
        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
        $content = $geshi->parse_code();

        return "\n<div class=\"markdown_code\">\n<div class=\"markdown_code_head\">".$language." Code: </div>\n<div class=\"markdown_code_body\">".
                $content.
            "</div>\n</div>\n";
    }

    function _codemirrorHighlight($content, $language=''){
        $objPage = Core_Classes_coreObj::getPage();

        if( is_empty($content) ){
            return false;
        }

        $objPage->addCSSFile(array(
            'href'     => '/'.root().'assets/styles/codemirror-min.css',
            'priority' => LOW
        ));
        $objPage->addJSFile(array(
            'src'      => '/'.root().'assets/javascript/codemirror-min.js',
            'priority' => LOW
        ), 'footer');
        $objPage->addJSFile(array(
            'src'      => '/'.root().'assets/javascript/codemirror-langs-min.js',
            'priority' => LOW
        ), 'footer');
        $objPage->addJSFile(array(
            'src'      => '/'.root().'assets/javascript/codemirror/highlighter.js',
            'priority' => LOW
        ), 'footer');


        $language = grabLangInfo($language, 'mime');
        $content = trim($content);
        $content = htmlspecialchars_decode($content, ENT_NOQUOTES);

        return "\n<div data-lang=\"".$language."\" data-codemirror=\"true\"><pre>".$content."</pre></div>\n";
    }




}
?>