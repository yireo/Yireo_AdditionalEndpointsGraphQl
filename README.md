# Additional GraphQL endpoints for Magento 2

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-additional-endpoints-graph-ql)
<!-- badges.specs.end -->

This module offers some additional endpoints for loading information through GraphQL.

## Installation
Use the following commands to install this module into Magento 2:

    composer require magento2-additional-endpoints-graph-ql:@dev
    bin/magento module:enable Yireo_AdditionalEndpointsGraphQl
    bin/magento setup:upgrade

## `productById(Int id)` - Get a product by its ID
Example:
```graphql
{
  productById(id:42){
    sku
    name
  }
}
```

## `productBySku(String! sku)` - Get a product by its SKU
Example:
```graphql
{
  productBySku(sku:"VA22-SI-NA"){
    sku
    name
  }
}
```

## `categoryById(Int id)` - Get a category by its ID
Example:
```graphql
{
  categoryById(id:1){
    id
    name
  }
}
```

## `cmsBlock(String! identifier)` - Get a CMS block by its identifier
Example:
```graphql
{
  cmsBlock(identifier:"example"){
    title
    content
  }
}
```

## `cmsPages` - Get all CMS pages
Example:
```graphql
{
  cmsPages {
    items {
      title
    }
  }
}
```

## `cmsWidget(Int! id)` - Get a CMS widget by its ID
Example:
```graphql
{
  cmsWidget(id: "2") {
    id
    title
    html
    parameters {
      name
      value
    }
  }
}
```

## `validateCustomerToken` - Validate a customer token
Example:
```graphql
query validateToken {
  validateCustomerToken(token:"abc")
}
```

## Current status

<!-- badges.test.start -->
![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_AdditionalEndpointsGraphQl/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_AdditionalEndpointsGraphQl/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_AdditionalEndpointsGraphQl/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_AdditionalEndpointsGraphQl/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_AdditionalEndpointsGraphQl/compile.yml?label=compile)
<!-- badges.test.end -->
