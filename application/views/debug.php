<html>
    <head>
        <title>Bug Report</title>
    </head>
    <body>
        <h1>ERROR</h1>
        
        <h2>Message</h2>
        <p><?php echo nl2br($exception->getMessage()); ?></p>
        
        <?php if($exception instanceof \Lucinda\SQL\StatementException) { ?>
        <h2>Query</h2>
        <p><?php echo nl2br($exception->getQuery()); ?></p>
        <?php } ?>
        
        <h2>Trace</h2>
        <p><?php echo nl2br($exception->getTraceAsString()); ?></p>
    </body>
</html>