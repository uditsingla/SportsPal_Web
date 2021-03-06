<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Teams Model
 */
class TeamsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('teams');
        $this->displayField('id');
        $this->primaryKey('id');
		$this->addBehavior('Timestamp');
		$this->belongsTo('Users',['foreignKey' => 'creator_id']);
		$this->belongsTo('Sports',['foreignKey' => 'sport_id']);
		$this->hasMany('TeamMembers',['foreignKey' => 'team_id']);
    }
	
	public function getTeamdetails($team_id){	
		if($team_id) { 
			return $this->findById($team_id)->select(['id','team_name'])->first();
		} else {
			return false;
		}
	}
}
