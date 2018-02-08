# Wasa Kredit Client PHP SDK v2.1

**Table of Content**

* [Change log](#change_log)
* [Available methods](#available_methods)
  * [Calculate Leasing Cost](#calculate_leasing_cost)
  * [Calculate Total Leasing Costs](#calculate_total_leasing_cost)
  * [Create Checkout](#create_checkout)  
  * [Validate Allowed Leasing Amount](#validate_allowed_leasing_amount)
  * [Create Product Widget](#create_product_widget)
    * [Callback handling](#callback_handling)
  * [Add Order Reference](#add_order_reference)
  * [Get Order](#get_order)
* [Handling the Response](#handling_the_response)

## <a name="change_log"></a>Change log

### What's new in v2.1

* Updated documentation to reflect recent changes to code base.
* Add description of *Get Order* and *Add Order Reference* methods.

### What's new in v2.0

Custom callbacks on events from the Checkout has been introduced.  
The snippet from create_checkout will no longer auto initialize the checkout.  
This to allow the integrating partner to be able to handle the callbacks manually.

## Getting Started

This documentation is about the PHP SDK for communicating with Wasa Kredit checkout services.

### Prerequisites

  * Partner credentials
  * PHP 5.7 or above

### Acquiring the SDK

You can apply to recieve the SDK and Partner credentials by sending a mail to [ehandel@wasakredit.se](mailto:ehandel@wasakredit.se).

### Initialization

Initialize the main *Client* class by passing in your issued *Client ID* and *Client Secret*.
You can optionally supply a *Test Mode* parameter which is by default is set to true.

```
 /**
  * Checks if the input is valid JSON or not   
  * @param   string       clientId
  * @param   string       clientSecret
  * @param   boolean      testMode
  *
  * @return Client
  */  

new Client({CLIENT ID}, {CLIENT SECRET}, {TEST MODE})
```

### Client

Orchestrates the main flow. *Client* will fetch and store an access token upon an initial request and save it in a PHP session for future requests.

#### Example

```
$this->_client = new Client(clientId, clientSecret, testMode);
```

#### Parameters

| Name | Type | Description |
|---|---|---|
| clientId | *string* (required) | The client id that has been issued by Wasa Kredit |
| clientSecret | *string* (required) | Your client secret issued by Wasa Kredit |
| testMode | *boolean* | A boolean value if SDK should make requests in test mode or not |

## <a name="available_methods"></a>Available methods

### <a name="calculate_leasing_cost"></a>Calculate Leasing Cost

When presenting a product list view this method calculates the leasing price for each of the products.

```
public function calculate_leasing_cost({ITEMS})
```

#### Parameters

| Name | Type | Description |
|---|---|---|
| items | *array[**Item**]* (required) | An array containing the data type **Item** |

##### Item
| Name | Type | Description |
|---|---|---|
| financed_price | *Price* (required) | ... |
| product_id | *string* (required) | Your unique product identifier |

##### Price
| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | A string value that will be parsed to a decimal, e.g. 199 is '199.00' |
| currency | *string* (required) | The currency |

#### Example usage:

```
$payload = array(
              'items' => array(
                 'financed_price' => array(
                    'amount' => '14995.00',
                    'currency' => 'SEK'
                 ),
                 'product_id' => '12345'
              )
           );

$response = $this->_client->calculate_leasing_cost($payload);
```

#### Response

| Name | Type | Description |
|---|---|---|
| leasing_costs | *array[**Response Item**]* | An array containing the data type **Response Item** |

##### Response Item

| Name | Type | Description |
|---|---|---|
| monthly_cost | *Price* | |
| product_id | *string* | Your unique product identifier |

##### Price

| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | A string value that will be parsed to a decimal, e.g. 199 is '199.00' |
| currency | *string* | The currency |


#### Example response:

```
$response->data

{
  'leasing_costs': [
    {
      'monthly_cost': {
        'amount': '1152.00',
        'currency': 'SEK'
      },
      'product_id': '12345'
    }
  ]
}
```


### <a name="calculate_total_leasing_cost"></a>Calculate Total Leasing Costs

Calculates the total monthly leasing costs for all the partner's contract lengths.

```
public function calculate_total_leasing_cost({PAYLOAD})
```

#### Parameters

| Name | Type | Description |
|---|---|---|
| total_amount | *Price* (required) | The total amount of the cart items excluding VAT as a Price object |

##### Price

| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | A string value that will be parsed to a decimal, e.g. 199 is '199.00' |
| currency | *string* (required) | The currency |

#### Example usage:

```
$payload = array(
              'total_amount' => array(
                 'amount' => '14995.00',
                 'currency' => 'SEK'
              )
           );

$response = $this->_client->calculate_total_leasing_cost($payload);
```

#### Response

| Name | Type | Description |
|---|---|---|
| default_contract_length | *int* | Default lease period in month |
| contract_lengths | *array[ContractLength]* | An array of all the partners available leasing options |

##### ContractLength

| Name | Type | Description |
|---|---|---|
| contract_length | *int* | Lease period in month |
| monthly_cost | *Price* | Price object containing the amount of the leasing option |

##### Price

| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | A string value that will be parsed to a decimal, e.g. 199 is '199.00' |
| currency | *string* | The currency |

```
$response->data

{
    'default_contract_length': 24,
    'contract_lengths': [
        {
            'contract_length': 12,
            'monthly_cost': {
                'amount': '802.00'
                'currency: 'SEK'
            }
        },
        {
            'contract_length': 24,
            'monthly_cost': {
                'amount': '442.00'
                'currency: 'SEK'
            }
        }
    ]
}
```

### <a name="create_checkout"></a>Create Checkout
The Checkout is inserted as a Payment Method in the checkout. It could be used either with or without input fields for address. Post the cart to Create Checkout to initiate the checkout.

An alternative use case for the Checkout is as a complete checkout if there is no need for other payment methods.

```
public function create_checkout({CHECKOUT})
```

#### Parameters

| Name | Type | Description |
|---|---|---|
| payment_types | *string* | Selected payment type to use in the checkout, e.g. 'leasing' |
| order_reference_id | *string* | The order reference of the partner |
| order_references | *array* | The order reference of the partner | A list containing order references. |
| cart_items | *array[Cart Item]* (required) | An array of the items in the cart as Cart Item objects |
| shipping_cost_ex_vat | *Price* (required) | Price object containing the shipping cost excluding VAT |
| customer_organization_number | *string* | Optional customer organization number |
| purchaser_name | *string* | Optional name of the purchaser |
| purchaser_email | *string* | Optional e-mail of the purchaser |
| purchaser_phone | *string* | Optional phone number of the purchaser |
| billing_address | *Address* | Optional Address object containing the billing address |
| delivery_address | *Address* | Optional Address object containing the delivery address |
| recipient_name | *string* | Optional name of the recipient |
| recipient_phone | *string* | Optional phone number of the recipient |
| request_domain | *string* | The domain of the partner, used to allow CORS |
| confirmation_callback_url | *string* | Url to the partner's confirmation page |
| ping_url | *string* | Receiver url for order status changes notifications |

##### Cart Item
| Name | Type | Description |
|---|---|---|
| product_id | *string* (required) | Id of the Product |
| product_name | *string* (required) | Name of the product |
| price_ex_vat | *Price* (required) | Price object containing the price of the product excluding VAT |
| quantity | *int* (required) | Quantity of the product |
| vat_percentage | *string* (required) | VAT percentage as a parsable string, e.g. '25' is 25%  |
| vat_amount | *Price* (required) | Price object containing the calculated VAT of the product |
| image_url | *string* | An optional image url of the product |

##### Price

| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | A string value that will be parsed to a decimal, e.g. 199 is '199.00' |
| currency | *string* (required) | The currency |

##### Address

| Name | Type | Description |
|---|---|---|
| company_name | *string* | Company name |
| street_address | *string* | Street address |
| postal_code | *string* | Postal code |
| city | *string* | City |
| country | *string* | Country |


#### Response

The data property on the response object ($response->data) will return a unique html snippet to be embedded in your checkout html.

| Name | Type | Description |
|---|---|---|
| HtmlSnippet | *string* | The checkout snippet for embedding. |

#### Example usage:

```
$payload = array(
  'order_reference_id' => 'a1234567890-1337',
  'order_references' => array(
    [0] => array(
      'key' => 'magento_quote_id',
      'value' => $orderId
    )
  ),  
  'cart_items' => array(
    array(
      'product_id' => 'ez-3000b-1',
      'product_name' =>'Kylskåp EZ3',
      'price_ex_vat' => array(
        'amount' => '11996.00',
        'currency' => 'SEK'
      ),
      'quantity' => 1,
      'vat_percentage' => '25',
      'vat_amount' => array(
        'amount' => '2999.00',
        'currency' => 'SEK'
      )
    )
  ),
  'shipping_cost_ex_vat' => array(
    'amount' => '448.00',
    'currency' => 'SEK'
  ),
  'request_domain' => 'https://www.wasakredit.se/',
  'confirmation_callback_url' => 'https://www.wasakredit.se/payment-callback/',
  'ping_url' => 'https://www.wasakredit.se/ping-callback/'
);           

$response = $this->_client->create_checkout($payload);
```

##### Initialization

When you want to initialize the checkout, just call the global ```window.wasaCheckout.init()```.

```javascript
<script>
    window.wasaCheckout.init();
</script>
```

## <a name="callback_handling"></a>Callback handling

Optionally, you're able to pass an options object to the ```init```-function. Use this if you want to manually handle the onComplete, onRedirect and onCancel events.

```javascript
<script>
    var options = {
      onComplete: function(orderReferences){
        //[...]
      },
      onRedirect: function(orderReferences){
        //[...]
      },
      onCancel: function(orderReferences){
        //[...]
      }
    };   
    window.wasaCheckout.init(options);
</script>
```

The ```onComplete``` event will be raised when a User has completed the checkout process. We recommend that you convert your cart/checkout to an order here if you haven't done it already.

The ```onRedirect``` event will be raised the user clicks the "back to store/proceed"-button. The default behaviour will redirect the user to the ```confirmation_callback_url``` passed into the ```create_checkout```-function.

The ```onCancel``` event will be raised if the checkout process is canceled by the user or Wasa Kredit.

All callback functions will get the ```orderReferences``` parameter passed from the checkout. This parameter consists of an Array of ```KeyValue``` objects.
These are the same values as the ones that was passed to the ```create_checkout```-function as the ```order_references``` property.

```javascript
orderReferences = [
  { key: "partner_checkout_id", value: "900123" },
  { key: "partner_reserved_order_number", value: "123456" }
];    
```

### <a name="validate_allowed_leasing_amount"></a>Validate Allowed Leasing Amount

Validates that the amount is within the min/max financing amount for the partner.

```
public function validate_allowed_leasing_amount($amount)
```

#### Parameters
| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | The amount excluding VAT to be validated as a string, e.g. 199 is '199.00' |

#### Example usage:

```
$amount = '14995.00';

$response = $this->_client->validate_allowed_leasing_amount($amount);
```

#### Response

| Name | Type | Description |
|---|---|---|
| validation_result | *boolean* | Amount sent is between min/max limit for the partner |

```
$response->data

{
  "validation_result": true
}
```

### <a name="create_product_widget"></a>Create Product Widget

To inform the customer about leasing as a Payment Method the Product Widget should be displayed close to the price information on the product detail page.

```
public function create_product_widget($payload)
```

#### Parameters
| Name | Type | Description |
|---|---|---|
| financial_product | *string* (required) | The amount to be validated |
| price_ex_vat | *Price* (required) | Price object excluding VAT |

##### Price

| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | A string value that will be parsed to a decimal, e.g. 199 is '199.00' |
| currency | *string* (required) | The currency |

#### Example usage:

```
$payload = array(
              'financial_product' => 'leasing',
              'price_ex_vat' => array(
                'amount': '14995.50',
                'currency': 'SEK'  
              )
           );

$response = $this->_client->validate_allowed_leasing_amount($amount);
```

#### Response

The response will return a unique html snippet to be embedded in your product view html.

```
$response->data

   "<div> ... </div>"
```


### <a name="add_order_reference"></a>Add Order Reference

Adds a new order reference and appends it to the current order references of the order. The purpose of supporting multiple order references for a single order is to provide generic support for e-commerce platforms and solutions that use multiple references in their purchase and order flow.

#### Parameters
| Name | Type | Description |
|---|---|---|
| order_id | *string* (required) | The id of the target order object |
| order_references | *array* | Please see below. |

#### order_references
| Name | Type | Description |
|---|---|---|
| Key | *string* | A key describing the order reference. |
| Value | *string* | The order reference value. |

#### Example usage:

```
$orderReferences = array(
  'key' => 'magento_order_id',
  'value' => $order2
);
$orderId = 'f404e318-7180-47ab-91db-fbb66addf577'
$response = $this->api_client->add_order_reference($orderId, $orderReferences);
```


### <a name="get_order"></a>Get Order

Fetches a Wasa Kredit order based on its id.

When a checkout is created (by calling the CreateCheckout method) a Wasa Kredit order is created. Anytime the status of the order is changed, a notification will be sent to the ping_url specified when the checkout was created. This notification includes the ID of the order, which is supposed to be used to call this method.

#### Parameters
| Name | Type | Description |
|---|---|---|
| order_id | *string* (required) | The id of the target order object |


#### Example usage:

```
$orderId = 'f404e318-7180-47ab-91db-fbb66addf577'
$response = $this->_client->get_order($orderId);
```

#### Response

```
{
  "customer_organization_number": "222222-2222",
  "delivery_address": {
    "company_name": "Star Republic",
    "street_address": "Ekelundsgatan 9",
    "postal_code": "41118",
    "city": "Gothenburg",
    "country": "Sweden"
  },
  "billing_address": {
    "company_name": "Star Republic",
    "street_address": "Ekelundsgatan 9",
    "postal_code": "41118",
    "city": "Gothenburg",
    "country": "Sweden"
  },
  "order_references": [
    {
      "key": "partner_order_number",
      "value": "123456"
    }
  ],
  "recipient_name": "Anders Svensson",
  "recipient_phone": "070-1234567",
  "status": {
    "status": "shipped"
  },
  "cart_items": [
    {
      "product_id": "ez-41239b",
      "product_name": "Kylskåp EZ3",
      "price_ex_vat": {
        "amount": "14995.50",
        "currency": "SEK"
      },
      "quantity": 1,
      "vat_percentage": "25",
      "vat_amount": {
        "amount": "14995.50",
        "currency": "SEK"
      },
      "image_url": "https://unsplash.it/500/500"
    }
  ]
}
```

## <a name="handling_the_response"></a>Handling the Response

We are using a Response class when passing information through the SDK.

###  Properties
| Name | Type | Description |
|---|---|---|
| statusCode | *string* | Http status code of the response |
| data | *string* | Contains the body of the response |
| error | *string* | Error code passed from the API |
| errorMessage | *string* | Developer error message |
| curlError | *string* | Curl error message |



## Running the tests

```
php vendor/bin/phpunit
```
