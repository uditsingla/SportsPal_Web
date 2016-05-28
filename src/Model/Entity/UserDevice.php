<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * UserDevice Entity.
 */
class UserDevice extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'user_id' => true,
        'device_type' => true,
        'device_token' => true,
        'usertoken' => true
    ];

}
