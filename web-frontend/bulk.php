<!doctype html>
<html lang="en">
	<head>
		
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="Ben Pettis" />
        <title>Bulk Censys Data | HTTP 451</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="images/favicon/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="images/favicon/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/favicon/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/favicon/favicon-16x16.png">
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
        
        <?php include('shared/analytics.php'); ?>
		
		<!-- Lightbox CSS -->
		<link href="css/lightbox.css" rel="stylesheet" />
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
				<h1>Bulk Censys Data</h1>
				<p>This website is still under construction -  Please imagine that it is still the 1990s and this is Geocities...</p>
				<img src="images/construction.gif" alt="" class="img-fluid"/>
			</header>
			
			<div class="clearfix">
				<p>After using the <a href="search-list.php">search query</a> to compile a list of hosts returning a HTTP 451 code <em>AND</em> have content in the response body, I used the Censys Bulk Query API endpoint to retrieve full information about each host from the most recently available Censys scan. I then parse this data to retrieve the <a href="responses.php">HTTP responses</a> from each host. However, there is still a large amount of data that Censys includes in the bulk response. Though I am not currently using this data, it is being collected as part of my processing scripts and in the interest of supporting future research projects I am providing copies of that search result here.</p>
				<hr />
			</div>
								
			<section>
				<h3>Full Scan Results</h3>
				<p>Each linked JSON file contains the complete data that Censys reported for each host from the above IP list. This includes <em>every</em> piece of information that Censys scanned from each host, including services other than HTTP</p>
				<ul>
				<?php
				$directoryPrefix = 'bulk/bulk';
				$options = ['prefix' => $directoryPrefix];
			
				foreach ($bucket->objects($options) as $object) {
					echo "<li>";
					echo '<a href="https://storage.googleapis.com/451-response-stats/' . $object->name() . '">';
					print(substr($object->name(), 5));
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