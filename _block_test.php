<?php
// PROBLEM: Looping through block txs from Blockstream API intermittently returns garbled data

// Let's test with this known block hash

$block_hash = "00000000000000000020957e9c49fb476deeb5184c1698800827a1eae0ba6ceb";
$block_data = json_decode(file_get_contents("https://blockstream.info/api/block/".$block_hash),TRUE);
$block_height = $block_data['height'];
$tx_count = $block_data['tx_count'];

$block = array(); $count = 0;

// Start gathering block data starting with $offset = 0
getBlock(0);

// if we have block data, save to file
if(!empty($block)){
	echo "Saving block.txt<P>";	
	$fp = fopen("block.txt", "w");
	fwrite($fp, json_encode($block,TRUE)); 
	fclose($fp);	
}

// This function is triggered when something weird happens
function save($b){
	echo "<b>SOMETHING WENT WRONG.</b> Saving to _junk.txt<P>";	
	$fp = fopen("_junk.txt", "w");
	fwrite($fp, $b); 
	fclose($fp);	
}

// Gather block data into array
function getBlock($offset){
	global $block_hash, $block, $tx_count, $count;
	$url = "https://blockstream.info/api/block/".$block_hash."/txs/".$offset;
	$b = file_get_contents($url);
	$txs = json_decode($b,TRUE);
	if($count <= $tx_count){ 
		array_push($block, $txs); 
		$count += count($txs);
		$offset = $offset + 25;
		if(300 % $offset == 0) { usleep(1000000); } // sleep for 1 second every dozen pages (in case rate limit)
		if(count($txs) > 0){ getBlock($offset); }else{ save($b); }
	}
	if($count == $tx_count){ echo "Done"; }
}
?>