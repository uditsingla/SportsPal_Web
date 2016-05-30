<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Games Model
 */
class GamesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('games');
        $this->displayField('id');
        $this->primaryKey('id');
		$this->addBehavior('Timestamp');
		$this->belongsTo('Users',['foreignKey' => 'user_id']);
		$this->belongsTo('Sports',['foreignKey' => 'sport_id']);
		$this->belongsTo('Teams',['foreignKey' => 'team_id']);
		$this->hasMany('GameChallenges',['foreignKey' => 'game_id']);
    }
	
	public function getGamedetails($game_id){	
		if($game_id) { 
			return $this->findById($game_id)->select(['id','name'])->first();
		} else {
			return false;
		}
	}
}
