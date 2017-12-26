<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Activity extends Model
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

    public function getActivityById($id){
        return Activity::where('id','=',$id)->first();
    }

    /**
     * Get the user that the activity belongs to.
     *
     * @return object
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function addActivity($data){
        return Activity::insert($data);
    }

}
