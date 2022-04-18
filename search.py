"""Search hosts data set."""
from censys.search import CensysHosts
import json, time

print('Starting Censys search...')
priint('Listing IPs that return HTTP 451 and have a response body larger than 0')
h = CensysHosts()

# View all results - uncomment this line to make a real API call
query = h.search("services.http.response.status_code=451 AND NOT services.http.response.body_size=0", per_page=100, virtual_hosts="INCLUDE")
json_result = query.view_all()
query = json_result

# TO DO: re-work the API call to return all pages:
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
filename = 'output/search-' + timestr + '.txt'

for result in query:
	#print(result)
	print(query[result]['ip'])
	with open(filename, 'a') as outfile:
		outfile.write(query[result]['ip'])
		outfile.write('\n')
	
print(f'Wrote list to file {filename}')
print('Done!')