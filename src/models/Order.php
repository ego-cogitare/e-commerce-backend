<?php
namespace Models;

/**
 * Class Order
 *
 * @collection Order
 *
 * @primary id
 *
 * @property string     $id
 * @property array      $products
 * @property string     $stateId
 * @property string     $userName
 * @property string     $address
 * @property string     $email
 * @property string     $comment
 * @property string     $phone
 * @property int        $dateCreated
 * @property boolean    $isDeleted
 *
 * @method void save()
 */
class Order extends \MongoStar\Model
{
  public function expand()
  {
    if (count($this->products) > 0) {
      $this->products = array_map(
        function($item) {
          $product = \Models\Product::fetchOne([
              'id' => $item['id'],
              'isDeleted' => [
                  '$ne' => true
              ]
          ]);

          return array_merge(
            $product->expand()->toArray(),
            ['count' => (int)$item['count']]
          );
        },
        $this->products
      );
    }

    return $this;
  }
}
