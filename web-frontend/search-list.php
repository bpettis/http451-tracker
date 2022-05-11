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
				<h1>HTTP 451 Hosts</h1>
				<p>This website is still under construction -  Please imagine that it is still the 1990s and this is Geocities...</p>
				<img src="images/construction.gif" alt="" class="img-fluid"/>
			</header>
			
			<div class="clearfix">
				<p>The Censys <a href="aggregate.php">aggregate</a> query returns descriptive statistics on the total number of HTTP 451 responses alongside all other HTTP response codes. That query is useful for determining how many responses in general are being returned. However, my interest is also in the specific content that is actually returned to the user's browser (if any content is returned at all). To that end, I needed to query for more specific information from each host returning a HTTP 451 code.</p>
				<p>To accomplish this, one of the scripts writes a text file with a list of IP addresses for the next script to query bulk information from. These IP addresses represent the hosts that meet 2 criteria:
				<ol>
					<li>Returning an HTTP response code of 451</li>
					<li>Have <em>something</em> in the response body</li>
				</ol>
				On its own, this list doesn't really do much. It is primarily just the input data that the <a href="bulk.php">bulk query</a> uses to retrieve the full HTTP response header and body from each host. The results of that bulk query are then parsed to provide just the <a href="responses.php">HTTP responses</a>. However, in the interests of open access scholarship I am providing these lists that are used as an interim data retrieval step for potential use in future research projects.
				</p>
				<hr />
			</div>
								
			<section>
				<h3>IP Lists</h3>
				<p>These text files contain all of the hosts that returned an HTTP response code of 451 to the Censys scanners AND contain something in their response body. The specific Censys search query used is: <code>services.http.response.status_code=451 AND NOT services.http.response.body_size=0</code></p>
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