<!doctype html>
<html lang="en">
	<head>
		<title>Tracking the HTTP 451 Response Code</title>
		
<?php

	# Includes the autoloader for libraries installed with composer
	require __DIR__ . '/vendor/autoload.php';
	
	# Imports the Google Cloud client library
	use Google\Cloud\Storage\StorageClient;

	/**
	 * Function to List Cloud Storage bucket objects with specified prefix.
	 *
	 * @param string $bucketName The name of your Cloud Storage bucket.
	 * @param string $directoryPrefix the prefix to use in the list objects API call.
	 */
	function list_objects_with_prefix($bucketName, $directoryPrefix)
	{
	    // $bucketName = 'my-bucket';
	    // $directoryPrefix = 'myDirectory/';
	
	    $storage = new StorageClient();
	    $bucket = $storage->bucket($bucketName);
	    $options = ['prefix' => $directoryPrefix];
	    foreach ($bucket->objects($options) as $object) {
	        printf('Object: %s' . PHP_EOL, $object->name());
	    }
	}
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
		
			<hr />
			
			
		</main>
	</body>
</html>