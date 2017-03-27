<html>
	<head>
		<title>Bug Report</title>
	</head>
	<body>
		<h1>ERROR</h1>
		
		<h2>Message</h2>
		<?php echo $exception->getMessage(); ?>
		
		<h2>Trace</h2>
		<?php echo nl2br($exception->getTraceAsString()); ?>
	</body>
</html>