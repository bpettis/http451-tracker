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

<?php

# Include the jpGraph files
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');
require_once ('jpgraph/jpgraph_bar.php');
require_once ('jpgraph/jpgraph_pie.php');

# Size for all graphs:
$width = 600; $height = 600;

# Read the HTTP 451 count data into an array

function parse451count($bucket, &$timestamp, &$count) {
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
	$recentRows = count($rows) - 15;

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
$graph->footer->right->Set('Graph generated in (ms): ');
$graph->footer->SetTimer($timer);
// Save the graph
$graph->Stroke('images/tmp/aggregate-count-line.jpg');

# END aggregate-count line graph

#################

# START multiple-code grouped bar graph

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
$graph->footer->right->Set('Graph generated in (ms): ');
$graph->footer->SetTimer($timer);
$graph->Stroke('images/tmp/grouped-bar-chart.jpg');
# END multiple-code grouped bar graph

# START generate the pie-chart

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
$graph->footer->right->Set('Graph generated in (ms): ');
$graph->footer->SetTimer($timer);
$graph->Stroke('images/tmp/pie-chart.jpg');
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
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<hr />
			</div>
			
			<div class="row">
				<div class="col-md">
					<img src="images/tmp/aggregate-count-line.jpg" class="img-thumbnail" />

					
				</div>
				<div class="col-md">
					<img src="images/tmp/grouped-bar-chart.jpg" class="img-thumbnail" />
				</div>
				<div class="col-md">
					<img src="images/tmp/pie-chart.jpg" class="img-thumbnail" />
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