<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * GameChallenge Entity.
 */
class GameChallenge extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'game_id' => true,
        'user_id' => true,
        'team_id' => true,
        'status' => true,
        'created' => true,
        'modified' => true
    ];
}
