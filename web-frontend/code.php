<!doctype html>
<html lang="en">
	<head>
		
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="Ben Pettis" />
        <title>Code | HTTP 451</title>
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
				<h1>Open Source Code</h1>
				<p>This website is still under construction -  Please imagine that it is still the 1990s and this is Geocities...</p>
				<img src="images/construction.gif" alt="" class="img-fluid"/>
			</header>
			
			<div class="clearfix">
				<img src="images/logo.png" class="img-thumbnail w-lg-25 ms-md-3 float-md-end" />
				<p>
				The data is from <a href="https://search.censys.io">Censys</a> but I am searching and parsing it to look for HTTP 451 data specifically.
				<p>
				The Censys data includes details on HTTP servers, including the response code and response body that they return. I use the Censys API to automatically perform searches, for publicly accessible servers that are providing HTTP access and returning a response code of 451 gather daily statistics, and save copies of the fully HTTP response body.
				</p>
				<p>
				If you would like to recreate your own version of this project, you will need to substitute in your own Censys API key. The number of queries that this series of scripts will perform <em>will</em> exceed the limit of the Censys free tier, so you will need to either request researcher-level access or subscribe to one of the paid tiers. Here are my estimates on the number of queries required by each individual script:
				<ul>
					<li>Aggregate - 1</li>
					<li>Search - 70</li>
					<li>Bulk - 900</li>
					<li>Parse - 0</li>
				</ul>
				</p>
				<hr />
			</div>
			
			<div>
				<h2>GitHub Repository</h2>
				<p>
				The code used to query and parse data from the Censys data is written in python. It is publicly available <a href="https://github.com/bpettis/http451-tracker">via GitHub</a> for you to view, use, and remix in your own projects.
				</p>
				<p>
				As a caveat, this is <em>not</em> great code by any imagination! I know just enough Python to be dangerous, but not enough to actually follow good coding practices or efficient methods. Please keep this large grain of salt in mind as you look over the code
				</p>
				<hr />
			</div>
			
			<div>
				<h2>Google Cloud Architecture</h2>
				<p>
				The scripts are set up to stash content in a Google Cloud Storage bucket. When running the scripts, you will need to have a service account configured in Google Cloud - you will also need to create an authentication key for the service account, and ensure that the account has the "Storage Admin" and "Pub/Sub Publisher" roles. Make sure that your environment has the GOOGLE_APPLICATION_CREDENTIALS variable set so that it knows to use this auth key.
				</p>
				<p>
If you are making your own version of these scripts, you will need to substitute the top of each file with the correct project-id and bucket-name that matches your Google Cloud project setup
				</p>
				<p>
I have tested the scripts running in Google Cloud Functions in the Python 3.9 runtime environment. The scripts each send a Pub/Sub notification to trigger the next script. The last two scripts are pretty beefy and pull a lot of data, so be sure to allocate enough memory for them to run!
				<ul>
					<li>Aggregate (256MB)</li>
					<li>Search (256MB)</li>
					<li>Bulk (4GB)</li>
					<li>Parse (4GB)</li>
				</ul>
				</p>
				<h3>Scheduling</h3>
				<p>I use Google Cloud Scheduler to trigger a Pub/Sub notification to start the first script (aggregate.py) on a regular basis. Currently, I have scheduled the series of scripts to run on Monday, Wednesday, and Friday.</p>
				
				<h3>Costs</h3>
				<p>Because the scripts run only periodically, and only require a few minutes of active compute time, the total costs for this project are very low. Additionally, the data produced by the scripts are stored in a Google Cloud Storage bucket, which offers very affordable storage rates. However, the costs will gradually increase the longer the project runs. My plan is to eventually implement object lifecycles where only data from the current year is available in the Standard Tier, and older data is moved to the "Archive Class" - which is cheaper. This will also require me to re-work this website to display older data in a slightly different way.</p>
				<p>Additionally, because the data is stored as simple static files - TXT, JSON, and CSV - there is no need to run a database. While this does somewhat limit my ability to query the data in dynamic ways, it does keep costs very low and manageable for my grad student budget. Anyone choosing to implement a similar structure in their own projects is encouraged to consider their own needs and whether a database will better serve their goals.</p>
				<img class="mw-50" src="images/gcloud-billing-chart.png" alt="a simple line graph showing Google Cloud Platform costs. There are three blue triangles toward the left of the graph, indicating costs of around $0.02 on May 3rd, May 5th, and May 8th"/>
				<br />
				<img class="mw-50" src="images/gcloud-billing-table.png" alt="a table of costs for various Google Cloud Platform services. Most items are a total of $0.00. The service 'Regional Standard Class A Operations' is $0.04 and 'Standard Storage US Multi-region' is $0.01"/>
				<hr />
			</div>
		
		</main>
		
		<?php include('shared/footer.php'); ?>
		
		</div>
		</div>
	</div>
	</body>
</html>