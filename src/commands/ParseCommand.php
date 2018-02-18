<?php
    namespace Commands;

    use Components\ExcelParser;
    use Components\ParserContainer;
    use Models\Product;
    use Models\Brand;
    use Models\Category;
    use Models\ProductProperty;
    
    require_once '../../bootstrap.php';
    
    $parser_config = [
        'Описание бытовая химия 2017 х описом 9999.xlsx' => [
            'dataRows' => [1, 95],
            'sku' => 0,
            'propsColumns' => [1, 2],
            'title' => 5,
            'briefly' => 3,
            'description' => 4,
            'brand' => '/Sonett|Urtekram/'
        ],
        'Прайс  бытовая химия Sonett 2018.xlsx' => [
            'dataRows' => [1, 42],
            'sku' => 0,
            'propsColumns' => [1, 2],
            'title' => 3,
            'briefly' => 3,
            'description' => 3,
            'pricePdv' => 5,
            'priceNds' => 6,
            'brand' => '/Sonett/'
        ],
        'Презентація Dr.Goerg 2018.xlsx' => [
            'dataRows' => [1, 11],
            'sku' => 0,
            'propsColumns' => [1, 2, 8],
            'title' => 4,
            'briefly' => 3,
            'description' => 3,
            'pricePdv' => 6,
            'priceNds' => 7,
            'brand' => '/Dr\.Goerg/'
        ],
        'Описание продуктов питания NEW 2017 Дил.xls' => [
            'dataRows' => [2, 120],
            'sku' => 0,
            'propsColumns' => [1, 2, 5],
            'title' => 3,
            'briefly' => 3,
            'description' => 3,
            'brand' => '/Ekomil|Le Pain des Fleurs|Ma vie sans Gluten|Bisson|Premial|EOS BIO/'
        ],
        'Прайс молоко 2018.xls' => [
            'dataRows' => [2, 35],
            'sku' => 0,
            'propsColumns' => [1, 2, 7, 8],
            'title' => 3,
            'briefly' => 3,
            'description' => 3,
            'pricePdv' => 5,
            'priceNds' => 6,
            'brand' => '/Ekomil/'
        ],
        'Прайс продукты питания 2018.xls' => [
            'dataRows' => [2, 64],
            'sku' => 0,
            'propsColumns' => [1, 2, 7, 8],
            'title' => 3,
            'briefly' => 3,
            'description' => 3,
            'pricePdv' => 5,
            'priceNds' => 6,
            'brand' => '/Le Pain des Fleurs|Ma vie sans Gluten|Bisson|Premial/'
        ],
    ];
    
    $parser = new ParserContainer();
    
    foreach ($parser_config as $file_name => $parser_config)
    {
        $excel_parser = new ExcelParser(__DIR__ . '/../data/' . $file_name, $parser_config);
        
        // Create parser instanse
        $parser->setParser($excel_parser);
        
        // Parse results
        $parser->parse();
        
        $brand = null;
        $category = null;
        $data = $parser->getData();
        
        foreach ($data as $key => $row)
        {
            // Obtain table header
            if ($key === 0)
            {
                $header_row = $row;
                //print_r($header_row);exit;
                continue;
            }
            
            // Check if row with category/brand titles
            if (!empty($row[0]) && empty($row[1]) && empty($row[2]))
            {
                if (!preg_match($parser_config['brand'], $row[0], $matches)) 
                {
                    throw new \Exception('Brand not found.');
                }
                // Try to find brand with one of the matched names
                $brand = Brand::fetchOne([
                    'title' => $matches[0],
                    'isDeleted' => [
                        '$ne' => true
                    ]
                ]);
                // If brand not found - add new brand record
                if (empty($brand)) 
                {
                    $brand = Brand::getBootstrap();
                    $brand->type = 'final';
                    $brand->title = $matches[0];
                    $brand->save();
                }
                
                // Try to find category
                $category = Category::fetchOne([
                    'title' => $row[0],
                    'isDeleted' => [
                        '$ne' => true
                    ]
                ]);
                if (empty($category))
                {
                    $category = Category::getBootstrap();
                    $category->title = $row[0];
                    $category->type = 'final';
                    $category->save();
                }
                continue;
            }
            
            $product = new Product;
            
            /** 
             * Autogenerated fields
             */
            $product->isDeleted = false;
            $product->dateCreated = time();
            $product->type = 'final';
            $product->isAvailable = true;
            $product->isAuction = false;
            $product->isBestseller = false;
            $product->isNovelty = false;
            $product->relatedProducts = [];
            $product->video = '';
            $product->discountTimeout = 0;
            
            /** 
             * Fields from excel
             */
            $product->sku = $row[$parser_config['sku']];
            $product->title = $row[$parser_config['title']];
            $product->briefly = $row[$parser_config['briefly']];
            $product->description = $row[$parser_config['description']];
            if (empty($parser_config['priceNds'])) 
            {
                $product->price = 0.0;
                $product->discount = 0.0;
                $product->discountType = '';
            }
            else 
            {
                $product->price = $row[$parser_config['priceNds']];
                $product->discount = $row[$parser_config['priceNds']] - $row[$parser_config['pricePdv']];
                $product->discountType = 'const';
            }
            $product->pictures = [];
            $product->pictureId = '';            
            $product->brandId = $brand->id;
            $product->categoryId = $category->id;
            
            // Save product properties
            $properties = [];
            foreach ($header_row as $i => $prop_title)
            {
                if (in_array($i, $parser_config['propsColumns']))
                {
                    // Save product properties titles
                    $property_title = ProductProperty::fetchOne([
                        'key' => $prop_title,
                        'isDeleted' => [
                            '$ne' => true
                        ]
                    ]);
                    if (empty($property_title))
                    {
                        $property_title = new ProductProperty;
                        $property_title->key = $prop_title;
                        $property_title->isDeleted = false;
                        $property_title->parentId = '';
                        $property_title->save();
                    }
                    
                    // Save product properties
                    $property_product = ProductProperty::fetchOne([
                        'key' => $row[$i],
                        'isDeleted' => [
                            '$ne' => true
                        ],
                        'parentId' => $property_title->id
                    ]);
                    if (empty($property_product))
                    {
                        $property_product = new ProductProperty;
                        $property_product->key = $row[$i];
                        $property_product->isDeleted = false;
                        $property_product->parentId = $property_title->id;
                        $property_product->save();
                    }
                    
                    $properties[] = $property_product->id;
                }
            }
            $product->properties = $properties;
            
            $product->save();
        }
    }

