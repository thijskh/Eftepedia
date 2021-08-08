<?php
$wgHooks['BeforeDisplayNoArticleText'][] = 'EftepediaCustom404::onBeforeDisplayNoArticleText';

class EftepediaCustom404
{
	public static function onBeforeDisplayNoArticleText($article) {

		$text = "<div class='noarticletext'>\n==Niet gevonden (404)==
Dit lemma bestaat (nog) niet op {{SITENAME}}.

U kunt [[Speciaal:Zoeken/{{PAGENAME}}|naar deze tekst zoeken]] in &eacute;&eacute;n van de {{NUMBEROFARTICLES}} wel bestaande lemma's of uw ontdekkingstocht voortzetten op de [[Hoofdpagina]].

Bent u van mening dat er iets mis is, of dat dit lemma ontbreekt op {{SITENAME}}? Neemt u dan contact op via het adres in de voettekst van deze pagina.</div>\n";
		$article->getContext()->getOutput()->addWikiText($text);
		return false;
	}
}
