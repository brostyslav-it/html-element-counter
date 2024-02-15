console.clear()

/**
 * Constants for request configuration.
 * @type {string}
 */
const requestType = 'POST'

/**
 * URL for the fetchUrl API endpoint.
 * @type {string}
 */
const requestUrl = '/api/fetchUrl.php'

/**
 * String representation of an invalid HTML element object.
 * @type {string}
 */
const invalidElementObjectString = '[object HTMLUnknownElement]'

/**
 * Maximum length allowed for a URL.
 * @type {number}
 */
const maxUrlLength = 500

/**
 * Error message for an invalid HTML tag name.
 * @type {string}
 */
const invalidTagNameErrorMessage = 'Invalid HTML tag name<br>'

/**
 * Error message for an invalid symbols in tag name.
 * @type {string}
 */
const tagHasInvalidSymbols = 'Tag name contains invalid symbols'

/**
 * Error message for a URL that exceeds the maximum length.
 * @type {string}
 */
const tooLongUrlErrorMessage = 'Too long URL, max 500 symbols<br>'

/**
 * Header for displaying error responses.
 * @type {string}
 */
const errorResponseHeader = '<h3>Error</h3>'

/**
 * Separator for joining error messages.
 * @type {string}
 */
const joinErrorsBy = '<br>'

/**
 * jQuery selector for the fetch-data-form.
 * @type {HTMLElement}
 */
const form = $('#fetch-data-form')

/**
 * jQuery selector for the form-errors element.
 * @type {HTMLElement}
 */
const errorsBlock = $('#form-errors')

/**
 * jQuery selector for the errors' element.
 * @type {HTMLElement}
 */
const errors = $('#errors')

/**
 * jQuery selector for the response-content element.
 * @type {HTMLElement}
 */
const responseContent = $('#response-content')

/**
 * Event listener for form submission.
 * @param {Event} e - The form submission event.
 */
form.submit((e) => {
    e.preventDefault()

    // Check if the HTML tag name is valid
    if (!validateForm()) {
        errorsBlock.show()
        return
    }

    errorsBlock.hide()
    sendForm()
})

/**
 * Function to validate form inputs.
 * @returns {boolean} - True if the form is valid, false otherwise.
 */
function validateForm() {
    let ok = true

    errors.empty()

    try {
        // Check if the HTML tag name is valid
        if (!isValidTagName($('input[name="element"]').val())) {
            ok = false
            errors.append(invalidTagNameErrorMessage)
        }
    } catch (e) {
        if (e instanceof DOMException) {
            ok = false
            errors.append(tagHasInvalidSymbols)
        }
    }

    // Check if the URL length is within the limit
    if ($('input[name="url"]').val().length > maxUrlLength) {
        ok = false
        errors.append(tooLongUrlErrorMessage)
    }

    return ok
}

/**
 * Function to check if the provided HTML tag name is valid.
 * @param {string} tagName - The HTML tag name to check.
 * @returns {boolean} - True if the tag name is valid, false otherwise.
 */
function isValidTagName(tagName) {
    return document.createElement(tagName).toString() !== invalidElementObjectString
}

/**
 * Function to handle successful AJAX response.
 * @param {Object} data - The data received in the AJAX response.
 */
function responseSuccess(data) {
    responseContent.html(`
        <h3>Request results:</h3>
        URL ${data.url} Fetched on ${data.fetchDateTime}, took ${data.responseTime}msec.<br>
        Element  &lt;${data.element}&gt; appeared ${data.count} times in page.<br>
        <h3>General statistics:</h3>
        ${data.urlCountForDomain} different URLs from ${data.domain} have been fetched<br>
        Average fetch time from ${data.domain} during the last 24 hours is ${data.averagePageFetchTimeForDomain}ms<br>
        There was a total of ${data.totalElementCountForDomain} &lt;${data.element}&gt; elements from ${data.domain}<br>
        Total of ${data.totalElementCount} &lt;${data.element}&gt; elements counted in all requests ever made.<br>
    `)
}

/**
 * Function to handle AJAX response with errors.
 * @param {Object} data - The data received in the AJAX response.
 */
function responseError(data) {
    responseContent.html(errorResponseHeader)
    responseContent.append(data.errors.join(joinErrorsBy))
}

/**
 * Function to send form data using AJAX.
 */
function sendForm() {
    $.ajax({
        type: requestType,
        url: requestUrl,
        data: new FormData(form[0]),
        contentType: false,
        processData: false,
        dataType: "json",
        encode: true
    }).done((data) => {
        // Handle the response based on 'ok' flag

        if (data.ok) {
            responseSuccess(data);
        } else {
            responseError(data);
        }
    })
}
