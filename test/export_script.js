$( "#selectCounty" )
		.click(function () {
			var countyName = "";
			$( "select option:selected" ).each(function() {
				$( "#selectCounty")
				countyName += $( this ).text();
		});
		$.ajax({
			url: 'countyData.php',
			type: "POST",
			data:({county: countyName}),
			success:function(result){
					$("#selectFields").html(result);
				}
		});
	});
	
$( "#selectFields" )
	.on("click", "#confirm", function(){
		var selectedFields = new Array();
		$("input:checked").each(function(){
			selectedFields.push($(this).val());
		});
		/*$.ajax({
			url:'do_export.php',
			type: "POST",
			data: ({selection: selectedFields}),
			success:function(result){
				alert(selectedFields + " passed to exporter!");
		}
		});*/
		$.ajax({
			url: 'checkboxes.php',
			type: "POST",
			data:({fields: selectedFields}),
			success:function(result){
				alert("success!");
				$("#selectFields").append(result);
			}
		});

});