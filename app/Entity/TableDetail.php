<?php

namespace App\Entity;
use App\User_data_source;
use Illuminate\Database\Eloquent\Model;
/**
 * @ORM\Entity
 * @ORM\Table(name="team_table_mappings")
 * @ORM\HasLifecycleCallbacks()
 */
class TableDetail extends Model
{
    protected $table = 'team_table_mappings';
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer", unique=true, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $tableName;
    /**
     * @ORM\Column(type="string")
     */
    private $tableId;

    /**
     * @ORM\Column(type="string")
     */
    private $teamId;

    /**
     * @ORM\Column(type="string")
     */
    private $auth;

    /**
     * @ORM\Column(type="string")
     */
    private $socketApi;

    /**
     * @ORM\Column(type="string")
     */
    private $newEntryApi;



    public function setId($id)
    {
        return $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($table_name)
    {
        $this->tableName = $table_name;
    }

    public function getTableId()
    {
        return $this->tableId;
    }

    public function setTableId($table_id)
    {
        $this->tableId = $table_id;
    }

    public function getTeamId()
    {
        return $this->teamId;
    }

    public function setTeamId($team_id)
    {
        $this->teamId = $team_id;
    }

    public function getAuth()
    {
        return $this->auth;
    }

    public function setAuth($auth)
    {
        $this->auth = $auth;
    }

    public function getSocketApi()
    {
        return $this->socketApi;
    }

    public function setSocketApi($socketApi)
    {
        $this->socketApi = $socketApi;
    }

    public function getNewEntryApi()
    {
        return $this->newEntryApi;
    }

    public function setNewEntryApi($new_entry_api)
    {
        $this->newEntryApi = $new_entry_api;
    }

    public function dataSource()
    {
        return $this->hasMany(User_data_source::class,'table_incr_id','id');
    }

    public function tableStructure() {
        return $this->hasMany(TableStructure::class, 'table_id', 'id')->orderBy('ordering','ASC');
    }

}
