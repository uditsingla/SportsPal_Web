<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * Game Entity.
 */
class Game extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'sport_id' => true,
        'name' => true,
        'user_id' => true,
        'game_type' => true,
        'team_id' => true,
        'date' => true,
        'time' => true,
        'latitude' => true,
        'longitude' => true,
        'address' => true,
        'game_status' => true,
        'member_limit' => true
    ];

}
