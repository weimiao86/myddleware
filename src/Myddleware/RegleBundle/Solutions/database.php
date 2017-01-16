<?php
/*********************************************************************************
 * This file is part of Myddleware.

 * @package Myddleware
 * @copyright Copyright (C) 2013 - 2015  Stéphane Faure - CRMconsult EURL
 * @copyright Copyright (C) 2015 - 2016  Stéphane Faure - Myddleware ltd - contact@myddleware.com
 * @link http://www.myddleware.com	
 
 This file is part of Myddleware.
 
 Myddleware is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Myddleware is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Myddleware.  If not, see <http://www.gnu.org/licenses/>.
*********************************************************************************/

namespace Myddleware\RegleBundle\Solutions;

use Symfony\Bridge\Monolog\Logger;
use Myddleware\RegleBundle\Classes\rule as ruleMyddleware; // SugarCRM Myddleware

class databasecore extends solution { 
	Protected $baseUrl;
	Protected $messages = array();
	Protected $duplicateDoc = array();
	
	protected $required_fields =  array('default' => array('id','date_modified'));

	protected $driver;
	protected $pdo;
	
	protected $stringSeparator = '`';

	public function login($paramConnexion) {
		parent::login($paramConnexion);
		try {
			try {
			    $this->pdo = new \PDO($this->driver.':host='.$this->paramConnexion['host'].';port='.$this->paramConnexion['port'].';dbname='.$this->paramConnexion['database_name'], $this->paramConnexion['login'], $this->paramConnexion['password']);
			    $this->connexion_valide = true;	
			} catch (\PDOException $e) {
				$error = 'Failed to login to Database : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
				echo $error . ';';
				$this->logger->error($error);
				return array('error' => $error);
			}
		} catch (\Exception $e) {
			$error = 'Failed to login to Database : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
			echo $error . ';';
			$this->logger->error($error);
			return array('error' => $error);
		}
	} // login($paramConnexion)
 	
	
	public function getFieldsLogin() {	
		return array(
					 array(
                            'name' => 'login',
                            'type' => 'text',
                            'label' => 'solution.fields.login'
                        ),
					array(
                            'name' => 'password',
                            'type' => 'password',
                            'label' => 'solution.fields.password'
                        ),
					array(
                            'name' => 'host',
                            'type' => 'text',
                            'label' => 'solution.fields.host'
                        ),
					array(
                            'name' => 'database_name',
                            'type' => 'text',
                            'label' => 'solution.fields.dbname'
                        ),
					array(
                            'name' => 'port',
                            'type' => 'text',
                            'label' => 'solution.fields.dbport'
                        )
		);
	}
	
	// Renvoie les modules passés en paramètre
	public function get_modules($type = 'source') {		
		try{
			if($type == 'source') {
				$modules = array();
				
				// Appel de la requête
				$q = $this->pdo->prepare($this->get_query_show_tables());
				$exec = $q->execute();
				
				if(!$exec) {
					$errorInfo = $this->pdo->errorInfo();
					throw new \Exception('Show Tables: '.$errorInfo[2]);
				}
				$fetchAll = $q->fetchAll();
			
				foreach ($fetchAll as $table) {
					if(isset($table[0]))
						$modules[$table[0]] = $table[0];
				}
				
				return $modules;
			} else {
				// ajout du module de base
				$modules = array('NewTable' => 'New Table');
				return $modules;
			}
<<<<<<< HEAD
			/*
			// Récupération de toutes les règles avec l'id table en cours qui sont root et qui ont au moins une référence
			$sql = "SELECT DISTINCT
						Rule.id,
						Rule.name,
						Rule.name_slug
					FROM Rule
						INNER JOIN RuleField
							ON Rule.id = RuleField.rule_id
						INNER JOIN RuleParam
							ON Rule.id = RuleParam.rule_id
					WHERE
							Rule.deleted = 0
						AND Rule.conn_id_target = :idConnector
						AND RuleField.target_field_name LIKE '%_Reference'
						AND RuleParam.value = 'root'
						AND RuleParam.name = 'group'";
			$stmt = $this->conn->prepare($sql);
			$stmt->bindValue(":idConnector", $this->paramConnexion['idConnector']);
			$stmt->execute();
			$rules = $stmt->fetchAll();
			if (!empty($rules)) {
				foreach ($rules as $rule) {
					$modules[$rule['name']] = $rule['name'];
				}
			}
			
			return $modules;
			*/	
=======
>>>>>>> refs/remotes/origin/hotfix
		} catch (\Exception $e) {
			$error = 'Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
			return $error;			
		}
	} 	
	
	// Renvoie les champs du module passé en paramètre
	public function get_module_fields($module, $type = 'source') {
		parent::get_module_fields($module, $type);
		try{
			if($type == 'source') {
				// Création de l'objet PDO (DESCRIBE + ALTER TABLE)
				$this->pdo->beginTransaction();

				// Récupération des champs de la table actuelle			
				$q = $this->pdo->prepare($this->get_query_describe_table($module));
				$exec = $q->execute();
				
				if(!$exec) {
					$errorInfo = $this->pdo->errorInfo();
					throw new \Exception('CheckTable: (Describe) '.$errorInfo[2]);
				}
				
				$fetchAll = $q->fetchAll();

				// Parcours des champs de la table sélectionnée
				foreach ($fetchAll as $field) {
					$this->moduleFields[$field[$this->fieldName]] = array(
							'label' => $field[$this->fieldLabel],
							'type' => $field[$this->fieldType],
							'type_bdd' => 'varchar(255)',
							'required' => false
					);
					$this->fieldsRelate[$field[$this->fieldName]] = array(
							'label' => $field[$this->fieldLabel],
							'type' => $field[$this->fieldType],
							'type_bdd' => 'varchar(255)',
							'required' => false,
							'required_relationship' => 0
					);
				}
				return $this->moduleFields;
			} else {
				$this->moduleFields = array();
				return $this->moduleFields;
			}
		}
		catch (\Exception $e){
			$error = 'Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
			return false;
		}
	} // get_module_fields($module) 
	
	
	// Redéfinition de la méthode pour ne plus renvoyer la relation Myddleware_element_id
	public function get_module_fields_relate($module) {
		if(isset($module)) {
			$this->addRequiredRelationship($module);
		}
		// Récupération de tous les champ référence de la règle liées (= module)	
		$this->fieldsRelate = array();
		$sql = "SELECT 	
					RuleField.target_field_name,
					Rule.name
				FROM Rule
					INNER JOIN RuleField
						ON Rule.id = RuleField.rule_id
					WHERE
							Rule.name = :name
						AND Rule.deleted = 0	
						AND RuleField.target_field_name LIKE '%_Reference'";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(":name", $module);
		$stmt->execute();
		$ruleFields = $stmt->fetchAll();
		if (!empty($ruleFields)) {
			foreach ($ruleFields as $ruleField) {
				$this->fieldsRelate[$ruleField['target_field_name']] = array(
																'label' => $ruleField['target_field_name'].' ('.$ruleField['name'].')',
																'type' => 'varchar(255)',
																'type_bdd' => 'varchar(255)',
																'required' => 0,
																'required_relationship' => 0
															);
			}
		}
		
		return $this->fieldsRelate;
	}
	
	// Permet de récupérer le dernier enregistrement de la solution (utilisé pour tester le flux)
	public function read_last($param) {
		$result = array();
		$result['error'] = '';
		try {
			$query = '';
			// Si le tableau de requête est présent alors construction de la requête
			if (!empty($param['query'])) {
				foreach ($param['query'] as $key => $value) {
					if (!empty($query)) {
						$query .= ' AND ';
					} else {
						$query .= ' WHERE ';
					}
					$query .= $key." = '".$value."'";
				}
			}
			
			// ************************************************************************** TARGET
			if($param['module'] == 'NewTable') {
				// Si on a pas de table Database alors on renvoie une erreur
				if (empty($param['ruleParams']['tableID'])) {
					throw new \Exception("No table in Database for the Rule. ");
				}
				$tableID = $param['ruleParams']['tableID'];
				$tableID = mb_strtolower($tableID); // On s'assure que le nom de la règle est bien le slug en miniscule
				
				if(!isset($param['fields'])) {
					$param['fields'] = array();
				}
				$param['fields'] = array_unique($param['fields']);
				// !important $param['fields'] = $this->addRequiredField($param['fields']);
				$param['fields'] = array_values($param['fields']);
				$param['fields'] = $this->cleanMyddlewareElementId($param['fields']);
				
				// Construction de la requête SQL
				$requestSQL = $this->get_query_select_header_read_last();
				
				// Tableau des noms de champs cible de la forme 'targetField' => 'myddlewareField_TYPE'
				$targetFields = array();
				
				foreach ($param['fields'] as $field){
					// On enlève le type pour avoir le nom du champ Source
					$tab = explode('_',$field, -1);
					$fieldName = '';
					foreach ($tab as $morceau) {
						$fieldName .= $morceau.'_';
					}
					$fieldName = substr($fieldName, 0, -1);
					$targetFields[$fieldName] = $field;
					// $field = str_replace(" ", "", $field); // Solution si JQuery ajoute un espace, à n'utiliser qu'en dernier recours car fonctionne pas bien
				    $requestSQL .= $fieldName . ", "; // Ajout de chaque champ souhaité
				}
				// Suppression de la dernière virgule en laissant le +
				$requestSQL = rtrim($requestSQL,' '); 
				$requestSQL = rtrim($requestSQL,',').' '; 
				$requestSQL .= "FROM ".$tableID;

				$requestSQL .= $query; // $query vaut '' s'il n'y a pas, ça enlève une condition inutile.
					
				$requestSQL .= " ORDER BY date_modified DESC"; // Tri par date de modification
				$requestSQL .= $this->get_query_select_limit_read_last(); // Ajout de la limite souhaitée
				
				// Appel de la requête
				$q = $this->pdo->prepare($requestSQL);
				$exec = $q->execute();
				
				if(!$exec) {
					$errorInfo = $this->pdo->errorInfo();
					throw new \Exception('ReadLast: '.$errorInfo[2]);
				}
				$fetchAll = $q->fetchAll(\PDO::FETCH_ASSOC);
				$row = array();
				if(!empty($fetchAll[0])) {
					foreach ($fetchAll[0] as $key => $value) {
						if($key === 'id') {
							$row[$key] = $value;
							continue;
						} elseif($key === 'date_modified') {
							$row[$key] = $value;
							continue;
						} elseif(in_array($key, array_keys($targetFields))) {
							// Si $key existe dans les clés du tableau, on enregistre sa valeur dans $row à la clé $myddlewareField_TYPE correspondant
							$row[$targetFields[$key]] = $value;
						}
				    }
					$result['values'] = $row;
					$result['done'] = true;
				} 
				else {
					$result['done'] = false;
					$result['error'] = "No data found in ".$tableID;
				}
			} else { // **************************************************************** SOURCE
				// Si le tableau de requête est présent alors construction de la requête
				if (!empty($param['query'])) {
					$query = '';
					foreach ($param['query'] as $key => $value) {
						if (!empty($query)) {
							$query .= ' AND ';
						} else {
							$query .= ' WHERE ';
						}
						$query .= $key." = '".$value."'";
					}
				}
				
				if(!isset($param['fields'])) {
					$param['fields'] = array();
				}
				$param['fields'] = array_unique($param['fields']);
				$param['fields'] = $this->addRequiredField($param['fields']);
				$param['fields'] = array_values($param['fields']);
				$param['fields'] = $this->cleanMyddlewareElementId($param['fields']);
				
				// Construction de la requête SQL
				$requestSQL = $this->get_query_select_header_read_last();
				// TODO Ajout des champs id et date de l'utilisateur
				
				foreach ($param['fields'] as $field){
				    $requestSQL .= $field . ", "; // Ajout de chaque champ souhaité
				}
				// Suppression de la dernière virgule en laissant le +
				$requestSQL = rtrim($requestSQL,' '); 
				$requestSQL = rtrim($requestSQL,',').' '; 
				$requestSQL .= "FROM ".$param['module'];
	
				$requestSQL .= $query; // $query vaut '' s'il n'y a pas, ça enlève une condition inutile.
				
				// $requestSQL .= " ORDER BY date_modified DESC"; // Tri par date de modification TODO
				$requestSQL .= $this->get_query_select_limit_read_last(); // Ajout de la limite souhaitée	
				
				// Appel de la requête
				$q = $this->pdo->prepare($requestSQL);
				$exec = $q->execute();
				
				if(!$exec) {
					$errorInfo = $this->pdo->errorInfo();
					throw new \Exception('ReadLast: '.$errorInfo[2]);
				}
				$fetchAll = $q->fetchAll(\PDO::FETCH_ASSOC);	
				
				$row = array();
				if(!empty($fetchAll[0])) {
					foreach ($fetchAll[0] as $key => $value) {
						// Could be ampty when we use simulation for example
						if(
								!empty($param['ruleParams']['fieldId'])
							&& $key === $param['ruleParams']['fieldId']
						) { // ID non trouvé
							$row[$key] = $value;
							$row['id'] = $value;
						} 
						if(
								!empty($param['ruleParams']['fieldDateRef'])
							&& $key === $param['ruleParams']['fieldDateRef']
						) {
							$row[$key] = $value;
							$row['date_modified'] = $value;
						} 
						// On doit faire le continue de façon extérieur car le fieldId peut être égal au fieldDateRef
						if (!empty($row[$key])) {
							continue;
						}
						if(in_array($key, $param['fields'])) {
							$row[$key] = $value;
						}
				    }
					$result['values'] = $row;
					$result['done'] = true;
				} 
				else {
					$result['done'] = false;
					$result['error'] = "No data found in ".$tableID;
				}
			}
		}
		catch (\Exception $e) {
			$result['done'] = -1;
		    $result['error'] = 'Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
		}
// print_r($result);			
		return $result;
	} // read_last($param)
	
	// Permet de récupérer les enregistrements modifiés depuis la date en entrée dans la solution
	public function read($param) {
		$result = array();
		try {
			// On contrôle la date de référence, si elle est vide on met 0 (cas fréquent si l'utilisateur oublie de la remplir)		
			if(empty($param['date_ref'])) {
				$param['date_ref'] = 0;
			}
			
			// On contrôle que les champs "id" et "date_modified" ont bien été renseignés
			if(!isset($param['ruleParams']['fieldId'])) throw new \Exception('FieldId has to be specified for the read.');
			if(!isset($param['ruleParams']['fieldDateRef'])) throw new \Exception('"fieldDateRef" has to be specified for the read.');
			$this->required_fields =  array('default' => array($param['ruleParams']['fieldId'], $param['ruleParams']['fieldDateRef']));
			
			if(!isset($param['fields'])) {
				$param['fields'] = array();
			}
			$param['fields'] = array_unique($param['fields']);
			$param['fields'] = $this->addRequiredField($param['fields']);
			$param['fields'] = array_values($param['fields']);
			$param['fields'] = $this->cleanMyddlewareElementId($param['fields']);
			
			// Construction de la requête SQL
			$requestSQL = "SELECT ";
			// TODO Ajout des champs id et date de l'utilisateur
			
			foreach ($param['fields'] as $field){
			    $requestSQL .= $field . ", "; // Ajout de chaque champ souhaité
			}
			// Suppression de la dernière virgule en laissant le +
			$requestSQL = rtrim($requestSQL,' '); 
			$requestSQL = rtrim($requestSQL,',').' '; 
			$requestSQL .= "FROM ".$param['module'];

			$requestSQL .= " WHERE ".$param['ruleParams']['fieldDateRef']. " > '".$param['date_ref']."'";
			
			$requestSQL .= " ORDER BY ".$param['ruleParams']['fieldDateRef']. " ASC"; // Tri par date utilisateur
			
			// Appel de la requête
			$q = $this->pdo->prepare($requestSQL);
			$exec = $q->execute();
			
			if(!$exec) {
				$errorInfo = $this->pdo->errorInfo();
				throw new \Exception('Read: '.$errorInfo[2]);
			}
			$fetchAll = $q->fetchAll(\PDO::FETCH_ASSOC);
			
			$row = array();
			if(!empty($fetchAll)) {
				$result['count'] = count($fetchAll);
				foreach ($fetchAll as $elem) {
					$row = array();
					foreach ($elem as $key => $value) {
						if($key === $param['ruleParams']['fieldId']) {
							$row["id"] = $value;
						} elseif($key === $param['ruleParams']['fieldDateRef']) {
							$row['date_modified'] = $value;
							$result['date_ref'] = $value;
						}
						if(in_array($key, $param['fields'])) {
							$row[$key] = $value;
						}
				    }
					$result['values'][$row['id']] = $row;
				}
			} 
			else {
				$result['error'] = "No Results";
			}
		}
		catch (\Exception $e) {
		    $result['error'] = 'Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
		}
		return $result;	
	} // read($param)
	
	// Permet de créer des données
	public function create($param) {
		try {
			// Si on a pas de table Database alors on renvoie une erreur
			if (empty($param['ruleParams']['tableID'])) {
				throw new \Exception("No table in Database for the Rule. ");
			}
			$tableID = $param['ruleParams']['tableID'];						
			// Boucle sur chaque document en entrée
			foreach($param['data'] as $idDoc => $data) {					
				try {
					// Check control before create
					$data = $this->checkDataBeforeCreate($param, $data);
					// Construction de la requête
					$sql = "INSERT INTO ".$this->stringSeparator.$tableID.$this->stringSeparator." (";
					$first = true;
					
					$values = "(";
					// Boucle sur chaque champ du document
					foreach ($data as $key => $value) {				
						// Saut de la première ligne qui contient l'id du document
						if ($first) {
							$first = false;
							continue;
						}
						if($key == "target_id") {
							continue;
						}
					
						$fieldName = substr($key, 0, strrpos($key, '_'));
						$mappingType = $this->getMappingType($key);
						$sql .= $fieldName.",";
						$values .= "'".$value."',";
					}
					
					$sql = substr($sql, 0, -1); // INSERT INTO table_name (column1,column2,column3,...)
					$values = substr($values, 0, -1);
					$values .= ")"; // VALUES (value1,value2,value3,...)
					$sql .= ") VALUES ".$values; // INSERT INTO table_name (column1,column2,column3,...) VALUES (value1,value2,value3,...)						
					$q = $this->pdo->prepare($sql);
					$exec = $q->execute();
					
					if(!$exec) {
						$errorInfo = $this->pdo->errorInfo();
						throw new \Exception('Create: '.$errorInfo[2]);
					}
					
					if(empty($this->pdo->lastInsertId())) {
						throw new \Exception('Create: No ID returned.');
					}
					
					$idTarget = $this->pdo->lastInsertId();
					
					$result[$idDoc] = array(
											'id' => $idTarget,
											'error' => false
									);
				}
				catch (\Exception $e) {
					$error = 'Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
					$result[$idDoc] = array(
							'id' => '-1',
							'error' => $error
					);
				}
				// Modification du statut du flux
				$this->updateDocumentStatus($idDoc,$result[$idDoc],$param);	
			}
		}
		catch (\Exception $e) {
			$error = 'Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
			$result[$idDoc] = array(
					'id' => '-1',
					'error' => $error
			);
		}
		return $result;
	}

	// Permet de créer des données
	public function update($param) {
		try {
			// Si on a pas de table Database alors on renvoie une erreur
			if (empty($param['ruleParams']['tableID'])) {
				throw new \Exception("No table in Database for the Rule. ");
			}
			$tableID = $param['ruleParams']['tableID'];
			
			// Boucle sur chaque document en entrée
			foreach($param['data'] as $idDoc => $data) {
				try {
					// Check control before update
					$data = $this->checkDataBeforeUpdate($param, $data);
					// Construction de la requête
					$sql = "UPDATE ".$this->stringSeparator.$tableID.$this->stringSeparator." SET "; 
					$first = true;
					
					//$values = "(";
					// Boucle sur chaque champ du document
					foreach ($data as $key => $value) {				
						// Saut de la première ligne qui contient l'id du document
						if ($first) {
							$first = false;
							continue;
						}
						if($key == "target_id") {
							$idTarget = $value;
							continue;
						}
						
						$fieldName = substr($key, 0, strrpos($key, '_'));
						$mappingType = $this->getMappingType($key);
						$sql .= $fieldName."='".$value."',";
					}
					
					$sql = substr($sql, 0, -1);
					$sql .= " WHERE id='".$idTarget."'";;					
					$q = $this->pdo->prepare($sql);
					$exec = $q->execute();
					
					if(!$exec) {
						$errorInfo = $this->pdo->errorInfo();
						throw new \Exception('Create: '.$errorInfo[2]);
					}

					$result[$idDoc] = array(
											'id' => $idTarget,
											'error' => false
									);
				}
				catch (\Exception $e) {
					$error = 'Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
					$result[$idDoc] = array(
							'id' => '-1',
							'error' => $error
					);
				}
				// Modification du statut du flux
				$this->updateDocumentStatus($idDoc,$result[$idDoc],$param);	
			}
		}
		catch (\Exception $e) {
			$error = 'Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
			$result[$idDoc] = array(
					'id' => '-1',
					'error' => $error
			);
		}
		return $result;
	}
	
	// Permet de renvoyer l'id de la table en récupérant la table liée à la règle ou en la créant si elle n'existe pas
	protected function checkTable($param) {
		try {
			// On entre dans le IF si on n'est pas sur la 1ère version de la règle
			// Ou si on est sur une règle child
<<<<<<< HEAD
			if(
					$param['rule']['version'] != "001"
				|| (
						!empty($param['content']['params']['group'])
					&& $param['content']['params']['group'] == 'child'
				)
			) { 
				// Ici on va aller chercher le idTable des versions précédentes			
				// Cette requette permet de récupérer toutes les règles portant le même nom que la notre ET AYANT un tableID
				// Les résultats sont triés de la version la plus récente à la plus vieille
				$sql = "SELECT R1.`value` , R2.`version` 
						FROM  `RuleParam` R1,  `Rule` R2
						WHERE  `name` =  'tableID'
=======
			if($param['rule']['version'] != "001") { 
				// Ici on va aller chercher le idTable des versions précédentes			
				// Cette requette permet de récupérer toutes les règles portant le même nom que la notre ET AYANT un tableID
				// Les résultats sont triés de la version la plus récente à la plus ancienne
				$sql = "SELECT R1.`value` , R2.`version` 
						FROM  `RuleParam` R1,  `Rule` R2
						WHERE  R1.`name` =  'tableID'
>>>>>>> refs/remotes/origin/hotfix
						AND R1.`rule_id` = R2.`id` 
						AND R1.`rule_id` IN (	SELECT  `id` 
												FROM  `Rule` 
												WHERE  `name` =  :name)
						ORDER BY R2.`version` DESC";
				$stmt = $this->conn->prepare($sql);
				$stmt->bindValue(":name", $param["rule"]["name"]);
				$stmt->execute();
				
				// On récupère d'abord le premier résultat afin de vérifier que le tableID n'est pas vide
				$fetch = $stmt->fetch();
				if(!empty($fetch['value'])) {
					$tableID = $fetch['value'];
				}
				
				// Si toutefois il était vide, on prend tous les résultats afin d'en récupérer un non-vide (tjrs dans l'ordre du plus récent au plus vieux)
				$fetchAll = $stmt->fetchAll();
				foreach ($fetchAll as $result) {
					if(!empty($result['value'])) {
						$tableID = $result['value'];
						break;
					}
				}
	
				// Dernier test, si on a tjrs rien dans $tableID et que l'on est pas sur une règle child (jamais de création de table pour une règle child)
				// alors on crée une nouvelle table
<<<<<<< HEAD
				if(
						empty($tableID)
					&&	(
							$param['content']['params']['group'] != 'child'
						|| empty($param['content']['params']['group'])
					)
				) {
					return $this->createDatabaseTable($param);
				}
				// Récupération de la table dans la règle root
				elseif (
						empty($tableID)
					&&	(
							!empty($param['content']['params']['group'])
						&&	$param['content']['params']['group'] == 'child'
					)
				) {
					$sql = "SELECT 
								RuleParam.value
							FROM RuleRelationShip
								INNER JOIN RuleParam
									ON RuleRelationShip.field_id = RuleParam.rule_id
							WHERE 
									RuleRelationShip.rule_id = :ruleId
								AND RuleParam.name = 'tableID'";
					$stmt = $this->conn->prepare($sql);
					$stmt->bindValue(":ruleId", $param["ruleId"]);
					$stmt->execute();
					
					// On récupère d'abord le premier résultat afin de vérifier que le tableID n'est pas vide
					$fetch = $stmt->fetch();
					if(!empty($fetch['value'])) {
						$tableID = $fetch['value'];
					}
				}
				// Si on a pas de table à ce stade alors on renvoie une erreur car on a besoin de l'ID pour faie la modification de cette table
=======
>>>>>>> refs/remotes/origin/hotfix
				if(empty($tableID)) {
					return $this->createDatabaseTable($param);
				}
				
				/*
				* 		MAJ du connecteur avec le nouveau mapping
				*/
				$tableID = mb_strtolower($tableID);
				try {				
					// Récupération des champs de la table actuelle				
					$q = $this->pdo->prepare($this->get_query_describe_table($tableID));
					$exec = $q->execute();			
					if(!$exec) {
						$errorInfo = $this->pdo->errorInfo();
						throw new \Exception('CheckTable: (Describe) '.$errorInfo[2]);
					}
					
					$fetchAll = $q->fetchAll();
				} catch (\PDOException $e) {
					$error = 'CheckTable: Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
					$this->messages[] = array('type' => 'error', 'message' => $error);
					$this->logger->error($error);
					return null;
				}			
				$tableFields = array();
				foreach ($fetchAll as $fetch) {					
					$Type = $this->getMappingType(mb_strtoupper($fetch[$this->fieldType]));
					if($Type == 'VARCHAR(255)') {
						$Type = 'VARCHAR';
					}
					$tableFieldnames[] = $fetch[$this->fieldName];
					$tableFields[] = $fetch[$this->fieldName].'_'.$Type;
				}
				
				// COMPARAISON DES CHAMPS			
				$diff = array();
				$add = array();
				foreach ($param['ruleFields'] as $ruleField) {
					$mappingType = $this->getMappingType($ruleField['target_field_name']);
					
					if (empty($mappingType)) {
						throw new \Exception("Mapping Type unknown for the field ".$ruleField['target_field_name'].". Failed to create the table in Database");
					}
					// Récupération du nom d'affichage du champ : nom du champ complet sans le type en fin de nom
					$fieldName = substr($ruleField['target_field_name'], 0, strrpos($ruleField['target_field_name'], '_'));

					// Si le nom du champ Database que l'on veut envoyer existe déjà dans la table actuel alors on ne l'envoie pas.
					if (!in_array($fieldName, $tableFieldnames)) {
						$add[] = array("NAME" => $fieldName, "TYPE" => $mappingType);
					} else {
						if(!in_array($ruleField['target_field_name'], $tableFields))
							$diff[] = array("NAME" => $fieldName, "TYPE" => $mappingType);
					}
				}
				if(empty($diff) && empty($add)) {
					$this->messages[] = array('type' => 'success', 'message' => 'No added or modified field on your rule. The table has not been changed in Database. ');
					return $this->saveConnectorParams($param['ruleId'], $tableID);
				} 
				
				$fieldstext = '';
				
				try {
					$this->pdo->beginTransaction();
					if(!empty($diff)) {
						foreach ($diff as $fieldDiff) {
							$fieldstext .= $fieldDiff['NAME'].' ';  
							// Création de la requête
							$sql= "ALTER TABLE ".$this->stringSeparator.$tableID.$this->stringSeparator.$this->get_query_alter_column().$fieldDiff['NAME']." ".$fieldDiff['TYPE'];						
							$q = $this->pdo->prepare($sql);
							$exec = $q->execute();
							
							if(!$exec) {
								throw new \Exception("Error AlterTable (Modify): Please check the FieldName.");
							}
						}
					}
					if(!empty($add)) {
						foreach ($add as $fieldAdd) {
							$fieldstext .= $fieldAdd['NAME'].' ';  
							// Création de la requête
							$sql= "ALTER TABLE ".$this->stringSeparator.$tableID.$this->stringSeparator." ADD ".$fieldAdd['NAME']." ".$fieldAdd['TYPE'];								
							$q = $this->pdo->prepare($sql);
							$exec = $q->execute();
							
							if(!$exec) {
								$errorInfo = $this->pdo->errorInfo();
								throw new \Exception("Error AlterTable (Add): ".$errorInfo[2]);
							}
						}
					}
					// Commit
					$this->pdo->commit();	
				} catch (\PDOException $e) {
					if(!empty($this->pdo)) {
						$this->pdo->rollBack();
					}
					$error = 'CheckTable: Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
					$this->messages[] = array('type' => 'error', 'message' => 'CheckTable: (Modify)'.$error);
					$this->logger->error($error);
					return null;
				}
				
				// Mise à jour des données de la table créée pour la nouvelle règle dans la base de données 
				$sqlFields = "INSERT INTO `RuleParam` (`rule_id`,`name`,`value`) VALUES (:ruleId, 'tableID', :tableID)";
				$stmt = $this->conn->prepare($sqlFields);
				$stmt->bindValue(":ruleId", $param['ruleId']);
				$stmt->bindValue(":tableID", $tableID);
				$stmt->execute();	
				
				$this->messages[] = array('type' => 'success', 'message' => 'Table '.$tableID.' successfully updated in Database. Fields added / modified : '.$fieldstext.'.');
				return $tableID;
			}
			else {
				return $this->createDatabaseTable($param);
			} 
			return null;
		} catch (\Exception $e) {
			$error = 'CheckTable: Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
			$this->messages[] = array('type' => 'error', 'message' => $error);
			$this->logger->error($error);
			return null;
		}
	}
	
	// Créer un table dans Database
	protected function createDatabaseTable($param) {
<<<<<<< HEAD
	    $dbh = new \PDO($this->driver.':host='.$this->host.';port='.$this->port.';dbname='.$this->dbname, $this->login, $this->password);

		$sql = "CREATE TABLE `".$param['rule']['name_slug']."` (
			id INT(6) UNSIGNED AUTO_INCREMENT,
			date_modified TIMESTAMP ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,";

=======
	    $sql = $this->get_query_create_table_header($param['rule']['name_slug']);
>>>>>>> refs/remotes/origin/hotfix
		
		if (empty($param['ruleFields'])) {
			throw new \Exception("Failed to create the table, no field in the Rule ".$param['rule']['name_slug']);
		}
		// Création du mapping dans Database
		Foreach ($param['ruleFields'] as $ruleField) {
			$mappingType = $this->getMappingType($ruleField['target_field_name']);
			
			if (empty($mappingType)) {
				throw new \Exception("Mapping Type unknown for the field ".$ruleField['target_field_name'].". Failed to create the table in Database");
			}
			
			// Pour les champs date et metric (fixés car obligatoire), on garde le nom de champ source sinon on met le champ saisi par l'utilisateur pour affichage dans Database
			$tab = explode('_',$ruleField['target_field_name'], -1);
			$fieldName = '';
			foreach ($tab as $morceau) {
				$fieldName .= $morceau.'_';
			}
			$fieldName = substr($fieldName, 0, -1);
			$sql.= $fieldName." ".$mappingType.",";
<<<<<<< HEAD
			/*$xml.=		"<mapping>
							<fileField>".$param['rule']['rule_module_source'].'_'.$ruleField['target_field_name']."</fileField>
							<displayName>".(in_array($ruleField['target_field_name'],array('Metric','Date')) ? $ruleField['source_field_name'] : $fieldName)."</displayName>
							<mappingType>".$mappingType."</mappingType>
							".($mappingType == 'DATE' ? "<pattern>dd/MM/yyyy hh:mm</pattern>" : "")."
						</mapping>";*/
		}
		$sql.= "PRIMARY KEY (`id`),
	    		INDEX `".$param['rule']['name_slug']."_date_modified` (`date_modified`)
				)";
			   
		$q = $dbh->prepare($sql);
=======
		}
		$sql.= " INDEX ".$this->stringSeparator.$param['rule']['name_slug']."_date_modified".$this->stringSeparator." (date_modified))";						   
		$q = $this->pdo->prepare($sql);
>>>>>>> refs/remotes/origin/hotfix
		$exec = $q->execute();
		
		if(!$exec) { // Si erreur
			$errorInfo = $this->pdo->errorInfo();
			throw new \Exception('Failed to create the table, :' . $errorInfo[2].' - Query : '.$sql);
			$this->logger->error('Failed to create the table, :' . $errorInfo[2].' - Query : '.$sql);
		}
		$this->messages[] = array('type' => 'success', 'message' => 'Table '.$param['rule']['name_slug'].' successfully created in Database. ');		
		return $this->saveConnectorParams($param['ruleId'], $param['rule']['name_slug']);
	}
	
	protected function saveConnectorParams($ruleId, $idTable) {
		// Mise à jour du connecteur dans la base de données 
		$sqlFields = "INSERT INTO `RuleParam` (`rule_id`,`name`,`value`) VALUES (:ruleId, 'tableID', :tableID)";
		$stmt = $this->conn->prepare($sqlFields);
		$stmt->bindValue(":ruleId", $ruleId);
		$stmt->bindValue(":tableID", $idTable);
		$stmt->execute();	   				
		return $idTable;
	}
<<<<<<< HEAD
	
	// Permet d indiquer si on envoie les champs standard ou si on renvoie en plus les champs de relation dans get_module_field
	public function extendField ($moduleTarget) {
		return true;
	}
	
	// Permet de générer un document d'une règle child
	protected function generateChildDocument($param,$data) {
		// Si on est en create c'est que l'on est forcément sur une règle root (les child ne fond que de l'update)
		// Si des règles child pointe sur la règle en cours il faut générer des documents sur les autres règles 
		// afin que toutes les données de la ligne en cours soient rensignées
		// Récupération de toutes les règles liées
		$sql = "SELECT 
					RuleRelationShip.rule_id,
					RuleRelationShip.field_name_target
				FROM RuleRelationShip
					INNER JOIN Rule
						ON RuleRelationShip.rule_id = Rule.id
				WHERE 
						RuleRelationShip.field_id = :ruleId
					AND Rule.deleted = 0	
				";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(":ruleId", $param["ruleId"]);
		$stmt->execute();
		$relationships = $stmt->fetchAll();
		if (!empty($relationships)) {
			// Pour chaque relationship, création d'un document
			foreach ($relationships as $relationship) {
				$param['ruleId'] = $relationship['rule_id'];
				// Récupération de l'ID correspondant à l'enregistrement de la règle liée dans le système source
				// Si l'id de l'enregistrement lié est renseigné alors on génère le docuement sinon on ne le genère pas (il n'est pas obligatoirement renseigné)				
				if (!empty($data[$relationship['field_name_target']])) {
					$rule = new ruleMyddleware($this->logger, $this->container, $this->conn ,$param);
					// Si un document sur la même règle avec le même id source a déjà été fait dans ce paquet d'envoi alors on ne régénère pas un autre document qui serait doublon
					if (empty($this->duplicateDoc[$param['ruleId']][$data[$relationship['field_name_target']]])) {
						$generateDocuments = $rule->generateDocuments($data[$relationship['field_name_target']]);	
						// Si on a eu une erreur alors on arrête de générer les documents child
						if (!empty($generateDocuments->error)) {
							return $generateDocuments->error;
						}
						$this->duplicateDoc[$param['ruleId']][$data[$relationship['field_name_target']]] = 1;
					}
				}
			}
		}
		return true;
	}
	
	//function to make cURL request	
	protected function call($method, $parameters = array()){		
	 	ob_start();
		
		$curl_request = curl_init($this->url);

		curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, $method); // On construit une requête de type $method (GET ou POST)
		curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0); // Ne vérifie pas le certificat SSL
		curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($curl_request, CURLOPT_HEADER, false); // !important, permet d'enlever le header http de la réponse
	
		$headers = array(             
					"Content-type: application/xml",
					"charset=\"utf-8\"", 
					"token:".$this->paramConnexion['token']
				); 
				
				
		curl_setopt($curl_request, CURLOPT_HTTPHEADER, $headers);
		if (!empty($parameters)) {
			curl_setopt($curl_request, CURLOPT_POSTFIELDS, $parameters); 
		}
		$result = curl_exec($curl_request); // Exécute le cURL
		curl_close($curl_request);	
		
		$xml = new \SimpleXMLElement($result); // Transforme la réponse en élément XML
		
		$result = (json_decode(json_encode((array)$xml), true)); // Encode en json (avec une convertion en array) puis le décode afin d'obtenir un array correctement traitable
		if(empty($result))	throw new \Exception ("Call returned an empty response."); // Traitement d'erreur si on a une réponse vide
=======
>>>>>>> refs/remotes/origin/hotfix
		
	// Function de conversion de datetime format Myddleware à un datetime format solution
	protected function dateTimeFromMyddleware($dateTime) {
		try {
			if (empty($dateTime)) {			
				throw new \Exception("Date empty. Failed to send data. ");
				return null;
			}
			if(date_create_from_format('Y-m-d H:i:s', $dateTime)) {
				$date = date_create_from_format('Y-m-d H:i:s', $dateTime);
			} else {
				$date = date_create_from_format('Y-m-d', $dateTime);
				if($date) {
					$date->setTime( 0 , 0 , 0 );
				} else {
					throw new \Exception("Wrong format for your date. Please check your date format. Contact us for help.");
				}
				
			}
			return $date->format('d/m/Y H:i');
		} catch (\Exception $e) {
			$result['error'] = 'Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
			return $result;
		}
	}// dateTimeFromMyddleware($dateTime)   
<<<<<<< HEAD
	
	
	// Permet de récupérer la source ID du document en paramètre
	protected function getSourceId($idDoc) {
		// Récupération du source_id
		$sql = "SELECT `source_id` FROM `Document` WHERE `id` = :idDoc";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(":idDoc", $idDoc);
		$stmt->execute();
		$sourceId = $stmt->fetch();		
		return $sourceId['source_id'];
	}
=======

>>>>>>> refs/remotes/origin/hotfix
	
	// Fonction permettant de récupérer le type d'un champ
	protected function getMappingType($field) {
		if (stripos($field, 'TEXT') !== false) {
			return 'TEXT';
		}
		if (stripos($field, 'VARCHAR') !== false) {
			return 'VARCHAR(255)';
		}
		// Les champs référence sont considéré comme des filtres et permettent de lier plusieurs règles
		if (stripos($field, 'INT') !== false) {
			return 'INT';
		}
		if (stripos($field, 'BOOL') !== false) {
			return 'TINYINT';
		}
		if (stripos($field, 'DATE') !== false) {
			return 'DATE';
		}
		return null;
	}
	
	// Ajout de champ personnalisé dans la target ex : Database 
	public function getFieldMappingAdd($moduleTarget) {
		return array(
			'TEXT' => 'TEXT',
			'VARCHAR' => 'VARCHAR',
			'INT' => 'INT',
			'BOOL' => 'BOOL',
			'DATE' => 'DATE'
		);
	}
	
	// Permet d'indiquer le type de référence, si c'est une date (true) ou un texte libre (false)
	public function referenceIsDate($module) {
		return false;
	}
	
<<<<<<< HEAD
	// Ajout de contrôle lors d'un sauvegarde de la règle
	public function beforeRuleSave($data,$type) {
		if($type == "target") {
			// Vérification de la suppression d'un champ référence
			// Si on est sur une édition 'oldRule' existe
			if (!empty($data['oldRule'])) {
				// Récupération des champs référence de cette ancienne règle qui sont utilisés dans une autre règle
				$sql = "SELECT
							Rule.id,
							Rule.name,
							RuleRelationShip.field_name_target
						FROM RuleRelationShip
							INNER JOIN Rule
								ON RuleRelationShip.rule_id = Rule.id
						WHERE 
								RuleRelationShip.field_id = '".$data['oldRule']."'
							AND Rule.deleted = 0";
				$stmt = $this->conn->prepare($sql);
				$stmt->bindValue(":oldRule", $data['oldRule']);
				$stmt->execute();
				$referenceFields = $stmt->fetchAll();
				// Pour tous les champs trouvés, on vérifie qu'ils sont toujours existant dans la nouvelle règle
				if (!empty($referenceFields)) {
					foreach ($referenceFields as $referenceField) {
						// Si le champs est absent alors on génère une erreur.
						if (empty($data['content']['fields']['name'][$referenceField['field_name_target']])) {
							return array('done'=>false, 'message'=> 'The field '.$referenceField['field_name_target'].' is linked to the rule '.$referenceField['name'].'. Change this rule before removing this field.');
						}
					}
				}		
			}
			
			// Si le module d'entrée Database n'est pas Container alors on est sur une règle Child. On vérifie que la relation est donc bien présente dans la règle
			if (
					$data['module']['target']['name'] != 'NewTable'
				&& empty($data['relationships'])
			) {
				return array('done'=>false, 'message'=>'Failed to save the rule. You have to create a relationship with the Table '.$data['module']['target']['name'].' that you selected in the first step.');
			}
		
			// Pour Database, les relations sont un peu plus manuelles donc on vérifie que le champ de la relation appartien bien à la règle sélectionnée
			// Il ne peut y avoir qu'un relation par règle avec Database
			if (!empty($data['relationships'])) {
				$sql = "SELECT rule_id
						FROM RuleField
						WHERE 
								rule_id = :rule_id
							AND target_field_name = :target_field_name";
				$stmt = $this->conn->prepare($sql);
				$stmt->bindValue(":rule_id", $data['relationships'][0]['rule']);
				$stmt->bindValue(":target_field_name", $data['relationships'][0]['target']);
				$stmt->execute();
				$fetch = $stmt->fetch();
				if(empty($fetch['rule_id'])) {
					return array('done'=>false, 'message'=>'Failed to save the relationship. The field '.$data['relationships'][0]['target'].' doesn\'t belong to the selected rule ('.$data['relationships'][0]['rule'].'). Change the relationShip to save this rule. ');
				}
				// Ajout du paramètre child à la règle puisqu'une relation existe
				return array('done'=>true, 'message'=>'', 'params' => array('group' => 'child'));
			}
			else {
				// Ajout du paramètre root à la règle puisqu'aucune relation n'existe
				return array('done'=>true, 'message'=>'', 'params' => array('group' => 'root'));
			}
			return array('done'=>true, 'message'=>'');
		}
		return array('done'=>true, 'message'=>'');
	}
	
	
=======
>>>>>>> refs/remotes/origin/hotfix
	// Après la sauvegarde d'une règle Database (en cible) on crée ou modifie la table Database
	public function afterRuleSave($data,$type) {
		try {
			if($type == 'target') {
				$paramLogin = $this->getParamLogin($data['connector']['cible']);
				$this->login($paramLogin);
				if ($this->connexion_valide == false){
					$this->messages[] = array('type' => 'error', 'message' => 'Failed to login to Database.');
				}
				
				// Récupération des données de la règle
				$sql = "SELECT * FROM Rule WHERE id = :ruleId";
				$stmt = $this->conn->prepare($sql);
				$stmt->bindValue(":ruleId", $data['ruleId']);
				$stmt->execute();
				$data['rule'] = $stmt->fetch();
				if(empty($data['rule'])) {
					$this->messages[] = array('type' => 'error', 'message' => 'Failed to retrieve the rule in the database.');
				}
							
				// Récupération de tous les ruleFields de la règle en cours
				$sql = "SELECT * FROM RuleField WHERE rule_id = :ruleId";
				$stmt = $this->conn->prepare($sql);
				$stmt->bindValue(":ruleId", $data['ruleId']);
				$stmt->execute();
				$data['ruleFields'] = $stmt->fetchAll();
				if(empty($data['ruleFields'])) {
					$this->messages[] = array('type' => 'error', 'message' => 'Failed to retrieve the ruleFields in the database.');
				}
				
				// Tout d'abord on vérifie si la table existe déjà sur une version précédente de la règle ou sur une règle root
				// La fonction check créera la table ou renverra l'existante
				if (empty($this->messages)) {
					$idTable = $this->checkTable($data);
				}
			}
		}
		catch (\Exception $e) {
			$error = 'Error : '.$e->getMessage().' '.__CLASS__.' Line : ( '.$e->getLine().' )';
			$this->messages[] = array('type' => 'error', 'message' => 'Failed to create the table in Database : '.$error);
		}
		return $this->messages;
	}

	public function getFieldsParamUpd($type, $module, $myddlewareSession) {	
		try {
			if ($type == 'source'){
				$fieldsSource = $this->get_module_fields($module, $type, false);
				if(!empty($fieldsSource)) {
					$idParam = array(
								'id' => 'fieldId',
								'name' => 'fieldId',
								'type' => 'option',
								'label' => 'Field ID',
								'required'	=> true
							);
					$dateParam = array(
								'id' => 'fieldDateRef',
								'name' => 'fieldDateRef',
								'type' => 'option',
								'label' => 'Field Date Reference',
								'required'	=> true
							);
					foreach ($fieldsSource as $key => $value) {
						$idParam['option'][$key] = $value['label'];
						$dateParam['option'][$key] = $value['label'];
					}
					$params[] = $idParam;
					$params[] = $dateParam;
				}
				return $params;
			}
			return array();
		}
		catch (\Exception $e){
			return array();
		}
	}

}

/* * * * * * * *  * * * * * *  * * * * * * 
	si custom file exist alors on fait un include de la custom class
 * * * * * *  * * * * * *  * * * * * * * */
$file = __DIR__.'/../Custom/Solutions/database.php';
if(file_exists($file)){
	require_once($file);
}
else {
	//Sinon on met la classe suivante
	class database extends databasecore {
		
	}
}