import json, time, os
from google.cloud import storage


filename = 'output/bulk-most-recent-results.json'
timestr = time.strftime("%Y-%m-%d_%H-%M")
bucket_name = '451-response-stats'


# upload_file function to upload a file, instead of just a string to a Google Cloud Storage bucket
def upload_file(bucket_name, source_file_name, destination_blob_name):
    """Uploads a file to the bucket."""
    # The ID of your GCS bucket
    # bucket_name = "your-bucket-name"
    # The path to your file to upload
    # source_file_name = "local/path/to/file"
    # The ID of your GCS object
    # destination_blob_name = "storage-object-name"

    storage_client = storage.Client()
    bucket = storage_client.bucket(bucket_name)
    blob = bucket.blob(destination_blob_name)

    blob.upload_from_filename(source_file_name)

    print(
        "File {} uploaded to {}.".format(
            source_file_name, destination_blob_name
        )
    )
    
# download_blob function to get an object from the Google Cloud Storage bucket
# 
# We need this to get the most recent results JSON file for us to actually parse

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


def parse():
	print(f'Now parsing {filename}')
	
	download_blob(bucket_name, 'bulk-most-recent-results.json', filename)
	
	with open(filename) as input_file:
		bulk_data = json.load(input_file)
	
	for host in bulk_data:
	   
		print(bulk_data[host]['ip'])
		#print(bulk_data[host]['last_updated_at'])
		
	    
		for service in bulk_data[host]['services']:
			try:
				if(service['http']['response']['status_code'] == 451):
	
					#Print HTTP headers prettier:				
	#				for item in service['http']['response']['headers']:
	#					try:
	#						print(str(item) + ': ' + str(service['http']['response']['headers'][item][0]))
	#					except:
	#						print('')
	#				print('\n')
	#				print(service['http']['response']['body'])
	#				print('\n')
	#				
					#create an output directory to try and organize things
					path = 'output/responses/' + timestr
					isExist = os.path.exists(path)
					if not isExist:
						os.makedirs(path)
						print("The new directory is created!")
					
					response_filename = 'output/responses/' + timestr + '/'+ str(bulk_data[host]['ip']) + '.txt'
					response_bucket_filename = 'responses/' + timestr + '/'+ str(bulk_data[host]['ip']) + '.txt'
					html_filename = 'output/responses/' + timestr + '/' + str(bulk_data[host]['ip']) + '.html'
					html_bucket_filename = 'responses/' + timestr + '/' + str(bulk_data[host]['ip']) + '.html'
					with open(response_filename, 'w') as outfile:
						print('writing HTTP headers')
						for item in service['http']['response']['headers']:
							try:
								outfile.write(str(item) + ': ' + str(service['http']['response']['headers'][item][0]) + '\n')
							except:
								print('')
						outfile.write('\n\n')
						outfile.write(str(service['http']['response']['body']))
					
					upload_file(bucket_name, response_filename, response_bucket_filename)
					with open(html_filename, 'w') as html:
						try:
							html.write(str(service['http']['response']['body']))
							print('wrote HTTP body')
						except:
							print('')
					upload_file(bucket_name, html_filename, html_bucket_filename)
						
				else:
					print('not 451')
				
			except:
				print('failed - likely not http\n')
				
	
	
		print('*** *** *** *** ***\n')
	
	print('Done!')


def pubsub_entry(event, context):
	parse()
if __name__ == "__main__":
	parse()