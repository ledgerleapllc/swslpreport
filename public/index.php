<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');

if(!is_logged_in()) {
	header('location:/login');
	exit();
}

$a = _request('a');
$action = _request('action');

if(
	$action &&
	$action == 'poll_data' &&
	is_logged_in()
) {
	$polled = poll_nodes();
	elog($polled);
	exit($polled);
}

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
	<link rel="icon" sizes="32x32" href="/assets/images/32x32.png">
	<link rel="icon" sizes="64x64" href="/assets/images/64x64.png">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet"> 
	<style>
		body {
			font-family: 'Poppins', sans-serif;
			background-color: #fff;
		}

		h1,h2,h3 {
			font-weight: bold;
		}

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
			top: 0px;
			left: 0px;
			height: 114px;
			background: #3F51B5 0% 0% no-repeat padding-box;
			box-shadow: 0px 3px 6px #00000029;
			opacity: 1;
			border: none;
			position: relative;
			border-radius: 0;
		}

		.nav-inner {
			max-width: 1500px;
			display: flex; height: 100%;
			width: 100%;
			flex-direction: row;
			align-items: center;
			position: relative;
		}

		#logout-btn {
			color: #fff;
			font-size: 17px;
			position: absolute;
			right: 0;
			padding: 8px;
			cursor: pointer;
		}

		.door-icon {
			width: 19px;
			height: 19px;
		}

		.card {
			border: 1px solid #DDDDDD;
			background-color: #fff;
			border-radius: 3px;
			padding: 20px;
			box-shadow: 0px 3px 6px #00000029;
		}

		.btn-wrap {
			position: absolute;
			right: 15px;
		}

		#download-csv-btn,
		#refresh-btn
		{
			/*position: absolute;
			right: 15px;*/
			margin-left: 10px;
			background-color: #3F51B5;
			color: #fff;
			border: none;
			width: 188px;
			height: 52px;
			box-shadow: 0px 3px 6px #00000029;
			font-size: 15px;
		}

		#download-csv-btn:hover,
		#refresh-btn:hover
		{
			background-color: #2F41A5;
		}

		.btn-waiting-icon {
			width: 18px;
			height: 18px;
			margin-right: 5px;
			margin-top: -3px;
		}

		<?php
		for($i = 5; $i < 200; $i += 5) {
			echo ".pt".$i."{ padding-top: ".$i."px; }\n";
			echo ".pb".$i."{ padding-bottom: ".$i."px; }\n";
		}
		?>

	</style>
</head>
<body>
	<div class="container-fluid pt15 pb15 navbar">
		<div class="container-fluid nav-inner">
			<img src="/assets/images/logo-white.png" id="main-logo" style="height: 78%; width: auto; cursor: pointer;">
			<div id="logout-btn">
				<img src="/assets/images/feather.png" class="door-icon">
				&ensp;Log Out
			</div>
		</div>
	</div>

	<div class="container-fluid pt100" style="max-width: 1250px;">
		<div class="row" style="position: relative;">
			<div class="col-md-12" style="display: flex; flex-direction: row; align-items: center; position: relative;">
				<h2 style="margin: 0;">SWS LP Report Portal</h2>
				<div class="btn-wrap">
					<button id="download-csv-btn">Download CSV</button>
					<button id="refresh-btn">Refresh Table</button>
				</div>
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
var polling_data = false;

function populate_table_data() {
	try {
		nodes_data = JSON.parse(nodes_data);
	} catch(err) {
		nodes_data = {};
	}

	if(nodes_data.nodes) {
		nodes = nodes_data.nodes;
	}

	nodes_datatable = [];

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
}

populate_table_data();

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
	"pageLength": 100
});

$("#logout-btn").click(function() {
	window.location.href = '/logout';
});

$("#main-logo").click(function() {
	window.location.href = '/';
});

$("#refresh-btn").click(function() {
	// window.location.reload();
	if(!polling_data) {
		disable_poll_btn();

		$.ajax({
			url: '/',
			method: 'post',
			data: {
				action: 'poll_data'
			}
		})
		.done(function(res) {
			// console.log(res);
			try {
				var test = JSON.parse(res);

				if(test.nodes) {
					window.location.reload();
				}
			} catch(err) {
				nodes_data = {};
			}
		});
	}
});

function disable_poll_btn() {
	polling_data = true;
	$("#refresh-btn").css({
		"pointer-events": "none",
		"opacity": 0.7
	})
	.html(`
		<img src="/assets/images/wait.png" class="btn-waiting-icon">
		Polling
	`);
}

function enable_poll_btn() {
	polling_data = false;
	$("#refresh-btn").css({
		"pointer-events": "all",
		"opacity": 1
	})
	.html(`
		Refresh Table
	`);
}

$("#download-csv-btn").click(function() {
	var filteredRows = nodesTable.rows({filter: 'applied'}).data();
	// console.log(filteredRows);
	// console.log(filteredRows.length);
	var datestamp = moment.unix(Date.now() / 1000 | 0).format("MM/DD/YYYY");
	var fname = 'lp-report-'+datestamp;
	var searchstring = $('.dataTables_filter input').val();

	if(
		searchstring &&
		searchstring != ''
	) {
		fname = fname + '-search-' + searchstring;
	}

	exportToCsv(fname+'.csv', filteredRows);
});

function exportToCsv(filename, rows) {
	var processRow = function (row) {
		var finalVal = '';
		for (var j = 0; j < row.length; j++) {
			var innerValue = row[j] === null ? '' : row[j].toString();
			if (row[j] instanceof Date) {
				innerValue = row[j].toLocaleString();
			};
			var result = innerValue.replace(/"/g, '""');
			if (result.search(/("|,|\n)/g) >= 0)
				result = '"' + result + '"';
			if (j > 0)
				finalVal += ',';
			finalVal += result;
		}
		return finalVal + '\n';
	};

	var csvFile = 'ID,"Tranche ID",Address,Balance,Name,Country,"Physical Address"'+"\n";
	for (var i = 0; i < rows.length; i++) {
		csvFile += processRow(rows[i]);
	}

	var blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
	if (navigator.msSaveBlob) { // IE 10+
		navigator.msSaveBlob(blob, filename);
	} else {
		var link = document.createElement("a");
		if (link.download !== undefined) { // feature detection
			// Browsers that support HTML5 download attribute
			var url = URL.createObjectURL(blob);
			link.setAttribute("href", url);
			link.setAttribute("download", filename);
			link.style.visibility = 'hidden';
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		}
	}
}

//});

</script>
</body>
</html>