"""Search hosts data set."""
from censys.search import CensysHosts
from google.cloud import storage
from google.cloud import pubsub_v1
import json, time, os

bucket_name = '451-response-stats'

project_id = 'http-451-tracker'
topic_id = 'bulk'
publisher = pubsub_v1.PublisherClient()
topic_path = publisher.topic_path(project_id, topic_id)

h = CensysHosts()

per_page = 100
pages = 100

# upload_blob function to store object in a Google Cloud Storage bucket 
def upload_blob(bucket_name, contents, destination_blob_name):
    """Uploads a file to the bucket."""

    # The ID of your GCS bucket
    # bucket_name = "your-bucket-name"

    # The contents to upload to the file
    # contents = "these are my contents"

    # The ID of your GCS object
    # destination_blob_name = "storage-object-name"

    storage_client = storage.Client()
    bucket = storage_client.bucket(bucket_name)
    blob = bucket.blob(destination_blob_name)

    blob.upload_from_string(contents)

    print(
        "Success! {} was uploaded to {}.".format(
            destination_blob_name, bucket_name
        )
    )

# copy_blob function to create a copy of the timestamped file as search-most-recent-list.txt for future iterations of the script to access
def copy_blob( bucket_name, blob_name, destination_bucket_name, destination_blob_name ):
    """Copies a blob from one bucket to another with a new name."""
    # bucket_name = "your-bucket-name"
    # blob_name = "your-object-name"
    # destination_bucket_name = "destination-bucket-name"
    # destination_blob_name = "destination-object-name"

    storage_client = storage.Client()

    source_bucket = storage_client.bucket(bucket_name)
    source_blob = source_bucket.blob(blob_name)
    destination_bucket = storage_client.bucket(destination_bucket_name)

    blob_copy = source_bucket.copy_blob(
        source_blob, destination_bucket, destination_blob_name
    )

    print(
        "Blob {} in bucket {} copied to blob {} in bucket {}.".format(
            source_blob.name,
            source_bucket.name,
            blob_copy.name,
            destination_bucket.name,
        )
    )


def search():
	print('Starting Censys search...')
	print('Listing IPs that return HTTP 451 and have a response body larger than 0')
	print(f'Querying up to {pages} pages with {per_page} results per page - possibly up to {pages * per_page} results! This may take a while...')
	
	# View all results - uncomment this line to make a real API call
	query = h.search("services.http.response.status_code=451 AND NOT services.http.response.body_size=0", per_page=per_page, pages=pages, virtual_hosts="INCLUDE")
	
	all_results = {}
	
	for page in query:
		for item in page:
			all_results[item['ip']] = item
		
		
	
	
	
	query = all_results
	result_count = len(all_results)
	print(f'Finished Searching... now parsing + listing {result_count} addresses')
	
	
	timestr = time.strftime("%Y-%m-%d_%H-%M-%S")
	filename = 'ip-list/search-' + timestr + '.txt'
	all_results = ""
	for result in query:
		all_results += query[result]['ip']
		all_results += '\n'
		
	# Write the file into a Cloud Storage object
	upload_blob(bucket_name, all_results, filename)
		
	# Copy that timestamped list into a common list that we can access the next time the script runs	
	copy_blob(bucket_name, filename, bucket_name, 'search-most-recent-list.txt')
	
	print('Overwrote new version of search-most-recent-list.txt')
	
	topic = publisher.create_topic(request={"name": topic_path})
	print('Done!')


def pubsub_entry(event, context):
	search()
if __name__ == "__main__":
	search()