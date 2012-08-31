<?php
/**
 * Whether or not you want ADOdb to backtick field names in AutoExecute, GetInsertSQL and GetUpdateSQL.
 */
$ADODB_QUOTE_FIELDNAMES = true;

/**
 * Information required to start a new database connection.
 */
$auth_db = NewADOConnection(DATABASE_TYPE);
$auth_db->Connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, AUTH_DATABASE);
$auth_db->SetFetchMode(ADODB_FETCH_ASSOC);
?>