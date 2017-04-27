<?php

if(!defined('APP_ENTRY_POINT') || !APP_ENTRY_POINT || APP_ENTRY_POINT === false ){
	throw new TimeException('Entry point not defined.' , 403);
}

class HtmlFormValidator{

	public static $_criteria;
	public static $_fields;

	// $_ERRORS global var.
	private static $_ERRORS;


	public function __construct(){
	}

	/*
	*@ method to set field and value.
	*
	*/

	public function setField( $fieldname, $fieldvalue ){
		self::$_fields[$fieldname] = $fieldvalue;
	}

	public function setHtmlFields( $fields ){
		if(!is_array($fields) || empty($fields)){
			return false;
		}
		foreach ($fields as $fieldname => $fieldvalue) {
			self::setField($fieldname,$fieldvalue);
		}
		
	}

	public function setrule( $criteriaName, $criteriaValue ){
		self::$_criteria[$criteriaName] = $criteriaValue;
	}	

	private function getAllCriteria(){
		return self::$_criteria;
	}

	private function getAllFields(){
		return self::$_fields;
	}

	private function _process_criteria(){
		if(is_null(self::$_criteria) || empty(self::$_criteria)){
			die('bad criteria.');
		}

	}
	
	private function _process_criteria_regex( array $params ){
		if($params['enabled'] === true){
			foreach ($params['fields'] as $key => $value) {
				// preg_match() devuelve 1 si pattern coincide con el subject dado, 0 si no, o FALSE si ocurrió un error.
				// si no coincide retornar false

 				if(preg_match($params['regex'], self::$_fields[$value]) == false && !empty(self::$_fields[$value])){
					$errors[$value] = htmlentities(self::$_fields[$value]);
				}
			}
			if(!empty($errors)){
				return self::$_ERRORS['HTMLregex'] = $errors;
			}

		}
	}

	private function __process_criteria_twitter_username( array $params ){
		if($params['enabled'] === true){
			foreach ($params['fields'] as $key => $value) {
				if(preg_match('/@([A-Za-z0-9_]{1,15})/', self::$_fields[$value]) == false){
					$errors[$key] = $value;
				}
			}
			if(!empty($errors)){
				return self::$_ERRORS['HTMLvalid_twitter'] = $errors;
			}

		}
	}

	private function _process_criteria_valid_mail( array $params ){
		if($params['enabled'] === true){
			foreach ($params['fields'] as $key => $value) {
				if(filter_var(self::$_fields[$value], FILTER_VALIDATE_EMAIL) === false){
					$errors[$value] = self::$_fields[$value];
				}
			}
			if(!empty($errors)){
				return self::$_ERRORS['HTMLinvalid_email'] = $errors;
			}
		}
	}

	private function _process_criteria_match( array $params ){
		if($params['enabled'] === true){
			$param1 = self::$_fields[$params['fields'][0]];
			$param2 = self::$_fields[$params['fields'][1]];

			if($param1 != $param2){
				$errors['match'] = array(true);
			}
			if(!empty($errors)){
				return self::$_ERRORS['HTMLmatch'] = $errors;
			}
		}
	}

	private function _process_criteria_empty( array $params ){
		if($params['enabled'] === true){
			if($params['fields'] == '*' || $params['fields'][0] == '*'){
				$fields = self::getAllFields();
				foreach ($fields as $key => $value) {
					if(empty($value) || is_null($value) || $value === null){
						$errors[$key] = $value;
					}
				}
			}else{
				foreach ($params['fields'] as $field) {
					if(empty(self::$_fields[$field]) || is_null(self::$_fields[$field]) || self::$_fields[$field] === null){
						$errors[$field] = self::$_fields[$field];
					}
				}
			}

			if(!empty($errors)){
				return self::$_ERRORS['HTMLempty'] = $errors;
			}
		}
	}

	private function _process_criteria_minlenght ( array $params ) {
		if($params['enabled'] === true){
			if($params['fields'] == '*' || $params['fields'][0] == '*'){
				$fields = self::getAllFields();
				foreach ($fields as $key => $value) {
					if(strlen($value) < $params['value']){
						$errors[$key] = array('strMinLenght' => $params['value']);
					} 
				}
			}else{
				foreach ($params['fields'] as $field => $value) {

					if(strlen(self::$_fields[$value]) < $params['value']){
						$errors[$value] = array('strMinLenght' => $params['value']);
					} 
				}
			}
			if(!empty($errors)){
				return self::$_ERRORS['HTMLminlength'] = $errors;
			}
		}
	}

	private function _process_criteria_maxlenght ( array $params ) {
		if($params['enabled'] === true){
			if($params['fields'] == '*' || $params['fields'][0] == '*'){
				$fields = self::getAllFields();
				foreach ($fields as $key => $value) {
					if(strlen($value) > $params['value']){
						$errors[$key] = array('strMaxLenght' => $params['value']);
					} 
				}

			}else{
				foreach ($params['fields'] as $field => $value) {

					if(strlen(self::$_fields[$value]) > $params['value']){
						$errors[$value] = array('strMaxLenght' => $params['value']);
					} 
				}

			}
			if(!empty($errors)){
				return self::$_ERRORS['HTMLmaxlength'] = $errors;
			}
		}
	}

	public function formatToJson( array $html ){
		$filtersList = array_keys( $html );
		$longitud = count($filtersList);

		$htmlOutput = '';
		for($i = 0; $i < $longitud ; $i++) {
			switch ($filtersList[$i]) {
				case 'HTMLregex':
				$format = '" %s " no parece un valor válido.';
				
				foreach ($html['HTMLregex'] as $key => $value) {
					$errors[] = sprintf($format,$value,$key);
				}

				break;

				case 'HTMLempty':
				$format = 'Oops! el campo " %s " parece estar vacio.';
				foreach ($html['HTMLempty'] as $key => $value) {
					$errors[] = sprintf($format,$key,$value);
				}
				break;

				case 'HTMLminlength':
				$format = 'El %s debe tener como mínimo %s carácteres ';
				foreach ($html['HTMLminlength'] as $key => $value) {
					if(is_array($value) && !empty($value)){
						foreach ($value as $k => $v) {
							$errors[] = sprintf($format,$key,$v);
						}
					}else{
						$errors[] = sprintf($format,$key,$value);
					}
				}
				break;

				case 'HTMLmaxlength':
				$format = 'El %s debe tener como máximo %s carácteres ';
				foreach ($html['HTMLmaxlength'] as $key => $value) {
					if(is_array($value) && !empty($value)){
						foreach ($value as $k => $v) {
							$errors[] = sprintf($format,$key,$v);
						}
					}else{
						$errors[] = sprintf($format,$key,$value);
					}
				}
				break;
				
				case 'HTMLinvalid_email':
				$format = 'La dirección %s no es válida.';
				foreach ($html['HTMLinvalid_email'] as $key => $value) {
					if(is_array($value) && !empty($value)){
						foreach ($value as $k => $v) {
							$errors[] = sprintf($format,$key,$k);
						}
					}else{
						$errors[] = sprintf($format,$value,$key);
					}
				}
				break;

				case 'HTMLmatch':
				$format = 'Los campos no coinciden.';
				$errors[] = $format;
				break;

				default:
				if(empty($errors) || is_null($errors))
					$errors[] = '';
					
				break;
			}
		}
		#echo sprintf($htmlOutput, $html['HTMLregex']['username'], 'nombre de usuario');
		return json_encode(array( 'htmlErrors' => $errors), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT );
	}
	public function formatToHtml( array $html ){
		$filtersList = array_keys( $html );
		$longitud = count($filtersList);

		$htmlOutput = '';
		for($i = 0; $i < $longitud ; $i++) {
			switch ($filtersList[$i]) {
				case 'HTMLregex':
				if(empty($out) || is_null($out))
					$out = '';
				$format = '" %s " no parece un valor válido.';
				$out .= $this->translateToSprintf( $html['HTMLregex'], $format,true );

				break;

				case 'HTMLempty':
				if(empty($out) || is_null($out))
					$out = '';
				$format = 'Oops! el campo " %s " parece estar vacio.';
				$out .= $this->translateToSprintf( $html['HTMLempty'], $format );
				break;

				case 'HTMLminlength':
				if(empty($out) || is_null($out))
					$out = '';
				$format = 'El %s debe tener como mínimo %s carácteres ';
				$out .= $this->translateToSprintf( $html['HTMLminlength'], $format );
				break;

				case 'HTMLmaxlength':
				if(empty($out) || is_null($out))
					$out = '';
				$format = 'El %s debe tener como máximo %s carácteres ';
				$out .= $this->translateToSprintf( $html['HTMLmaxlength'], $format );
				break;
				
				case 'HTMLinvalid_email':
				if(empty($out) || is_null($out))
					$out = '';
				$format = 'La dirección %s no es válida.';
				$out .= $this->translateToSprintf( $html['HTMLinvalid_email'], $format,true );
				break;

				case 'HTMLmatch':
				if(empty($out) || is_null($out))
					$out = '';
				$format = 'Los campos no coinciden.';
				$out .= $this->translateToSprintf( $html['HTMLmatch'], $format,true );
				break;

				default:
				if(empty($out) || is_null($out))
					$out = '';
				$out = '';				
				break;
			}
		}
		#echo sprintf($htmlOutput, $html['HTMLregex']['username'], 'nombre de usuario');
		return $out;
	}
	private function translateToSprintf( $array,$format,$reverse = false ){
		if(empty($schema) || is_null($schema))
			$schema = '';
		foreach ($array as $fieldname => $fieldvalue) {
			if( $reverse === true){
				if(is_array($fieldvalue) && !empty($fieldvalue)){
					foreach ($fieldvalue as $k => $v) {
						$schema .= sprintf($format,$fieldname,$v);
					}
				}else{
				$schema .= sprintf($format,$fieldvalue,$fieldname);	
				}
			}else{
				if(is_array($fieldvalue) && !empty($fieldvalue)){
					foreach ($fieldvalue as $k => $v) {
						$schema .= sprintf($format,$fieldname,$v);
					}
					
				}else{
				$schema .= sprintf($format,$fieldname,$fieldvalue);
				}
			}
		}
		return $schema;
	}

	public function process( $returnType = 'array', Closure $callback ){
		$_PROCESS = array(
			'HTMLempty' => '_process_criteria_empty',
			'HTMLminlength' => '_process_criteria_minlenght',
			'HTMLmaxlength' => '_process_criteria_maxlenght',
			'HTMLinvalid_email' => '_process_criteria_valid_mail',
			'HTMLmatch' => '_process_criteria_match',
			'HTMLregex' => '_process_criteria_regex',
			'HTMLvalid_twitter' => '__process_criteria_twitter_username',
			);
		foreach (self::$_criteria as $filter => $params) {
			if(@method_exists($this, $_PROCESS[$filter])){
				call_user_func(array($this,$_PROCESS[$filter]),$params);
			}else{
				continue;
			}	
		}
		if($returnType == 'array'){
			return call_user_func_array($callback,array(self::$_ERRORS));	
		}else{
			return call_user_func_array($callback,array(json_encode(self::$_ERRORS)));
		}

		
	}

}

?>