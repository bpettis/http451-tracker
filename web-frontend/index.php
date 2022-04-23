<!doctype html>
<html lang="en">
	<head>
		<title>Tracking the HTTP 451 Response Code</title>
		
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
		<header>
			<h1>Tracking the HTTP 451 Response Code</h1>
			<nav>
				<ul>
					<li>Home</li>
					<li>About</li>
					<li>HTTP 451 Responses</li>
					<li>Aggregate Stats</li>
					<li>Additional Data
						<ul>
							<li>IP Lists</li>
							<li>Bulk Data</li>
						</ul>
				</ul>
			</nav>
		</header>
		<main>
			<h2><?php echo "Hello world!" ?></h2>
			<p>This website is still under construction -  Please imagine that it is still the 1990s and this is Geocities...</p>
			<img src="images/construction.gif" alt="" />
			<p>Despite its name describe it as “world wide,” the Web is not, and perhaps never has been, truly global. The individual nation-state still matters and exercises its power within online spaces—even at the level of the protocol—to control where and how media can move. The Hypertext Transfer Protocol (HTTP) is the major technological backbone to the World Wide Web and describes the technical standards for computers to follow and exchange hypertext documents with each other. A recently adopted HTTP standard, the “451 – Unavailable for Legal Reasons” response code, shows that legal structures of the nation operate in online spaces and represents the continued restrictions on the flow of media through the Web. Though the extent of its actual implementation remains difficult to determine, the existence of the HTTP 451 status code represents the intertwined nature of law, technology, and cultural practices and prompts us to reconsider just how “worldwide” the World Wide Web is. I argue that the 451 code shows that the Web has not eliminated the significance of national borders and in fact has enabled entirely new fine-grained control over how media does and does not move. A secondary goal of this paper is to introduce “full stack historiography” as a model of Web history research in which the protocols and technical underpinnings of the Web are confronted alongside the immediately apparent text to piece together a more expansive view of online media. By studying the implementation of HTTP 451 status codes across multiple levels of the Web stack, I show how law and regulation continue to operate online.</p>
			<p>To find examples of current HTTP 451 implementations and to view the full HTTP conversation, I wrote a <a href="https://github.com/bpettis/http451-tracker">series of python scripts</a> to automatically perform queries on the <a href="https://search.censys.io">Censys dataset</a>. <a href="https://censys.io">Censys</a> is an information security research project which performs daily port scans on the entire IPv4 address space.  In (marginally more) simple terms, Censys queries every publicly accessible IP address and checks for common running services and indexes the results into a searchable database. The Censys data includes details on HTTP servers, including the response code and response body that they return. I use the Censys API to automatically perform searches, for publicly accessible servers that are providing HTTP access and returning a response code of 451 gather daily statistics, and save copies of the fully HTTP response body.  These scripts are ongoing, and in addition to the examples I discuss below, up-to-date data is publicly accessible at this webpage. By collecting information from multiple layers of the Web stack, I am able to view both the page content that a user might view and details about the underlying protocol too.</p>
			<p>Better website design and further explanations forthcoming...</p>
			<p><strong>--Ben Pettis</strong></p>
			<hr />
			
			<section>
				<h3>Aggregate Data</h3>
				<p>Each JSON file contains a count of the number of hosts responding with a given HTTP response code at the time the script ran</p>
				<ul>
				<?php
				$directoryPrefix = 'aggregate/';
				$options = ['prefix' => $directoryPrefix];
			
				foreach ($bucket->objects($options) as $object) {
					echo "<li>";
					echo '<a href="https://storage.googleapis.com/451-response-stats/' . $object->name() . '">';
					# printf('Object: %s' . PHP_EOL, $object->name());
					print($object->name());
					echo '</a>';
					echo "</li>";
				}
				?>
				</ul>
			<hr />
			</section>
			
			<section>
				<h3>IP Lists</h3>
				<p>These text files contain all of the hosts that returned an HTTP response code of 451 to the Censys scanners</p>
				<ul>
				<?php
				$directoryPrefix = 'ip-list/';
				$options = ['prefix' => $directoryPrefix];
			
				foreach ($bucket->objects($options) as $object) {
					echo "<li>";
					echo '<a href="https://storage.googleapis.com/451-response-stats/' . $object->name() . '">';
					# printf('Object: %s' . PHP_EOL, $object->name());
					print($object->name());
					echo '</a>';
					echo "</li>";
				}
				?>
				</ul>
			<hr />
			</section>
			
			<section>
				<h3>HTTP Responses</h3>
				<p>The below list includes a generated version of the HTML page that a user may have seen when receiving an HTTP 451 error from a given host. The directory prefix indicates the date and time that the data was parses, and the file name indicates the IP address. The HTML file includes the entirety of the HTTP response body (which in most cases is HTML, but may have been raw text or JSON). The TXT file contains the full HTTP response, including the header.</p>
				<ul>
				<?php
				$directoryPrefix = 'responses/';
				$options = ['prefix' => $directoryPrefix];
			
				foreach ($bucket->objects($options) as $object) {
					echo "<li>";
					echo '<a href="https://storage.googleapis.com/451-response-stats/' . $object->name() . '">';
					# printf('Object: %s' . PHP_EOL, $object->name());
					print($object->name());
					echo '</a>';
					echo "</li>";
				}
				?>
				</ul>
			<hr />
			</section>
			
			<section>
				<h3>Bulk</h3>
				<p>Each linked JSON file contains the complete data that Censys reported for each host from the above IP list. This includes <em>every</em> piece of information that Censys scanned from each host, including services other than HTTP</p>
				<ul>
				<?php
				$directoryPrefix = 'bulk/';
				$options = ['prefix' => $directoryPrefix];
			
				foreach ($bucket->objects($options) as $object) {
					echo "<li>";
					echo '<a href="https://storage.googleapis.com/451-response-stats/' . $object->name() . '">';
					# printf('Object: %s' . PHP_EOL, $object->name());
					print($object->name());
					echo '</a>';
					echo "</li>";
				}
				?>
				</ul>
			<hr />
			</section>
		</main>
		<footer>
			Host scan data is Copyright &copy; <?php echo date("Y"); ?> <a href="https://censys.io">Censys</a> <br />
			Website design and data analysis <span class="copy-left">&copy;</span> 2022 - <?php echo date("Y"); ?> Ben Pettis - Some Rights Reserved <br />
			Except where otherwise noted, this work is released under a Creative Commons License <br />
			<a href="https://creativecommons.org/licenses/by-nc-sa/4.0/">(CC-BY-NC-SA 4.0)</a>
		</footer>
	</body>
</html>