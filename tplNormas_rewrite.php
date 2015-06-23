<?php
/**
 * use: php tplNormas_rewrite.php > pagina.txt
 * tplNormas_rewrite v2 2015-06-23
 */


// CONFIGS:
  $normalize = TRUE;
  $urlWiki = 'http://xmlfusion.org/wikosol';
  $P       = 'Levantamento_das_pe%C3%A7as_legislativas';


include "MediawikiNavigator.php";
$mn = new MediawikiNavigor($urlWiki);
$mn->wikitext_normalizeConfig['CACHE'] = function (&$p) {
	if ( isset($p['#2']) && preg_match('~(\d\d)[/\-](\d\d)[/\-](\d\d\d\d)$~',$p['#2'],$m) ) {
		$ano = $p['kx_ano'] = $m[3];
		$p['#2'] = "$m[1]/$m[2]/$ano"; // normalized
	} else
		$p['kx_ano'] = '';
	$tituloFull = isset($p['pretitulo'])? "{$p['pretitulo']} {$p['#1']}": $p['#1'];
	if ( !isset($p['url-transcricao']) && !isset($p['url-fonte']) ) {
		if ($p['#name']=='norma') {    // lei ou outra Norma
			$p['kx_qDiarioLivre']=str_replace(' ','+',trim($tituloFull));
			list($p['kx_cadlem_tipo'],$p['kx_cadlem_num'])=str_urlCadlem($tituloFull);
		}  elseif ($p['kx_ano']) // Proposição Normativa
			$p['kx_qRadarMunicipal']=str_urlRadarMunicipal($tituloFull,$p['kx_ano']);
	} // if 
}; // func

if ($argc>1)
	echo $mn->get("/$P");
else {
	$mn->getByTitle('raw',$P);
	if ($normalize)
		$mn->wikitextTpl_normalize();
	echo $mn->wikitext;
}


///////////////////////////
// // // EXTRA LIB // // //

function str_urlCadlem($full) {
	$tipo = 'L';
	$num = 0;
	if (preg_match('/decreto/is',$full))
		$tipo = 'D';
	if (preg_match('/\d[\d\.]*/s',$full,$m))
		$num=str_replace('.','',$m[0]);
	return array($tipo,$num);
}

function str_urlRadarMunicipal($full,$ano) {
	$tipo = 'projeto-de-lei';
	$num = 0;
	if (preg_match('/\d[\d\.]+/s',$full,$m))
		$num=str_replace('.','',$m[0]);
	return "proposicoes/$tipo-$num-$ano"; // ex. http://www.radarmunicipal.com.br/proposicoes/projeto-de-lei-200-2015
}

