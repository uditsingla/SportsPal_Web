<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * UserFavLocation Entity.
 */
class UserFavLocation extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'user_id' => true,
        'latitude' => true,
        'longitude' => true,
        'address' => true,
        'created' => true,
        'modified' => true
    ];
}
