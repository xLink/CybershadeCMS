<?php

if( !class_exists('MarkdownExtra_Parser') ){
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

    /**
     * Overload to support ```-fenced code blocks
     * https://github.com/github/github-flavored-markdown/blob/gh-pages/index.md#fenced-code-blocks
     */
    function doCodeBlocks( $text ) {
        $text = preg_replace_callback(
            '#' .
            '^```' . // Fenced code block
            '[^\n]*$' . // No language-specific support yet
            '\n' . // Newline
            '(.+?)' . // Actual code here
            '\n' . // Last newline
            '^```$' . // End of block
            '#ms', // Multiline mode + dot matches newlines
            array( $this, '_doCodeBlocks_callback' ),
            $text
        );

        return parent::doCodeBlocks( $text );
    }

}
?>