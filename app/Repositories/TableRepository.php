<?php
    namespace App\Repositories;

    use App\Tables;
    use App\team_table_mapping;

    class TableRepository
    {
        public function getUserAllTables()
        {
            $teams = session()->get('team_array');
            $teamIdArr = array();
            $teamNameArr = array();
    
            // $user =  \Auth::user();
            $email = 'harshwardhan@msg91.com';
            //print_r($email);
            $readOnlytableLst = team_table_mapping::getUserTablesNameByEmail($email);
    
    
    
            foreach ($teams as $teamId => $teamName) {
                $teamNameArr[] = $teamName;
                $teamIdArr[] = $teamId;
            }
            session()->put('teamNames', $teamNameArr);
            session()->put('teams', $teams);
    
            $tableLst = $this->getUserTablesByTeamId($teamIdArr);
            $teamTables = $table_incr_id_arr = array();
            foreach ($tableLst as $key => $value) {
                $teamTables[$value['team_id']][] = $value;
                $table_incr_id_arr[] = $value['id'];
            }
            $tableCounts = array();
            foreach($teamTables as $teamTable)
            {
                if(is_array($teamTable))
                {
                    foreach($teamTable as $data)
                    {
                        $tableCounts[$data['table_id']] = \DB::table($data['table_id'])->count();
                    }
                }
                
            }
            echo '</pre>';
            $data = json_decode(json_encode(team_table_mapping::getTableSourcesByTableIncrId($table_incr_id_arr)), true);
    
            $source_arr = array();
            foreach ($data as $key => $value) {
                $source_arr[$value['table_incr_id']][] = $value['source'];
            }
    
            return array(
                'teamsArr' => $teams,
                'source_arr' => $source_arr,
                'teamTables' => $teamTables,
                'readOnlyTables'=> $readOnlytableLst,
                'tableCounts'=>$tableCounts
            );
        }

        function getUserTablesByTeamId($teamIdArr)
        {
            $tableLst = team_table_mapping::getUserTablesByTeam($teamIdArr);
            $tableLstJson = json_decode(json_encode($tableLst), true);
            return $tableLstJson;
        }
    }
    
?>
