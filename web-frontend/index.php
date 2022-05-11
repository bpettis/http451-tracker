<!doctype html>
<html lang="en">
	<head>
		
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="Ben Pettis" />
        <title>Tracking the HTTP 451 Response Code</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="images/favicon/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="images/favicon/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/favicon/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/favicon/favicon-16x16.png">
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
        
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
$graph->footer->right->Set('Graph generated at ' . date('Y-m-d H:i:s') . ' in (ms): ');
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
$graph->footer->right->Set('Graph generated at ' . date('Y-m-d H:i:s') . ' in (ms): ');
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
$graph->footer->right->Set('Graph generated at ' . date('Y-m-d H:i:s') . ' in (ms): ');
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
				<h1>Tracking the HTTP 451 Response Code</h1>
				<p>This website is still under construction -  Please imagine that it is still the 1990s and this is Geocities...</p>
				<img src="images/construction.gif" alt="" class="img-fluid"/>
			</header>
			
			<div class="clearfix">
				<img src="images/logo.png" class="img-thumbnail w-lg-25 ms-md-3 float-md-end" />
				<p><strong><pre><code>451 Unavailable For Legal Reasons</code></pre></strong>
				</p>
				<p><code>This status code indicates that the server is denying access to the resource as a consequence of a legal demand.
				</code></p>
				<p><code>The use of the 451 status code implies neither the existence nor nonexistence of the resource named in the request.  That is to say, it is possible that if the legal demands were removed, a request for the resource still might not succeed.
				</code></p>
				<p><a href="https://datatracker.ietf.org/doc/html/rfc7725"><cite>RFC 7725</cite></a></p>
				<hr />
				<p>Despite its name describe it as “world wide,” the Web is not, and perhaps never has been, truly global. The individual nation-state still matters and exercises its power within online spaces—even at the level of the protocol—to control where and how media can move. The Hypertext Transfer Protocol (HTTP) is the major technological backbone to the World Wide Web and describes the technical standards for computers to follow and exchange hypertext documents with each other. A recently adopted HTTP standard, the “451 – Unavailable for Legal Reasons” response code, shows that legal structures of the nation operate in online spaces and represents the continued restrictions on the flow of media through the Web. Though the extent of its actual implementation remains difficult to determine, the existence of the HTTP 451 status code represents the intertwined nature of law, technology, and cultural practices and prompts us to reconsider just how “worldwide” the World Wide Web is. I argue that the 451 code shows that the Web has not eliminated the significance of national borders and in fact has enabled entirely new fine-grained control over how media does and does not move. A secondary goal of this project is to introduce “full stack analysis” as a model of Web history research in which the protocols and technical underpinnings of the Web are confronted alongside the immediately apparent text to piece together a more expansive view of online media. By studying the implementation of HTTP 451 status codes across multiple levels of the Web stack, I show how law and regulation continue to operate online.</p>
				<p>To find examples of current HTTP 451 implementations and to view the full HTTP conversation, I wrote a <a href="https://github.com/bpettis/http451-tracker">series of python scripts</a> to automatically perform queries on the <a href="https://search.censys.io">Censys dataset</a>. <a href="https://censys.io">Censys</a> is an information security research project which performs daily port scans on the entire IPv4 address space.  In (marginally more) simple terms, Censys queries every publicly accessible IP address and checks for common running services and indexes the results into a searchable database. The Censys data includes details on HTTP servers, including the response code and response body that they return. I use the Censys API to automatically perform searches, for publicly accessible servers that are providing HTTP access and returning a response code of 451 gather daily statistics, and save copies of the fully HTTP response body.  These scripts are ongoing, and in addition to the examples I discuss below, up-to-date data is publicly accessible at this webpage. By collecting information from multiple layers of the Web stack, I am able to view both the page content that a user might view and details about the underlying protocol too.</p>
				<p>Better website design and further explanations forthcoming...</p>
				<p><strong>--Ben Pettis</strong></p>
				<hr />
			</div>
			
			<div class="row">
				<div class="col-md">
					<a href="images/tmp/aggregate-count-line.jpg" data-lightbox="charts" data-title="Count of HTTP 451 Responses"><img src="images/tmp/aggregate-count-line.jpg"  class="img-thumbnail" /></a>

					
				</div>
				<div class="col-md">
					<a href="images/tmp/grouped-bar-chart.jpg" data-lightbox="charts" data-title="Proportion of Selected HTTP Error Codes"><img src="images/tmp/grouped-bar-chart.jpg" class="img-thumbnail" /></a>
				</div>
				<div class="col-md">
					<a href="images/tmp/pie-chart.jpg" data-lightbox="charts" data-title="Selected Codes from Most Recent Scan"><img src="images/tmp/pie-chart.jpg" class="img-thumbnail" /></a>
				</div>
				
			</div>
		

		</main>
		
		<?php include('shared/footer.php'); ?>
		
		</div>
		</div>
	</div>
	</body>
</html>