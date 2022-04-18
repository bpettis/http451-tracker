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


## (2) Search

This script will search for hosts that are returning HTTP 451 codes _and_ have a HTTP response body larger than 0 (e.g. there is actual data for us to look at)

The specific Censys query it is making is: `"services.http.response.status_code=451 AND NOT services.http.response.body_size=0"`

To run the search:

`python3 aggregate.py`

## (3) Bulk

## (4) Parse