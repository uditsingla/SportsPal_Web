<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * Team Entity.
 */
class Team extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'sport_id' => true,
        'team_name' => true,
        'team_type' => true,
        'members_limit' => true,
        'latitude' => true,
        'longitude' => true,
        'address' => true,
        'creator_id' => true,
        'created' => true,
        'modified' => true
    ];

}
