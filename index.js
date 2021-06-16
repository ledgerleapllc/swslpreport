/* ***

06-08-21
@blockchainthomas

*** */

var path = require('path');
var fs = require('fs');
var abifile = 'abi.json';
var savefile = 'nodes.json';
var dotenv = require('dotenv').config();
var appname = process.env.APP_NAME;
var apikey = process.env.INFURA_API_KEY;
var sendfromaddress = process.env.SEND_FROM_ADDRESS;
var contract_address = process.env.CONTRACT_ADDRESS;
var Regex = require("regex");
var eth_regex = /^0x[a-fA-F0-9]{40}$/;
var Web3 = require('web3');
var web3 = new Web3(
	new Web3.providers.HttpProvider("https://rinkeby.infura.io/v3/" + apikey)
);

var do_not_include_before = 6;
var whitelist_array = {};
var chunk_size = 10;
var save;
var savejson;

// console.log(appname);

try {
	var abi = fs.readFileSync(abifile, 'utf8');
	var abijson = JSON.parse(abi);
} catch(err) {
	console.log(err);
	console.log("Could not find the contract ABI. Please make sure the file 'abi.json' exists and contains valid ABI JSON data in the root directory of this utility.");
	process.exit(1);
}

try {
	save = fs.readFileSync(savefile, 'utf8');
	savejson = JSON.parse(save);
	var check_count = savejson.node_count;
	var check_nodes = savejson.nodes;

	if(
		!check_count ||
		check_count == 0 ||
		!check_nodes
	) {
		throw "Invalid save file. Re-writing..";
	}
} catch(err) {
	savejson = {
		"total_supply": 0,
		"node_count": 0,
		"nodes": {}
	};

	fs.writeFileSync(
		savefile,
		JSON.stringify(savejson, null, "\t")
	);
}

if(savejson.node_count > 10) {
	savejson.node_count = savejson.node_count.toString();
	savejson.node_count = savejson.node_count.substring(
		0,
		savejson.node_count.length - 1
	);
	savejson.node_count = savejson.node_count + '0';
	savejson.node_count = parseInt(savejson.node_count);
}

// process.exit(0);

var SkywayContract = new web3.eth.Contract(
	abijson,
	contract_address
);

function delay(ms) {
	return new Promise(resolve => setTimeout(resolve, ms));
}

async function get_address_by_index(index) {
	try {
		let this_address = await SkywayContract.methods
			.whiteListedAddresses(index)
			.call()
		this_address = this_address.toLowerCase();
		console.log('Got address by index', index, ': ', this_address);

		if(!whitelist_array[this_address]) {
			let balance = await SkywayContract.methods
				.balanceOf(this_address)
				.call();

			if(balance > 0) {
				let address_details = await SkywayContract.methods
					.whiteListed(this_address)
					.call();

				// console.log(this_address);
				// console.log(address_details);
				// console.log(balance);

				if(index > do_not_include_before) {
					whitelist_array[this_address] = {
						"id": address_details.id,
						"full_name": address_details.fullname,
						"tranche_id": address_details.trancheId,
						"country": address_details.country,
						"physical_address": address_details.physicalAddress,
						"balance": balance
					};
				}

				if(address_details) {
					return 'iterating';
				}
			} else {
				return 'iterating';
			}
		} else {
			return 'iterating';
		}
	} catch(err) {
		// console.log(err);
		return 'stopped';
	}
}

async function do_chunk(index) {
	var chunk_finished = false;
	var res = '';
	var i = index;

	await delay(200);

	while(!chunk_finished) {
		if(res == 'stopped') {
			chunk_finished = true;
			// console.log(whitelist_array);
			// console.log(Object.keys(whitelist_array).length);
			savejson.node_count = Object.keys(whitelist_array).length;
			savejson.nodes = whitelist_array;

			fs.writeFileSync(
				savefile,
				JSON.stringify(savejson, null, "\t")
			);

			return 'finished';
		}

		if(
			i != index &&
			i % chunk_size == 0
		) {
			// console.log(i);
			// console.log(chunk_size);
			chunk_finished = true;
			return 'chuck_finished';
		}

		res = await get_address_by_index(i);
		i += 1;
	}
}

async function get_supply_cap() {
	var sc = await SkywayContract.methods
		.totalSupply()
		.call()
	return sc;
}

async function poll_data(callback = null) {
	whitelist_array = {};
	var finished = false;
	var res = '';
	var start_index = 0;
	var total_supply = 0;

	while(!finished) {
		if(res == 'finished') {
			finished = true;
			console.log('finished');
			total_supply = await get_supply_cap();

			var ret = {
				"total_supply": total_supply,
				"nodes": whitelist_array
			}

			savejson.total_supply = total_supply;

			fs.writeFileSync(
				savefile,
				JSON.stringify(savejson, null, "\t")
			);

			return ret;
		}

		if(res == 'chuck_finished') {
			console.log('------------- next chunk -------------');
		}

		res = await do_chunk(start_index);
		start_index += chunk_size;
	}

	total_supply = await get_supply_cap();
	savejson.total_supply = total_supply;

	fs.writeFileSync(
		savefile,
		JSON.stringify(savejson, null, "\t")
	);

	var ret = {
		"total_supply": total_supply,
		"nodes": whitelist_array
	}

	return ret;
}

// poll_data(function(res) {
// 	console.log(res);
// });

function asyncWrapper(fn) {
	return (req, res, next) => {
		return Promise.resolve(fn(req))
			.then((result) => res.send(result))
			.catch((err) => next(err))
	}
}

function get_data() {
	try {
		save = fs.readFileSync(savefile, 'utf8');
		savejson = JSON.parse(save);
		var check_count = savejson.node_count;
		var check_nodes = savejson.nodes;

		if(
			!check_count ||
			check_count == 0 ||
			!check_nodes
		) {
			throw "Invalid save file. Re-writing..";
		}
	} catch(err) {
		savejson = {
			"total_supply": 0,
			"node_count": 0,
			"nodes": {}
		};

		fs.writeFileSync(
			savefile,
			JSON.stringify(savejson, null, "\t")
		);
	}

	return savejson;
}


var express = require('express');
var throttle = require("express-throttle");
var app = express();
var port = 8088;
var rate_fast = "5/s";
var rate_slow = "5/h";
// app.use(express.json());

app.use(function(req, res, next) {
	res.setHeader(
		"Content-Security-Policy", 
		"script-src 'self'"
	);
	return next();
});

app.get(
	'/',
	throttle({
		"rate": rate_fast
	}),
	function(req, res) {
		res.send('');
	}
);

app.get(
	'/poll_data',
	throttle({
		"rate": rate_slow
	}),
	asyncWrapper(poll_data)
);

app.get(
	'/get_data',
	throttle({
		"rate": rate_fast
	}),
	function(req, res) {
		res.send(
			get_data()
		);
	}
);

console.log('Starting '+appname+' server on 127.0.0.1:'+port);
app.listen(port);