<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\ConnectionManager;

/**
 * Notifications Model
 */
class NotificationsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('notifications');
        $this->displayField('id');
        $this->primaryKey('id');
		$this->addBehavior('Timestamp');
    }
	
	public function getNotifications() {	
		return $this->find('all');
	}
	
	public function deleteNotifications($ids) {	
		return $this->deleteAll(['id IN' => $ids]);
	}
}
