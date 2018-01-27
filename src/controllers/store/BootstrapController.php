<?php
    namespace Controllers\Store;
    
    use Components\MenuComponent;

    class BootstrapController
    {
        const KEYS = [
            'currencyList',
            'currencyCource',
            'currencyCode',
            'payment',
            'delivery',
        ];
        
        private static function convertToKeyVal(array $settings)
        {
            $keyVal = [];
            foreach ($settings as $setting) {
                $keyVal[$setting['key']] = in_array($setting['key'], ['payment', 'delivery', 'currencyList']) 
                    ? json_decode($setting['value']) : $setting['value'];
            }
            return $keyVal;
        }
        
        public function index($request, $response)
        {
            $data = [];
            
            // Extract all menus
            $menus = \Models\Menu::fetchAll([
                '$and' => [
                    [ 'isDeleted' => ['$ne' => true] ],
                    ['$or' => [
                        ['parrentId' => '' ],
                        ['parrentId' => ['$exists' => false] ]
                    ]]
                ]
            ], [ 'order' => 1 ]);
            
            if (count($menus) !== 0) {
                foreach ($menus->toArray() as $menu) {
                    $data['menus'][$menu['id']] = (new MenuComponent($menu['id']))->fetch();
                }
            }
            
            // Get site settings
            $settings = \Models\Settings::fetchAll([
                'key' => [
                    '$in' => self::KEYS
                ]
            ])->toArray();
            
            $data['settings'] = self::convertToKeyVal($settings);
            
            return $response->write(
                json_encode($data)
            );
        }
    }
