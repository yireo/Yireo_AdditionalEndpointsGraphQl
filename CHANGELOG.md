# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 25 November 2026
### Removed
- Support for PHP 8.1 (now requires PHP 8.2 up to 8.5)
- The `token` argument of `validateCustomerToken`: the token is now read from the Authorization header

### Changed
- The `id` argument of `cmsWidget` is now an `Int` instead of a `String`
- `validateCustomerToken` is no longer cacheable
- Declared the missing module dependencies (Authorization, CatalogGraphQl, CmsGraphQl, Store, Widget), so the module installs cleanly on Magento 2.4

### Security
- `validateCustomerToken` returned true for revoked, expired, admin and integration tokens (and false for JWT customer tokens); it now only returns true for an authenticated customer
- `cmsWidget` could instantiate any class stored as widget type and expose any widget instance by guessing IDs; it now only renders widget types declared in widget.xml for the current store view and theme, and only returns allowlisted parameters
- `productById` and `productBySku` exposed disabled products, products not visible individually and products of other websites
- `categoryById` exposed inactive categories and categories outside the root category of the current store
- `cmsBlock` and `cmsPages` exposed inactive content and content of other store views, and `cmsBlock` echoed the requested identifier in its error message

### Fixed
- Unknown `cmsWidget` IDs caused a fatal error
- CMS block and page content directives were not processed
- PHP 8.4 deprecation warnings about implicitly nullable resolver parameters
