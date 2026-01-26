# Source Templates

@TODO

## Ticketmaster-API

* Create an account on https://developer-acct.ticketmaster.com/user/login to get API key ("Consumer Key" on "My Apps" after login)
* Get venueId using `https://app.ticketmaster.com/discovery/v2/venues.json?locale=fr-fr&keyword=[NAME]&apikey=[API_KEY]`
* Get events using `https://app.ticketmaster.com/discovery/v2/events.json?locale=fr-fr&venueId=[VENUE_ID]&size=200&apikey=[API_KEY]`
* Check item page>totalElements doesn't exceed max size 200. Use `page=[NUMBER]` is needed.
* Create Source with events list as url, check `is Json result` and select model "Ticketmaster - API"
