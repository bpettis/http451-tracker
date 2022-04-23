# http451-tracker
Searches the Censys database for hosts that return an HTTP status code of 451 - a code for content that is not available for legal reasons

# Requirements

- Censys API key + secret (sign up for account at https://censys.io)
- Python (developed/tested with Python 3.7.9)

# Installation

Use `pip install -r requirements.txt` to install python packages

# Setup
These scripts all assume that you have set up your environment with your Censys API key and secret

To configure your search credentials run censys config or set both `CENSYS_API_ID` and `CENSYS_API_SECRET` environment variables.
```
$ censys config

Censys API ID: XXX
Censys API Secret: XXX

Successfully authenticated for your@email.com
```
To configure your ASM credentials run censys asm config or set the `CENSYS_ASM_API_KEY` environment variables.

```
$ censys asm config

Censys ASM API Key: XXX

Successfully authenticated
```

## Google Cloud Storage
The scripts are set up to stash content in a Google Cloud Storage bucket. When running the scripts, you will need to have a service account configured in (Google Cloud)["https://cloud.google.com"] - you will also need to create an authentication key for the service account, and ensure that the account has the "Storage Admin" and "Pub/Sub Publisher" roles. Make sure that your environment has the `GOOGLE_APPLICATION_CREDENTIALS` variable set so that it knows to use this auth key.

If you are make a new version of these scripts, you will need to substitute the top of each file with the correct project-id and bucket-name that matches _your_ Google Cloud project setup

I have tested the scripts running in Google Cloud Functions in the Python 3.9 runtime environment. The scripts each send a Pub/Sub notification to trigger the next script. The last two scripts are pretty beefy and pull a lot of data, so be sure to allocate enough memory for them to run!

- Aggregate (256MB)
- Search (256MB)
- Bulk (4GB)
- Parse (4GB)


# Usage

- To be written... eventually

## (1) Aggregate

This script will query aggregate data about what HTTP response codes are being returned by the hosts that Censys is scanning.

`python3 aggregate.py`

The script will output a few things into the output/aggregate/ directory:
- aggregate-[TIMESTAMP].json - an individual JSON file with the aggregate data 
- aggregate.csv - every time the script is ran it will add a new row with a timestamp and counts for the specified HTTP codes

Example JSON output:

```
{"buckets": [{"key": "101", "count": 108712}, {"key": "200", "count": 465947192}, {"key": "201", "count": 15057}, {"key": "202", "count": 221808}, {"key": "203", "count": 993048}, {"key": "204", "count": 220252}, {"key": "301", "count": 250734229}, {"key": "302", "count": 70732713}, {"key": "303", "count": 726840}, {"key": "307", "count": 2989049}, {"key": "308", "count": 4736089}, {"key": "400", "count": 95192392}, {"key": "401", "count": 76275891}, {"key": "402", "count": 172049}, {"key": "403", "count": 83562242}, {"key": "404", "count": 104118934}, {"key": "405", "count": 532575}, {"key": "406", "count": 664925}, {"key": "407", "count": 2863997}, {"key": "409", "count": 2557440}, {"key": "410", "count": 709111}, {"key": "412", "count": 68801}, {"key": "414", "count": 30714}, {"key": "416", "count": 16919}, {"key": "418", "count": 89015}, {"key": "421", "count": 32728}, {"key": "422", "count": 27968}, {"key": "423", "count": 52376}, {"key": "425", "count": 20137}, {"key": "426", "count": 472615}, {"key": "429", "count": 3781669}, {"key": "444", "count": 257786}, {"key": "451", "count": 14104}, {"key": "452", "count": 24163}, {"key": "464", "count": 14391}, {"key": "479", "count": 36969}, {"key": "500", "count": 11668759}, {"key": "501", "count": 285316}, {"key": "502", "count": 4392727}, {"key": "503", "count": 14300503}, {"key": "504", "count": 158438}, {"key": "511", "count": 29543}, {"key": "520", "count": 449338}, {"key": "521", "count": 1720960}, {"key": "522", "count": 51988}, {"key": "523", "count": 369843}, {"key": "525", "count": 176060}, {"key": "526", "count": 249924}, {"key": "530", "count": 839736}, {"key": "999", "count": 39512}], "duration": 1273, "field": "services.http.response.status_code", "potential_deviation": 1120, "query": "service.service_name: HTTP", "total": 1873322794, "total_omitted": 223311}
```

The aggregate script will take 

## (2) Search

This script will search for hosts that are returning HTTP 451 codes _and_ have a HTTP response body larger than 0 (e.g. there is actual data for us to look at)

The specific Censys query it is making is: `"services.http.response.status_code=451 AND NOT services.http.response.body_size=0"`

To run the search:

`python3 aggregate.py`

This search only provides basic information about each host, so we use this script to generate a list of IP addresses to query later with bulk.py

This will output some files in the output/ directory:
- output/search-most-recent-list.txt - this file will be used by the bulk.py script
- output/ip-list/search-[TIMESTAMP].txt - this is the IP list from a specific point in time

The script has options to set how many pages of results you would like to return. I have found that 100 pages with 100 results each is enough to cover the IPv4 space and just barely dip into IPv6.

When I run this script, it takes ~70 Censys API calls

## (3) Bulk

This script uses the IP addresses listed in `search-most-recent-list.txt` and queries full data on each host. This is necessary to collect the full HTTP response for each host, but it will also save the full responses if you are interested in any other data that Censys has on these hosts

When I run this script, it takes ~1000 Censys API calls

To run the search:

`python3 bulk.py`

## (4) Parse

This script looks through the most recent `bulk-most-recent-results.json` file and parses _all_ of the returned data and produces individual files with the HTTP respons from each host

These resulting files are saved in output/responses/[TIMESTAMP]/
- [TIMESTAMP]-[IP ADDRESS].txt - contains the HTTP header and response body
- [TIMESTAMP]-[IP ADDRESS].html - contains the HTTP response body only -- Note: this will _always_ save the body as an HTML file, even if the body is not HTML. Sorry about it.
To run the script:

`python3 parse.py`

When I run this script, it takes 0 Censys API calls because it is processing data that has already been collected