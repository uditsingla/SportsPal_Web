<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\ConnectionManager;

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
		$this->hasMany('SportsPreferences',['foreignKey' => 'user_id']);
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']));
        return $rules;
    }
	
	public function getNearbyUsers($user_id='',$sport_id='',$latitude='',$longitude='') {
		$conn = ConnectionManager::get('default');
		$data = $conn->execute("SELECT `id`,`first_name`,`last_name`,`email`,`dob`,`gender`,`image`,`latitude`,`longitude`, SQRT(POW(69.1 * (latitude - ".$latitude."), 2) + POW(69.1 * (".$longitude." - longitude) * COS(latitude / 57.3), 2)) AS distance FROM users WHERE `id`!=".$user_id." HAVING distance < 50 ORDER BY distance");
		return $data->fetchAll('assoc');
	}
}
