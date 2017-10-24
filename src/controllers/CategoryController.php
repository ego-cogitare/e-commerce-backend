<?php
    namespace Controllers;
    
    class CategoryController
    {
        public function __invoke($request, $response, $args) 
        {
            switch ($args['action']) {
                case 'list':
                    $products = \Models\Category::fetchAll([
                        'isDeleted' => [
                            '$ne' => true
                        ]
                    ]);
                    
                    return $response->write(
                        json_encode($products->toArray())
                    );
                break;
            
                case 'get':
                    $product = \Models\Category::fetchOne([
                        'id' => $args['id'],
                        'isDeleted' => [ 
                            '$ne' => true 
                        ]
                    ]);

                    if (empty($product)) {
                        return $response->withStatus(404)->write(
                            json_encode([
                                'error' => 'Категория не найдена'
                            ])
                        );
                    }
                     
                    return $response->write(
                        json_encode($product->toArray())
                    );
                break;
            
                default:
                    return $response->withStatus(404)->write(
                        json_encode(['error' => 'Action not allowed'])
                    );
                break;
            }
        }
    }