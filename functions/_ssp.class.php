<?php
/**
 * @ Author: Bill Minozzi
 * @ Copyright: 2020 www.BillMinozzi.com
 * @ Modified time: 2020-03-17 22:05:22
 */
if (!defined('ABSPATH')) 
    exit;
class ANTIBOT_SSP
{
	/**
	 * Create the data output array for the DataTables rows
	 *
	 *  @param  array $columns Column information array
	 *  @param  array $data    Data from the SQL get
	 *  @return array          Formatted data in a row based format
	 */
	static function data_output($columns, $data)
	{
		$out = array();
		for ($i = 0, $ien = count($data); $i < $ien; $i++) {
			$row = array();
			for ($j = 0, $jen = count($columns); $j < $jen; $j++) {
				$column = $columns[$j];
				// Is there a formatter?
				if (isset($column['formatter'])) {
					$row[$column['dt']] = $column['formatter']($data[$i][$column['db']], $data[$i]);
				} else {
					$row[$column['dt']] = $data[$i][$columns[$j]['db']];
				}
			}
			$out[] = $row;
		}
		return $out;
	}
	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL limit clause
	 */
	static function limit($request, $columns)
	{
		$limit = '';
		if (isset($request['start']) && $request['length'] != -1) {
			$limit = "LIMIT " . intval($request['start']) . ", " . intval($request['length']);
		}
		return $limit;
	}
	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL order by clause
	 */
	static function order($request, $columns)
	{
		$order = '';
		if (isset($request['order']) && count($request['order'])) {
			$orderBy = array();
			$dtColumns = self::pluck($columns, 'dt');
			for ($i = 0, $ien = count($request['order']); $i < $ien; $i++) {
				// Convert the column index into the column data property
				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];
				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];
				if ($requestColumn['orderable'] == 'true') {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';
					$orderBy[] = '`' . $column['db'] . '` ' . $dir;
				}
			}
			if (count($orderBy)) {
				$order = 'ORDER BY ' . implode(', ', $orderBy);
			}
		}
		return $order;
	}
	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *    sql_exec() function
	 *  @return string SQL where clause
	 */
	static function filter($request, $columns, &$bindings)
	{
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = self::pluck($columns, 'dt');
		if (isset($request['search']) && $request['search']['value'] != '') {
			$str = $request['search']['value'];
			for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];
				if ($requestColumn['searchable'] == 'true') {
					$globalSearch[] = "`" . $column['db'] . "` LIKE '%" . $str . "%'";
				}
			}
		}
		// Individual column filtering
		if (isset($request['columns'])) {
			for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];
				$str = $requestColumn['search']['value'];
				$str = strip_tags($str);
				if (
					$requestColumn['searchable'] == 'true' &&
					$str != ''
				) {
					$columnSearch[] = "`" . $column['db'] . "` LIKE '%" . $str . "%'";
				}
			}
		}
		// Combine the filters into a single string
		$where = "";
		if (count($globalSearch)) {
			$where = '(' . implode(' OR ', $globalSearch) . ')';
		}
		if (count($columnSearch)) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where . ' AND ' . implode(' AND ', $columnSearch);
		}
		if ($where !== '') {
			$where = 'WHERE ' . $where;
		}
		error_log($where, 0);
		return $where;
	}
	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  string $table SQL table to query
	 *  @param  string $primaryKey Primary key of the table
	 *  @param  array $columns Column information array
	 *  @return array          Server-side processing response array
	 */
	static function simple($request, $table, $primaryKey, $columns)
	{
		$limit = self::limit($request, $columns);
		$order = self::order($request, $columns);
		$where = self::filter($request, $columns, $bindings);
		// Main query to actually get the data
		$data = self::sql_exec(
			"SELECT `" . implode("`, `", self::pluck($columns, 'db')) . "`
			 FROM `$table`
			 $where
			 $order
			 $limit"
		);
		// Data set length after filtering
		$resFilterLength = self::sql_exec(
			"SELECT COUNT(`{$primaryKey}`)
			 FROM   `$table`
			 $where"
		);
		$recordsFiltered = $resFilterLength[0]['COUNT(`date`)'];
		// Total data set length
		$query = "SELECT COUNT(`{$primaryKey}`)
		FROM   `$table`";
		$resTotalLength = self::sql_exec($query);
		$recordsTotal = $resTotalLength[0]['COUNT(`date`)'];
		/*
		 * Output
		 */
		return array(
			"draw"            => isset($request['draw']) ?
				intval($request['draw']) :
				0,
			"recordsTotal"    => intval($recordsTotal),
			"recordsFiltered" => intval($recordsFiltered),
			"data"            => self::data_output($columns, $data)
		);
	}
	/**
	 * Execute an SQL query on the database
	 *
	 */
	static function sql_exec($sql)
	{
		global $wpdb;
		// Execute
		try {
			$results = $wpdb->get_results($sql, ARRAY_A);
		} catch (Exception $e) {
			self::fatal("An SQL error occurred: ");
		}
		if (!$results) {
			error_log($sql, 0);
		} else
			error_log($sql, 0);
		$arr = array();
		foreach ($results as $result) {
			$arr[] = $result;
		}
		return $arr;
	}
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Internal methods
	 */
	/**
	 * Throw a fatal error.
	 *
	 * This writes out an error message in a JSON string which DataTables will
	 * see and show to the user in the browser.
	 *
	 * @param  string $msg Message to send to the client
	 */
	static function fatal($msg)
	{
		echo json_encode(array(
			"error" => $msg
		));
		exit(0);
	}
	/**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	static function pluck($a, $prop)
	{
		$out = array();
		for ($i = 0, $len = count($a); $i < $len; $i++) {
			$out[] = $a[$i][$prop];
		}
		return $out;
	}
	/**
	 * Return a string from an array or a string
	 *
	 * @param  array|string $a Array to join
	 * @param  string $join Glue for the concatenation
	 * @return string Joined string
	 */
	static function _flatten($a, $join = ' AND ')
	{
		if (!$a) {
			return '';
		} else if ($a && is_array($a)) {
			return implode($join, $a);
		}
		return $a;
	}
}