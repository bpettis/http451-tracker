# Modified from the example in the docs: https://censys-python.readthedocs.io/en/stable/usage-v2.html#aggregate

"""Aggregate hosts data set."""
from censys.search import SearchClient
import json, time


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

json_report = json.dumps(report)

timestr = time.strftime("%Y%m%d-%H%M%S")
filename = 'output/aggregate' + timestr + '.json'

with open(filename, 'w') as outfile:
	outfile.write(json_report)
	
print(f'Done! Wrote data to file {filename}')