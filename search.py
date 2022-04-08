"""Search hosts data set."""
from censys.search import CensysHosts
import json

h = CensysHosts()

# View all results - uncomment this line to make a real API call
# query = h.search("services.http.response.status_code=451 AND NOT services.http.response.body_size=0", per_page=100, virtual_hosts="INCLUDE")
# print(query.view_all())

# load from an example file instead of making a real API call
with open('search-example_response.json') as test_file:
	query = json.load(test_file)





for result in query['result']['hits']:
	print(result['ip'])