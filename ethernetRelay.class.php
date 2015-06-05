<?php

/**
* Classe de gestion SQL de la table ethernetRelay liée à la classe ethernetRelay 
* @author: valentin carruesco <idleman@idleman.fr>
*/

//La classe ethernetRelay hérite de SQLiteEntity qui lui ajoute des méthode de gestion de sa table en bdd (save,delete...)
class ethernetRelay extends SQLiteEntity{

	public $name,$description,$code,$room,$ip,$id,$offcommand,$oncommand,$icon,$secure; //Pour rajouter des champs il faut ajouter les variables ici...
	protected $TABLE_NAME = 'plugin_ethernetRelay'; 	//Pensez à mettre le nom de la table sql liée a cette classe
	protected $CLASS_NAME = 'ethernetRelay'; //Nom de la classe courante
	protected $object_fields = 
	array( // Ici on définit les noms des champs sql de la table et leurs types
		'name'=>'string',
		'oncommand'=>'string',
		'offcommand'=>'string',
		'description'=>'string',
		'secure'=>'string',
		'ip'=>'string',
		'code'=>'int',
		'room'=>'int',
		'icon'=>'string',
		'id'=>'key'
	);

	function __construct(){
		parent::__construct();
	}

}

?>
