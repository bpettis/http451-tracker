<!doctype html>
<html lang="en">
	<head>
		
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="Ben Pettis" />
        <title>HTTP Responses | HTTP 451</title>
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
				<h1>HTTP Responses</h1>
				<p>This website is still under construction -  Please imagine that it is still the 1990s and this is Geocities...</p>
				<img src="images/construction.gif" alt="" class="img-fluid"/>
			</header>
			
			<div class="clearfix">
				<p>The HTTP 451 standard states that one “should” provide information about the entity performing the block, as well as an explanation of the block reason for the user to understand. The HTTP responses that I examined take this “should” directive very loosely and provide very little information about the reasons that the content on the page is not available. There were no examples of a server naming the blocking entity within the HTTP response header, leaving it unclear whether it was the website operator that received the legal request or if it was an Internet Service Provider or other intermediary.</p>
				</p>While there were examples of the HTTP response body containing more information, there is still very little meaning explanation given to the user. Many response bodies were a single line of text containing “451,” “not authorized,” or simply “error.” None of these responses adequately explain to a user the reason for content being inaccessible, and for a user who is not familiar with HTTP response codes, they are unlikely to even associate such a message with legal content restrictions. In other examples, such as Figure 3, there was a full Web page delivered with a slightly longer message, but still with little clarity or explanation provided for the user.</p>
				<p>In the handful of examples of actual HTTP 451 responses that I have reviewed, it is apparent that there is often very little explanation of the legal reasoning behind content blocking. This is a reminder that while geoblocking is a common feature of the modern Web, there is a tendency to downplay its role and significance. The use of an HTTP 451 response code tends to obfuscates whose interests are being served when Web content is restricted. The code suggests that when content is inaccessible it is a failure of technology, and not the result of corporate interests and government power enacted within code. And while some of the above examples contain cryptic messages and unhelpful explanations, I suppose it is at very least <em>some</em> acknowledgement that content is being withheld for legal reasons, rather than providing a more inaccurate error such as “404 - Not Found” or terminating the connection to the server entirely. The HTTP 451 response code along with the response body that is returned to the user represents the ability of governments to exert their power in online spaces. The government action is seen not just in the page content, but reflected in the lower levels of code as well</p>
				<hr />
			</div>
								
			<section>
				<h3>HTTP Responses - Sorted by Scan Date</h3>
				<p>The below list includes a generated version of the HTML page that a user may have seen when receiving an HTTP 451 error from a given host. The directory prefix indicates the date and time that the data was parses, and the file name indicates the IP address. The HTML file includes the entirety of the HTTP response body (which in most cases is HTML, but may have been raw text or JSON). The TXT file contains the full HTTP response, including the header.</p>
				<?php
				$directoryPrefix = 'responses/';
				$options = ['prefix' => $directoryPrefix];
				$alldates = array();
				foreach ($bucket->objects($options) as $object) {
					array_push($alldates, substr($object->name(), 0, 26));
				}
				$dates = array_unique($alldates);
				?>
				
				
			</section>
			
			<div>
				<div class="accordion" id="responses-accordian">
				<?php
				
				foreach ($dates as $date) {
					$options = ['prefix' => $date];
					
					# We have to truncate the name with substr(), but then add back in a prefix because HTML ID names break when they start with an integer
					$date_truncated = 'responses-' . substr($date, 10);
				?>
				<div class="accordion-item">
						<h2 class="accordion-header" id="<?php echo $date_truncated; ?>-heading">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $date_truncated; ?>" aria-expanded="true" aria-controls="<?php echo $date_truncated; ?>">
							<?php echo substr($date_truncated, 10); ?>
							</button>
						</h2>					
					<div class="accordion-collapse collapse" id="<?php echo $date_truncated; ?>" aria-labelledby="<?php echo $date_truncated; ?>-heading">
						<a href="#bottom-<?php echo $date_truncated; ?>" id="top-<?php echo $date_truncated; ?>">Jump to Bottom of Section</a> <br />
						<ul>
						<?php
						foreach ($bucket->objects($options) as $object) {
							echo "<li>";
							echo '<a href="https://storage.googleapis.com/451-response-stats/' . $object->name() . '">';
							# printf('Object: %s' . PHP_EOL, $object->name());
							print(substr($object->name(), 10));
							echo '</a>';
							echo "</li>";
						}
						?>
						</ul>
						<a href="#top-<?php echo $date_truncated; ?>" id="bottom-<?php echo $date_truncated; ?>">Jump to Top of Section</a> <br />
					</div>
				</div>
				<?php
				}
				?>
				
			
		</main>
		
		<?php include('shared/footer.php'); ?>
		
		</div>
		</div>
	</div>
	</body>
</html>