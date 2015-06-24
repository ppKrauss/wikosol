# wikosol
Wiki da ECOSOL , preliminar em http://xmlfusion.org/wikosol

## Ferramentas
Recursos para gestão e normalização de metadados sobre [legislação brasileira numa Mediawiki](http://xmlfusion.org/wikosol/Marco_regulat%C3%B3rio). Os algoritmos de normalização e gestão de dados estão na ordem típica de utilização:

* `tplNormas_rewrite.php` - refaz a página Wiki com um mínimo de intervenções, focando na normalização dos metadados e inclusão de uma seção de tags.
* `tplNormas_exportCSV.php` - exporta todos os metadados das leis numa planilha formato `.tsv` ou `.csv`.
* `tplNormas_mergimportCSV.php` - importa de volta a planilha, já fazendo uma "mescla" (merge) com os metadados existentes. 

O arquivo `.tsv`  exemplifica o uso, o arquivo `MediawikiNavigator.php` é uma versão adaptada do [projeto de mesmo nome](https://github.com/ppKrauss/MediawikiNavigator).

## MediaWIki

... como foi instalada, extenções ... e previsões futuras...
