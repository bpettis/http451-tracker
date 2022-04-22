# Modified from the example in the docs: https://censys-python.readthedocs.io/en/stable/usage-v2.html#aggregate

"""Aggregate hosts data set."""
from censys.search import SearchClient
from google.cloud import storage
import json, time, csv, os


all_codes = [101,200,201,202,203,204,301,302,303,307,308,400,401,402,403,404,405,406,407,409,410,412,414,416,418,421,422,423,425,426,429,444,451,452,464,479,500,501,502,503,504,511,520,521,522,523,525,526,530,999]

bucket_name = '451-response-stats'

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
# We need this to get the most recent aggregate.csv file and add the row into it

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


def aggregate():
	print('Querying Censys for aggregate data on HTTP status codes')
	print('Please wait...')
	
	c = SearchClient()
	
	# The aggregate method constructs a report using a query, an aggregation field, and the
	# number of buckets to bin.
	
	report = c.v2.hosts.aggregate(
	    "service.service_name: HTTP",
	    "services.http.response.status_code",
	    num_buckets=50,
	    virtual_hosts="INCLUDE"
	)
	
	
	# Sort so that the JSON file is organized by the # of the HTTP code:
	sorted_codes = sorted(report['buckets'], key=lambda x: x['key'])
	
	# add the query metadata back into the data that we are going to save
	data = {
		'buckets' : sorted_codes,
		'duration' : report['duration'],
		'field' : report['field'],
		'potential_deviation' : report['potential_deviation'],
		'query' : report['query'],
		'total' : report['total'],
		'total_omitted' : report ['total_omitted']
	}
	
	json_report = json.dumps(data)
	
	timestr = time.strftime("%Y-%m-%d_%H-%M-%S")
	bucket_filename = 'aggregate/aggregate-' + timestr + '.json'
	
	# Upload the aggregate JSON to the cloud
	upload_blob(bucket_name, json_report, bucket_filename)
	
	# create a variable to build the row that we are about to add to the CSV  file
	row = []
	row.append(timestr)
	for code in all_codes:
		try:
			code_count = list(filter(lambda item: item['key'] == str(code), data['buckets']))
			code_count = code_count[0]['count']
		except:
			code_count = ''
		row.append(code_count)
	
	# Get the aggregate.csv file from the cloud
	download_blob(bucket_name, 'aggregate.csv', 'aggregate-temp.csv')
	
	# Append our newly constructed row:
	with open('aggregate-temp.csv', 'a', newline='') as outfile:
		writer = csv.writer(outfile)
		writer.writerow(row)
	
	# Upload the updated aggregate.csv back into the cloud
	upload_file(bucket_name, 'aggregate-temp.csv', 'aggregate.csv')
	
	# Do some cleanup and delete our temp file
	if os.path.exists("aggregate-temp.csv"):
	  os.remove("aggregate-temp.csv")
	else:
	  print("The file does not exist")
	  
	print('Done!')
	return

if __name__ == "__main__":
	aggregate()