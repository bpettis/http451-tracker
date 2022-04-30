<!doctype html>
<html lang="en">
	<head>
		
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="Ben Pettis" />
        <title>IP Lists | HTTP 451</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="images/favicon/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="images/favicon/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/favicon/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/favicon/favicon-16x16.png">
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
		
<?php

	# Includes the autoloader for libraries installed with composer
	require __DIR__ . '/vendor/autoload.php';
	
	# Imports the Google Cloud client library
	use Google\Cloud\Storage\StorageClient;
	
	# Set up storage client and specify a bucket
	$storage = new StorageClient();
	$bucket = $storage->bucket('451-response-stats');
?>
	</head>
	
	<body>
	<div class="d-flex" id="wrapper">
		<!-- Sidebar-->
        <?php include('shared/sidebar.php'); ?>
		
		<!-- Page content wrapper-->
        <div id="page-content-wrapper">
		
		<!-- Top navigation-->
		<?php include('shared/topnav.php'); ?>
		
		
		<!-- Page content-->
        <div class="container-fluid">
		<main>
			<header>
				<h1>HTTP 451 Hosts</h1>
				<p>This website is still under construction -  Please imagine that it is still the 1990s and this is Geocities...</p>
				<img src="images/construction.gif" alt="" class="img-fluid"/>
			</header>
			
			<div class="clearfix">
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<hr />
			</div>
								
			<section>
				<h3>IP Lists</h3>
				<p>These text files contain all of the hosts that returned an HTTP response code of 451 to the Censys scanners</p>
				<ul>
				<?php
				$directoryPrefix = 'ip-list/search';
				$options = ['prefix' => $directoryPrefix];
			
				foreach ($bucket->objects($options) as $object) {
					echo "<li>";
					echo '<a href="https://storage.googleapis.com/451-response-stats/' . $object->name() . '">';

					print(substr($object->name(), 8));
					echo '</a>';
					echo "</li>";
				}
				?>
				</ul>
			<hr />
			</section>
		</main>
		
		<?php include('shared/footer.php'); ?>
		
		</div>
		</div>
	</div>
	</body>
</html>