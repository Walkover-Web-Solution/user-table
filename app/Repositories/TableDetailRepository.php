<?php

namespace App\Repositories;

use App\Entity\TableDetail;
use Illuminate\Database\Eloquent\Collection;

class TableDetailRepository implements TableDetailRepositoryInterface
{
    /**
     * Get’s a table by it’s ID
     *
     * @param int
     * @return Collection
     */
    public function get($table_id)
    {
        return TableDetail::with('tableStructure.columnName')->find($table_id);
    }

    /**
     * Get’s all tables.
     *
     * @return mixed
     */
    public function all()
    {
        return TableDetail::all();
    }

    /**
     * Deletes a table.
     *
     * @param int
     */
    public function delete($table_id)
    {
        TableDetail::destroy($table_id);
    }

    /**
     * Updates a user.
     *
     * @param int
     * @param array
     */
    public function update($table_id, array $table_data)
    {
        TableDetail::find($table_id)->update($table_data);
    }


    /**
     * Get’s a table by it’s Unique Identifier
     * @param $table_identifier
     * @return collection
     */
    public function getByIdentifier($table_identifier)
    {
        return TableDetail::where('table_id', $table_identifier)->first();
    }

    /**
     * Get’s a table by it’s Auth
     * @param $auth
     * @return collection
     */
    public function getByAuth($auth)
    {
        return TableDetail::where('auth', $auth)->first();
    }

    /**
     * Get's all table of a team
     * @param $team_id
     * @return collection
     */
    public function getTeamTables($team_id)
    {
        return TableDetail::where('team_id', $team_id)->get();
    }

    /**
     * @param $teams
     * @return mixed
     */
    public function getUserTeamTables($teams)
    {
        return TableDetail::with('dataSource')->whereIn('team_id', $teams)->get();
    }
}