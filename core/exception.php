<link rel="stylesheet" href="/<?php echo root(); ?>assets/css/exception.css" type="text/css" />
<script src="/<?php echo root(); ?>assets/scripts/exception.js" type="text/javascript"></script>
<div id="wrapper">
    <h1><?php echo $exception->_class; ?><small><?php echo $exception->_code; ?></small></h1><br />
    <h2>Message:</h2>
    <div id="message"><?php echo str_replace("<a href='", "<a target='_blank' href='http://www.php.net/manual/en/", $exception->_message); ?></div><br />
    <h2>Source Code:<small><?php echo $exception->_file; ?> (line: <?php echo $exception->_line; ?>)</small></h2>
    <div id="source"><?php echo $exception->_source; ?></div><br />
    <h2>Stack Trace:<small id="master">[expand all]</small></h2>
    <div id="trace"><?php echo $exception->_trace; ?></div>
</div>