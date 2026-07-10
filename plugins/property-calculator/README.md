# Property & Land Calculator Plugin

A vanilla JavaScript, database-integrated property and land advisory calculator component.

## Integration

### PHP Loader
First register and load the assets in your header, then call the render helper.

```php
require_once 'plugins/property-calculator/property-calculator.php';

// Register Styles & Scripts
PropertyCalculator::register_assets();

// Render Calculator Widget Container & Script Init
echo PropertyCalculator::render('property-calculator-widget');
```

## Styling
Custom styles are fully isolated in `property-calculator.css` and use clean CSS selectors to override layout grids, slider knobs, tab styles, and total summary card highlight boxes.
