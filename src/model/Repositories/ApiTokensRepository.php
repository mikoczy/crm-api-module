<?php

namespace Crm\ApiModule\Repository;

use Crm\ApplicationModule\Repository;
use Crm\UsersModule\Auth\Access\TokenGenerator;
use DateTime;
use Nette\Database\Table\IRow;

class ApiTokensRepository extends Repository
{
    protected $tableName = 'api_tokens';

    final public function all()
    {
        return $this->getTable()->order('created_at DESC');
    }

    final public function generate($name, $ipRestrictions = '*', $active = true)
    {
        $token = TokenGenerator::generate();
        return $this->insert([
            'name' => $name,
            'token' => $token,
            'ip_restrictions' => $ipRestrictions,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
            'active' => $active,
        ]);
    }

    final public function update(IRow &$row, $data)
    {
        $data['updated_at'] = new DateTime();
        return parent::update($row, $data);
    }

    final public function findToken($token)
    {
        $tokenRow = $this->getTable()->where('token', $token)->fetch();
        if (!$tokenRow) {
            return false;
        }
        return $tokenRow;
    }
}
