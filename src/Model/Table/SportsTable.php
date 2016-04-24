<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Sports Model
 */
class SportsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('sports');
        $this->displayField('id');
        $this->primaryKey('id');
		$this->addBehavior('Timestamp');
		//$this->hasOne('Userdetails',['foreignKey' => 'user_id']);
    }
}
