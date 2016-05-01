<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * TeamChallenge Entity.
 */
class TeamChallenge extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'user_id' => true,
        'team1_id' => true,
        'team2_id' => true,
        'status' => true,
        'created' => true,
        'modified' => true
    ];
}
