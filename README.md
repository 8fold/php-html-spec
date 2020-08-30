# 8fold HTML Specification Structured

The mission here is to present the [Living HTML Specification](https://html.spec.whatwg.org/) as structured, and interconnected data.

The structured data is placed in a folder for the type of structured data used (`json`, for example). It's important to note that everything withing the folder for the structured data is automatically generated; therefore, no guarantee is provided regarding the accuracy and correcting the issue may require correcting an issue in the specification itself.

The information architecture of the `html` directory matches the URL of the documentation provided in [w3c/elements-of-html](https://raw.githubusercontent.com/w3c/elements-of-html/master/elements.json).

## Installation

{how does one install the product}

## Usage

{brief example of how to use the product}

## Details

The scripts do not scrape HTML-like version of the specification or any other website. Instead, they rely on various public documents stored in GitHub and then parsed.

1. Valid HTML elements are pulled from w3c/elements-of-html. If an element is not listed there but referenced by the HTML specification, the element will be ignored.
2. When we learn of updates to the specification or list of elements, the scripts are run again and any manual updates are performed before submitting a new release.

## Other

{links or descriptions or license, versioning, and governance}

### Versioning

As this package relays on time-sensitive, not functionality-sensitive details, versioning is time based using an ISO standard; therefore, semantic versioning `[major].[minor].[patch]` becomes `[year].[month].[day]`.

- year: represents the four-digit year the compiling scripts were run.
- month: representing the two-digit month the compiling scripts were run.
- day: represents the two-digit day the compiling scripts were run.
