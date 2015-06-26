<?php
/**
 * Import specifyed columns
 * use: php tplNormas_mergeCSV.php > wikitext.txt
 * tplNormas_export v1 2015-06-23
 */
// alternativa: usar os campos 'csv_' da exportação como chaves para o merge


// CONFIGS:
  $normalize = TRUE;
  $urlWiki = 'http://xmlfusion.org/wikosol';  // or php://stdin or filename
  $P       = 'Levantamento_das_pe%C3%A7as_legislativas';
  $csvFile = 'data/ecosol-marcoRegulatorio2015-levantamento2.tsv';
  $DEBUG = 0;
  $MAIN_PARAMS  = array('local','autoridade','ementa','tags','url-fonte','url-transcricao');

$wikitext_pk=array();
$wikitext_err='';

include "MediawikiNavigator.php";

$mn = new MediawikiNavigor($urlWiki);
$mn->wikitext_normalizeConfig['CACHE'] = function ($i,&$p) use (&$wikitext_pk) {
	$tituloFull = isset($p['pretitulo'])? "{$p['pretitulo']} {$p['#1']}": $p['#1'];
	$pk = clean_title($tituloFull,isset($p['kx_ano'])?$p['kx_ano']:'');
	if (isset($wikitext_pk[$pk]))
		file_put_contents('php://stderr', "\n wikitext REPETIU $tituloFull\n",FILE_APPEND);
	else
		$wikitext_pk[$pk]=$p;
}; // func


$mn->getByTitle('raw',$P);
$mn->wikitextTpl_tokenize(TRUE);
$mn->wikitextTpl_normalize_tpls(TRUE);

// // // // // // // //
// // BEGIN:ANALYSIS

ksort($wikitext_pk);

$csv_rows=array();
//$txt = file_get_contents($csvFile);
$row = 0;
if (($handle = fopen($csvFile, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 2000, "\t")) !== FALSE){
	//0prj	1local	2autoridade	3tipo	4Ano	5cod	6Norma	status	viavel	Nota	Tags	Ementa
	$pk = clean_title($data[6],$data[4]);
	if ($row++) {
		if ($DEBUG) print "\n--csv-$row= $pk";
		if (isset($csv_rows[$pk]))
			die("\n--- REPETIU linha '$pk' ----\n");
		elseif ($pk)
			$csv_rows[$pk]=$data;
	} else
		$csv_rows['#header']=$data;

    }//while
    fclose($handle);
}
$csv_rows_header = array_flip($csv_rows['#header']); // access wrapper
$csv_rows_header2 = $csv_rows['#header'];

$i=0;
$newRows_atPlanilha = array();
foreach($wikitext_pk as $k=>$v){
	$i++;
	$ok = '??';
	if (isset($csv_rows[$k])) { 
		$ok = 2;
		if ($DEBUG) print "\n-- $k ($i) = OK! fazendo merge:";
		$wikitext_pk[$k]['local'] = $csv_rows[$k][$csv_rows_header['local']];
		$wikitext_pk[$k]['autoridade'] = $csv_rows[$k][$csv_rows_header['autoridade']];
		$wikitext_pk[$k]['kx_tipo'] = $csv_rows[$k][$csv_rows_header['tipo']];
		$wikitext_pk[$k]['kx_cod'] = $csv_rows[$k][$csv_rows_header['cod']];
		//var_dump($wikitext_pk[$k]);
		$mn->wikitext_tpls[$wikitext_pk[$k]['#idx']]=$wikitext_pk[$k]; // perigo
	} else {
		file_put_contents('php://stderr', "\n--!err! '$k' ficou de fora da planilha\n",FILE_APPEND);
		$newRows_atPlanilha[$k]=$v;
	}
} // for


// // END:ANALYSIS
// // // // // // //


// BEGIN:GENERATE NEW TPLs
print "\n----BEGIN:GENERATE NEW TPLs----\n";
foreach($csv_rows as $pk=>$v) if ($pk!='#header' && !isset($wikitext_pk[$pk])) {
	$params = csv_rows_header2params($v,array(
		'Ementa'=>'ementa', 'Ano'=>'kx_ano', 'Norma'=>'#1', 'Tags'=>'tags', 
		'cod'=>'kx_cod', 'tipo'=>'kx_tipo'
	));
	if (isset($params['kx_ano'])) {
		if (!isset($params['#2'])) 
			$params['#2'] = $params['kx_ano'];
		$tplName='norma';
		if ((isset($params['prj']) && $params['prj']) || strpos($pk,'PROJETO')!==FALSE)
			$tplName='prjnorma';
		print "\n\n".$mn->wikitextTpl_untokenize1Tpl($params,$tplName,$MAIN_PARAMS);
	}
}
print "\n----END:GENERATE NEW TPLs----\n";
// END:GENERATE NEW TPLs


$mn->wikitextTpl_untokenize($MAIN_PARAMS);
print $mn->wikitext;


if ($DEBUG) {
	print "\n---- de fora do wikitext ---\n";
	foreach($csv_rows as $k=>$v) if ($k!='#header' && !isset($wikitext_pk[$k]))
			print "\n* $k ";
}

///////////////////////////
// // // EXTRA LIB // // //

function csv_rows_header2params($row,$tr) {
	global $csv_rows_header2; // idx2name
	$params = array();
	foreach($row as $idx=>$v) {
		$name = $csv_rows_header2[$idx];
		if (isset($tr[$name]))
			$params[$tr[$name]]=$v;
		else
			$params[$name]=$v;
	}
	return $params;
}

function clean_title($a,$b) {
	$pk = mb_strtoupper($a,'UTF-8')." - $b";
	$pk = preg_replace('/Nº| NUM\.| NUM | NO |[,;\-\'"]+/',' ',$pk);
	$pk = preg_replace('/\.+/','',$pk);
	return trim(preg_replace('/\s+/',' ',$pk));
}

