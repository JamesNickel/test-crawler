# test-crawler

A crawler with 2 drivers for digikala.com and technolife.com


-----------------------

Before you start run:
php artisan db:seed

-----------------------



Start a crawler by calling: 

POST: /api/start-crawl/ 

{source\_id: 1, start\_index: 0}

1: Digikala, 2: Technolife



-----------------------



Get the status of a crawler by calling:

GET: /api/crawl-status/{crawler id}



-----------------------



