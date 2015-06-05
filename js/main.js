$(document).ready(function(){



});




function plugin_ethernetRelay_set_icon(element,icon){
	$(element).parent().find('i').removeClass('btn-success');
	$('#iconethernetRelay').val(icon);
	$(element).addClass('btn-success');
}

//Ajout / Modification
function plugin_ethernetRelay_save(element){
	var form = $(element).closest('fieldset');
 	var data = form.toData();
 	data.action = 'ethernetRelay_save_ethernetRelay'
	$.action(data,
		function(response){
			alert(response.message);
			form.find('input').val('');
			location.reload();
		}
	);
}

//Supression
function plugin_ethernetRelay_delete(id,element){

	if(!confirm('Êtes vous sûr de vouloir faire ça ?')) return;
	$.action(
		{
			action : 'ethernetRelay_delete_ethernetRelay', 
			id: id
		},
		function(response){
			$(element).closest('tr').fadeOut();
		}
	);

}
