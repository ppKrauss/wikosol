Faz duas coisas: merge no que já tem, export no que não tem.

Chave condicional: primeiro carrega a array do wikitext. Se a chave-base é unica, resolvido, se não for única,
acrescenta a coluna condicional, só para aquele registro específico... o que cria um esquema de hierarquia.

<?php
/**
 * Import specifyed columns
 * use: php tplNormas_mergeCSV.php > wikitext.txt
 * tplNormas_export v1 2015-06-23
 */


// CONFIGS:
  $normalize = TRUE;
  $urlWiki = 'http://xmlfusion.org/wikosol';
  $P       = 'Levantamento_das_pe%C3%A7as_legislativas';


include "MediawikiNavigator.php";

$mn = new MediawikiNavigor($urlWiki);
$mn->wikitext_normalizeConfig['CACHE'] = function (&$p) {
	// prj	local	autoridade	tipo	Ano	cod	Norma	status	viavel	Nota	Tags	Ementa
	$tituloFull = isset($p['pretitulo'])? "{$p['pretitulo']} {$p['#1']}": $p['#1'];
	$p['csv_prj'] = ($p['#name']=='prjnorma')? 'x': '';
	$p['csv_local'] = isset($p['lex_local'])? $p['lex_local']: '??';
	$p['csv_autoridade'] = isset($p['lex_autoridade'])? $p['lex_autoridade']: '??';
	$p['csv_tipo'] = isset($p['lex_tipo'])? $p['lex_tipo']: '??';
	$p['csv_ano'] = isset($p['kx_ano'])? $p['kx_ano']: '??';
	$p['csv_cod'] = preg_replace('/[^\d]+/','',$tituloFull);
	$p['csv_Norma'] = $tituloFull;
	$p['csv_status'] = isset($p['status'])? $p['status']: '??';
	$p['csv_viavel'] = isset($p['viavel'])? $p['viavel']: '??';
	$p['csv_Nota'] = isset($p['nota'])? $p['nota']: '??';
	$p['csv_Tags'] = isset($p['tags'])? $p['tags']: '??';
	$p['csv_Ementa'] = isset($p['ementa'])? $p['ementa']: '??';
}; // func


$mn->getByTitle('raw',$P);
$mn->wikitextTpl_tokenize(TRUE);
$mn->wikitextTpl_normalize_tpls(TRUE);
echo $mn->wikitextTpl_expCSV('tsv');





///////////////////////////
// // // EXTRA LIB // // //





