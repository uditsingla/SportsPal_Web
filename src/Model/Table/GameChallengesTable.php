<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * GameChallenges Model
 */
class GameChallengesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('game_challenges');
        $this->displayField('id');
        $this->primaryKey('id');
		$this->addBehavior('Timestamp');
		$this->belongsTo('Users',['foreignKey' => 'user_id']);
		$this->belongsTo('Teams',['foreignKey' => 'team_id']);
    }
	
	public function getGameChallengesdetails($challenge_id){	
		if($challenge_id) { 
			return $this->find('all',['contain' => ['Teams', 'Users']])->where(['GameChallenges.id'=>$challenge_id])->first();
		} else {
			return false;
		}
	}
}
