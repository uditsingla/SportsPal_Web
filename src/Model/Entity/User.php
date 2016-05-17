<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * User Entity.
 */
class User extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'email' => true,
        'password' => true,
        'first_name' => true,
        'last_name' => true,
        'dob' => true,
        'gender' => true,
        'image' => true,
        'social_platform' => true,
        'social_id' => true,
        'latitude' => true,
        'longitude' => true,
        'address' => true,
        'bio' => true
    ];
    
    protected function _setPassword($value)
	{
		$hasher = new DefaultPasswordHasher();
		return $hasher->hash($value);
	}
}
