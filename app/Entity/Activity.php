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

    public function addActivity($data){
        return Activity::insert($data);
    }

    /**
     * Get’s a table by it’s Auth
     * @param $content_id
     * @return collection
     */
    public function getByContentId($content_id)
    {
        return Activity::where('content_id', $content_id)->get();
    }

    public function getDescription($act){
        $desc = '';
        if(strtolower($act->action) == 'create')
            $desc .= $act->user_id.' '.$act->action.' '.$act->content_type;
        else
            $desc .= $act->user_id.' '.$act->action.' '.$act->content_type.' from '.$act->old_data.' to '.$act->details;
        return $desc;
    }

}
