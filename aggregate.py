# Modified from the example in the docs: https://censys-python.readthedocs.io/en/stable/usage-v2.html#aggregate

"""Aggregate hosts data set."""
from censys.search import SearchClient
import json, time, csv


all_codes = [101,200,201,202,203,204,301,302,303,307,308,400,401,402,403,404,405,406,407,409,410,412,414,416,418,421,422,423,425,426,429,444,451,452,464,479,500,501,502,503,504,511,520,521,522,523,525,526,530,999]

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

# Load data from a test file instead of making API calls
#with open('output/aggregate20220407-193529.json') as test_file:
#	report = json.load(test_file)

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
filename = 'output/aggregate-' + timestr + '.json'

with open(filename, 'w') as outfile:
	outfile.write(json_report)
	
print(f'Done! Wrote data to file {filename}')

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

with open('output/aggregate.csv', 'a', newline='') as outfile:
	writer = csv.writer(outfile)
	writer.writerow(row)

print('Added row to CSV file output/aggregate.csv')