<?php
namespace Models;

/**
 * Class Brand
 *
 * @collection Brand
 *
 * @primary id
 *
 * @property string     $id       
 * @property string     $title
 * @property array      $pictureId
 * @property array      $pictures
 * @property boolean    $isDeleted
 *
 * @method void save()
 */
class Brand extends \MongoStar\Model {}