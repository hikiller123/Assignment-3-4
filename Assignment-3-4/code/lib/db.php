<?php

$dbconn = pg_pconnect("host=$pg_host port=$pg_port dbname=$pg_dbname user=$pg_dbuser password=$pg_dbpassword") or die("Could not connect");
$dsn = "pgsql:host=$pg_host;dbname=$pg_dbname;user=$pg_dbuser;password=$pg_dbpassword";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,];

if ($debug) {
	echo "host=$pg_host, port=$pg_port, dbname=$pg_dbname, user=$pg_dbuser, password=$pg_dbpassword<br>";
	$stat = pg_connection_status($dbconn);
	if ($stat === PGSQL_CONNECTION_OK) {
		echo 'Connection status ok';
	} else {
		echo 'Connection status bad';
	}    
}

function run_query($dbconn, $query) {
	if ($debug) {
		echo "$query<br>";
	}
	try {
		$result = pg_fetch_array($query);
	} catch (\PDOException $e) {
     		throw new \PDOException($e->getMessage(), (int)$e->getCode());
	}
	if ($result == False and $debug) {
		echo "Query failed<br>";
	}
	return $result;
}

//database functions
function get_article_list($dbconn){
	$query=pg_prepare($dbconn, "1", 'SELECT 
		articles.created_on as date,
		articles.aid as aid,
		articles.title as title,
		authors.username as author,
		articles.stub as stub
		FROM
		articles
		INNER JOIN
		authors ON articles.author=authors.id
		ORDER BY
		date DESC');
	$query = pg_execute($dbconn, "1", array());
return run_query($dbconn, $query);
}

function get_article($dbconn, $aid) {
	$query=pg_prepare($dbconn, "2", 'SELECT 
		articles.created_on as date,
		articles.aid as aid,
		articles.title as title,
		authors.username as author,
		articles.stub as stub,
		articles.content as content
		FROM 
		articles
		INNER JOIN
		authors ON articles.author=authors.id
		WHERE
		aid=$1
		LIMIT 1');
	$query = pg_execute($dbconn, "2", array($aid));
return run_query($dbconn, $query);
}

function delete_article($dbconn, $aid) {
	$query=pg_prepare($dbconn, "3", 'DELETE FROM articles WHERE aid=$1');
	$query = pg_execute($dbconn, "3", array($aid));
	return run_query($dbconn, $query);
}

function add_article($dbconn, $title, $content, $author) {
	$stub = substr($content, 0, 30);
	$aid = str_replace(" ", "-", strtolower($title));
	$query=pg_prepare($dbconn, "4", 'INSERT INTO
		articles
		(aid, title, author, stub, content) 
		VALUES
		($1, $2, $3, $4, $5)');
	$query = pg_execute($dbconn, "4", array($aid, $title, $author, $stub, $content));
	return run_query($dbconn, $query);
}

function update_article($dbconn, $title, $content, $aid) {
	$query=pg_prepare($dbconn, "5", 'UPDATE articles
		SET 
		title=$1,
		content=$2
		WHERE
		aid=$3');
	$query = pg_execute($dbconn, "5", array($title, $content, $aid));
	return run_query($dbconn, $query);
}

function authenticate_user($dbconn, $username, $password) {
	$query=pg_prepare($dbconn, "6", 'SELECT
		authors.id as id,
		authors.username as username,
		authors.password as password,
		authors.role as role
		FROM
		authors
		WHERE
		username=$1
		AND
		password=$2
		LIMIT 1');
	$query = pg_execute($dbconn, "6", array($username, $password));
	return run_query($dbconn, $query);
}?>
