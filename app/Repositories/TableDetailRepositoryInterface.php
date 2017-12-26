<?php

namespace App\Repositories;
interface TableDetailRepositoryInterface
{
    /**
     * Get's a table by it's ID
     *
     * @param int
     */
    public function get($table_id);

    /**
     * Get's all tables.
     *
     * @return mixed
     */
    public function all();

    /**
     * Deletes a table.
     *
     * @param int
     */
    public function delete($table_id);

    /**
     * Updates a table.
     *
     * @param int
     * @param array
     */
    public function update($table_id, array $table_data);

    /**
     * Get's a table by unique identifier
     *
     * @param int
     * @param array
     */
    public function getByIdentifier($table_identifier);

    /**
     * @param $auth
     * @return mixed
     */
    public function getByAuth($auth);

    /**
     * @param $team_id
     * @return mixed
     */
    public function getTeamTables($team_id);
}