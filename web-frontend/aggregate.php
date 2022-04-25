<!doctype html>
<html lang="en">
	<head>
		
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="Ben Pettis" />
        <title>Aggregate Statistics | HTTP 451</title>
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
				<h1>Aggregate Statistics</h1>
				<p>This website is still under construction -  Please imagine that it is still the 1990s and this is Geocities...</p>
				<img src="images/construction.gif" alt="" class="img-fluid"/>
			</header>
			
			<div class="clearfix">
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<hr />
			</div>
			
			<div class="row">
				<div class="col-md">
					<img src="images/placeholder.png" class="img-thumbnail" />
				</div>
				<div class="col-md">
					<img src="images/placeholder.png" class="img-thumbnail" />
				</div>
				<div class="col-md">
					<img src="images/placeholder.png" class="img-thumbnail" />
				</div>
				
			</div>
					
			
			<div>
				<h3>Aggregate Data - Separated by Scan</h3>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<p>You can download the most recent CSV file of all the aggregate data <a href="https://storage.googleapis.com/451-response-stats/aggregate.csv">here</a>.</p>
				<div class="accordion" id="aggregate-data-accordion">
					<div class="accordion-item">
						<h2 class="accordion-header" id="json-files-heading">
							<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#json-files" aria-expanded="true" aria-controls="json-files">
							JSON Files
							</button>
						</h2>
						
						<div class="accordion-collapse collapse show" id="json-files" aria-labelledby="json-files-heading">
							<p>Each JSON file contains a count of the number of hosts responding with a given HTTP response code at the time the script ran</p>
							<ul>
							<?php
							$directoryPrefix = 'aggregate/aggregate-';
							$options = ['prefix' => $directoryPrefix];
			
							foreach ($bucket->objects($options) as $object) {
								echo "<li>";
								echo '<a href="https://storage.googleapis.com/451-response-stats/' . $object->name() . '">';
								# remove the first 10 characters of the string 
								# so we get aggregate-2022-04-22_10-48-29.json instead of aggregate/aggregate-2022-04-22_10-48-29.json
								print(substr($object->name(), 10));
								echo '</a>';
								echo "</li>";
							}
							?>
							</ul>
						</div>
					</div>
					
					<div class="accordion-item">
						<h2 class="accordion-header" id="aggregate-table-heading">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#aggregate-table" aria-expanded="false" aria-controls="aggregate-table">
							Aggregate Data Table
							</button>
						</h2>
						
						<div class="accordion-collapse collapse overflow-scroll" id="aggregate-table" aria-labelledby="aggregate-files-heading">
							<h3>Aggregate Data</h3>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

							<table class="table">
							<?php
							# This is *extremely* clunky but it works!!
				
							$object = $bucket->object('aggregate.csv');
							$contents = $object->downloadAsString();
							$contents = str_replace(PHP_EOL, ";", $contents);
							$rows = explode(";", $contents);
							foreach ($rows as $i => $row) {
								if ($i >0) {
									echo "<tr>";
									$cells = explode(",", $row);
									foreach ($cells as $j => $cell) {
										if ($j > 0) {
											echo "<td>";
											echo $cell;
											echo "</td>";
										} else {
											echo '<th scope="row">';
											echo $cell;
											echo "</th>";
										}
									}
									echo "</tr>";
								} else {
									echo "<tr>";
									$cells = explode(",", $row);
									foreach ($cells as $cell) {
										echo '<th scope="col">';
										echo $cell;
										echo '</th>';
									}
									echo "</tr>";
								}
								
								
							}
							?>
							</table>
						</div>
					</div>

				</div>
				
			</div>
		</main>
		
		<?php include('shared/footer.php'); ?>
		
		</div>
		</div>
	</div>
	</body>
</html>