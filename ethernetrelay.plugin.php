<?php
/*
@name ethernet Relay
@author Iya	<contact@iyadev.fr>
@link Http://iyadev.fr
@licence Cc -by-nc-sa
@version 0.3
@description Prise relais ethernet basé sur la dernière version du plugins WireRelais d'idleman (01/2015), commande vocal non testé, le code source pour l'arduino est dans le dossier du plugins. Correction par chriskross 
*/

//On appelle les entités de base de données
include('ethernetRelay.class.php');


//Cette fonction ajoute une commande vocale
function ethernetRelay_plugin_vocal_command(&$response,$actionUrl){
	global $conf;

	$ethernetRelayManager = new ethernetRelay();

	$ethernetRelays = $ethernetRelayManager->populate();
	foreach($ethernetRelays as $ethernetRelay){

		if(!empty($ethernetRelay->oncommand))
		$response['commands'][] = array('command'=>$conf->get('VOCAL_ENTITY_NAME').', '.$ethernetRelay->oncommand,'url'=>$actionUrl.'?action=ethernetRelay_vocal_change_state&engine='.$ethernetRelay->id.'&state=1','confidence'=>('0.90'+$conf->get('VOCAL_SENSITIVITY')));
		if(!empty($ethernetRelay->offcommand))
		$response['commands'][] = array('command'=>$conf->get('VOCAL_ENTITY_NAME').', '.$ethernetRelay->offcommand,'url'=>$actionUrl.'?action=ethernetRelay_vocal_change_state&engine='.$ethernetRelay->id.'&state=0','confidence'=>('0.90'+$conf->get('VOCAL_SENSITIVITY')));
	}
}

//cette fonction comprends toutes les actions du plugin qui ne nécessitent pas de vue html
function ethernetRelay_plugin_action(){
	global $_,$conf,$myUser;

	//Action de réponse à la commande vocale "Yana, commande de test"
	switch($_['action']){

		case 'ethernetRelay_save_ethernetRelay':
			Action::write(
				function($_,&$response){
					$ethernetRelayManager = new ethernetRelay();

					if(empty($_['nameethernetRelay'])) throw new Exception("Le nom est obligatoire");
					if(!is_numeric($_['codeethernetRelay']))  throw new Exception("Le code est obligatoire et doit être numerique");

					$ethernetRelay = !empty($_['id']) ? $ethernetRelayManager->getById($_['id']): new ethernetRelay();
					$ethernetRelay->name = $_['nameethernetRelay'];
					$ethernetRelay->description = $_['descriptionethernetRelay'];
					$ethernetRelay->code = $_['codeethernetRelay'];
					$ethernetRelay->room = $_['roomethernetRelay'];
					$ethernetRelay->ip = $_['ipethernetRelay'];
					$ethernetRelay->secure = $_['secureethernetRelay'];
					$ethernetRelay->oncommand = $_['onethernetRelay'];
					$ethernetRelay->offcommand = $_['offethernetRelay'];
					$ethernetRelay->icon = $_['iconethernetRelay'];
					$ethernetRelay->save();
					$response['message'] = 'Relais enregistré avec succès';
				},
				array('plugin_ethernetRelay'=>'c')
			);
		break;

		case 'ethernetRelay_delete_ethernetRelay':
			Action::write(
				function($_,$response){
					$ethernetRelayManager = new ethernetRelay();
					$ethernetRelayManager->delete(array('id'=>$_['id']));
				},
				array('plugin_ethernetRelay'=>'d') 
			);
		break;



		case 'ethernetRelay_plugin_setting':
			Action::write(
				function($_,$response){	
					$conf->put('plugin_ethernetRelay_emitter_pin',$_['emiterPin']);
					$conf->put('plugin_ethernetRelay_emitter_code',$_['emiterCode']);
				},
				array('plugin_ethernetRelay'=>'c') 
			);
		break;

		case 'ethernetRelay_manual_change_state':
			Action::write(
				function($_,&$response){	
					ethernetRelay_plugin_change_state($_['engine'],$_['state']);
				},
				array('plugin_ethernetRelay'=>'c') 
			);
		break;

		case 'ethernetRelay_vocal_change_state':
			global $_,$myUser;
			try{
				$response['responses'][0]['type'] = 'talk';
				if(!$myUser->can('relais filaire','u')) throw new Exception ('Je ne vous connais pas, ou alors vous n\'avez pas le droit, je refuse de faire ça!');
				ethernetRelay_plugin_change_state($_['engine'],$_['state']);
				$response['responses'][0]['sentence'] = Personality::response('ORDER_CONFIRMATION');
			}catch(Exception $e){
				$response['responses'][0]['sentence'] = Personality::response('WORRY_EMOTION').'! '.$e->getMessage();
			}
			$json = json_encode($response);
			echo ($json=='[]'?'{}':$json);
		break;

		case 'ethernetRelay_load_widget':

			require_once(dirname(__FILE__).'/../dashboard/Widget.class.php');
			
			Action::write(
				function($_,&$response){	

					$widget = new Widget();
					$widget = $widget->getById($_['id']);
					$data = $widget->data();



					if(empty($data['relay'])){
						$content = 'Choisissez un relais en cliquant sur l \'icone <i class="fa fa-wrench"></i> de la barre du widget';
					}else{
						$relay = new ethernetRelay();
						$relay = $relay->getById($data['relay']);
						
						$response['title'] = $relay->name;

						

						$content = '
						<!-- CSS -->
						<style>
							
							.relay_pane {
							    background: none repeat scroll 0 0 #50597b;
							    list-style-type: none;
							    margin: 0;
							    cursor:default;
							    width: 100%;
							}
							.relay_pane li {
							    background: none repeat scroll 0 0 #50597b;
							    display: inline-block;
							    margin: 0 1px 0 0;
							    padding: 10px;
							    cursor:default;
							    vertical-align: top;
							}
							.relay_pane li h2 {
							    color: #ffffff;
							    font-size: 16px;
							    margin: 0 0 5px;
							    padding: 0;
							    cursor:default;
							}
							.relay_pane li h1 {
							    color: #B6BED9;
							    font-size: 14px;
							    margin: 0 0 10px;
							    padding: 0;
							    cursor:default;
							}

							.relay_pane li.ethernetRelay-case{
								background-color:  #373f59;
								cursor:pointer;
								width: 55px;
							}
							.ethernetRelay-case i{
								color:#8b95b8;
								font-size:50px;
								transition: all 0.2s ease-in-out;
							}
							.ethernetRelay-case.active i{
								color:#ffffff;
								text-shadow: 0 0 10px #ffffff;
							}

							.ethernetRelay-case.active i.fa-lightbulb-o{
								color:#FFED00;
								text-shadow: 0 0 10px #ffdc00;
							}
							.ethernetRelay-case.active i.fa-power-off{
								color:#BDFF00;
								text-shadow: 0 0 10px #4fff00;
							}

							.ethernetRelay-case.active i.fa-flash{
								color:#FFFFFF;
								text-shadow: 0 0 10px #00FFD9;
							}

							.ethernetRelay-case.active i.fa-gears{
								color:#FFFFFF;
								text-shadow: 0 0 10px #FF00E4;
							}

						</style>
						
						<!-- CSS -->
						                    <ul class="relay_pane">
                            <li class="ethernetRelay-case" '.($relay->state?'active':'').' onclick="plugin_ethernetRelay_change(this,'.$relay->id.');" style="text-align:center;">
                                <i title="On/Off" class="'.$relay->icon.'"></i>
                            </li>
                            <li>
                                <h2>'.$relay->description.'</h2>
                                <h1>Code '.$relay->code.'</h1>
                            </li>
                        </ul>

                    <!-- JS -->
                    <script type="text/javascript">
                        function plugin_ethernetRelay_change(element,id){
                            var state = $(element).hasClass(\'active\') ? 0 : 1 ;

                            $.action(
                                {
                                    action : \'ethernetRelay_manual_change_state\', 
                                    engine: id,
                                    state: state
                                },
                                function(response){
                                    $(element).toggleClass("active");
                                }
                            );

                        }
                    </script>
						';
					}
					$response['content'] = $content;
				}
			);
		break;

		case 'ethernetRelay_edit_widget':
			require_once(dirname(__FILE__).'/../dashboard/Widget.class.php');
			$widget = new Widget();
			$widget = $widget->getById($_['id']);
			$data = $widget->data();
		
			$relayManager = new ethernetRelay();
			$relays = $relayManager->populate();

			$content = '<h3>Relais ciblé</h3>';

			if(count($relays) == 0){
				$content = 'Aucun relais existant dans yana, <a href="setting.php?section=ethernetRelay">Créer un relais ?</a>';
			}else{
				$content .= '<select id="relay">';
				$content .= '<option value="">-</option>';
				foreach ($relays as $relay) {
					$content .= '<option value="'.$relay->id.'">'.$relay->name.'</option>';
				}
				$content .= '</select>';
			}
			echo $content;
		break;

		case 'ethernetRelay_save_widget':
			require_once(dirname(__FILE__).'/../dashboard/Widget.class.php');
			$widget = new Widget();
			$widget = $widget->getById($_['id']);
			$data = $widget->data();
			
			$data['relay'] = $_['relay'];
			$widget->data($data);
			$widget->save();
			echo $content;
		break;

	}
}


function ethernetRelay_plugin_change_state($engine,$state){
	$ethernetRelay = new ethernetRelay();
	$ethernetRelay = $ethernetRelay->getById($engine);
	if($state  != 0)
	{
		$state="on";
	}else
	{
		$state = "off";
	}
	if($ethernetRelay->pulse==0){
		$cmd = 'wget "http://'.$ethernetRelay->ip.'/?'.$ethernetRelay->code.'='.$state.'&code='.$ethernetRelay->secure.'" --output-document=/tmp/test';
	}else{
		$cmd = 'wget "http://'.$ethernetRelay->ip.'/?'.$ethernetRelay->code.'='.$state.'&code='.$ethernetRelay->secure.'" --output-document=/tmp/test';
	}
	system($cmd, $out);
	system("echo '".$engine."'>> /tmp/test");
	system("echo 'xx".$cmd."'>> /tmp/test");
}




function ethernetRelay_plugin_setting_page(){
	global $_,$myUser,$conf;
	if(isset($_['section']) && $_['section']=='ethernetRelay' ){

		if(!$myUser) throw new Exception('Vous devez être connecté pour effectuer cette action');
		$ethernetRelayManager = new ethernetRelay();
		$ethernetRelays = $ethernetRelayManager->populate();
		$roomManager = new Room();
		$rooms = $roomManager->populate();
		$selected =  new ethernetRelay();
		$selected->code = 1;
		$selected->icon = 'fa fa-flash';

		//Si on est en mode modification
		if (isset($_['id']))
			$selected = $ethernetRelayManager->getById($_['id']);
			

		$icons = array(
			'fa fa-lightbulb-o',
			'fa fa-power-off',
			'fa fa-flash',
			'fa fa-gears',
			'fa fa-align-justify',
			'fa fa-adjust',
			'fa fa-arrow-circle-o-right',
			'fa fa-desktop',
			'fa fa-music',
			'fa fa-bell-o',
			'fa fa-beer',
			'fa fa-bullseye',
			'fa fa-automobile',
			'fa fa-book',
			'fa fa-bomb',
			'fa fa-clock-o',
			'fa fa-cutlery',
			'fa fa-microphone',
			'fa fa-tint'
			);
		?>

		<div class="span9 userBloc">

			<h1>Relais</h1>
			<p>Gestion des relais ethernet</p>  

			<fieldset>
			    <legend>Ajouter/Modifier un relais ethernet</legend>

			    <div class="left">

				    <label for="nameethernetRelay">Nom</label>
				    <input type="hidden" id="id" value="<?php echo $selected->id; ?>">
				    <input type="text" id="nameethernetRelay" value="<?php echo $selected->name; ?>" placeholder="Lumiere Canapé…"/>
				    
				    <label for="descriptionethernetRelay">Description</label>
				    <input type="text"  value="<?php echo $selected->description; ?>" id="descriptionethernetRelay" placeholder="Relais sous le canapé…" />

					<label for="iconethernetRelay">Icone</label>
				    <input type="hidden"  value="<?php echo $selected->icon; ?>" id="iconethernetRelay"  />
					
					<div>
						<div style='margin:5px;'>
						<?php foreach($icons as $i=>$icon){
							if($i%6==0) echo '</div><div style="margin:5px;">';
							?>
							<i style="width:25px;" onclick="plugin_ethernetRelay_set_icon(this,'<?php echo $icon; ?>');" class="<?php echo $icon; ?> btn <?php echo $selected->icon==$icon?'btn-success':''; ?>"></i>
						<?php } ?> 
						</div>
					</div>

				    <label for="onethernetRelay">Commande vocale "ON" associée</label>
				    <?php echo $conf->get('VOCAL_ENTITY_NAME') ?>, <input type="text" id="onethernetRelay" value="<?php echo $selected->oncommand; ?>" placeholder="Allume la lumière, Ouvre le volet…"/>
				   
				    
				    <label for="offethernetRelay">Commande vocale "OFF" associée</label>
				    <?php echo $conf->get('VOCAL_ENTITY_NAME') ?>, <input type="text" id="offethernetRelay" value="<?php echo $selected->offcommand; ?>" placeholder="Eteinds la lumière, Ferme le volet…"/>
				    
				    
				    <label for="codeethernetRelay">Code</label>
				    <input type="number" value="<?php echo $selected->code; ?>" id="codeethernetRelay" placeholder="0,1,2…" />
				    
				    <label for="roomethernetRelay">Pièce de la maison</label>
				    <select id="roomethernetRelay">
				    	<?php foreach($rooms as $room){ ?>
				    	<option <?php if ($selected->room == $room->getId()){echo "selected";} ?> value="<?php echo $room->getId(); ?>"><?php echo $room->getName(); ?></option>
				    	<?php } ?>
				    </select>
				   <label for="ipethernetRelay">IP</label>
				   <input type="text" value="<?php echo $selected->ip; ?>" id="ipethernetRelay" placeholder="0" />
				   
				   <label for="secureethernetRelay">SecureCode</label>
				   <input type="text" value="<?php echo $selected->secure; ?>" id="secureethernetRelay" placeholder="0" />
				</div>

	  			<div class="clear"></div>
			    <br/><button onclick="plugin_ethernetRelay_save(this)" class="btn">Enregistrer</button>
		  	</fieldset>
			<br/>


			<fieldset>
				<legend>Consulter les relais filaires existants</legend>
				<table class="table table-striped table-bordered table-hover">
				    <thead>
					    <tr>
					    	<th>Nom</th>
						    <th>Description</th>
						    <th>Code</th>
						    <th>Pièce</th>
						    <th>Ip</th>
						    <th>SecureCode</th>
					    </tr>
				    </thead>
			    
			    	<?php foreach($ethernetRelays as $ethernetRelay){ 
			    		$room = $roomManager->load(array('id'=>$ethernetRelay->room)); 
			    	?>
					<tr>
				    	<td><?php echo $ethernetRelay->name; ?></td>
					    <td><?php echo $ethernetRelay->description; ?></td>
					    <td><?php echo $ethernetRelay->code; ?></td>
					    <td><?php echo $room->getName(); ?></td>
					    <td><?php echo $ethernetRelay->ip; ?></td>
					    <td><?php echo $ethernetRelay->secure; ?></td>
					    <td>
					    	<a class="btn" href="setting.php?section=ethernetRelay&id=<?php echo $ethernetRelay->id; ?>"><i class="fa fa-pencil"></i></a>
					    	<div class="btn" onclick="plugin_ethernetRelay_delete(<?php echo $ethernetRelay->id; ?>,this);"><i class="fa fa-times"></i></div>
					    </td>
					    </td>
			    	</tr>
			    <?php } ?>
			    </table>
			</fieldset>
		</div>

<?php
	}
}

function ethernetRelay_plugin_setting_menu(){
	global $_;
	echo '<li '.(isset($_['section']) && $_['section']=='ethernetRelay'?'class="active"':'').'><a href="setting.php?section=ethernetRelay"><i class="fa fa-angle-right"></i> Relais ethernet</a></li>';
}


function ethernetRelay_plugin_widget(&$widgets){
		$widgets[] = array(
		    'uid'      => 'dash_ethernetRelay',
		    'icon'     => 'fa fa-flash',
		    'label'    => 'Relais Ethernet',
		    'background' => '#50597b', 
		    'color' => '#fffffff',
		    'onLoad'   => 'action.php?action=ethernetRelay_load_widget',
		    'onEdit'   => 'action.php?action=ethernetRelay_edit_widget',
		    'onSave'   => 'action.php?action=ethernetRelay_save_widget',
		);
}



Plugin::addCss("/css/main.css"); 
Plugin::addJs("/js/main.js"); 


//Lie ethernetRelay_plugin_setting_page a la zone réglages
Plugin::addHook("setting_bloc", "ethernetRelay_plugin_setting_page");
//Lie ethernetRelay_plugin_setting_menu au menu de réglages
Plugin::addHook("setting_menu", "ethernetRelay_plugin_setting_menu"); 
//Lie ethernetRelay_plugin_action a la page d'action qui perme d'effecuer des actionx ajax ou ne demdnant pas de retour visuels
Plugin::addHook("action_post_case", "ethernetRelay_plugin_action");    
//Lie ethernetRelay_plugin_vocal_command a la gestion de commandes vocales proposées par yana
Plugin::addHook("vocal_command", "ethernetRelay_plugin_vocal_command");
//Lie ethernetRelay_plugin_widget aux widgets de la dashboard
Plugin::addHook("widgets", "ethernetRelay_plugin_widget");

?>
