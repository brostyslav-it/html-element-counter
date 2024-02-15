<!doctype html>
<html lang="en">
<head>
    <!-- Set the character set and viewport for better display on various devices -->
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

    <!-- Define compatibility with Internet Explorer -->
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- Link to the external stylesheet -->
    <link rel="stylesheet" href="./static/style.css">

    <!-- Set the title of the HTML document -->
    <title>HTML Element Counter</title>
</head>
<body>
<!-- Header section containing the application name -->
<header>
    <b>HTML Element Counter</b>
</header>

<!-- Main content section -->
<main>
    <!-- Section to display form submission errors -->
    <section id="form-errors"><section id="errors"></section></section>

    <!-- Form for submitting URL and HTML element information -->
    <form id="fetch-data-form" method="post" action="./api/fetchUrl.php">
        <input type="url" name="url" placeholder="Enter URL here" required>
        <input type="text" name="element" placeholder="Enter HTML-element here" required>

        <!-- Button to trigger form submission -->
        <button type="submit">Fetch</button>
    </form>

    <!-- Section to display the response from the server -->
    <section id="response-area">
        <section id="response-content">
            <!-- Bold header for the response area -->
            <b>Response area</b>
        </section>
    </section>
</main>

<footer>
    <b>Bykhal Rostyslav</b>
</footer>

<!-- Include jQuery library from a CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<!-- Include the custom JavaScript file -->
<script src="./static/script.js"></script>
</body>
</html>
