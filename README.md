# Additional GraphQL endpoints for Magento 2
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
