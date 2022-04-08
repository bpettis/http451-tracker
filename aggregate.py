# Modified from the example in the docs: https://censys-python.readthedocs.io/en/stable/usage-v2.html#aggregate

"""Aggregate hosts data set."""
from censys.search import SearchClient
import json, time


print('Querying Censys for aggregate data on HTTP status codes')
print('Please wait...')

# c = SearchClient()

# The aggregate method constructs a report using a query, an aggregation field, and the
# number of buckets to bin.


#report = c.v2.hosts.aggregate(
#    "service.service_name: HTTP",
#    "services.http.response.status_code",
#    num_buckets=50,
#    virtual_hosts="INCLUDE"
#)

# Load data from a test file instead of making API calls
with open('output/aggregate20220407-193529.json') as test_file:
	report = json.load(test_file)

# Sort so that the JSON file is organized by the # of the HTTP code:
sorted_codes = sorted(report['buckets'], key=lambda x: x['key'])
print(sorted_codes)

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

timestr = time.strftime("%Y%m%d-%H%M%S")
filename = 'output/aggregate-' + timestr + '.json'

with open(filename, 'w') as outfile:
	outfile.write(json_report)
	
print(f'Done! Wrote data to file {filename}')
