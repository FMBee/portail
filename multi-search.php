<?php

	$root = 'portail';
	$file = '{sep}search.yml';
	$sep = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? '\\' : '/' ;
	$dir = substr(__DIR__, 0, strrpos(__DIR__, $root));
	$path = $dir .$root .str_replace('{sep}', $sep, $file);

	$targets = yaml_parse_file($path);
// print_r($targets);

	$seek = $_GET['search'];

	$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
	$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
	$seek = str_replace($a, $b, $seek);
	
	foreach ( $targets as $search ) {

		$params = $search['params'];
		$data = array();
	
		switch ( $search['type'] ) {
			
			case 'mysql':
				
				$connect = $params['connexion'];
				
				$pdo = new PDO(	'mysql:dbname=' .$connect['basename']
								.';host=' .$connect['host'],
								$connect['username'],
								$connect['userpwd']
				);
				
				switch ( $params['search'] ) {
					
					case 'where':
						
						$data = sqlWhere($pdo, $params, $seek);
						break;
					
					case 'match':
						
				}
				break;
				
			case 'mssql':
	
				$connect = $params['connexion'];
				$os = strtoupper(substr(PHP_OS, 0, 3));
				
				switch( $os ) {
		
					case 'WIN':
		
						$pdo = new PDO(	"sqlsrv:Server={$connect['host']},{$connect['port']};Database={$connect['basename']}",
										$connect['username'],
										$connect['userpwd']
						);
						break;
		
					case 'LIN':
		
						$pdo = new PDO(	"dblib:host={$connect['host']}:{$connect['port']};dbname={$connect['basename']}",
										$connect['username'],
										$connect['userpwd']
						);
						break;
				}
				switch ( $params['search'] ) {
					
					case 'where':
						
						$data = sqlWhere($pdo, $params, $seek);
						break;
					
					case 'match':
						
				}
				break;
				
			case 'files':
				
				$query = $params['url']	.str_replace('?', $seek, $params['options']);
// echo $query;				
				$files = json_decode( file_get_contents($query), true );
				$lignes = array();
// print_r($files);				
				foreach ( $files as $file ) {

					$ligne = array();
					
					foreach ( $file as $key => $value ) {
					
						$ligne[ $params['fields'][$key] ] = $value;
					}
					$lignes[] = $ligne;
				}
				$data = $lignes;
				
				break;
		}
// print_r($data);		
		if ( count($data) > 0 ) {
			
			$results[$search['order']] = array(
											'order' => $search['order'],
											'label' => $search['label'],
											'short' => $search['short'],
											'results' => $data
										);
		}
	}
	ksort($results);
// print_r($results);
	
	
	/*
	 * clause WHERE classique
	 */
	function sqlWhere($pdo, $params, $seek) {
		
		$fields = '';
		$where = '';
			
		foreach ( $params['fields'] as $key => $value ) {
		
			$where .= "{$key} LIKE '%{$seek}%' OR ";
			$fields .= "{$key}, ";
		}
		$where = substr($where, 0, -3);
		$fields = substr($fields, 0, -2);
			
		$sql = "
		SELECT {$fields} FROM {$params['view']}
		WHERE {$where}
		";
// echo $sql;
		$query = $pdo->prepare($sql);
		$query->execute();
		
		$results =  $query->fetchAll(PDO::FETCH_ASSOC);
		$data = array();
		
		foreach ( $results as $result ) {	// recherche des labels et decoding
			
			$ligne = array();
			
			foreach ( $result as $key => $value ) {
				
				$ligne[ $params['fields'][$key] ] = utf8_encode($value);
			}
			$data[] = $ligne;
		}
		return $data;
	}

	
