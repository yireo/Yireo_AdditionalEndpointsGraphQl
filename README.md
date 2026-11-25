# Additional GraphQL endpoints for Magento 2

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-additional-endpoints-graph-ql)

</div>
<!-- badges.specs.end -->

This module offers some additional endpoints for loading information through GraphQL.

## Installation
Use the following commands to install this module into Magento 2:

    composer require yireo/magento2-additional-endpoints-graph-ql:@dev
    bin/magento module:enable Yireo_AdditionalEndpointsGraphQl
    bin/magento setup:upgrade

## `productById(Int! id)` - Get a product by its ID
Only enabled products that are visible in the catalog or search, and that are assigned to the current website, are returned.

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
The same restrictions as for `productById` apply.

Example:
```graphql
{
  productBySku(sku:"VA22-SI-NA"){
    sku
    name
  }
}
```

## `categoryById(Int! id)` - Get a category by its ID
Only active categories within the root category of the current store are returned.

Example:
```graphql
{
  categoryById(id:3){
    id
    name
  }
}
```

## `cmsBlock(String! identifier)` - Get a CMS block by its identifier
Only active blocks of the current store view (or all store views) are returned. Directives in the content are processed.

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
Returns the first 20 active pages of the current store view (or all store views). Directives in the content are processed.

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
Only widget instances of a known widget type, assigned to the current store view (or all store views) and to the theme of the current store view, are returned. Only scalar parameters from an allowlist are returned (the `$allowedParameters` constructor argument of `Yireo\AdditionalEndpointsGraphQl\Model\Resolver\CmsWidget`, which you can change via `di.xml`).

Example:
```graphql
{
  cmsWidget(id: 2) {
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
Send the customer token as `Authorization: Bearer <token>` header. The query returns `true` when the token belongs to a customer and is valid (not expired, not revoked). It returns `false` without a token. An invalid or expired token is rejected by Magento with an authorization error.

Example:
```graphql
query validateToken {
  validateCustomerToken
}
```

Up to version 0.0.3, this query had a `token` argument. This argument was removed for security reasons.

## Current status

<!-- badges.test.start -->
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_AdditionalEndpointsGraphQl/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_AdditionalEndpointsGraphQl/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_AdditionalEndpointsGraphQl/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_AdditionalEndpointsGraphQl/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_AdditionalEndpointsGraphQl/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
