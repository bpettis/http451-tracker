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
        
        <!-- Lightbox CSS -->
		<link href="css/lightbox.css" rel="stylesheet" />
		
		<?php include('shared/analytics.php'); ?>
<?php

	# Includes the autoloader for libraries installed with composer
	require __DIR__ . '/vendor/autoload.php';
	
	# Imports the Google Cloud client library
	use Google\Cloud\Storage\StorageClient;
	
	# Set up storage client and specify a bucket
	$storage = new StorageClient([
		'keyFile' => json_decode(file_get_contents('http-451-tracker-creds.json'), true)
	]);
	$bucket = $storage->bucket('451-response-stats');
	
	# Set the timezone for graph timestamps
	date_default_timezone_set('America/Chicago');
?>

<?php

# Include the jpGraph files
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');
require_once ('jpgraph/jpgraph_bar.php');
require_once ('jpgraph/jpgraph_pie.php');

# Size for all graphs:
$width = 800; $height = 600;

# Read the HTTP 451 count data into an array

function parse451count($bucket, &$timestamp, &$count) {
	$object = $bucket->object('aggregate.csv');
	$contents = $object->downloadAsString();
	$contents = str_replace(PHP_EOL, ";", $contents);
	$rows = explode(";", $contents);

	# Figure out where to start pulling data from so that we only get the most recent data on the graph
	$recentRows = count($rows) - 45;

	foreach ($rows as $i => $row) {
		# The first row is the header row, so we want to be sure to skip it. I think.
		if ($i > $recentRows AND $i !== 0) {
			$cells = explode(",", $row);
			foreach ($cells as $j => $cell) {
				# The aggregate.csv file has data for *all* http codes, and we only want the 451s, which are in the 33rd column
				if ($j == 33) {
					array_push($count, $cell);
				# The first (0th) column has the dates, so we want to handle it differently
				} elseif ($j == 0) {
					$dateSplit = explode("_", $cell);
					array_push($timestamp, $dateSplit[0]);
				# Just skip everything else in the file
				} else {
					continue;
				}
			}
		} else {
			continue;
		}


	}
}

# Read data from a few different codes into an array
function parseMultipleCodes($bucket, &$timestamp, &$count403, &$count404, &$count418, &$count451, &$count500, &$count502) {
	$object = $bucket->object('aggregate.csv');
	$contents = $object->downloadAsString();
	$contents = str_replace(PHP_EOL, ";", $contents);
	$rows = explode(";", $contents);

	# Figure out where to start pulling data from so that we only get the most recent data on the graph
	$recentRows = count($rows) - 20;

	foreach ($rows as $i => $row) {
		# The first row is the header row, so we want to be sure to skip it. I think.
		if ($i > $recentRows AND $i !== 0) {
			$cells = explode(",", $row);
			foreach ($cells as $j => $cell) {
				# The aggregate.csv file has data for *all* http codes, and we only want the 451s, which are in the 33rd column
				if ($j == 33) {
					array_push($count451, $cell);
				# The data for 404 codes is in the 16th column
				} elseif ($j == 16) {
					array_push($count404, $cell);
				# THe data for 403 codes is in the 15th column
				} elseif ($j == 15) {
					array_push($count403, $cell);
				# Data for the 418 codes is in the 25th column
				} elseif ($j == 25){
					array_push($count418, $cell);
				# Data for the 500 codes is in the 37th column
				} elseif ($j == 37) {
					array_push($count500, $cell);
				#Data for the 502 codes is in the 39th column
				} elseif ($j == 39) {
					array_push($count502, $cell);
				# The first (0th) column has the dates, so we want to handle it differently
				} elseif ($j == 0) {
					$dateSplit = explode("_", $cell);
					array_push($timestamp, $dateSplit[0]);
				# Just skip everything else in the file
				} else {
					continue;
				}
			}
		} else {
			continue;
		}


	}
}

# Read data from some codes to be used in the pie chart
function parsePie($bucket, &$pieScanTime, &$pieData){
	$object = $bucket->object('aggregate.csv');
	$contents = $object->downloadAsString();
	$contents = str_replace(PHP_EOL, ";", $contents);
	$rows = explode(";", $contents);
	#Next we reverse the array, because we only care about the most recent row (the most recent scan)
	$row = array_reverse($rows);
	# We grab the 1st item in the array (and not the zeroth) because there is a blank line at the end of the file
	$cells = explode(',', $row[1]);
	foreach ($cells as $i => $cell){
		# The aggregate.csv file has data for *all* http codes, and we only want the 451s, which are in the 33rd column
		if ($i == 33) {
			array_push($pieData, $cell);
		# The data for 404 codes is in the 16th column
		} elseif ($i == 16) {
			array_push($pieData, $cell);
		# THe data for 403 codes is in the 15th column
		} elseif ($i == 15) {
			array_push($pieData, $cell);
		# Data for the 418 codes is in the 25th column
		} elseif ($i == 25){
			array_push($pieData, $cell);
		# Data for the 500 codes is in the 37th column
		} elseif ($i == 37) {
			array_push($pieData, $cell);
		#Data for the 502 codes is in the 39th column
		} elseif ($i == 39) {
			array_push($pieData, $cell);
		#Data for 200 codes is in the 2nd column
		} elseif ($i == 2) {
			array_push($pieData, $cell);
		# Data for 301 codes is in the 7th column
		} elseif ($i == 7) {
			array_push($pieData, $cell);
		# Data for 307 codes is in the 10th column
		} elseif ($i == 10) {
			array_push($pieData, $cell);
		# The first (0th) column has the dates, so we want to handle it differently
		} elseif ($i == 0) {
			$dateSplit = explode("_", $cell);
			$pieScanTime = $dateSplit[0];
		# Just skip everything else in the row
		} else {
			continue;
		}
	}
}

# START Generate the aggregate-count line graph
$aggregateTimestamp = array();
$aggregateCount = array();
parse451count($bucket, $aggregateTimestamp, $aggregateCount);

@unlink('images/tmp/aggregate-count-line.jpg');

// Create a new timer instance
$timer = new JpgTimer();
 
// Start the timer
$timer->Push();
// Create a graph instance
$graph = new Graph($width,$height);

// Make the bottom margin large enough to hold the timer value
$graph->SetMargin(50,20,20,100);

// Specify what scale we want to use,
// int = integer scale for the X-axis
// int = integer scale for the Y-axis
$graph->SetScale('intint');

// Setup a title for the graph
$graph->title->Set('Count of HTTP 451 Responses');

// Setup titles and X-axis labels
// Set the tick numbers to the timestamp from the file
$graph->xaxis->SetTickLabels($aggregateTimestamp);
$graph->xaxis->SetLabelAngle('45');



// Create the linear plot
$lineplot=new LinePlot($aggregateCount);
// Add some fill to this bad boy
$lineplot->SetFillColor('orange@0.5');
// Add the plot to the graph
$graph->Add($lineplot);
// Add the timing data to the graph
$graph->footer->right->Set('Graph generated at ' . date('Y-m-d H:i:s') . ' in (ms): ');
$graph->footer->SetTimer($timer);
// Save the graph
$graph->Stroke('images/tmp/aggregate-count-line-large.jpg');

# END aggregate-count line graph

#################

# START multiple-code grouped bar graph
@unlink('images/tmp/grouped-bar-chart.jpg');
$groupedBarTimestamp = array();
$groupedBar403 = array();
$groupedBar404 = array();
$groupedBar418 = array();
$groupedBar451 = array();
$groupedBar500 = array();
$groupedBar502 = array();

parseMultipleCodes($bucket, $groupedBarTimestamp, $groupedBar403, $groupedBar404, $groupedBar418, $groupedBar451, $groupedBar500, $groupedBar502);

// Create a new timer instance
$timer = new JpgTimer();
 
// Start the timer
$timer->Push();

$graph = new Graph($width,$height);
$graph->SetScale("textlin");


// Make the bottom margin large enough to hold the timer value
$graph->SetMargin(50,20,20,125);

// Create the bar plots
$b1plot = new BarPlot($groupedBar403);
$b1plot->SetFillColor("orange");
$b1plot->SetLegend("403");
$b2plot = new BarPlot($groupedBar404);
$b2plot->SetFillColor("blue");
$b2plot->SetLegend("404");
$b3plot = new BarPlot($groupedBar418);
$b3plot->SetFillColor("burlywood4");
$b3plot->SetLegend('418');
$b4plot = new BarPlot($groupedBar451);
$b4plot->SetFillColor("red");
$b4plot->SetLegend('451');
$b5plot = new BarPlot($groupedBar500);
$b5plot->SetFillColor("yellow");
$b5plot->SetLegend('500');
$b6plot = new BarPlot($groupedBar500);
$b6plot->SetFillColor("purple");
$b6plot->SetLegend('502');
// Create the grouped bar plot
$gbplot = new GroupBarPlot(array($b1plot,$b2plot,$b3plot, $b4plot, $b5plot, $b6plot));

// ...and add it to the graPH
$graph->Add($gbplot);

// Setup titles and X-axis labels
// Set the tick numbers to the timestamp from the file
$graph->xaxis->SetTickLabels($groupedBarTimestamp);
$graph->xaxis->SetLabelAngle('45');

$graph->yaxis->SetLabelAngle('90');

$graph->title->Set("Proportions of Selected HTTP Error Codes");
$graph->yaxis->title->Set("Count of Hosts");
$graph->legend->SetPos(0.5,0.98,'center','bottom');

// Add the timing data to the graph
$graph->footer->right->Set('Graph generated at ' . date('Y-m-d H:i:s') . ' in (ms): ');
$graph->footer->SetTimer($timer);
$graph->Stroke('images/tmp/grouped-bar-chart-large.jpg');
# END multiple-code grouped bar graph

# START generate the pie-chart
@unlink('images/tmp/pie-chart.jpg');
$pieData = array();
$pieScanTime = 'unknown time';

parsePie($bucket, $pieScanTime, $pieData);
// Create a new timer instance
$timer = new JpgTimer();
 
// Start the timer
$timer->Push();
$graph = new PieGraph($width,$height);
$graph->title->Set("Selected Codes from Last Scan (" . $pieScanTime . ")");
 
$p1 = new PiePlot($pieData);



$legend = array("200","301","307","403","404", "418", "451", "500", "502");
$labels = array("200 - (%.0f)",
                "301 - (%.0f)","307 - (%.0f)",
                "403 - (%.0f)","404 - (%.0f)",
                "418 - (%.0f)","451 - (%.0f)",
                "500 - (%.0f)","502 - (%.0f)",
                );

$p1->SetLabels($labels);
$p1->SetLabelPos(1);
$p1->SetLegends($legend);
$p1->SetLabelType(PIE_VALUE_ABS);
$p1->SetGuideLines( true , false );
$graph->Add($p1);
$p1->SetCenter(0.4, 0.45);
// Add the timing data to the graph
$graph->footer->right->Set('Graph generated at ' . date('Y-m-d H:i:s') . ' in (ms): ');
$graph->footer->SetTimer($timer);
$graph->Stroke('images/tmp/pie-chart-large.jpg');
# END pie chart
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
				<p>	The 451 response code has been a formal part of the HTTP standard for several years, but my parsing of the Censys scan dataset indicates that it is yet to have been widely implemented. There are only a few tens of thousands of hosts returning a 451 code, a miniscule proportion of the hundreds of millions of hosts on the public Internet. This paucity is likely an indication that the restriction of online content is not merely the result of government action, but may be through the actions of private corporations, which is less likely to be reflected in the 451 code. This underscores the utility of analyzing the Web across multiple layers of the stack, rather than homing in on a single textual layer. </p>
				<p>These charts are dynamically generated each time the page loads, and as such will contain the most recently available information. If you'd like to save a current chart, please Right Click and "Save As" onto your own computer. Alternatively, you can download a <a href="https://storage.googleapis.com/451-response-stats/aggregate.csv">CSV file</a> that contains all of the aggregate data to create your own (prettier) charts.</p>
				<hr />
				<p>Click <a href="#downloads">here</a> to skip past the charts to the downloadable data</p>
			</div>
			
			<div class="row">
				<div class="col-md-6">
					<a href="images/tmp/aggregate-count-line-large.jpg" data-lightbox="charts" data-title="Count of HTTP 451 Responses"><img src="images/tmp/aggregate-count-line-large.jpg" class="img-thumbnail" /></a>

					
				</div>
				<div class="col-md-6">
					<h3>Count of HTTP 451 Responses</h3>
					<p>This chart shows the total number of HTTP 451 responses that were present during the last <a href="aggregate.php">aggregate query</a>. This chart offers a snapshot of whether the total amount of HTTP 451 codes "in the wild" is experience any significant increases or decreases. Note that the Y-axis does <em>not</em> start at zero, and that the dynamic scale of the graph is such that it may only encompass a range of a few hundred. Therefore, the chart may tend to over-represent the overall size of any fluctuations in the count of HTTP 451 responses.</p>
				</div>
				<hr /> 
				<div class="col-md-6">
					<a href="images/tmp/grouped-bar-chart-large.jpg" data-lightbox="charts" data-title="Proportions of Selected HTTP Error Codes"><img src="images/tmp/grouped-bar-chart-large.jpg" class="img-thumbnail" /></a>
				</div>
				<div class="col-md-6">
					<h3>Proportion of Selected HTTP Error Codes</h3>
					<p>Despite the HTTP 451 standard having been adopted six years ago, it has yet to be implemented at large scale. An optimistic reading of this may be that there is actually very little legal action requiring website operators to withhold content from certain reasons. The pessimistic view is that such restrictions are still taking place, but in ways that are not as readily apparent. For example, an Internet Service Provider may silently intercept requests for a certain site and return a “404 – Not Found” or “403 – Forbidden” code before the request even reaches the server. This may also mean that in cases of geoblocking, a server is still returning a 200 - OK response. Instead of a 451, the response contains a message about unavailable content rather than the content the user was seeking out, which is frequently the case when a video streaming platform restricts its content to certain regions of the world.</p>
					<p>Interestingly, there are generally significantly more HTTP 418 codes than HTTP 451. The 418 code was introduced as part of an <a href="https://datatracker.ietf.org/doc/html/rfc2324">April Fool's RFC</a> and describes the "HyperText Coffee Pot Control Protocol." The fact that this joke HTTP code is more widespread than HTTP 451, an actual approved standard, is further sign of how unused the 451 code really is.</p>
				</div>
				<hr /> 
				<div class="col-md-6">
					<a href="images/tmp/pie-chart-large.jpg" data-lightbox="charts" data-title="Proportions of Selected HTTP Response Codes from Most Recent Query"><img src="images/tmp/pie-chart-large.jpg" class="img-thumbnail" /></a>
				</div>
				<div class="col-md-6">
					<h3>Proportions of Selected HTTP Response Codes</h3>
					<p>Unsurprisingly, most responses are “200 – OK,” indicating that most public-facing HTTP servers are successfully serving content. The large number of 301 and 307 codes is also unsurprising, as these redirect codes are often used when a website operator redirects plain HTTP requests to use the more secure HTTPS version of the standard. Given the short timespan of this data and the wide fluctuation in accessible Web servers, the precise values of each code are not significant. However, what is notable is that the number of HTTP 451 codes is a whole order of magnitude less than other codes. While there are tens or hundreds of millions of HTTP servers returning 200, 301, 307, 403, 404, and 500, there are mere tens of thousands of 451 responses.</p>
					<p>Interestingly, there are generally significantly more HTTP 418 codes than HTTP 451. The 418 code was introduced as part of an <a href="https://datatracker.ietf.org/doc/html/rfc2324">April Fool's RFC</a> and describes the "HyperText Coffee Pot Control Protocol." The fact that this joke HTTP code is more widespread than HTTP 451, an actual approved standard, is further sign of how unused the 451 code really is.</p>
				</div>
			</div>
					
			<hr />
			
			<div id="downloads">
				<h3>Aggregate Data - Separated by Scan</h3>
				<p>Here you can view the results of the Censys aggregate query from each time the scripts were ran. Each individual JSON file will contain a breakdown of the number of responses for each HTTP response code. Or, you can view the aggregate table where each row is a different time the scripts ran. This is a very large and poorly formatted HTML table, and will likely not display in a very pretty fashion in most browsers.</p>
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