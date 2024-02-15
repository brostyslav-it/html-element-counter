# html-element-counter

This is a single-page website designed to run on a PHP server with MySQL. The page, titled "HTML Element Counter," allows users to input the URL of another webpage and specify the name of an HTML element for inspection.

Upon submission of the form (via AJAX), a designated area on the page will be dynamically updated with the following details:

A. Request results: URL retrieved, Date & Time of response, Response time in milliseconds, Count of elements.
B. Aggregate statistics for all requests:
    1. Total number of URLs from the same domain checked thus far.
    2. Average page retrieval time from that domain within the past 24 hours.
    3. Cumulative count of the specified element across all requests from this domain.
    4. Overall count of the specified element from all requests made.

On the server-side, if a duplicate request is detected within a 5-minute window, the system will provide the previous response. Additionally, the system handles potential errors such as invalid URLs, inaccessible URLs, invalid HTML content, and ensures the use of a proper user-agent to prevent blocking from external servers.
