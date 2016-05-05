<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 */
class UsersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('users');
        $this->displayField('id');
        $this->primaryKey('id');
		$this->addBehavior('Timestamp');
		$this->hasMany('FavouriteUsers',['foreignKey' => 'user_id']);
		$this->hasMany('UserFavLocations',['foreignKey' => 'user_id']);
		$this->hasMany('Games',['foreignKey' => 'user_id']);
		$this->hasMany('TeamMembers',['foreignKey' => 'user_id']);
		$this->hasMany('Teams',['foreignKey' => 'creator_id']);
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']));
        return $rules;
    }
}
