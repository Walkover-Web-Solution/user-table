<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Storage;

use App\ImportData;
use App\TableStructure;
use App\team_table_mapping;
use App\Http\Controllers\TableController;

class ImportUserData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($jobsData)
    {
        $this->importData = $jobsData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jobsData = $this->importData;
        
        $fileName = $jobsData['FileName'];
        $tableAuth = $jobsData['TableAuthKey'];
        $userId = $jobsData['UserId'];
        $mapData = $jobsData['MapData'];

        $handle = fopen(public_path($fileName), "r");


        $response = team_table_mapping::getTableByAuth($tableAuth);

        $table_name = $response['table_id'];
        $table_incr_id = $response['id'];
        $table_structure = TableStructure::formatTableStructureData($response['table_structure']);

        $i = 0;
        while ($csvLine = fgetcsv($handle)) {
            $k = 0;

            $insertData = array();
            foreach ($mapData as $value) {
                if ($value != "") {                    
                    $insertData[$value] = $csvLine[$k];
                }
                $k++;
            }
            
            $teamData = team_table_mapping::makeNewEntryInTable($table_name, $insertData, $table_structure);

            $importRequest = new ImportData;

            $importRequest->table_id = $table_incr_id;
            $importRequest->user_id = $userId;
            $importRequest->table_data = json_encode($insertData);
            $importRequest->operation_response = json_encode($teamData);
            $importRequest->save();

            team_table_mapping::makeNewEntryForSource($table_incr_id, 'CSV_IMPORT');
            TableController::insertActivityDataStatic($table_name, $teamData);

            $i++;
        }

    }
}
