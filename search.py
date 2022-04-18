"""Search hosts data set."""
from censys.search import CensysHosts
import json, time


h = CensysHosts()

per_page = 100
pages = 100

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
# TO DO: re-work the API call to return all pages:
# It looks like we're currently limited to only getting 100 results... which is not anywhere near all of them
# e.g. - 
# Multiple pages of search results
# query = h.search("service.service_name: HTTP", per_page=5, pages=2)
# for page in query:
#     print(page)
#
# from: https://censys-python.readthedocs.io/en/stable/usage-v2.html#search


# load from an example file instead of making a real API call
#with open('test-json_result.json') as test_file:
#	query = json.load(test_file)

timestr = time.strftime("%Y-%m-%d_%H-%M-%S")
filename = 'output/ip-list/search-' + timestr + '.txt'
all_results = ""
for result in query:
	all_results += query[result]['ip']
	all_results += '\n'
	
# Write a file with timestamped name
with open(filename, 'a') as outfile:
	outfile.write(all_results)
	
print(f'Wrote list to file {filename}')

# Overwrite the "recent" list
with open('output/search-most-recent-list.txt', 'w') as outfile:
	outfile.write(all_results)
	

print('Overwrote new version of search-most-recent-list.txt')


print('Done!')