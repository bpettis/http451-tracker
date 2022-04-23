from censys.search import CensysHosts
from google.cloud import storage
from google.cloud import pubsub_v1
import json, time, os

print('Gathering bulk data for the list of recent IP addresses')

h = CensysHosts()

filename = '/tmp/search-most-recent-list.txt'
bucket_name = '451-response-stats'

project_id = 'http-451-tracker'
topic_id = 'search'
publisher = pubsub_v1.PublisherClient()

topic_path = 'projects/{project_id}/topics/{topic}'.format(
	project_id=project_id,
    topic=topic_id,  # Set this to something appropriate.
)

# download_blob function to get an object from the Google Cloud Storage bucket
# 
# We need this to get the most recent search-most-recent-list.txt file

def download_blob(bucket_name, source_blob_name, destination_file_name):
    """Downloads a blob from the bucket."""
    # The ID of your GCS bucket
    # bucket_name = "your-bucket-name"

    # The ID of your GCS object
    # source_blob_name = "storage-object-name"

    # The path to which the file should be downloaded
    # destination_file_name = "local/path/to/file"

    storage_client = storage.Client()

    bucket = storage_client.bucket(bucket_name)

    # Construct a client side representation of a blob.
    # Note `Bucket.blob` differs from `Bucket.get_blob` as it doesn't retrieve
    # any content from Google Cloud Storage. As we don't need additional data,
    # using `Bucket.blob` is preferred here.
    blob = bucket.blob(source_blob_name)
    blob.download_to_filename(destination_file_name)

    print(
        "Downloaded storage object {} from bucket {} to local file {}.".format(
            source_blob_name, bucket_name, destination_file_name
        )
    )

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

# copy_blob function to create a copy of the timestamped file as most-recent-results.json for future iterations of the script to access
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

def bulk():
	# Get the most recent file from cloud
	download_blob(bucket_name, 'search-most-recent-list.txt', filename)
	
	# Read IP addresses from most recent search into an array
	with open(filename) as f:
	    hosts = [line.rstrip('\n') for line in f]
	
	ip_count = len(hosts)
	print(f'Now querying censys for bulk data on {ip_count} addresses')
	print('This will likely take a little while...')
	
	bulk_results = h.bulk_view(hosts)
	
	
	
	timestr = time.strftime("%Y-%m-%d_%H-%M-%S")
	# filename = 'output/bulk/bulk-' + timestr + '.json'
	filename = 'bulk/bulk-' + timestr + '.json'
	
	pretty_json = json.dumps(bulk_results, indent=4, sort_keys=True)
	
	# Write a file with timestamped name
	#with open(filename, 'a') as outfile:
	#	outfile.write(str(pretty_json))	
	#print(f'Wrote list to file {filename}')
	
	upload_blob(bucket_name, str(pretty_json), filename)
		
	# Overwrite the most recent fiile
	#with open('output/bulk-most-recent-results.json', 'w') as outfile:
	#	outfile.write(str(pretty_json))
		
	copy_blob(bucket_name, filename, bucket_name, 'bulk-most-recent-results.json')	
		
	print('Overwrote new version of output/bulk-most-recent-results.json')
	
	
	# Do some cleanup and delete our temp file
	if os.path.exists('/tmp/search-most-recent-list.txt'):
	  os.remove('/tmp/search-most-recent-list.txt')
	  print('removed temp file')
	else:
	  print("The file does not exist")
	
	message = publisher.publish(topic_path, b'Bulk finished - Start Parse!')  
	print('Done!')

def pubsub_entry(event, context):
	bulk()
if __name__ == "__main__":
	bulk()