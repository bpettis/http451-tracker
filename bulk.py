from censys.search import CensysHosts
import json, time

print('Gathering bulk data for the list of recent IP addresses')

h = CensysHosts()

filename = 'output/search-most-recent-list.txt'

# Read IP addresses from most recent search into an array
with open(filename) as f:
    hosts = [line.rstrip('\n') for line in f]

ip_count = len(hosts)
print(f'Now querying censys for bulk data on {ip_count} addresses')
print('This will likely take a little while...')

bulk_results = h.bulk_view(hosts)



timestr = time.strftime("%Y-%m-%d_%H-%M-%S")
filename = 'output/bulk/bulk-' + timestr + '.json'

pretty_json = json.dumps(bulk_results, indent=4, sort_keys=True)

# Write a file with timestamped name
with open(filename, 'a') as outfile:
	outfile.write(str(pretty_json))
	
print(f'Wrote list to file {filename}')
	
# Overwrite the most recent fiile
with open('output/bulk-most-recent-results.json', 'w') as outfile:
	outfile.write(str(pretty_json))
	
print('Overwrote new version of output/bulk-most-recent-results.json')
print('Done!')