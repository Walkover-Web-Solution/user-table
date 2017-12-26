<?php

namespace App\Entity;
use App\ColumnType;
use Illuminate\Database\Eloquent\Model;

/**
 * @ORM\Entity
 * @ORM\Table(name="table_structures")
 * @ORM\HasLifecycleCallbacks()
 */
class TableStructure extends Model
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer", unique=true, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $tableId;

    /**
     * @ORM\Column(type="string")
     */
    private $columnName;

    /**
     * @ORM\Column(type="integer")
     */
    private $columnTypeId;

    /**
     * @ORM\Column(type="text")
     */
    private $defaultValue;

    /**
     * @ORM\Column(type="int")
     */
    private $isUnique;

    /**
     * @ORM\Column(type="int")
     */
    private $display;
    /**
     * @ORM\Column(type="int")
     */
    private $ordering;

    protected $table = 'table_structures';

    private $structure;


    public function setId($id)
    {
        return $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTableId()
    {
        return $this->tableId;
    }

    public function setTableId($table_id)
    {
        $this->tableId = $table_id;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

    public function setColumnName($column_name)
    {
        $this->columnName = $column_name;
    }

    public function getColumnTypeId()
    {
        return $this->columnTypeId;
    }

    public function setColumnTypeId($column_type_id)
    {
        $this->columnTypeId = $column_type_id;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setDefaultValue($default_value)
    {
        $this->defaultValue = $default_value;
    }

    public function getIsUnique()
    {
        return $this->isUnique;
    }

    public function setIsUnique($is_unique)
    {
        $this->isUnique = $is_unique;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function setDisplay($display)
    {
        $this->display = $display;
    }

    public function getOrder()
    {
        return $this->ordering;
    }

    public function setOrder($order)
    {
        $this->ordering = $order;
    }

    public function details()
    {
        return $this->belongsTo(TableDetail::class);
    }

    public function columnName()
    {
        return $this->belongsTo(ColumnType::class, 'column_type_id', 'id');
    }


}
