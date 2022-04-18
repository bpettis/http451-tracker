import json


filename = 'output/bulk-most-recent-results.json'

with open(filename) as input_file:
	bulk_data = json.load(input_file)

for host in bulk_data:
	print(bulk_data[host])
	for service in bulk_data[host]['services']:
		print(service['_decoded'])
		if('http' in service):
		#if(service['http']):
			if (service['http']['response']['status_code'] == '451'):
				try:
					print(service['http']['response']['status_code'])
				except:
					print('could not get status code')
				try:
					print(service['http']['response']['headers'])
				except:
					print('could not get response header')
				print('\n')
				try:
					print(service['http']['response']['body'])
				except:
					print('could not get response body')
			else:
				print('Not a 451...')
		else:
			print('Skipping...')
	print('*** *** *** *** ***\n')
