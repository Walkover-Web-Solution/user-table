<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        return Activity::where('content_id', $content_id)->orderBy('id','desc')->paginate(10);
    }

    public function getDescription($act){
        $desc = '';
        if(strtolower($act->action) == 'create')
            $desc .= $act->action.' '.$act->content_type;
        else {
            $desc .= 'Updated';//' '.$act->content_type.' from '.$act->old_data.' to '.$act->details;
            $oldData = json_decode($act->old_data, true);
            unset($oldData['is_deleted']);
            $newData = json_decode($act->details, true);

            foreach ($oldData as $column => $value) {
                if(is_array($newData[$column]))
                    $newData[$column] = json_encode($newData[$column]);
                if(is_array($value))
                    $value = json_encode($value);
                if(empty($value))
                    $value='NULL';

                $desc .= ' <span class="column-name">' . $column . '</span><span class="new-val"> ' . $newData[$column] . '</span> from <span class="old-val"> ' . $value . '</span>, ';
            }

            $desc = rtrim($desc, ',');
        }

        return $desc;
    }

}
