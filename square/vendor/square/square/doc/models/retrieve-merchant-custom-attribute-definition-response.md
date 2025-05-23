
# Retrieve Merchant Custom Attribute Definition Response

Represents a [RetrieveMerchantCustomAttributeDefinition](../../doc/apis/merchant-custom-attributes.md#retrieve-merchant-custom-attribute-definition) response.
Either `custom_attribute_definition` or `errors` is present in the response.

## Structure

`RetrieveMerchantCustomAttributeDefinitionResponse`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `customAttributeDefinition` | [`?CustomAttributeDefinition`](../../doc/models/custom-attribute-definition.md) | Optional | Represents a definition for custom attribute values. A custom attribute definition<br>specifies the key, visibility, schema, and other properties for a custom attribute. | getCustomAttributeDefinition(): ?CustomAttributeDefinition | setCustomAttributeDefinition(?CustomAttributeDefinition customAttributeDefinition): void |
| `errors` | [`?(Error[])`](../../doc/models/error.md) | Optional | Any errors that occurred during the request. | getErrors(): ?array | setErrors(?array errors): void |

## Example (as JSON)

```json
{
  "custom_attribute_definition": {
    "created_at": "2023-05-05T19:06:36.559Z",
    "description": "This is the other name this merchant goes by.",
    "key": "alternative_seller_name",
    "name": "Alternative Merchant Name",
    "schema": {
      "key1": "val1",
      "key2": "val2"
    },
    "updated_at": "2023-05-05T19:06:36.559Z",
    "version": 1,
    "visibility": "VISIBILITY_READ_ONLY"
  },
  "errors": [
    {
      "category": "MERCHANT_SUBSCRIPTION_ERROR",
      "code": "INVALID_EXPIRATION",
      "detail": "detail6",
      "field": "field4"
    }
  ]
}
```

