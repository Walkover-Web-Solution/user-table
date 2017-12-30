<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Contact extends Model
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer", unique=true, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;
    protected $table;


    public function __construct($table)
    {
        $this->table = $table;
    }

    public function setId($id)
    {
        return $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getContactById($id){
        return Contact::where('id','=',$id)->first();
    }

}
