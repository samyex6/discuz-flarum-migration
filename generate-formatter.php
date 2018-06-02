<?php

include_once __DIR__ . '/vendor/autoload.php';

use s9e\TextFormatter\Configurator\Bundles\MediaPack;

error_reporting(E_ALL);

$configurator = new s9e\TextFormatter\Configurator;
$configurator->loadBundle('Forum');
$configurator->tags->onDuplicate('replace');
$configurator->BBCodes->onDuplicate('replace');

(new MediaPack)->configure($configurator);


$emoticons = [':)', ':-)', ';)', ';-)', ':D', ':-D', ':(', ':-(', ':-*', ':P', ':-P', 
              ':p', ':-p', ';P', ';-P', ';p', ';-p', ':?', ':-?', ':|', ':-|', ':o'];
foreach ($emoticons as $e)
    $configurator->Emoji->removeAlias($e);

$configurator->BBCodes->addFromRepository('HR');
$configurator->BBCodes->addFromRepository('ALIGN');
$configurator->BBCodes->addFromRepository('B');
$configurator->BBCodes->addFromRepository('I');
$configurator->BBCodes->addFromRepository('U');
$configurator->BBCodes->addFromRepository('S');
$configurator->BBCodes->addFromRepository('EMAIL');
$configurator->BBCodes->addFromRepository('CODE');
$configurator->BBCodes->addFromRepository('QUOTE');
$configurator->BBCodes->addFromRepository('LIST');
$configurator->BBCodes->addFromRepository('DEL');
$configurator->BBCodes->addFromRepository('COLOR');
$configurator->BBCodes->addFromRepository('CENTER');
$configurator->BBCodes->addFromRepository('SIZE');
$configurator->BBCodes->addFromRepository('*');
//$configurator->BBCodes->addCustom('[url={URL;useContent}]{TEXT}[/url]', '<a href="{URL}">{TEXT}');
$configurator->BBCodes->addCustom(
    '[IMG src={URL;useContent} width={UINT;optional} height={UINT;optional}]{URL}[/IMG]', 
    '<img>
		<xsl:attribute name="src">{@src}</xsl:attribute>
		<xsl:if test="@width"><xsl:attribute name="width">{@width}</xsl:attribute></xsl:if>
		<xsl:if test="@height"><xsl:attribute name="height">{@height}</xsl:attribute></xsl:if>
		<xsl:apply-templates/>
    </img>');

$configurator->BBCodes->addCustom(
    '[TABLE width={UINT;optional}]{ANYTHING}[/TABLE]', 
    '<table>
        <xsl:if test="@width">
			<xsl:attribute name="width">{@width}%</xsl:attribute>
		</xsl:if>
		{ANYTHING}
    </table>');
$configurator->BBCodes->addCustom('[TR]{ANYTHING}[/TR]', '<tr>{ANYTHING}</tr>');
$configurator->BBCodes->addCustom(
    '[TH colspan={UINT;optional} rowspan={UINT;optional} width={UINT;optional}]{ANYTHING}[/TH]', 
    '<th>
	    <xsl:if test="@colspan"><xsl:attribute name="colspan">{@colspan}</xsl:attribute></xsl:if>
	    <xsl:if test="@rowspan"><xsl:attribute name="rowspan">{@rowspan}</xsl:attribute></xsl:if>
	    <xsl:if test="@width"><xsl:attribute name="width">{@width}</xsl:attribute></xsl:if>
	    {ANYTHING}
    </th>');
$configurator->BBCodes->addCustom(
    '[TD colspan={UINT;optional} rowspan={UINT;optional} width={UINT;optional}]{ANYTHING}[/TD]', 
    '<td>
	    <xsl:if test="@colspan"><xsl:attribute name="colspan">{@colspan}</xsl:attribute></xsl:if>
	    <xsl:if test="@rowspan"><xsl:attribute name="rowspan">{@rowspan}</xsl:attribute></xsl:if>
	    <xsl:if test="@width"><xsl:attribute name="width">{@width}</xsl:attribute></xsl:if>
	    {ANYTHING}
    </td>');

$configurator->BBCodes->addCustom(
    '[PSHUFFLE height={UINT;optional} width={UINT;optional}]{SIMPLETEXT}[/PSHUFFLE]',
    '<img src="https://s.pokeuniv.com/pokemon/shuffle/{SIMPLETEXT}.png" width="{@width}" height="{@height}">'
);

$configurator->BBCodes->addCustom(
    '[PICON]{SIMPLETEXT}[/PICON]',
    '<img src="https://s.pokeuniv.com/pokemon/icon/{SIMPLETEXT}.png">'
);

$configurator->BBCodes->addCustom(
    '[UPL-FILE uuid={IDENTIFIER} size={SIMPLETEXT2}]{SIMPLETEXT1}[/UPL-FILE]',
    '<div class="ButtonGroup">
        <div class="Button hasIcon Button--icon Button--primary flagrow-download-button" data-uuid="{@uuid}"><i class="fa fa-download"></i></div>
        <div class="Button">
            {SIMPLETEXT1}
        </div>
        <div class="Button">
            <xsl:value-of select="@size"/>
        </div>
    </div>'
);

$configurator->BBCodes->addCustom('[upl-image-preview url={URL}]', '<img src="{@url}" title="{@base_name}" />');

$tagName = 'POSTMENTION';
$tag = $configurator->tags->add($tagName);
$tag->attributes->add('username');
$tag->attributes->add('number')->filterChain->append('#uint');
$tag->attributes->add('discussionid')->filterChain->append('#uint');
$tag->attributes->add('id')->filterChain->append('#uint');
$tag->attributes['number']->required = false;
$tag->attributes['discussionid']->required = false;
$tag->filterChain
    ->prepend('addId')
    ->setJS('function() { return true; }');
$tag->template = '<a href="{$DISCUSSION_URL}{@discussionid}/{@number}" class="PostMention" data-id="{@id}"><xsl:value-of select="@username"/></a>';
$configurator->Preg->match('/\B@(?<username>[\-_a-zA-Z0-9\x{0800}-\x{9fa5}]+)#(?<id>\d+)/iu', $tagName);

function addId($tag) {
    return true;
}
$configurator->BBCodes->setRegexpLimit(500000);
$configurator->tags['PSHUFFLE']->tagLimit = 5000;
$configurator->tags['TR']->tagLimit       = 5000;
$configurator->tags['TH']->tagLimit       = 5000;
$configurator->tags['TD']->tagLimit       = 50000;
$configurator->tags['IMG']->tagLimit      = 5000;

$configurator->rendering->setEngine('PHP', './library/TextBundle');
$configurator->saveBundle('TextFormatter', './library/TextBundle/TextFormatter.php');

//extract($configurator->finalize());
//echo $parser->parse('[pshuffle height=1 width=2]asd[/pshuffle]');
