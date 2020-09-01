# 8fold HTML Specification Structured

The mission here is to present the [Living HTML Specification](https://html.spec.whatwg.org/) as structured, and interconnected data.

The structured data is placed in a folder for the type of structured data used (`json`, for example). It's important to note that everything withing the folder for the structured data is automatically generated; therefore, no guarantee is provided regarding the accuracy and correcting the issue may require correcting an issue in the specification itself.

The information architecture of the `html` directory matches the URL of the documentation provided in [w3c/elements-of-html](https://raw.githubusercontent.com/w3c/elements-of-html/master/elements.json).

## Installation

```composer require 8fold/html-spec-structured```

## Usage

### Raw data

The compiled data from the writer scripts is stored in the `/json` directory, giving users direct access to the raw data using whatever interface they deem suitable.

### PHP Reader classes

The reader classes provide an API and lightweight ORM solution for retrieving data and are regularly refined to improve and ensure performance.

```php
HtmlIndex::all()->elementNamed("h1");
```

### PHP Writer classes

Because the data are collected across multiple documents, in multiple repositories, across multiple organizations, the writing scripts are very utilitarian and considered "throw away." They are minimally optimized for querying the documents and saving the subsequent data to files. Further, they inherit from the reader classes and are, therefore, completely decoupled from the user-oriented capabilties.

## Details

### Data collection

- The list of HTML elements is pulled from the w3c [Elements of HTML](https://raw.githubusercontent.com/w3c/elements-of-html/master/elements.json) repositiory. Note: If the element is not listed here, will not be listed anywhere else, even if referenced in other resources.
- The list of non-ARIA HTML attributes and element details are pulled from the WHATWG [HTML Living Standard](https://raw.githubusercontent.com/whatwg/html/master/source).
- The list of ARIA attributes is pulled from the w3c [HTML ARIA](https://raw.githubusercontent.com/w3c/html-aria/gh-pages/index.html) documentation.
- The ATRIA attribute categories are pulled from a local copy of the w3c HTML ARIA 1.1 recommendation available in the local folder of this repository.

ARIA seems to be the least consolidated and most volatile from a format perspective; however, it may become easier to aggregate and compile as time progresses.

## Other

### Versioning

As this package relays on time-sensitive, not functionality-sensitive details, versioning is time based using an ISO standard; therefore, semantic versioning `[major].[minor].[patch]` becomes `[year].[month].[day]`.

- year: represents the four-digit year the compiling scripts were run.
- month: representing the two-digit month the compiling scripts were run.
- day: represents the two-digit day the compiling scripts were run.
