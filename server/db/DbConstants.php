<?php

/**
 * Interface DbConstants
 *
 * Defines constants related to the database setup, migrations, and SQL queries.
 */
interface DbConstants
{
    /**
     * @var string The path to the directory containing database setup files.
     */
    const DB_SETUP_PATH = __DIR__ . '/db_setup/';

    /**
     * @var string The path to the directory containing migration files.
     */
    const MIGRATIONS_PATH = __DIR__ . '/migrations/';

    /**
     * @var string The path to the directory containing SQL query files.
     */
    const SQL_PATH = __DIR__ . '/sql/';
}
