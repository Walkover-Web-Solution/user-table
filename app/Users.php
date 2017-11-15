<?php

namespace App;

use App\Classes\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Users extends Model
{
    use SoftDeletes;

    protected $table = 'user_data';
    protected $fillable = ['username',
        'firstname', 'lastname',
         'email', 'city','country',
        'contact', 'source', 'follow_up_date','utm_source','utm_campaign',
        'reference','salary','comment',
        'deleted_at', 'created_at','updated_at','status', 'purpose',
        'industry','true_client', 'assign_to', 'won_or_lost','id'
    ];
    protected $dates = ['deleted_at'];
    protected $hidden = ['password'];

    public static function getAll(){
        $drive = Users::whereNull('deleted_at')->get();
    }

    public static function updateData($data,$id){
          $drive=Users::where(array('id'=>$id))->update($data);
          return $drive;
    }

    public static function getHomeTabData(){
        $data=Users::paginate(50);
        return $data;
    }

    public static function getSearchedData($tab,$query){
        $tabQuery= Tabs::getTabQuery("All");
        if( $tab != "All") {
            $req =  Tabs::getTabQuery($tab);
            $req = (array)json_decode($req);
            $tabQuery  = Users::getFilteredUsersDetails($tabQuery,$req);
         }
        
        $fields = Utility::$records;

        $users = Users::selectRaw($fields);
       
            $users->where('email','LIKE','%'.$query.'%')
                     ->orWhere('firstname','LIKE','%'.$query.'%')
                    ->orWhere('lastname','LIKE','%'.$query.'%')
                    ->orWhere('city','LIKE','%'.$query.'%')
                    ->orWhere('contact','LIKE','%'.$query.'%')
                    ->orWhere('industry','LIKE','%'.$query.'%');
          return $users->get();
    }

    public static function getFilteredData($req){
        $data = Users::getFilteredUsersDetailsData($req);
        return $data;
    }

    public static function getAppliedFiltersData($req){
        $users = Users::selectRaw(Utility::$records);
        
        foreach(array_keys($req) as $paramName) {

            if (isset($req[$paramName]['is'])) {
                $users->where($paramName,'=',$req[$paramName]['is']);
            }
            else if (isset($req[$paramName]['is_not']))
            {
                $users->where($paramName,'<>',$req[$paramName]['is_not']);
            }

            else if (isset($req[$paramName]['contains'])) {
                $users->where($paramName,'LIKE','%'.$req[$paramName]['contains'].'%');
            }
            else if (isset($req[$paramName]['not_contains'])) {
                $users->where($paramName,'LIKE','%'.$req[$paramName]['not_contains'].'%');
            }
            else if (isset($req[$paramName]['greater_than'])) {
                $users->where($paramName,'>',$req[$paramName]['greater_than']);
            }
            else if (isset($req[$paramName]['less_than'])) {
                $users->where($paramName,'<',$req[$paramName]['less_than']);
            }
            else if (isset($req[$paramName]['equals_to'])) {
                $users->where($paramName,'=',$req[$paramName]['equals_to']);
            }
            else if (isset($req[$paramName]['equals_to'])) {
                $users->where($paramName,'=',$req[$paramName]['equals_to']);
            }
            else if (isset($req[$paramName]['from'])) {
                $users->where($paramName,'>=',$req[$paramName]['from']);
            }
            if (isset($req[$paramName]['to'])) {
                $users->where($paramName,'<=',$req[$paramName]['to']);
            }
        }
        $data =  $users->get();

        return $data;
    }


    public static function getFilteredUsersDetailsData($req){
        
        $assignedTo = Utility::getAssignedTo();
        $loginedInusername = session('assign_to');
        $fields = Utility::$records;

        $users = Users::selectRaw($fields);
        foreach(array_keys($req) as $paramName) {
            
            if (isset($req[$paramName]->is)) {
                $users->where($paramName,'=',$req[$paramName]->is);
            }
            else if (isset($req[$paramName]->is_not))
            {
                $users->where($paramName,'<>',$req[$paramName]->is_not);
            }

            else if (isset($req[$paramName]->contains)) {
                //dd($req[$paramName]->contains);
                $users->where($paramName,'LIKE','%'.$req[$paramName]->contains.'%');
            }
            else if (isset($req[$paramName]->not_contains)) {
                $users->where($paramName,'LIKE','%'.$req[$paramName]->not_contains.'%');
            }
            else if (isset($req[$paramName]->greater_than)) {
                $users->where($paramName,'>',$req[$paramName]->greater_than);
            }
            else if (isset($req[$paramName]->less_than)) {
                $users->where($paramName,'<',$req[$paramName]->less_than);
            }
            else if (isset($req[$paramName]->equals_to)) {
                $users->where($paramName,'=',$req[$paramName]->equals_to);
            }
            else if (isset($req[$paramName]->equals_to)) {
                $users->where($paramName,'=',$req[$paramName]->equals_to);
            }
            else if (isset($req[$paramName]->from)) {
                $users->where($paramName,'>=',$req[$paramName]->from);
            }
            if (isset($req[$paramName]->to)) {
                $users->where($paramName,'<=',$req[$paramName]->to);
            }

        }
        $data =  $users->where(['assign_to'=>$loginedInusername])
            ->orderBy('created_at', 'desc')->get();

        return $data;

    }
    public static function getFilteredUsersDetails($tabQuery,$req){
        $addWhere = 0; // to append where in query only once
        $addAnd = 0; // to append And in query
        // $conditionArr =  array();
        foreach(array_keys($req) as $paramName) {

            if($addWhere==0)
            {
                $tabQuery .= ' Where ';
                $addWhere++;
            }
            if (isset($req[$paramName]->is)) {
                //   $data->where([$paramName => $req[$paramName]->is]);
                //  array_push($conditionArr,[$paramName => ['is' => $req[$paramName]->is] ]);
                if(!$addAnd) {
                    $tabQuery .= " ".$paramName . " = '" . $req[$paramName]->is . "' ";
                    $addAnd++;
                }else
                    $tabQuery .= " And ".$paramName ." = '"  .$req[$paramName]->is."' ";
            }
            else if (isset($req[$paramName]->is_not))
            {
                //  $data->where($paramName, '<>' ,$req[$paramName]->is_not);
                //  array_push($conditionArr,[$paramName => ['is_not' => $req[$paramName]->is_not] ]);
                if(!$addAnd) {
                    $tabQuery .= " ".$paramName . " <> '" . $req[$paramName]->is_not . "' ";
                    $addAnd++;
                }else
                    $tabQuery .= " And ".$paramName ." <> '"  .$req[$paramName]->is_not."' ";
            }

            else if (isset($req[$paramName]->contains)) {
                //   $data->whereRaw(" {$paramName} LIKE '%{$req[$paramName]->contains}%'");
                //  array_push($conditionArr,[$paramName => ['contains' => $req[$paramName]->contains] ]);
                if(!$addAnd) {
                    $tabQuery .= " ".$paramName . " LIKE '%" . $req[$paramName]->contains . "%' ";
                    $addAnd++;
                }else
                    $tabQuery .= " And ".$paramName . " LIKE '%" . $req[$paramName]->contains . "%' ";
            }
            else if (isset($req[$paramName]->not_contains)) {
                //   $data->whereRaw(" {$paramName} NOT LIKE '%{$req[$paramName]->not_contains}%'");
                //   array_push($conditionArr,[$paramName => ['not_contains' => $req[$paramName]->not_contains] ]);
                if(!$addAnd) {
                    $tabQuery .= " ".$paramName . " NOT LIKE '%" . $req[$paramName]->not_contains . "%' ";
                    $addAnd++;
                }else
                    $tabQuery .= " And ".$paramName . " NOT LIKE '%" . $req[$paramName]->not_contains . "%' ";
            }
            else if (isset($req[$paramName]->greater_than)) {
                //   $data->where($paramName, '>', $req[$paramName]->greater_than);
                //  array_push($conditionArr,[$paramName => ['greater_than' => $req[$paramName]->greater_than] ]);
                if(!$addAnd) {
                    $tabQuery .= " ".$paramName . " > " . $req[$paramName]->greater_than . " ";
                    $addAnd++;
                }else
                    $tabQuery .= " And ".$paramName ." > "  .$req[$paramName]->greater_than." ";
            }
            else if (isset($req[$paramName]->less_than)) {
                //   $data->where($paramName, '<', $req[$paramName]->less_than);
                //  array_push($conditionArr,[$paramName => ['less_than' => $req[$paramName]->less_than] ]);
                if(!$addAnd) {
                    $tabQuery .= " ".$paramName . " < " . $req[$paramName]->less_than . " ";
                    $addAnd++;
                }else
                    $tabQuery .= " And ".$paramName ." < "  .$req[$paramName]->less_than." ";
            }
            else if (isset($req[$paramName]->equals_to)) {
                //   $data->where($paramName, '=', $req[$paramName]->equals_to);
                // array_push($conditionArr,[$paramName => ['equals_to' => $req[$paramName]->equals_to] ]);
                if(!$addAnd) {
                    $tabQuery .= " ".$paramName . " = " . $req[$paramName]->equals_to . " ";
                    $addAnd++;
                }else
                    $tabQuery .= " And ".$paramName ." = "  .$req[$paramName]->equals_to." ";
            }
            else if (isset($req[$paramName]->equals_to)) {
                //   $data->where($paramName, '=', $req[$paramName]->equals_to);
                // array_push($conditionArr,[$paramName => ['equals_to' => $req[$paramName]->equals_to] ]);
                if(!$addAnd) {
                    $tabQuery .= " ".$paramName . " = " . $req[$paramName]->equals_to . " ";
                    $addAnd++;
                }else
                    $tabQuery .= " And ".$paramName ." = "  .$req[$paramName]->equals_to." ";
            }
            else if (isset($req[$paramName]->from)) {
                //   $data->where($paramName, '=', $req[$paramName]->equals_to);
                // array_push($conditionArr,[$paramName => ['equals_to' => $req[$paramName]->equals_to] ]);
                if(!$addAnd) {
                    $tabQuery .= " ".$paramName . " >= '" . $req[$paramName]->from . "' ";
                    $addAnd++;
                }else
                    $tabQuery .= " And ".$paramName ." >= '"  .$req[$paramName]->from."' ";
            }
             if (isset($req[$paramName]->to)) {
                //   $data->where($paramName, '=', $req[$paramName]->equals_to);
                // array_push($conditionArr,[$paramName => ['equals_to' => $req[$paramName]->equals_to] ]);
                if(!$addAnd) {
                    $tabQuery .= " ".$paramName . " <= '" . $req[$paramName]->to . "' ";
                    $addAnd++;
                }else
                    $tabQuery .= " And ".$paramName ." <= '"  .$req[$paramName]->to."' ";
            }
        }

        return $tabQuery;
    }

    public static function getFiltrableData($tab)
    {
        $forStr = array('is' => null,
                        'is_not' =>null,
                        'contains'=>null,
                        'not_contains' =>null
                        );
        $forInt = array( 'less_than'=> null,
                         'greater_than'=> null,
                         'equals_to'=> null);
        $forDate = array('from' =>null,'to' => null);
        $data = array();

        $table_info_columns =  \DB::select("SHOW COLUMNS FROM user_data WHERE FIELD NOT IN ('id','password','deleted_at')");
        $tabQuery  = (array) json_decode(Tabs::getTabQuery($tab));

        foreach ($table_info_columns as $column) {
            $col_name = $column->Field;
            $col_type = $column->Type;

            if(isset($tabQuery[$col_name]))
            {
                //for string fields
               if(isset($tabQuery[$col_name]->is))
                   $data[$col_name]['is'] = $tabQuery[$col_name]->is;

               else  if(isset($tabQuery[$col_name]->is_not))
                   $data[$col_name]['is_not'] = $tabQuery[$col_name]->is_not;

               else  if(isset($tabQuery[$col_name]->contains))
                   $data[$col_name]['contains'] = $tabQuery[$col_name]->contains;

               else  if(isset($tabQuery[$col_name]->not_contains))
                   $data[$col_name]['not_contains'] = $tabQuery[$col_name]->not_contains;

               //for int fields
               else  if(isset($tabQuery[$col_name]->less_than))
                   $data[$col_name]['less_than'] = $tabQuery[$col_name]->less_than;

               else  if(isset($tabQuery[$col_name]->greater_than))
                   $data[$col_name]['greater_than'] = $tabQuery[$col_name]->greater_than;

               else  if(isset($tabQuery[$col_name]->equals_to))
                   $data[$col_name]['equals_to'] = $tabQuery[$col_name]->equals_to;

               // for dates
               else  if(isset($tabQuery[$col_name]->from))
                   $data[$col_name]['from'] = $tabQuery[$col_name]->from;

               else  if(isset($tabQuery[$col_name]->to))
                   $data[$col_name]['to'] = $tabQuery[$col_name]->to;
            }
            else{
                if (strpos($col_type, 'varchar') !== false) {
                    $data[$col_name] =  $forStr;
                }
                else if (strpos($col_type, 'int') !== false) {
                    $data[$col_name] =  $forInt;
                }
                else if(strpos($col_type, 'timestamp') !== false)
                   $data[$col_name] =  $forDate;
            }
        }
        return $data;
    }

    # for internal use in crone job
    public static function getUserDetails($username){
       $data =  \DB::table('user_data')
                ->selectRaw(Utility::$records)->whereUsername($username)->first();
        return $data;
    }

    # get column type
    public static function getColumnDetails(){
        $table_info_columns =  \DB::select("SHOW COLUMNS FROM user_data WHERE FIELD NOT IN ('id','firstname','lastname','password','deleted_at')");
        // list of col on which drop down options are possible
        $possibleDropDownArr= ['status','purpose','industry','true_client','assign_to','won_or_lost'];

        $details = array(
            'name' => ['type'=> 'varchar(20)']
        );
        foreach ($table_info_columns as $column) {
            $col_name = $column->Field;
            $col_type = $column->Type;

            if (in_array($col_name, $possibleDropDownArr)) {
                // get available options for col
                $options = Utility::getOptions($col_name);
                $details[$col_name] = ['type'=>'enum','options'=>$options];
            }
            else
                $details[$col_name] = ['type' => $col_type];

        }
        return $details;
    }

    # get all possible values of enum
    public static function getPossbileStatuses($table,$column){
        $type = \DB::select(\DB::raw("SHOW COLUMNS FROM $table WHERE Field = '{$column}'"))[0]->Type ;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = array();
        foreach( explode(',', $matches[1]) as $value )
        {
            $v = trim( $value, "'" );
            array_push($enum, $v);
        }
        return $enum;
    }

    // login check
    public static function checkUserLogin($userData){
        $data =  \DB::table('user_data')
                  ->select('username')
                  ->where($userData)
                   ->first();

        return $data;
    }

}
