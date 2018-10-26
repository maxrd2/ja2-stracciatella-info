<?php

error_reporting(E_ALL & ~E_NOTICE);

// taken from Points.h
define('DEFAULT_APS', 20);
define('DEFAULT_AIMSKILL', 80);
define('AP_BURST', 5);
define('AP_MAXIMUM', 25);

$weapons = json_decode(file_get_contents('https://raw.githubusercontent.com/maxrd2/ja2-stracciatella/master/assets/externalized/weapons.json'));

if(php_sapi_name() == 'cli') {
	ob_start();
} else {
	$path = '../docs/';
}

echo '<!doctype html>';
echo '<html>';
echo '<head>';
echo '<title>Jagged Alliance 2 - Stracciatella - Weapon Stats Table</title>';
echo '<link href="https://fonts.googleapis.com/css?family=Oxygen" rel="stylesheet">';
echo '<link href="' . $path . 'ja2-style.css" rel="stylesheet">';
echo '<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>';
echo '<script src="' . $path . 'jquery.tablesorter.js" type="text/javascript"></script>';
echo '<script type="text/javascript">';
echo <<<EOJS
$(function(){ 
	var table = $('.weapon-list');
    table.tablesorter({
		sortList: [[1, 0], [22, 0]],
		headers: {
			2: { sorter: 'digit' },
			3: { sorter: 'digit' },
			4: { sorter: 'digit' },
			5: { sorter: 'digit' },
			6: { sorter: 'digit' },
			7: { sorter: 'digit' },
			8: { sorter: 'digit' },
			9: { sorter: 'digit' },
			11: { sorter: 'digit' },
			12: { sorter: 'digit' },
			20: { sorter: 'digit' },
			22: { sorter: 'currency' }
		}
    });
    
    $('.row-filter').on('change.row-filter', function(){
		table.find('>tbody>tr').hide().filter('.' + $(this).val().replace(/\s+/, ',.')).show();
    }).trigger('change.row-filter');
}); 
EOJS;
echo '</script>';
echo '</head>';
echo '<body>';

$weaponType = [
	'PISTOL' => 'Pistol',
	'M_PISTOL' => 'Pistol',
	'SMG' => 'SMG',
	'SN_RIFLE' => 'Sniper',
	'ASRIFLE' => 'Assault',
	'RIFLE' => 'Rifle',
	'SHOTGUN' => 'Shotgun',
	'LMG' => 'LMG',
	'BLADE' => 'Blade',
	'THROWINGBLADE' => 'Throwing',
	'PUNCHWEAPON' => 'Melee',
	'LAUNCHER' => 'Launcher',
	'LAW' => 'LAW',
	'CANNON' => 'Cannon',
];

$weaponTypeFilter = [];
foreach($weaponType as $c => $t) {
	if(!isset($weaponTypeFilter[$t]))
		$weaponTypeFilter[$t] = '<option value="type-' . strtolower($c) . '">' . $t . '</option>';
	else
		$weaponTypeFilter[$t] = substr($weaponTypeFilter[$t], 0, 15) . 'type-' . strtolower($c) . ' ' . substr($weaponTypeFilter[$t], 15);
}
$weaponTypeFilter = '<select class="row-filter">' . implode('', $weaponTypeFilter) . '</select>';

$ammoType = [
	'' => '',
	'NOAMMO' => '',
	'AMMO38' => '.38 cal',
	'AMMO9' => '9mm',
	'AMMO45' => '.45 cal',
	'AMMO357' => '.357 cal',
	'AMMO12G' => '12 gauge',
	'AMMOCAWS' => 'CAWS',
	'AMMO545' => '5.45mm',
	'AMMO556' => '5.56mm',
	'AMMO762N' => '7.62 NATO',
	'AMMO762W' => '7.62 WP',
	'AMMO47' => '4.7mm',
	'AMMO57' => '5.7mm',
	'AMMOMONST' => 'Monster',
	'AMMOROCKET' => 'Rocket',
	'AMMODART' => 'Dart',
	'AMMOFLAME' => 'Fuel',
];

echo '<h1>Jagged Alliance 2 - Stracciatella - Weapon Stats Table</h1>';

echo '<div class="filters">';
echo 'Weapon Type: ' . $weaponTypeFilter;
echo '</div>';

echo '<table class="weapon-list">';

echo '<thead>';
echo '<tr>';
echo '<th rowspan="2">Name</th>';
echo '<th rowspan="2">Type</th>';
echo '<th colspan="3">Rate of Fire</th>';
echo '<th rowspan="2">Damage</th>';
echo '<th rowspan="2">Range</th>';
echo '<th colspan="2">Volume</th>';
echo '<th colspan="2">Ammo</th>';
echo '<th rowspan="2">Reliability</th>';
echo '<th rowspan="2">Repairability</th>';
echo '<th colspan="7">Attachments</th>';
echo '<th rowspan="2">Weight</th>';
echo '<th rowspan="2">Size</th>';
echo '<th rowspan="2">Price</th>';
echo '</tr>';
echo '<tr>';
echo '<th>Single</th>';
echo '<th>Burst</th>';
echo '<th>Ready</th>';
echo '<th>Fire</th>';
echo '<th>Hit</th>';
echo '<th>Amt</th>';
echo '<th>Type</th>';
echo '<th class="small">Bipod</th>';
echo '<th class="small">Sniper</th>';
echo '<th class="small">Laser</th>';
echo '<th class="small">Silencer</th>';
echo '<th class="small">Spring</th>';
echo '<th class="small">Barrel</th>';
echo '<th class="small">Grenade</th>';
echo '</tr>';
echo '</thead>';

echo '<tbody>';
foreach($weapons as $w) {
	if($w->internalType == 'NOWEAPON' || $w->bDefaultUndroppable)
		continue;

	$classes = [
		'type-' . strtolower($w->internalType),
	];
	
	echo '<tr class="' . implode(' ', $classes) . '">';
	echo '<td>' . preg_replace('!_!', ' ', $w->internalName) . '</td>';
	echo '<td class="center">' . $weaponType[$w->internalType] . '</td>';
	
	// rate of fire
	$sTop = (int)(DEFAULT_APS * 2);
	$sBottom = (int)((50 + DEFAULT_AIMSKILL / 2) * $w->ubShotsPer4Turns / 4);
	$ubAttackAPs = (int)((100 * $sTop / $sBottom + 1) / 2);
	$ubBurstAPs = $ubAttackAPs + (int)($w->internalName == 'G11' ? 1 : max(3, (AP_BURST * DEFAULT_APS + (AP_MAXIMUM - 1)) / AP_MAXIMUM));
	
	echo '<td class="center">' . $ubAttackAPs . 'ap' . '</td>';
	echo '<td class="center">' . ($w->ubShotsPerBurst > 0 ? $ubBurstAPs . 'ap (' . $w->ubShotsPerBurst . 'x)' : '') . '</td>';
	echo '<td class="center">' . ($w->ubReadyTime ? '+' . $w->ubReadyTime : '') . '</td>';
	
	echo '<td class="center">' . $w->ubImpact . ' (' . $w->ubDeadliness . ')' . '</td>';
	
	echo '<td class="center">' . max(1, $w->usRange / 10) . 'm' . '</td>';
	
	// volume
	echo '<td class="center">' . $w->ubAttackVolume . 'dB' . '</td>';
	echo '<td class="center">' . $w->ubHitVolume . 'dB' . '</td>';
	
	// ammo
	echo '<td class="center">' . $w->ubMagSize . '</td>';
	echo '<td class="center">' . $ammoType[$w->calibre] . '</td>';
	
	echo '<td class="center">' . $w->bReliability . '</td>';
	
	echo '<td class="center">' . $w->bRepairEase . ($w->bRepairable ? ' yes' : ' no') . '</td>';
	
	// attachments
	$checkYes = '<svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="#00aa00" d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" /></svg>';
	$checkNo = '<svg style="width:24px;height:24px;opacity:.15" viewBox="0 0 24 24"><path fill="#aa0000" d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" /></svg>';
	echo '<td class="center"><span title="Bipod">' . ($w->attachment_Bipod ? $checkYes : $checkNo) . '</span></td>';
	echo '<td class="center"><span title="Sniper Scope">' . ($w->attachment_SniperScope ? $checkYes : $checkNo) . '</span></td>';
	echo '<td class="center"><span title="Laser Scope">' . ($w->attachment_LaserScope ? $checkYes : $checkNo) . '</span></td>';
	echo '<td class="center"><span title="Silencer">' . ($w->attachment_Silencer ? $checkYes : $checkNo) . '</span></td>';
	echo '<td class="center"><span title="Spring and Bolt">' . ($w->attachment_SpringAndBoltUpgrade ? $checkYes : $checkNo) . '</span></td>';
	echo '<td class="center"><span title="Gun Barrel Extender">' . ($w->attachment_GunBarrelExtender ? $checkYes : $checkNo) . '</span></td>';
	echo '<td class="center"><span title="Underbarrel Grenade Launcher">' . ($w->attachment_UnderGLauncher ? $checkYes : $checkNo) . '</span></td>';

	$ubWeight = max(0.1, $w->ubWeight / 10 /** 2.2*/);
	echo '<td class="right">' . $ubWeight . 'kg' . '</td>';
	echo '<td class="center">' . ($w->ubPerPocket ? 'Small (' . ($w->ubPerPocket . 'x') . ')' : (!$w->bTwoHanded ? 'Single' : 'Double')) . '</td>';
	echo '<td class="right">' . '$' . $w->usPrice . '</td>';
// 	echo '<td><pre>';
// 	print_r($w);
// 	echo '</pre></td>';
	echo '</tr>';
}
echo '</tbody>';

echo '</table>';

echo '</body>';
echo '</html>';

if(php_sapi_name() == 'cli') {
	$outFile = realpath(__DIR__ . '/../docs/') . '/' . basename(__FILE__, '.php') . '.html';
	file_put_contents($outFile, ob_get_clean());
	echo "Wrote output to $outFile.\n";
}
