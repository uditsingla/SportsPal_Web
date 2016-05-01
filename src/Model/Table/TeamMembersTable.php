<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TeamMembers Model
 */
class TeamMembersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('team_members');
        $this->displayField('id');
        $this->primaryKey('id');
		$this->addBehavior('Timestamp');
		$this->belongsTo('Users',['foreignKey' => 'user_id']);
    }
}
