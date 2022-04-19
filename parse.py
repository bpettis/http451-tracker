import json, time, os


filename = 'output/bulk-most-recent-results.json'
timestr = time.strftime("%Y-%m-%d_%H-%M")

print(f'Now parsing {filename}')

with open(filename) as input_file:
	bulk_data = json.load(input_file)

for host in bulk_data:
   
	print(bulk_data[host]['ip'])
	print(bulk_data[host]['last_updated_at'])
    
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
				
				response_filename = 'output/responses/' + timestr + '/'+ str(bulk_data[host]['last_updated_at']) + '-' + str(bulk_data[host]['ip']) + '.txt'
				html_filename = 'output/responses/' + timestr + '/' + str(bulk_data[host]['last_updated_at']) + '-' + str(bulk_data[host]['ip']) + '.html'
				with open(response_filename, 'w') as outfile:
					for item in service['http']['response']['headers']:
						try:
							outfile.write(str(item) + ': ' + str(service['http']['response']['headers'][item][0]) + '\n')
						except:
							print('')
					outfile.write('\n\n')
					outfile.write(str(service['http']['response']['body']))
				
				with open(html_filename, 'w') as html:
					html.write(str(service['http']['response']['body']))
					
			else:
				print('not 451')
			
		except:
			print('failed - likely not http\n')
			


	print('*** *** *** *** ***\n')

print('Done!')