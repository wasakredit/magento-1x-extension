# Wasa Kredit Magento Extension v1.5
Official Wasa Kredit payment extension for Magento. Allows store builders to offer **Wasa Kredit** as a payment option.


**Table of Content**

* [Change log](#change_log)
* [Requirements](#requirements)
* [Installation](#installation)
* [First time setup](#first_time_setup)
* [Product widget](#product_widget)
* [List view unstyled](#list_view_unstyled)
* [List view styled](#list_view_styled)
* [Folder structure](#folder_structure)

## <a name="change_log"></a>Change log

### v1.5

1. Update the internal php SDK to version 2.2.
2. Update the use of internal SDK endpoints from deprecated endpoints `calculate_leasing_cost` and `validate_allowed_leasing_amount` to new generic ones called `calculate_monthly_cost` and `validate_financed_amount`.  
_(This means no breaking changes to current implementation, the helper method is still called `calculateLeasingCost`)_
3. Update the SDK config to use the correct plugin header tag.
4. There should no longer be a problem with measuring and setting the height of the iframe if content is not loaded immediately.
5. The plugin will now handle order status updates from Wasa Kredit properly.
6. Discounted/Special prices should now be taking into account when rendering the monthly costs in product list views.

### v1.4         

1. Add new price calculation taking difference in currency into account.
2. Enable plugin on website and store level and add selection for supported shipping countries.
3. Add currency validation.
4. Modify functions that render product widget and leasing costs in list view to check if currency is SEK.


## <a name="requirements">Magento Version Requirements</a>

Type       | Version            | Status              
---------- | ------------------ |  ------------------
Enterprise | 1.14.2.3           | Tested              
Community  | 1.9.3.0            | Tested              
Community  | 1.9.1.1            | Tested              
Community  | 1.8.1.0            | Tested              


## <a name="installation">Installation</a>

1. Extract the zip file to your server or local machine.
2. Copy all files into the corresponding file location. ***Be careful not to replace the containing directory!***
3. Flush the Magento Cache in `System > Cache Management`.

## <a name="first_time_setup">First time setup</a>

1. Proceed to `System > Configuration > Sales > Payment Methods`.

2. Fill in your assigned ***Partner ID*** and ***Client Secret ID***.

3. Fill in your base domain and the url to the thank you page

4. Put in test mode.

5. If your system use a custom field for the organisations number, please fill in "Custom organisation number field".



## <a name="product_widget">Add widget showing leasing price on product page</a>

Add `<?php echo Mage::helper('wkcheckout')->createProductWidget($_product); ?>` to your desired view (example: `app/design/frontend/{choosen theme (default is 'rwd')}/default/template/catalog/product/view.phtml`). Make sure you pass in a product object such as ***$_product***.


## <a name="list_view_unstyled">Add leasing cost in product list</a>
[![Leasing cost in list view](https://static1.squarespace.com/static/59f2fd114c0dbf9244a51738/t/5a0422c90d9297d3168e7fe6/1510559477448/product-list-leasing.png?format=500w)]()

To calculate and display the leasing cost for each product in a list, use the `calculateLeasingCosts` method as seen below:

[![Code for list view](https://static1.squarespace.com/static/59f2fd114c0dbf9244a51738/t/5a04232c652deabdd321676c/1510220595471/product-list-example.png?format=750w)]()

(`app/design/frontend/{choosen theme (default is 'rwd')}/default/template/catalog/product/list.phtml`)

1. Retrieve the leasing costs for every product in a list and store them in a variable.
`<?php $leasingCosts = Mage::helper('wkcheckout')->calculateLeasingCosts($_productCollection); ?>`

2. Display each leasing cost using the corresponding product id.
`<?php echo $leasingCosts[$_product->getEntityId()]; ?>`


## <a name="list_view_styled">Formatting in product list</a>

[![Product Leasing](https://static1.squarespace.com/static/59f2fd114c0dbf9244a51738/t/5a042347e2c4838e3f20d8e3/1510220621104/leasing-list-formatting.png?format=500w)]()

To format the output, use the following syntax.

```
<?php echo Mage::getStoreConfig('payment/wkcheckout/active') && $leasingCosts[$_product->getEntityId()] ? '<p>'.'Leasing '.$leasingCosts[$_product->getEntityId()].' kr/m&aring;n'.'</p>' : ''; ?>
```


**Example of implementation on product list page:**

```
<?php echo Mage::helper('wkcheckout')->calculateLeasingCost($_product); ?>
```
Will return a string representation of the monthly leasing amount:
```
"650"
```
```
<?php echo Mage::helper('wkcheckout')->calculateLeasingCosts($_productCollection); ?>
```
will result in the following response:
```
[{
  "monthly_cost": {
    "amount": "631",
    "currency": "SEK"
  },
  "product_id": "2"
}, {
  "monthly_cost": {
    "amount": "650",
    "currency": "SEK"
  },
  "product_id": "1"
}]
```



## <a name="folder_structure">Folder structure</a>

```sh
.
├── app
│   ├── code
│   │   └── local
│   │       └── Wasa
│   │           └── Wkcheckout
│   │               ├── Block
│   │               │   ├── Form
│   │               │   │   └── Wkcheckout.php
│   │               │   ├── Info
│   │               │   │   └── Wkcheckout.php
│   │               │   └── Redirect
│   │               │       └── Wkcheckout.php
│   │               ├── Helper
│   │               │   └── Data.php
│   │               ├── Model
│   │               │   ├── Checkout.php
│   │               │   ├── System
│   │               │   │   └── Config
│   │               │   │       └── Source
│   │               │   │           └── Dropdown
│   │               │   │               └── Values.php
│   │               │   └── Wkcheckout.php
│   │               ├── controllers
│   │               │   └── CheckoutController.php
│   │               ├── etc
│   │               │   ├── config.xml
│   │               │   └── system.xml
│   │               └── sql
│   │                   └── wkcheckout_setup
│   │                       └── install-1.0.0.0.php
│   ├── design
│   │   └── frontend
│   │       └── base
│   │           └── default
│   │               └── template
│   │                   └── wkcheckout
│   │                       ├── form
│   │                       │   └── wkcheckout.phtml
│   │                       ├── redirect.phtml
│   │                       └── widget
│   │                           └── widget.phtml
│   └── etc
│       └── modules
│           └── Wasa.xml
├── docs
│   └── wasa_kredit_logotype.png
├── lib
│   └── Wasa
│       └── php
│           └── client-sdk.php
├── skin
│   └── frontend
│       └── base
│           └── default
│               └── wasa
│                   ├── css
│                   │   └── checkout.css
│                   ├── fonts
│                   │   ├── intro-cond-bold.ttf
│                   │   └── intro-cond-regular.ttf
│                   └── img
│                       └── wasa-kredit.svg
```
