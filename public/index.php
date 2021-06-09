<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');

$a = _request('a');

if($a) {
	elog($a);
	elog("\n");
}

$nodes = get_nodes();
// elog($nodes);

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo getenv('APP_NAME'); ?></title>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/datatable.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/datatable.responsive.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/datatable.scroller.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/iziToast.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/ui.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/materialdesignicons.min.css">
	<link rel="icon" sizes="32x32" href="/assets/images/favicon32x32.png">
	<link rel="icon" sizes="64x64" href="/assets/images/favicon64x64.png">
	<style>
		tr {
			cursor: pointer;
		}

		.form-control {
			margin-top: 8px;
		}

		.map-container {
			overflow: hidden;
			padding-bottom: 56.25%;
			position: relative;
			height: 0;
		}

		.map-container iframe {
			left: 0;
			top: 0;
			height: 100%;
			width: 100%;
			position: absolute;
		}

		.navbar {
			border-bottom: 1px solid #e6e6e6;
			background-color: #fff;
			box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
		}

		.card {
			border-bottom: 1px solid #e6e6e6;
			background-color: #fff;
			border-radius: 6px;
			padding: 20px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
		}

		<?php
		for($i = 5; $i < 200; $i += 5) {
			echo ".pt".$i."{ padding-top: ".$i."px; }\n";
			echo ".pb".$i."{ padding-bottom: ".$i."px; }\n";
		}
		?>

	</style>
</head>
<body style="background-color: #f4f5f6;">
	<header>
		<div class="container-fluid pt15 pb15 navbar">
			<button id="logout-btn" class="btn btn-primary" style="float: right;">
				Log Out
			</button>
		</div>
	</header>

	<div class="container-fluid pt60" style="max-width: 1300px;">
		<div class="row">
			<div class="col-md-12">
				<h1>Header</h1>
			</div>
		</div>
		<div class="row pt30">
			<div class="col-md-12">
				<div class="card">
					<table class="table" id="nodes-table">
						<thead class="blue lighten-4">
							<tr>
								<th>ID</th>
								<th>Tranche Id</th>
								<th>Address</th>
								<th>Balance</th>
								<th>Name</th>
								<th>Country</th>
								<th>Physical Address</th>
							</tr>
						</thead>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="pt100"></div>

<script src="/assets/js/jquery.min.js"></script>
<script src="/assets/js/moment.min.js"></script>
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/ui.js"></script>
<script src="/assets/js/iziToast.min.js"></script>
<script src="/assets/js/jquery.datatables.min.js"></script>
<script src="/assets/js/datatable.responsive.js"></script>
<script src="/assets/js/datatable.scroller.js"></script>
<script>

//$(document).ready(function() {

var nodes_data = '<?php echo $nodes; ?>';
var nodes = {};
var nodes_datatable = [];

try {
	nodes_data = JSON.parse(nodes_data);
} catch(err) {
	nodes_data = {};
}

if(nodes_data.nodes) {
	nodes = nodes_data.nodes;
}

Object.keys(nodes).forEach(function(key) {
	// console.log(key);
	// console.log(nodes[key]);
	let balance = parseFloat(nodes[key].balance) / (10**18);
	balance = Math.round(balance);
	balance = balance.toString();
	balance = balance + " sws"

	nodes_datatable.push([
		nodes[key].id,
		nodes[key].tranche_id,
		key,
		balance,
		nodes[key].full_name,
		nodes[key].country,
		nodes[key].physical_address,
	]);
});

var nodesTable = $('#nodes-table').DataTable({
	"info": true,
	"responsive": true,
	"data": nodes_datatable,
	"order": [[ 0, "asc" ]],
	"columnDefs": [
		{
			"targets": [0],
			"orderable": true
		}
	],
	"pageLength": 25
});

$("#logout-btn").click(function() {
	window.location.href = '/logout';
});

//});

</script>
</body>
</html>