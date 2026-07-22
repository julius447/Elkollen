<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: 
* @name: Ampy - Elkollen - Backend
* @type: PHP
* @status: published
* @created_by: 13
* @created_at: 2026-06-11 08:36:49
* @updated_at: 2026-07-22 14:28:10
* @is_valid: 1
* @updated_by: 13
* @priority: 10
* @run_at: all
* @load_as_file: 
* @load_in_block_editor: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php


/**
 * Ampy - Elkollen - Backend
 * Fluent Snippets: Run everywhere (all).
 *
 * Folds the standalone Elkollen plugin into one snippet, mirroring the battery
 * and LED delivery. The data file is baked in as the single source of truth
 * (edit the AMPYBKJSON block below to change copy/rules/links, then bump
 * AMPY_BK_VERSION). Data is injected inline as window.AmpyBK.data, so the JS
 * engine snippet runs unchanged. Provides: the [elkollen] / [behorighetskollen]
 * shortcodes, the crawlable server-rendered mount, dynamic OG meta per ?jobb=,
 * and the lead REST endpoint (off by default).
 *
 * LAUNCH GATE: the data still carries meta._pending_verification (an authorised
 * electrician must sign the 26-job matrix before public launch). While that is
 * set, public visitors see a short "under review" notice and only logged-in
 * editors can preview the tool. To go live, remove _pending_verification from
 * the data (and set reviewed_by + last_reviewed), or set AMPY_BK_FORCE_PUBLIC
 * to true below.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'AMPY_BK_VERSION' ) )      define( 'AMPY_BK_VERSION', '7.3.8' );
if ( ! defined( 'AMPY_BK_FORCE_PUBLIC' ) ) define( 'AMPY_BK_FORCE_PUBLIC', false ); // true = ignore the pending-verification gate
if ( ! defined( 'AMPY_BK_OG_URL' ) )       define( 'AMPY_BK_OG_URL', home_url( '/wp-content/uploads/elkollen/og/' ) );
if ( ! defined( 'AMPY_BK_OG_DIR' ) )       define( 'AMPY_BK_OG_DIR', WP_CONTENT_DIR . '/uploads/elkollen/og/' );
// The lead-magnet post that holds Elkollen's settings (Excel sheet + webhook +
// fallback email) in its post-editor metabox. Elkollen is embedded by shortcode
// on any page, so this constant tells the metabox, the data layer and the lead
// handler which post to read config from.
if ( ! defined( 'AMPY_BK_POST_ID' ) )      define( 'AMPY_BK_POST_ID', 58858 );


/* ==========================================================================
   1. DATA  (single source of truth - baked in, byte-identical to the plugin)
   ========================================================================== */
function ampy_bk_baked_data(): array {
    $json = <<<'AMPYBKJSON'
{"meta":{"version":"7.3.8","product_name":"Elkollen","page_heading":"Koppla elen","page_lead":"Se direkt vilka eljobb du får göra själv.","last_reviewed":"2026-06-XX","reviewed_by":"TBD: certified electrician (auktoriserad elinstallatör) before launch","_pending_verification":"","primary_source":"Elsäkerhetslagen (2016:732) & Elsäkerhetsverket","disclaimer":"Vägledning, inte juridisk rådgivning. Är du osäker, anlita ett registrerat elinstallationsföretag.","koppla_sakert_url":"https://www.kopplasakert.se/","verify_company_url":"https://www.elsakerhetsverket.se/kollaelforetaget/foretagsregister/?foretag=12047521&sok=1","ampy_offert_url":"https://ampy.se/offert/","contact_url":"https://ampy.se/offert/","privacy_url":"https://ampy.se/integritetspolicy/","cta_advice_label":"Boka kostnadsfri rådgivning","entry":{"prompt":"Var i hemmet gäller det?","all_label":"Alla eljobb","all_subline":"Hela listan, A till Ö","drawer_back":"Tillbaka","drawer_all_title":"Alla eljobb","_deprecated_eyebrow":"Får du fixa det själv?","_deprecated_search_label":"Sök eljobb","_deprecated_search_placeholder":"Sök eljobb – t.ex. vägguttag, laddbox, spis…","_deprecated_drawer_search_title":"Sökresultat","_deprecated_empty":"Inget jobb matchar. Prova ett annat ord eller välj ett rum.","list_subtitle":"Välj jobbet, så kollar vi vad som gäller i just ditt fall.","info_scenario":"Lagen skiljer på att byta något befintligt och att installera nytt. Ditt svar avgör vilken regel som gäller.","info_guess":"Beskedet bygger på Elsäkerhetslagen (2016:732), inte på vad som känns rimligt."},"lead_form":{"title":"Boka kostnadsfri rådgivning","intro":"Ampys behöriga elektriker hör av sig på telefon för din rådgivning!","submit":"Boka kostnadsfri rådgivning","submitting":"Skickar…","back":"Tillbaka till beskedet","fineprint":"Genom att trycka på \"Boka kostnadsfri rådgivning\" samtycker jag till att Ampy behandlar mina personuppgifter enligt vår integritetspolicy.","error_required":"Fyll i alla fält så ringer vi upp dig.","error_send":"Något gick fel. Ring oss på 010-265 79 79 så hjälper vi dig.","success_title":"Tack {namn}! Vi hör av oss.","success_title_noname":"Tack! Vi hör av oss.","success_body":"En behörig elektriker ringer upp dig på telefon, oftast inom en arbetsdag. Passa på att kolla ett annat eljobb medan du väntar.","success_back":"Kolla ett annat eljobb"},"quick_picks":["byta-vagguttag","byta-armatur-dcl","golvvarme","laddbox","spotlights","spis-ugn","byta-strombrytare","elcentral"],"_authored_questions_note":"[GAP] 5 authored two-option scenario questions remain after the v7.2 reversion (byta-gloldlampa, byta-armatur-dcl, jordfelsbrytare, badrum, koksrenovering): every option-to-verdict mapping is derived from the job's own summary/do/dont/why_text/tips and must be re-signed by the auktoriserad elinstallatör together with the existing matrix sign-off. The 10 v7 guess forks were removed in v7.2 (direct-verdict jobs again); no previously-fixed job that stays conditional carries a question flag.","source_line":"Källa: Elsäkerhetsverket & Elsäkerhetslagen (2016:732). Vägledning, inte juridisk rådgivning.","hero":{"h1":"Får du göra eljobbet själv? Kolla innan du kopplar.","h1_key":"Kolla innan du kopplar.","sub":"Välj installationen du funderar på och få ett tydligt besked direkt!","button_primary":"Kontakta oss","button_primary_url":"https://ampy.se/offert/","button_secondary":"010-265 79 79","button_secondary_url":"tel:+46102657979","mobile_ask":"Hellre prata med en elektriker direkt?","mobile_trust":"_deprecated","trust_3":"Få ett tydligt besked på några sekunder"},"_green_tips_note":"[GAP] 18 option-level green tip arrays authored 2026-07-15; practical safety guidance only, no legal claim; must be re-signed by the auktoriserad elinstallatör with the matrix (meta._pending_verification)."},"verdicts":{"green":{"label":"Det här får du göra själv","icon":"check","token":"--state-success","caveat":"Tillåtet om du vet hur du ska göra och bryter strömmen först. Är du det minsta osäker, ta in ett registrerat företag.","caveat_short":"Tillåtet om du vet hur. Är du det minsta osäker, ta in ett företag.","consequence":"Gör alltid jobbet strömlöst och kontrollera att allt sitter rätt. Känns något osäkert: avbryt och anlita ett registrerat elinstallationsföretag.","source":{"text":"Elsäkerhetsverket – Detta får du göra själv","url":"https://www.elsakerhetsverket.se/privatpersoner/detta-far-du-gora-sjalv-med-el/vad-far-jag-gora-sjalv-med-el/"},"caveat_planning":"Planera fritt! Själva installationen får bara ett registrerat elinstallationsföretag göra."},"yellow":{"_status":"SUPPORTED, currently unused. No job/option resolves to 'yellow' in this dataset; the badge, tab accent and CTA branch are kept ready for future conditional jobs whose answer is a genuine gray area. Intentional, not dead code.","label":"Det beror på","icon":"alert","token":"--state-warning","caveat":"Svaret beror på exakt vad du ska göra. Svara på frågan så ger vi rätt besked.","consequence":"I gränsfall: utgå från att det kräver ett registrerat företag tills du vet säkert.","source":{"text":"Elsäkerhetsverket – lekmannaarbete","url":"https://www.elsakerhetsverket.se/privatpersoner/detta-far-du-gora-sjalv-med-el/vad-far-jag-gora-sjalv-med-el/"}},"red":{"label":"Det här kräver elektriker","icon":"ban","token":"--state-error","caveat":"Detta är elinstallationsarbete och får enligt lag bara utföras av ett registrerat elinstallationsföretag.","consequence":"Att utföra elinstallationsarbete utan behörighet är ett brott enligt Elsäkerhetslagen (48 §). Straffet är böter eller fängelse i upp till ett år, oavsett om jobbet blir tekniskt rätt. Din hemförsäkring kan dessutom sätta ned eller neka ersättning om en skada beror på arbete som inte gjorts fackmannamässigt.","_consequence_verify":"Penalty citation: 48 § owner-verified 2026-07-16; the full consequence text above is the locked owner wording (restored in v7.2). The auktoriserad elinstallatör sign-off (meta._pending_verification) remains the launch gate.","source":{"text":"Elsäkerhetslagen (2016:732) 27 §","url":"https://www.riksdagen.se/sv/dokument-och-lagar/dokument/svensk-forfattningssamling/elsakerhetslag-2016732_sfs-2016-732/"},"lawbox_body":"_deprecated"}},"service_zones":["stockholm","solna","sundbyberg","nacka","huddinge","botkyrka","haninge","tyreso","taby","sollentuna","jarfalla","upplands-vasby","danderyd","lidingo","vaxholm","varmdo","ekero","sigtuna","marsta","upplands-bro","vallentuna","osteraker","akersberga","norrtalje","sodertalje","salem","nynashamn"],"rooms":[{"id":"badrum","label":"Badrum","icon":"bath","subline":"Belysning, våtrum","jobs":["badrum","badrumsrenovering","golvvarme","fast-armatur","vitvaror","byta-gloldlampa"]},{"id":"kok","label":"Kök","icon":"kitchen","subline":"Spis, vitvaror, uttag","jobs":["spis-ugn","vitvaror","koksrenovering","byta-vagguttag","byta-strombrytare","byta-armatur-dcl","spotlights","smarta-hem","byta-gloldlampa","golvvarme"]},{"id":"vardagsrum","label":"Vardagsrum","icon":"sofa","subline":"Lampor och uttag","jobs":["byta-armatur-dcl","fast-armatur","spotlights","byta-vagguttag","byta-strombrytare","inomhusbelysning","smarta-hem","luftvarmepump","byta-gloldlampa"]},{"id":"utomhus","label":"Utomhus","icon":"outdoor","subline":"Laddbox, solceller","jobs":["laddbox","solcellsbatterier","utomhusbelysning","dra-ny-kabel","luftvarmepump","byta-vagguttag"]},{"id":"elcentral","label":"Elcentral","icon":"panel","subline":"Säkringar, jordfel","jobs":["elcentral","jordfelsbrytare","lastbalansering","elbesiktning","felsokning","elrenovering","dra-ny-kabel","skarva-fast","laddbox","solcellsbatterier"]}],"jobs":[{"id":"byta-gloldlampa","label":"Byta glödlampa eller ljuskälla","icon":"bulb","service_page_url":"https://ampy.se/elservice/glodlampa/","type":"conditional","default_verdict":"green","choice_type":"scenario","question":"Vad gäller för lampan?","options":[{"label":"Byta ljuskällan i en befintlig armatur","clarifier":"Glödlampa, LED eller halogen, inne eller ute","verdict":"green","summary":"Att byta ljuskälla räknas inte som elinstallationsarbete.","do":"Byta ljuskälla i en befintlig armatur, inomhus eller utomhus.","dont":"Byta själva armaturen eller dra ny ledning kräver elektriker.","tips":[{"text":"Bryt strömmen vid strömbrytaren och låt ljuskällan svalna helt innan du rör den. Glödlampor och halogen blir brännheta.","allowed":true},{"text":"Matcha sockel och effekt (E27, E14, GU10) mot armaturens märkning. För hög effekt kan överhetta armaturen.","allowed":true},{"text":"Byter du till LED i en armatur med dimmer? Välj en ljuskälla märkt dimbar, annars kan den flimra.","allowed":true},{"text":"Är sockeln bränd, missfärgad eller sitter lampan löst? Bryt strömmen och låt en elektriker titta på armaturen.","allowed":false}]},{"label":"Sockeln är skadad eller ny ledning behövs","clarifier":"Bränd eller missfärgad sockel, ny dragning","verdict":"red","summary":"En bränd eller missfärgad sockel och ny ledning är ett jobb för ett registrerat elinstallationsföretag.","do":"Bryta strömmen och låta armaturen vara tills den är undersökt.","dont":"Fortsätta använda en armatur med skadad sockel eller dra ny ledning själv."}],"summary":"Att byta ljuskälla räknas inte som elinstallationsarbete.","do":"Byta ljuskälla i en befintlig armatur, inomhus eller utomhus.","dont":"Byta själva armaturen eller dra ny ledning kräver elektriker.","rule_citation":"Elsäkerhetsverket – tillåtet lekmannaarbete","why_text":"Att byta ljuskälla räknas inte som elinstallationsarbete. Matcha lampans wattal mot armaturen och bryt strömmen om du vill vara extra säker.","tips":[{"text":"Bryt strömmen och låt ljuskällan svalna helt innan du rör den. Glödlampor och halogen blir brännheta.","allowed":true},{"text":"Matcha effekt och sockel (E27, E14, GU10) mot armaturens märkning. För hög effekt kan överhetta armaturen.","allowed":true},{"text":"Byter du till LED i en armatur med dimmer? Välj en ljuskälla märkt dimbar, annars kan den flimra.","allowed":true},{"text":"Är sockeln bränd eller missfärgad? Bryt strömmen och låt en elektriker undersöka armaturen.","allowed":false}],"related_jobs":["byta-armatur-dcl","fast-armatur","inomhusbelysning"],"_authored_pending_signoff":"Option B derives from the job's own dont-row + the allowed:false tip (bränd sockel -> elektriker undersöker). NOTE for signoff: byta befintlig armatur i torrt rum is green in job fast-armatur; option B is therefore scoped to the DAMAGE/new-wiring case only."},{"id":"byta-armatur-dcl","label":"Byta taklampa med stickpropp (DCL)","chip_label":"Byta taklampa (DCL)","icon":"lamp","service_page_url":"https://ampy.se/elservice/armatur/","type":"conditional","default_verdict":"green","choice_type":"scenario","question":"Hur ser anslutningen i taket ut?","options":[{"label":"Det sitter ett DCL-uttag i taket","clarifier":"Den vita kopplingen som lampan klickas i","verdict":"green","summary":"Lampor med DCL-stickpropp räknas inte som fast installation.","do":"Byta en lampa med DCL-stickpropp i ett torrt rum.","dont":"Fast monterad armatur, våtrum eller ny ledning kräver elektriker.","tips":[{"text":"DCL-donet, den vita kopplingen i taket, är gjort för att du själv ska kunna klicka loss den gamla lampan och i den nya.","allowed":true},{"text":"Bryt ändå strömmen vid strömbrytaren först och kontrollera att armaturen verkligen sitter i ett DCL-don.","allowed":true},{"text":"Häng tunga armaturer i takkroken eller dosans fäste, aldrig i själva donet eller sladden.","allowed":true},{"text":"Sticker ledningarna ut direkt ur taket utan DCL-don, eller är det ett våtrum? Då kräver inkopplingen en elektriker.","allowed":false}]},{"label":"Ledningarna sticker ut direkt ur taket","clarifier":"Inget DCL-uttag, eller så är det ett våtrum","verdict":"red","summary":"Utan DCL-uttag är inkopplingen ett jobb för en elektriker.","do":"Låta ledningarna vara och planera var lampan ska sitta.","dont":"Koppla in armaturen direkt mot ledningarna i taket."}],"summary":"Lampor med DCL-stickpropp räknas inte som fast installation.","do":"Byta en lampa med DCL-stickpropp i ett torrt rum.","dont":"Fast monterad armatur, våtrum eller ny ledning kräver elektriker.","rule_citation":"Elsäkerhetsverket – byta fast ansluten ljusarmatur i torra utrymmen","why_text":"Att ansluta en lampa med stickpropp till ett befintligt lamputtag (DCL), eller byta lampproppen, räknas inte som fast installation. Gäller torra, icke brandfarliga rum.","source_quote":"Du får själv byta stickkontakten (lampproppen) på din lampa – det räknas inte som en fast installation.","tips":[{"text":"DCL-donet (den vita kopplingen i taket) är gjort just för att du själv ska kunna byta lampa. Du klickar loss den gamla och i den nya.","allowed":true},{"text":"Bryt ändå strömmen vid strömbrytaren först och kontrollera att armaturen verkligen sitter i ett DCL-don.","allowed":true},{"text":"Häng tunga armaturer i takkroken eller dosans fäste, aldrig i sladden. Donet är gjort för el, inte för vikt.","allowed":true},{"text":"Sticker ledningarna ut direkt ur taket utan DCL-don? Då kräver inkopplingen en elektriker.","allowed":false}],"related_jobs":["fast-armatur","byta-gloldlampa","inomhusbelysning"],"_authored_pending_signoff":"Option B derives from the allowed:false tip ('Sticker ledningarna ut direkt ur taket utan DCL-don? Då kräver inkopplingen en elektriker.'). NOTE for signoff: same fast-armatur tension as above; confirm the no-DCL red scope."},{"id":"fast-armatur","label":"Byta fast monterad armatur","icon":"pendant","service_page_url":"https://ampy.se/elservice/armatur/","type":"conditional","question":"Vad gäller för din armatur?","summary":"Byte i torrt rum är okej. Ny ledning eller våtrum kräver elektriker.","options":[{"label":"Jag byter en befintlig armatur","clarifier":"Samma plats, torrt rum, ledningen finns","verdict":"green","summary":"Du får byta armaturen, så länge ledningen och dosan redan sitter där.","do":"Byta en befintlig armatur i ett torrt rum där ledningen finns.","dont":"Byta lamputtaget, dra ny ledning eller jobba i våtrum.","tips":[{"text":"Bryt strömmen vid säkringen, inte bara vid strömbrytaren, och kontrollera med spänningsprovare att ledningarna är döda.","allowed":true},{"text":"Fotografera den gamla kopplingen innan du lossar något. Kan du inte återskapa exakt samma anslutning, ta in en elektriker.","allowed":true},{"text":"Skyddsjorden (gul och grön) ska anslutas i en metallarmatur. Är kopplingen oklar, koppla inte på gissning.","allowed":true},{"text":"Ny ledning, nytt lamputtag eller arbete i våtrum är elinstallationsarbete för ett registrerat företag.","allowed":false}]},{"label":"Nytt uttag, ny ledning eller våtrum","clarifier":"Inklusive badrum och utomhus","verdict":"red","summary":"Nytt lamputtag, ny ledning eller våtrum räknas som fast installation.","do":"Planera placering och välja armatur inför elektrikerns besök.","dont":"Byta lamputtaget, dra ny ledning eller jobba i våtrum."}],"rule_citation":"Elsäkerhetsverket – lekmannaarbete","why_text":"Att byta en befintlig fast armatur i torrt rum är tillåtet om du vet hur. Att byta lamputtaget, dra ny ledning eller arbeta i våtrum är elinstallationsarbete.","tips":[{"text":"Bryt strömmen vid säkringen, inte bara vid strömbrytaren, och kontrollera med en spänningsprovare att ledningarna är döda.","allowed":true},{"text":"Fotografera den gamla kopplingen innan du lossar något. Kan du inte återskapa exakt samma anslutning, ta in en elektriker.","allowed":true},{"text":"Skyddsjorden (gul och grön) ska sitta i en metallarmatur. Saknas jord helt är det ett jobb för en elektriker.","allowed":true},{"text":"Gäller det våtrum, ny ledning eller byte av själva lamputtaget? Då är det behörighetskrävande arbete.","allowed":false}],"_tips_verify":"Job 'fast-armatur' flagged by the independent safety reviewer as the most legally sensitive — verify scope at sign-off (is a 'fixed-mounted' luminaire with terminal-block connection still a layperson job?).","related_jobs":["byta-armatur-dcl","spotlights","badrum"]},{"id":"spotlights","label":"Installera spotlights","icon":"spotlight","service_page_url":"https://ampy.se/elservice/spotlights/","type":"fixed","default_verdict":"red","summary":"Spotlights kräver ny kabeldragning i taket.","do":"Välja typ av spotlights och planera placering i taket.","dont":"Infällda spotlights med ny kabeldragning i taket kräver elektriker.","rule_citation":"Elsäkerhetslagen 27 §; SS 436 40 00","why_text":"Infällda spotlights innebär nya punkter, kabeldragning och värmekrav i taket. Det är att ändra den fasta installationen.","related_jobs":["fast-armatur","inomhusbelysning","dra-ny-kabel"]},{"id":"byta-strombrytare","label":"Byta strömbrytare","icon":"switch","service_page_url":"https://ampy.se/elservice/strombrytare/","type":"conditional","question":"Vad gäller för din strömbrytare?","summary":"Byta befintlig på samma plats är okej. Flytt, ny eller ny ledning kräver elektriker.","options":[{"label":"Jag byter en befintlig, på samma plats","clarifier":"Samma sorts brytare, på samma plats","verdict":"green","summary":"Du får byta brytaren, så länge typ och koppling är desamma.","do":"Byta en befintlig brytare (högst 16 A) i sin egen dosa.","dont":"Sätta dimmer, flytta brytaren eller dra ny ledning.","tips":[{"text":"Bryt strömmen vid säkringen och prova med spänningsprovare. En strömbrytare kan ha spänning kvar även när lampan är släckt.","allowed":true},{"text":"Märk eller fotografera ledarna innan du lossar dem. Hittar du nolla (N) på brytaren i stället för fas (L) är den felkopplad, ta då hjälp.","allowed":true},{"text":"Byt mot samma typ. En trapp- eller korskoppling har fler ledare och kan inte ersättas med en enkel brytare.","allowed":true},{"text":"Vill du sätta dimmer, flytta brytaren eller dra ny ledning? Det är elinstallationsarbete och kräver elektriker.","allowed":false}]},{"label":"Jag vill flytta, sätta ny eller dra ny ledning","clarifier":"Ny placering, dimmer eller ny ledning","verdict":"red","summary":"Ändrar du placering eller funktion blir det fast installation.","do":"Välja modell och märka upp var brytaren ska sitta.","dont":"Flytta brytaren, sätta en ny eller dra ny ledning."}],"rule_citation":"Elsäkerhetsverket – byta befintlig strömbrytare ≤16 A","why_text":"Att byta en befintlig strömbrytare för högst 16 A i egen dosa får du göra själv om du vet hur. Att flytta brytaren, sätta en ny eller dra ny ledning är elinstallationsarbete.","source_quote":"Du får själv byta en befintlig strömbrytare för högst 16 A, som är placerad i en egen kapsling eller dosa, om du vet hur du ska göra. Är du det minsta osäker ska du alltid kontakta ett elinstallationsföretag.","tips":[{"text":"Bryt strömmen vid säkringen och prova med spänningsprovare. En strömbrytare kan ha spänning kvar även när lampan är släckt.","allowed":true},{"text":"Märk eller fotografera ledarna innan du lossar dem. Hittar du nolla (N) på brytaren i stället för fas (L) är den felkopplad.","allowed":true},{"text":"Byt mot samma typ. En trapp- eller korskoppling har fler ledare och kan inte ersättas med en enkel brytare.","allowed":true},{"text":"Vill du bygga om till dimmer eller smart brytare som kräver nolla? Det är ett nytt jobb för en elektriker.","allowed":false}],"related_jobs":["byta-vagguttag","smarta-hem","felsokning"]},{"id":"byta-vagguttag","label":"Byta vägguttag","icon":"outlet","service_page_url":"https://ampy.se/elservice/strombrytare/","type":"conditional","question":"Vad gäller för ditt uttag?","summary":"Byta befintligt på samma plats är okej. Flytt, ojordat till jordat eller nytt kräver elektriker.","options":[{"label":"Jag byter ett befintligt, på samma plats","clarifier":"Samma sorts uttag, på samma plats","verdict":"green","summary":"Du får byta uttaget, så länge du inte flyttar det eller ändrar typen.","do":"Byta mot ett likadant jordat uttag, högst 16 A, i egen dosa.","dont":"Flytta uttaget, byta ojordat till jordat eller sätta ett nytt.","source":{"text":"Elsäkerhetsverket – byta vägguttag","url":"https://www.elsakerhetsverket.se/privatpersoner/detta-far-du-gora-sjalv-med-el/byta-vagguttag/"},"tips":[{"text":"Bryt strömmen vid säkringen och kontrollera med spänningsprovare i uttaget. Det räcker inte att stänga av med strömbrytaren.","allowed":true},{"text":"Byt mot samma typ och märkström (oftast 16 A). Ett jordat uttag ska alltid ersättas med ett jordat, aldrig nedgraderas.","allowed":true},{"text":"Dra åt plintskruvarna ordentligt så ingen ledare sitter löst. Glapp är en vanlig orsak till värme och brand.","allowed":true},{"text":"Flytta uttaget, byta ojordat till jordat eller sätta ett nytt kräver ett registrerat företag.","allowed":false}]},{"label":"Jag vill flytta, sätta nytt eller byta ojordat till jordat","clarifier":"Du ändrar plats eller typ av uttag","verdict":"red","summary":"Så fort du ändrar plats eller typ blir det fast installation.","do":"Bestämma var uttaget ska sitta och köpa hem rätt modell.","dont":"Flytta uttaget, byta ojordat till jordat eller dra ny ledning."}],"rule_citation":"Elsäkerhetsverket – byta vägguttag","why_text":"Att byta ett befintligt jordat uttag mot ett likadant (≤16 A, egen dosa, samma plats) får du göra om du vet hur. Att flytta, byta ojordat till jordat eller sätta nytt kräver ett registrerat företag.","source_quote":"Byte från ojordade uttag till jordade får endast utföras av ett elinstallationsföretag. Det är inte tillåtet att byta ut endast ett uttag i ett rum och låta resten vara ojordade.","tips":[{"text":"Bryt strömmen vid säkringen och kontrollera med spänningsprovare i uttaget. Det räcker inte att stänga av med strömbrytaren.","allowed":true},{"text":"Byt mot samma typ och märkström (oftast 16 A). Ett jordat uttag ska ersättas med ett jordat, aldrig nedgraderas.","allowed":true},{"text":"Dra åt plintskruvarna ordentligt så ingen ledare sitter löst. Glapp är en vanlig orsak till värme och brand bakom uttag.","allowed":true},{"text":"Är dosan spräckt, ledningarna spröda, eller vill du flytta uttaget? Då är det inte längre ett lekmannajobb.","allowed":false}],"related_jobs":["byta-strombrytare","vitvaror","smarta-hem"]},{"id":"elcentral","label":"Arbete i elcentralen","icon":"panel","service_page_url":"https://ampy.se/elservice/elcentral/","type":"conditional","question":"Vad ska du göra i centralen?","summary":"Byta propp eller återställa säkring är okej. Allt annat i centralen kräver elektriker.","options":[{"label":"Byta propp eller återställa säkring","clarifier":"En propp eller säkring som löst ut","verdict":"green","summary":"Du får återställa och byta säkringar, men inte ändra något inuti centralen.","do":"Byta en trasig propp eller återställa en utlöst automatsäkring.","dont":"Lägga till en grupp eller byta en automatsäkring.","tips":[{"text":"Det du får göra i centralen är att byta en trasig propp och att återställa en utlöst automatsäkring eller jordfelsbrytare.","allowed":true},{"text":"Byt alltid mot samma typ och amperetal. En propp med högre värde tar bort skyddet för den ledningen.","allowed":true},{"text":"Löser samma säkring ut om och om igen? Det är ett tecken på fel, dra ur apparater och ring en elektriker om det fortsätter.","allowed":true},{"text":"Öppna aldrig kåpan för att skruva i plintar, lägga till en grupp eller byta en automatsäkring. Det är arbete för en elektriker.","allowed":false}]},{"label":"Ny grupp, byta automatsäkring eller annat","clarifier":"Arbete inuti den fasta centralen","verdict":"red","summary":"Allt som kopplas om inuti centralen är elinstallationsarbete.","do":"Notera vilka grupper som finns och vad de matar.","dont":"Lägga till en grupp, byta automatsäkring eller koppla om."}],"rule_citation":"Elsäkerhetsverket – byta proppsäkring","why_text":"Att byta en trasig propp (samma märkström) och återställa en utlöst automatsäkring får du göra. Allt annat arbete i centralen är elinstallationsarbete.","source_quote":"Om säkringen går sönder på nytt finns det fel som måste åtgärdas. En automatsäkring återställs genom att du trycker tillbaka den i till-läge.","tips":[{"text":"Det du får göra i elcentralen är att byta en trasig propp och att återställa en utlöst jordfelsbrytare.","allowed":true},{"text":"Byt alltid mot samma typ och amperetal. En propp med högre värde tar bort skyddet för den ledningen.","allowed":true},{"text":"Testa jordfelsbrytaren var sjätte månad med testknappen (T). Den ska lösa ut direkt, annars bör en elektriker kontrollera den.","allowed":true},{"text":"Nya grupper, byte av automatsäkringar eller installation av jordfelsbrytare får bara göras av ett registrerat företag.","allowed":false}],"related_jobs":["jordfelsbrytare","elrenovering","lastbalansering"]},{"id":"jordfelsbrytare","label":"Installera jordfelsbrytare","icon":"rcd","service_page_url":"https://ampy.se/elservice/jordfelsbrytare/","type":"conditional","default_verdict":"red","choice_type":"scenario","question":"Vad ska du göra med jordfelsbrytaren?","options":[{"label":"Testa den med testknappen","clarifier":"Den befintliga brytaren i elcentralen","verdict":"green","summary":"Att testa en befintlig jordfelsbrytare med testknappen får du göra själv.","do":"Testa en befintlig jordfelsbrytare med testknappen varje halvår.","dont":"Installera eller byta jordfelsbrytare i centralen kräver elektriker.","tips":[{"text":"Tryck på testknappen (T) på jordfelsbrytaren ungefär var sjätte månad. Den ska lösa ut direkt och bryta strömmen.","allowed":true},{"text":"Slår den inte ifrån när du testar? Då skyddar den inte som den ska, boka en elektriker för kontroll.","allowed":true},{"text":"Slå på brytaren igen efter testet och ställ om klockor och annat som nollställts.","allowed":true},{"text":"Att installera en ny jordfelsbrytare eller byta den gamla är arbete inne i elcentralen och kräver ett registrerat företag.","allowed":false}]},{"label":"Installera en ny eller byta den gamla","clarifier":"Arbete inne i elcentralen","verdict":"red"}],"summary":"Att installera eller byta jordfelsbrytare är arbete i den fasta installationen.","do":"Testa en befintlig jordfelsbrytare med testknappen varje halvår.","dont":"Installera eller byta jordfelsbrytare i centralen kräver elektriker.","rule_citation":"Elsäkerhetslagen 27 §","why_text":"Att installera eller byta jordfelsbrytare är arbete inne i den fasta installationen. Att testa en befintlig med testknappen får du dock göra själv.","related_jobs":["elcentral","elrenovering","felsokning"],"_authored_pending_signoff":"Option A = the job's own do-row + why_text ('Att testa en befintlig med testknappen får du dock göra själv.'). Option B falls back to job-level summary/do/dont."},{"id":"spis-ugn","label":"Ansluta spis eller ugn","icon":"stove","service_page_url":"https://ampy.se/elservice/ugn-spis/","type":"conditional","question":"Hur ansluts spisen eller ugnen?","summary":"Stickpropp i befintligt uttag är okej. Perilex eller fast anslutning kräver elektriker.","options":[{"label":"Stickpropp och matchande uttag finns","clarifier":"Stickpropp och ett uttag som passar","verdict":"green","summary":"Med färdig stickpropp och rätt uttag är det bara att koppla in.","do":"Ansluta en spis med stickpropp till ett befintligt matchande uttag.","dont":"Fast anslutning eller ett nytt Perilex-uttag.","tips":[{"text":"Det här gäller bara om spisen har en färdig stickpropp och det finns ett matchande uttag. Du sätter i kontakten, inget mer.","allowed":true},{"text":"Kontrollera att uttaget är avsett för spisens effekt. En spis drar mycket och ett underdimensionerat uttag blir varmt.","allowed":true},{"text":"Dra fram spisen försiktigt så att sladden inte kläms bakom eller mot heta delar.","allowed":true},{"text":"Behöver spisen fast anslutning eller ett nytt Perilex-uttag? Det ska en elektriker göra.","allowed":false}]},{"label":"Fast anslutning eller Perilex-uttag","clarifier":"Fast koppling eller nytt uttag","verdict":"red","summary":"Fast anslutning och Perilex-uttag är fast installation.","do":"Mäta plats och kontrollera vilken anslutning spisen kräver.","dont":"Koppla in fast eller sätta ett nytt Perilex-uttag."}],"rule_citation":"Elsäkerhetsverket – spisar och stickproppar","why_text":"En spis med stickpropp får du själv ansluta till ett befintligt matchande uttag. Fast anslutning, eller behov av nytt eller Perilex-uttag, kräver ett registrerat företag.","source_quote":"En spis med stickpropp får du själv ansluta till vägguttaget.","tips":[{"text":"Det här gäller bara om spisen har en färdig stickpropp och det finns ett matchande uttag. Du sätter i kontakten, inget mer.","allowed":true},{"text":"Kontrollera att uttaget är avsett för effekten. En spis drar mycket och ett underdimensionerat uttag blir varmt.","allowed":true},{"text":"Dra fram spisen försiktigt så att sladden inte kläms bakom eller mot heta delar.","allowed":true},{"text":"Ska spisen fast anslutas, eller behövs ett Perilex-uttag? Då krävs ett registrerat elinstallationsföretag.","allowed":false}],"related_jobs":["vitvaror","byta-vagguttag","koksrenovering"]},{"id":"vitvaror","label":"Ansluta vitvaror","icon":"appliance","service_page_url":"https://ampy.se/elservice/vitvaror/","type":"conditional","question":"Hur ansluts maskinen?","summary":"Stickpropp i befintligt uttag är okej. Fast anslutning eller nytt uttag kräver elektriker.","options":[{"label":"Stickpropp och uttag finns","clarifier":"Maskinen har en vanlig stickpropp","verdict":"green","summary":"Har maskinen stickpropp är det anslutning, inte elinstallation.","do":"Ansluta en vitvara med stickpropp till ett befintligt jordat uttag.","dont":"Fast anslutning eller ett nytt uttag nära vatten.","tips":[{"text":"Det här gäller vitvaror med stickpropp som ansluts till ett befintligt jordat uttag. Det är anslutning, inte elinstallation.","allowed":true},{"text":"Använd inte grenuttag eller skarvsladd till vitvaror med hög effekt. De ska sitta i ett eget jordat vägguttag.","allowed":true},{"text":"Kontrollera att uttaget är jordat. Vitvaror med metallhölje ska alltid ha jordad anslutning.","allowed":true},{"text":"Fast anslutning eller ett nytt uttag, särskilt nära vatten, kräver ett registrerat företag.","allowed":false}]},{"label":"Fast anslutning eller nytt uttag","clarifier":"Maskinen kopplas fast, utan stickpropp","verdict":"red","summary":"Fast inkoppling eller ett nytt uttag är fast installation.","do":"Ställa maskinen på plats och dra fram vatten inför besöket.","dont":"Koppla in fast eller sätta ett nytt uttag."}],"rule_citation":"Elsäkerhetsverket – montera stickpropp","why_text":"Vitvaror med stickpropp ansluter du själv till ett befintligt uttag. Fast anslutning eller nytt uttag, särskilt nära vatten, kräver ett registrerat företag.","tips":[{"text":"Det här gäller vitvaror med stickpropp som ansluts till ett befintligt jordat uttag. Det är anslutning, inte elinstallation.","allowed":true},{"text":"Använd inte grenuttag eller skarvsladd till vitvaror med hög effekt. De ska sitta i ett eget jordat vägguttag.","allowed":true},{"text":"Kontrollera att uttaget är jordat. Vitvaror med metallhölje ska alltid ha jordad anslutning.","allowed":true},{"text":"Behöver ett nytt uttag dras, eller ska maskinen fast anslutas? Då krävs en elektriker.","allowed":false}],"related_jobs":["spis-ugn","byta-vagguttag","badrum"]},{"id":"utomhusbelysning","label":"Utomhusbelysning","icon":"lantern","service_page_url":"https://ampy.se/elservice/utomhusbelysning/","type":"conditional","question":"Vad gäller för belysningen?","summary":"Plugin-belysning är fritt. Fast armatur eller kabel i mark kräver elektriker.","options":[{"label":"Plugin-belysning i befintligt uttag","clarifier":"Sladd till ett uttag som redan finns ute","verdict":"green","summary":"Lampor som bara pluggas in i ett befintligt uttag är fria.","do":"Koppla in plugin-belysning i ett befintligt utomhusuttag.","dont":"Fast armatur, ny ledning eller kabel i mark.","tips":[{"text":"Plugin-belysning som bara ansluts till ett befintligt utomhusuttag får du sätta upp fritt.","allowed":true},{"text":"Använd armaturer och skarvdon märkta för utomhusbruk, minst IP44, så fukt inte tränger in.","allowed":true},{"text":"Dra sladdar så att de inte ligger i vattensamlingar eller kläms i dörrar och fönster.","allowed":true},{"text":"Fast armatur, ny ledning eller kabel i mark är elinstallationsarbete och kräver elektriker.","allowed":false}]},{"label":"Fast armatur eller kabel i mark","clarifier":"Fast montering eller nedgrävd kabel","verdict":"red","summary":"Fast armatur och kabel i mark är fast installation.","do":"Planera placering och gräva schakt åt elektrikern.","dont":"Montera fast, dra ny ledning eller gräva ner kabel."}],"rule_citation":"Elsäkerhetsverket – förläggning av kabel i mark","why_text":"Plugin-belysning i befintligt utomhusuttag är fritt. Fast armatur, ny ledning eller kabel i mark är elinstallationsarbete.","tips":[{"text":"Du får byta en befintlig utomhusarmatur, men utomhus ställer hårdare krav. Bryt alltid strömmen vid säkringen först.","allowed":true},{"text":"Välj armatur med rätt kapslingsklass. Minst IP44 utomhus, och högre nära mark och vatten, så fukt inte tränger in.","allowed":true},{"text":"Kontrollera att tätningar och packningar är hela när du sätter tillbaka kupan. En otät armatur släpper in fukt.","allowed":true},{"text":"Ny belysning, markförlagd kabel eller ny ledning ut till friggeboden får bara en elektriker göra.","allowed":false}],"related_jobs":["inomhusbelysning","laddbox","dra-ny-kabel"]},{"id":"inomhusbelysning","label":"Inomhusbelysning","icon":"ceiling","service_page_url":"https://ampy.se/elservice/inomhusbelysning/","type":"conditional","question":"Vilken typ av belysningsjobb?","summary":"Ljuskällor och plugin är fritt. Nya punkter eller spotlights kräver elektriker.","options":[{"label":"Byta ljuskälla eller plugin-lampa","clarifier":"Glödlampa eller lampa med sladd","verdict":"green","summary":"Ljuskällor och sladdlampor räknas inte som elinstallation.","do":"Byta ljuskälla eller koppla in en lampa med stickpropp.","dont":"Nya punkter, spotlights eller ny ledning.","tips":[{"text":"Ljuskällor och lampor med stickpropp får du byta och koppla in fritt.","allowed":true},{"text":"Bryt strömmen och låt ljuskällan svalna innan du byter. Matcha sockel och effekt mot armaturen.","allowed":true},{"text":"Anpassa färgtemperaturen efter rummet. Varmt ljus (runt 2700 K) i vardagsrum, neutralt över arbetsytor.","allowed":true},{"text":"Nya punkter, spotlights eller ny ledning kräver ett registrerat företag.","allowed":false}]},{"label":"Byta befintlig fast armatur","clarifier":"Ledningen finns redan, torrt rum","verdict":"green","summary":"Du får byta armaturen så länge ledningen redan sitter där.","do":"Byta en befintlig fast armatur där ledningen redan finns.","dont":"Nya punkter, spotlights eller ny ledning.","tips":[{"text":"Bryt strömmen vid säkringen och kontrollera med spänningsprovare att ledningarna är döda innan du lossar armaturen.","allowed":true},{"text":"Fotografera kopplingen innan du lossar den. Kan du inte återskapa exakt samma anslutning, ta in en elektriker.","allowed":true},{"text":"Skyddsjorden (gul och grön) ska anslutas i en metallarmatur. Är du osäker på jorden, koppla inte på gissning.","allowed":true},{"text":"Nya punkter, spotlights eller ny ledning är elinstallationsarbete.","allowed":false}]},{"label":"Nya punkter, spotlights eller ny ledning","clarifier":"Ny installation i taket","verdict":"red","summary":"Nya punkter och ny ledning i taket är fast installation.","do":"Skissa belysningsplan och välja armaturer inför installationen.","dont":"Sätta nya punkter, infällda spotlights eller dra ny ledning."}],"rule_citation":"Elsäkerhetsverket – lekmannaarbete","why_text":"Ljuskällor och plugin-lampor är fritt. Befintlig fast armatur i torrt rum får du byta. Nya punkter, spotlights eller ny ledning kräver ett registrerat företag.","tips":[{"text":"Du får byta ljuskälla och byta en befintlig armatur i torrt rum där ledningen redan finns.","allowed":true},{"text":"Bryt strömmen vid säkringen och kontrollera att ledningarna är spänningslösa innan du lossar en fast armatur.","allowed":true},{"text":"Anpassa färgtemperaturen efter rummet. Varmt ljus (runt 2700 K) i vardagsrum, neutralt eller kallare över arbetsytor.","allowed":true},{"text":"Vill du ha infällda spotlights, ny ledning eller belysning i våtrum? Det är ett annat, behörighetskrävande jobb.","allowed":false}],"related_jobs":["spotlights","fast-armatur","byta-armatur-dcl"]},{"id":"smarta-hem","label":"Smarta hem","icon":"smart","service_page_url":"https://ampy.se/elservice/smarta-hem/","type":"conditional","question":"Hur installeras den smarta enheten?","summary":"Pluggar och brytarbyten är okej. Nolledare eller ny ledning kräver elektriker.","options":[{"label":"Smart plugg eller fjärrkontakt","clarifier":"Pluggas in i ett befintligt uttag","verdict":"green","summary":"Allt som bara pluggas in får du installera fritt.","do":"Koppla in smarta pluggar och fjärrkontakter i befintliga uttag.","dont":"Enheter som kräver nolledare eller ny ledning.","tips":[{"text":"Smarta uttag, pluggar, lampor och hubbar som ansluts via stickpropp får du installera helt fritt.","allowed":true},{"text":"Belasta inte smarta pluggar över deras märkeffekt. De tål ofta lägre last än ett vanligt vägguttag.","allowed":true},{"text":"Kontrollera sockel och kompatibilitet innan du köper smarta lampor och dimrar.","allowed":true},{"text":"Enheter som kräver nolledare eller ny ledning i väggen kräver en elektriker.","allowed":false}]},{"label":"Smart brytare eller dimmer som ersättning","clarifier":"Samma sorts brytare, på samma plats","verdict":"green","summary":"Byter du mot en likadan brytare gäller vanliga regler.","do":"Ersätta en befintlig brytare eller dimmer (högst 16 A) i egen dosa.","dont":"Enheter som kräver nolledare eller ny ledning.","tips":[{"text":"Bryt strömmen vid säkringen och prova med spänningsprovare innan du lossar den gamla brytaren.","allowed":true},{"text":"Byt mot en enhet av samma typ (högst 16 A) i den befintliga dosan. Märk ledarna innan du lossar dem.","allowed":true},{"text":"Kräver den smarta brytaren en nolledare? Kontrollera att den verkligen finns i dosan innan du börjar.","allowed":true},{"text":"Saknas nolledare eller behövs ny ledning? Då är det elinstallationsarbete för en elektriker.","allowed":false}]},{"label":"Kräver nolledare eller ny ledning","clarifier":"Ny fast installation bakom brytaren","verdict":"red","summary":"Behövs nolledare eller ny ledning är det fast installation.","do":"Välja system och planera vilka brytare som ska bytas.","dont":"Koppla in en enhet som kräver nolledare eller ny ledning."}],"rule_citation":"Elsäkerhetsverket – byta befintlig strömbrytare ≤16 A","why_text":"Smarta pluggar är fria. En smart brytare eller dimmer som ersätter en befintlig (≤16 A, egen dosa) får du byta om du vet hur. Kräver enheten nolledare eller ny ledning är det elinstallationsarbete.","tips":[{"text":"Smarta uttag, pluggar, lampor och hubbar som ansluts via stickpropp får du installera helt fritt.","allowed":true},{"text":"Kontrollera sockel och kompatibilitet med din dimmer eller styrning innan du köper smarta lampor.","allowed":true},{"text":"Belasta inte smarta pluggar över deras märkeffekt. De tål ofta lägre last än ett vanligt vägguttag.","allowed":true},{"text":"Smarta brytare eller relädosor som kopplas in i den fasta installationen kräver en elektriker.","allowed":false}],"related_jobs":["byta-strombrytare","byta-vagguttag","felsokning"]},{"id":"laddbox","label":"Installera laddbox","icon":"charger","service_page_url":"https://ampy.se/laddbox/","type":"fixed","default_verdict":"red","summary":"En laddbox kopplas in i den fasta installationen och kräver elektriker.","do":"Välja laddbox och planera placering vid parkeringen eller garaget.","dont":"Kabeldragning och anslutning till elcentralen kräver en elektriker.","rule_citation":"Elsäkerhetslagen 27 §","why_text":"En laddbox för elbil ansluts till den fasta elanläggningen, ofta med en egen grupp och lastbalansering. Installationen är behörighetskrävande och en behörig elektriker ska även kontrollera att huvudsäkringen klarar lasten.","related_jobs":["lastbalansering","solcellsbatterier","elcentral"]},{"id":"solcellsbatterier","label":"Solceller och batteri","icon":"solar","service_page_url":"https://ampy.se/solcellsbatterier/","type":"fixed","default_verdict":"red","summary":"Solceller och batterilagring ansluts till den fasta installationen och kräver elektriker.","do":"Planera takyta, väderstreck och var batteriet ska placeras.","dont":"Inkoppling av paneler, växelriktare och batteri mot elnätet kräver en elektriker.","rule_citation":"Elsäkerhetslagen 27 §","why_text":"En solcellsanläggning och ett hembatteri kopplas mot den fasta installationen och elnätet. Det är behörighetskrävande arbete och kräver dessutom föranmälan till din elnätsägare.","related_jobs":["laddbox","lastbalansering","elcentral"]},{"id":"luftvarmepump","label":"Luftvärmepump","icon":"heatpump","service_page_url":"https://ampy.se/elservice/luftvarmepump/","type":"fixed","default_verdict":"red","summary":"Både elen och köldmediet kräver en certifierad tekniker.","do":"Välja modell och planera placering inne och ute.","dont":"Både el och köldmedium kräver registrerat företag och kyltekniker.","rule_citation":"Elsäkerhetslagen 27 §; F-gasförordningen (EU) 517/2014","why_text":"Både elanslutningen och köldmediekretsen är reglerade. Installationen kräver ett registrerat elinstallationsföretag och en certifierad kyltekniker.","related_jobs":["lastbalansering","elcentral","elrenovering"]},{"id":"golvvarme","label":"Installera golvvärme","icon":"heat","service_page_url":"https://ampy.se/elservice/golvvarme/","type":"fixed","default_verdict":"red","summary":"Golvvärme är en del av den fasta elanläggningen.","do":"Välja system och planera ytan inför installationen.","dont":"Värmekabel och anslutning är fast installation och kräver elektriker.","rule_citation":"Elsäkerhetsverket – installation av golvvärme","why_text":"Hela golvvärmen, både värmekabel eller folie och anslutning, är en del av den fasta elanläggningen. Den måste göras av ett registrerat företag, särskilt i våtrum.","source_quote":"Även värmekablar, värmefolier och andra elektriska system är en del av den elektriska starkströmsanläggningen.","related_jobs":["badrum","badrumsrenovering","elrenovering"]},{"id":"lastbalansering","label":"Lastbalansering","icon":"balance","service_page_url":"https://ampy.se/elservice/lastbalansering/","type":"fixed","default_verdict":"red","summary":"Lastbalansering är arbete i och kring elcentralen.","do":"Kartlägga elförbrukning och se över abonnemanget.","dont":"Arbete i och kring elcentralen kräver elektriker.","rule_citation":"Elsäkerhetslagen 27 §","why_text":"Lastbalansering innebär arbete i och kring elcentralen och den fasta installationen.","related_jobs":["elcentral","laddbox","luftvarmepump"]},{"id":"elbesiktning","label":"Elbesiktning","icon":"inspect","service_page_url":"https://ampy.se/elservice/elbesiktning/","type":"conditional","question":"Vad gäller för elkontrollen?","summary":"Egna kontroller får du göra. En formell besiktning ska göras av behörig.","options":[{"label":"Mina egna kontroller hemma","clarifier":"Titta, känn efter och testa","verdict":"green","summary":"Egna kontroller med syn och känsel får du göra fritt.","do":"Testa jordfelsbrytaren och känna efter varma uttag och brännmärken.","dont":"Öppna centralen eller dosor för att inspektera inuti.","tips":[{"text":"Gör en egen rundvandring. Känn efter om uttag eller strömbrytare är varma och titta efter brännmärken.","allowed":true},{"text":"Testa jordfelsbrytaren med testknappen (T). Den ska lösa ut direkt när du trycker.","allowed":true},{"text":"Kontrollera att uttag och strömbrytare sitter fast och att inga höljen är spruckna.","allowed":true},{"text":"En formell elbesiktning vid husköp, för försäkring eller efter en skada ska göras av en behörig besiktningsman.","allowed":false}]},{"label":"Formell elbesiktning","clarifier":"Vid husköp, försäkring eller efter skada","verdict":"red","summary":"En besiktning med mätning och protokoll görs av en behörig.","do":"Lista din oro och samla ihop ritningar inför besöket.","dont":"Mäta i installationen eller intyga säkerheten själv."}],"rule_citation":"Elsäkerhetsverket – kontrollera din el","why_text":"Du får göra egna kontroller hemma, som att testa jordfelsbrytaren och leta efter varma uttag. En formell elbesiktning vid husköp, för försäkring eller efter en skada ska göras av en behörig besiktningsman.","tips":[{"text":"Gör en egen rundvandring. Känn efter om uttag eller strömbrytare är varma och titta efter brännmärken.","allowed":true},{"text":"Testa jordfelsbrytaren med testknappen (T). Den ska lösa ut direkt när du trycker.","allowed":true},{"text":"Kontrollera att uttag och strömbrytare sitter fast ordentligt och att inga höljen är spruckna.","allowed":true},{"text":"En formell elbesiktning för husköp eller försäkring ska göras av en behörig besiktningsman.","allowed":false}],"related_jobs":["felsokning","elcentral","jordfelsbrytare"]},{"id":"felsokning","label":"Felsökning av el","cta_short":"felsökning","icon":"search","service_page_url":"https://ampy.se/elservice/felsokning-av-el/","type":"conditional","question":"Vad handlar felsökningen om?","summary":"Testa och återställa är okej. Att öppna den fasta installationen kräver elektriker.","options":[{"label":"Titta, testa eller återställa","clarifier":"Testa, byta propp, återställa säkring","verdict":"green","summary":"Du får leta, testa och återställa, men inte öppna installationen.","do":"Testa jordfelsbrytaren, byta propp eller återställa en säkring.","dont":"Öppna dosor eller centralen för att mäta eller koppla om.","tips":[{"text":"Har strömmen försvunnit i en del av hemmet? Börja med att kontrollera jordfelsbrytaren och säkringarna i elcentralen.","allowed":true},{"text":"Löser jordfelsbrytaren ut igen direkt? Dra ur misstänkta apparater en i taget för att hitta felet.","allowed":true},{"text":"Byt en trasig propp mot en med exakt samma amperetal, aldrig en kraftigare.","allowed":true},{"text":"Så fort en dosa eller centralen måste öppnas för att mäta eller koppla om är det arbete för en elektriker.","allowed":false}]},{"label":"Öppna eller reparera fast installation","clarifier":"Felet sitter bakom väggen","verdict":"red","summary":"Måste installationen öppnas eller mätas tar en elektriker över.","do":"Notera när och var felet uppstår inför elektrikerns besök.","dont":"Öppna installationen, mäta på spänning eller koppla om."}],"rule_citation":"Elsäkerhetslagen 27 §","why_text":"Att titta, testa och återställa får du göra. Så fort den fasta installationen måste öppnas eller repareras är det elinstallationsarbete.","tips":[{"text":"Har strömmen försvunnit i en del av hemmet? Börja med att kontrollera jordfelsbrytaren och säkringarna i elcentralen.","allowed":true},{"text":"Löser jordfelsbrytaren ut direkt igen? Dra ur misstänkta apparater en i taget för att hitta felet.","allowed":true},{"text":"Byt en trasig propp mot en med exakt samma amperetal, aldrig en kraftigare. Det sätter brandskyddet ur spel.","allowed":true},{"text":"Lukt av bränt, varma uttag eller gnistor? Rör ingenting, bryt strömmen och ring en elektriker direkt.","allowed":false}],"related_jobs":["elcentral","jordfelsbrytare","byta-strombrytare"]},{"id":"elrenovering","label":"Elrenovering","icon":"renovate","service_page_url":"https://ampy.se/elservice/elrenovering/","type":"fixed","default_verdict":"red","summary":"Större ändringar i fast installation och central är kärn-elarbete.","do":"Planera rum, uttag och belysning inför renoveringen.","dont":"Större ändringar i fast installation och central kräver elektriker.","rule_citation":"Elsäkerhetslagen 27 §","why_text":"Större ändringar i den fasta installationen och i centralen är kärn-elinstallationsarbete.","related_jobs":["elcentral","dra-ny-kabel","koksrenovering"]},{"id":"koksrenovering","label":"Köksrenovering","icon":"kitchen","service_page_url":"https://ampy.se/elservice/koksrenovering/","type":"conditional","default_verdict":"red","choice_type":"scenario","question":"Hur mycket el ingår i renoveringen?","options":[{"label":"Bara ansluta vitvaror med stickpropp","clarifier":"Befintliga uttag, inga nya dragningar","verdict":"green","summary":"Att ansluta en vitvara med stickpropp till ett befintligt uttag får du göra själv.","do":"Ansluta vitvaror med stickpropp till befintliga jordade uttag.","dont":"Nya uttag, fast spisanslutning och ny ledning kräver en elektriker.","tips":[{"text":"Vitvaror med stickpropp får du ansluta till ett befintligt jordat uttag. Det är anslutning, inte elinstallation.","allowed":true},{"text":"Ge varje maskin med hög effekt ett eget jordat uttag, inte grenuttag eller skarvsladd.","allowed":true},{"text":"Kontrollera att uttaget är jordat och sitter fast innan du skjuter in maskinen.","allowed":true},{"text":"Nya uttag, fast spisanslutning och ny ledningsdragning i köket kräver ett registrerat företag.","allowed":false}]},{"label":"Nya uttag, fast spis eller ny ledning","clarifier":"Elen ändras eller byggs ut","verdict":"red"}],"summary":"Ny el i köket, som uttag och spisanslutning, kräver elektriker.","do":"Planera placering av uttag, vitvaror och belysning inför renoveringen.","dont":"Nya uttag, fast spisanslutning och ny ledning kräver en elektriker.","rule_citation":"Elsäkerhetslagen 27 §","why_text":"En köksrenovering innebär oftast nya uttag, fast anslutning av spis och ny ledningsdragning. Det är behörighetskrävande arbete. Att ansluta en vitvara med stickpropp till ett befintligt uttag får du dock göra själv.","related_jobs":["spis-ugn","vitvaror","elrenovering"],"_authored_pending_signoff":"Option A = the job's own why_text closing sentence. Option B falls back to job-level red content."},{"id":"badrum","label":"El i badrum","icon":"bath","service_page_url":"https://ampy.se/elservice/badrum/","type":"conditional","default_verdict":"red","choice_type":"scenario","question":"Vad gäller elen i badrummet?","options":[{"label":"Byta ljuskälla i en befintlig armatur","clarifier":"Armaturen sitter där, du byter bara ljuskällan","verdict":"green","summary":"Att byta ljuskälla räknas inte som elinstallationsarbete, inte heller i badrummet.","do":"Byta ljuskälla i en befintlig armatur i badrummet.","dont":"All fast el i våtrum kräver registrerat företag.","tips":[{"text":"Att byta ljuskälla i en armatur som redan sitter får du göra, även i badrummet.","allowed":true},{"text":"Bryt strömmen vid strömbrytaren och låt ljuskällan svalna. Matcha sockel och effekt mot armaturen.","allowed":true},{"text":"Torka händerna och se till att armaturen är hel och tät innan du sätter tillbaka kupan.","allowed":true},{"text":"All annan fast el i våtrummet, belysning, uttag, golvvärme och jordfelsbrytare, kräver ett registrerat företag.","allowed":false}]},{"label":"Fast el i våtrummet","clarifier":"Belysning, uttag, golvvärme eller jordfelsbrytare","verdict":"red"}],"summary":"All fast el i ett våtrum kräver ett registrerat företag.","do":"Byta ljuskälla i en befintlig armatur i badrummet.","dont":"All fast el i våtrum kräver registrerat företag.","rule_citation":"SS 436 40 00 (zonindelning, IP-klass); Elsäkerhetsverket – el i bad- och duschrum","why_text":"All fast el i ett våtrum, alltså belysning, uttag, golvvärme och jordfelsbrytare, måste göras av ett registrerat företag enligt zon- och IP-reglerna.","related_jobs":["badrumsrenovering","golvvarme","fast-armatur"],"_authored_pending_signoff":"The owner's own example. Option A = the job's do-row; option B falls back to job-level red content (summary/dont/why_text)."},{"id":"badrumsrenovering","label":"Badrumsrenovering","icon":"bath","service_page_url":"https://ampy.se/elservice/badrumsrenovering/","type":"fixed","default_verdict":"red","summary":"All fast el i ett våtrum kräver ett registrerat företag.","do":"Planera belysning, uttag och golvvärme inför renoveringen.","dont":"Belysning, uttag, golvvärme och jordfelsbrytare i våtrum kräver elektriker.","rule_citation":"SS 436 40 00 (zonindelning, IP-klass); Elsäkerhetsverket – el i bad- och duschrum","why_text":"Vid en badrumsrenovering ska all fast el följa våtrummets zon- och IP-regler. Det är behörighetskrävande arbete från belysning till golvvärme.","related_jobs":["badrum","golvvarme","elrenovering"]},{"id":"dra-ny-kabel","label":"Dra ny kabel","cta_short":"att dra ny kabel","icon":"cable","service_page_url":"https://ampy.se/elservice/elrenovering/","type":"fixed","default_verdict":"red","summary":"Ny fast ledning är elinstallationsarbete.","do":"Planera dragning och välja kabel inför elektrikerns besök.","dont":"Att dra ny fast ledning kräver elektriker.","rule_citation":"Elsäkerhetslagen 27 §","why_text":"Ny fast förlagd ledning är elinstallationsarbete och kräver ett registrerat företag.","related_jobs":["elrenovering","skarva-fast","spotlights"]},{"id":"skarva-fast","label":"Skarva eller förlänga fast installation","icon":"splice","service_page_url":"https://ampy.se/elservice/elrenovering/","type":"fixed","default_verdict":"red","summary":"Att skarva fast installation är att ändra den.","do":"Märka upp var skarven behövs och bryta strömmen.","dont":"Att skarva eller förlänga fast installation kräver elektriker.","rule_citation":"Elsäkerhetslagen 27 §","why_text":"Att skarva eller förlänga den fasta installationen är att ändra den. Det är elinstallationsarbete.","related_jobs":["dra-ny-kabel","elrenovering","badrum"]}]}
AMPYBKJSON;
    $data = json_decode( $json, true );
    return ( json_last_error() === JSON_ERROR_NONE && is_array( $data ) ) ? $data : [];
}

/**
 * Effective data with precedence: (1) parsed Excel sheet stored as a site
 * option, (2) baked default. A code-level apply_filters('ampy_bk_data') hook
 * still wins over both, exactly as before. Statically cached per request.
 */
function ampy_bk_get_data() {
    static $cached = null;
    if ( $cached !== null ) return $cached;
    $data  = ampy_bk_baked_data();
    // Parsed sheet now lives on the lead-magnet post (post-editor metabox). Fall
    // back to the legacy site option so an unmigrated install keeps working until
    // the .xlsx is re-picked on the post.
    $sheet = AMPY_BK_POST_ID ? (string) get_post_meta( AMPY_BK_POST_ID, '_ampy_bk_sheet_data', true ) : '';
    if ( $sheet === '' ) { $sheet = (string) get_option( 'ampy_bk_sheet_data', '' ); }
    if ( ! empty( $sheet ) ) {
        $parsed = json_decode( $sheet, true );
        if ( is_array( $parsed ) && ! empty( $parsed['jobs'] ) ) $data = $parsed;
    }
    if ( empty( $data ) ) { $cached = []; return $cached; }
    $cached = apply_filters( 'ampy_bk_data', $data ); // optional code-level override (always wins)
    return $cached;
}

function ampy_bk_is_pending( array $data ): bool {
    return ! empty( $data['meta']['_pending_verification'] );
}


/* ==========================================================================
   2. CRAWLABLE MOUNT  (server-rendered fallback; JS removes .ampy-bk__noscript)
   ========================================================================== */
function ampy_bk_render_mount( string $preselect, array $data, string $layout = 'hero' ): string {
    // v7.3.8 crawlable mount (ported from includes/render.php). ONE source line
    // (meta.source_line); the old separate disclaimer <p> is dropped. Data is
    // injected inline by the shortcode as window.AmpyBK.data, so no data-*-url
    // fetch attrs are emitted (they referenced the plugin's AMPY_BK_URL, which
    // this single-snippet build does not define; the JS reads inline data first).
    $preselect  = sanitize_key( $preselect );
    $valid_ids  = array_map( function ( $j ) { return $j['id']; }, $data['jobs'] );
    $preselect  = ( $preselect && in_array( $preselect, $valid_ids, true ) ) ? $preselect : '';
    $lead       = $data['meta']['page_lead'] ?? 'Se direkt vilka eljobb du får göra själv.';
    $source     = $data['meta']['source_line']
        ?? 'Källa: Elsäkerhetsverket & Elsäkerhetslagen (2016:732). Vägledning, inte juridisk rådgivning.';
    $hero       = ( $layout === 'hero' );

    ob_start();
    ?>
<div class="ampy-bk<?php echo $hero ? ' ampy-bk--hero' : ''; ?>"<?php if ( $hero ) : ?> data-layout="hero"<?php endif; ?><?php if ( $preselect ) : ?> data-preselect-job="<?php echo esc_attr( $preselect ); ?>"<?php endif; ?>>
    <div class="ampy-bk__noscript">
        <div class="ampy-bk__instrument">
            <p class="ampy-bk__tagline"><?php echo esc_html( $lead ); ?></p>
            <ul class="ampy-bk__noscript-grid" role="list">
                <?php foreach ( $data['jobs'] as $job ) : ?>
                    <li><a href="?jobb=<?php echo esc_attr( $job['id'] ); ?>"><?php echo esc_html( $job['label'] ); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <p class="ampy-bk__source-line"><?php echo esc_html( $source ); ?></p>
        </div>
    </div>
</div>
    <?php
    return ob_get_clean();
}


/* ==========================================================================
   3. SHORTCODE  [elkollen] / [behorighetskollen]  (optional jobb="<id>")
   ========================================================================== */
function ampy_bk_shortcode( $atts = [] ): string {
    /* tool_only="1"  -> just the tool (embeds on service pages).
       default        -> the prototype's split hero: copy column + tool. */
    $atts = shortcode_atts( [ 'jobb' => '', 'layout' => 'hero', 'tool_only' => '' ], $atts, 'behorighetskollen' );
    $data = ampy_bk_get_data();
    if ( empty( $data ) || empty( $data['jobs'] ) ) {
        return '<p>Elkollen kunde inte laddas (skadad data).</p>';
    }

    // Launch gate: unverified tool is hidden from the public, visible to editors.
    $pending = ampy_bk_is_pending( $data );
    $preview = current_user_can( 'edit_posts' );
    if ( $pending && ! AMPY_BK_FORCE_PUBLIC && ! $preview ) {
        return '<div class="ampy-bk-pending" style="max-width:60rem;margin:0 auto;padding:2.4rem;text-align:center;color:#5a5d7a;font-family:Outfit,system-ui,sans-serif;">Elkollen är under granskning och lanseras inom kort.</div>';
    }

    $preselect = sanitize_key( $atts['jobb'] );
    $layout    = ( sanitize_key( $atts['layout'] ) === 'hero' ) ? 'hero' : 'default';

    // Fonts: self-hosted, no third-party request. The families are declared in the
    // CSS snippet's @font-face rules, so nothing needs to be emitted here.
    $fonts = '';

    $payload = wp_json_encode( $data, JSON_UNESCAPED_UNICODE ); // slashes escaped -> safe inside <script>
    $rest    = wp_json_encode( esc_url_raw( rest_url( 'ampy-bk/v1/lead' ) ) );
    $nonce   = wp_json_encode( wp_create_nonce( 'wp_rest' ) );

    $script = '<script>window.AmpyBK=window.AmpyBK||{};window.AmpyBK.data=' . $payload
            . ';window.AmpyBK.restUrl=' . $rest . ';window.AmpyBK.restNonce=' . $nonce . ';</script>';

    $banner = ( $pending && $preview )
        ? '<div class="ampy-bk-admin-note" style="max-width:60rem;margin:0 auto 1.2rem;padding:1rem 1.4rem;border:1px solid #f5af19;background:#fff8e6;border-radius:1rem;color:#7a5b00;font-family:Outfit,system-ui,sans-serif;font-size:1.4rem;">Förhandsvisning (endast inloggade redaktörer): Elkollen väntar på signatur av auktoriserad elinstallatör före publik lansering.</div>'
        : '';

    $tool = ampy_bk_render_mount( $preselect, $data, $layout );

    $tool_only = ! empty( $atts['tool_only'] ) && $atts['tool_only'] !== '0';
    if ( $tool_only ) {
        $body = '<div class="elkollen-root">' . $tool . '</div>';
    } elseif ( $layout === 'hero' ) {
        $body = ampy_bk_hero( $tool ); // DEFAULT: the split-hero landing (preview/hero.html), 1:1
    } else {
        // Embedded layout: the bare tool, no page-head chrome. Only the hero layout
        // renders the marketing chrome (H1/sub/trust/CTAs); embeds stay minimal so
        // they drop cleanly into an existing Bricks page that supplies its own heading.
        $body = $tool;
    }

    return $fonts . $banner . $script . $body;
}
add_shortcode( 'behorighetskollen', 'ampy_bk_shortcode' );
add_shortcode( 'elkollen', 'ampy_bk_shortcode' ); // alias - same tool, newer name


/* ==========================================================================
   4. DYNAMIC OG META  (per ?jobb=, gated the same way as the tool)
   ========================================================================== */
add_action( 'wp_head', function () {
    if ( empty( $_GET['jobb'] ) ) return;
    $data = ampy_bk_get_data();
    if ( empty( $data ) ) return;
    if ( ampy_bk_is_pending( $data ) && ! AMPY_BK_FORCE_PUBLIC && ! current_user_can( 'edit_posts' ) ) return;

    $job_id = sanitize_key( wp_unslash( $_GET['jobb'] ) );
    foreach ( $data['jobs'] as $j ) {
        if ( $j['id'] !== $job_id ) continue;

        $brand = $data['meta']['product_name'] ?? 'Elkollen';
        $title = sprintf( 'Får jag göra %s själv? | %s', mb_strtolower( $j['label'] ), $brand );
        $desc  = wp_strip_all_tags( $j['summary'] ?? ( $j['why_text'] ?? '' ) );

        $verdict_key = ( $j['type'] === 'fixed' ) ? ( $j['default_verdict'] ?? 'yellow' ) : 'yellow';
        $og_url = '';
        $candidates = [
            AMPY_BK_OG_DIR . $j['id'] . '.png'      => AMPY_BK_OG_URL . $j['id'] . '.png',
            AMPY_BK_OG_DIR . $verdict_key . '.png'  => AMPY_BK_OG_URL . $verdict_key . '.png',
        ];
        foreach ( $candidates as $path => $url ) { if ( file_exists( $path ) ) { $og_url = $url; break; } }

        echo "\n<meta property=\"og:title\" content=\"" . esc_attr( $title ) . "\" />\n";
        echo "<meta property=\"og:description\" content=\"" . esc_attr( $desc ) . "\" />\n";
        if ( $og_url ) {
            echo "<meta property=\"og:image\" content=\"" . esc_url( $og_url ) . "\" />\n";
            echo "<meta name=\"twitter:image\" content=\"" . esc_url( $og_url ) . "\" />\n";
        }
        echo "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n";
        echo "<meta name=\"twitter:title\" content=\"" . esc_attr( $title ) . "\" />\n";
        echo "<meta name=\"twitter:description\" content=\"" . esc_attr( $desc ) . "\" />\n";
        return;
    }
} );


/* ==========================================================================
   5. LEAD REST ENDPOINT  (active in 5.7.9)
   The verdict CTA "Fa kostnadsfri radgivning" opens an inline lead form that
   POSTs here. GET /nonce hands the JS a fresh wp_rest nonce on form open so a
   stale nonce baked into a full-page-cached HTML page never breaks submits.
   Protections: fresh-nonce check, honeypot (webbplats), per-IP rate limit,
   server-side validation + sanitization, GDPR consent, error-log fallback so a
   lead is never silently lost. Emails the admin (no DB table by design); listen
   on do_action('ampy_bk_lead_received', ...) for a durable CRM/CPT sink.
   ========================================================================== */
add_action( 'rest_api_init', function () {
    // Fresh nonce for the form (uncached GET - survives page caching).
    register_rest_route( 'ampy-bk/v1', '/nonce', array(
        'methods'             => WP_REST_Server::READABLE,
        'permission_callback' => '__return_true',
        'callback'            => function () {
            return new WP_REST_Response( array( 'nonce' => wp_create_nonce( 'wp_rest' ) ), 200 );
        },
    ) );

    register_rest_route( 'ampy-bk/v1', '/lead', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'ampy_bk_handle_lead',
        'permission_callback' => function( $request ) {
            $nonce = $request->get_header( 'X-WP-Nonce' );
            return (bool) wp_verify_nonce( $nonce, 'wp_rest' );
        },
        'args' => array(
            'job_id'     => array( 'required' => true, 'type' => 'string' ),
            'verdict'    => array( 'required' => true, 'type' => 'string' ),
            'namn'       => array( 'required' => true, 'type' => 'string' ),
            'kontakt'    => array( 'required' => true, 'type' => 'string' ), // e-post
            'telefon'    => array( 'required' => true, 'type' => 'string' ),
            'postnummer' => array( 'required' => true, 'type' => 'string' ),
            'meddelande' => array( 'required' => false, 'type' => 'string' ),
            'samtycke'   => array( 'required' => true, 'type' => 'boolean' ),
            'webbplats'  => array( 'required' => false, 'type' => 'string' ), // honeypot
        ),
    ) );
} );

function ampy_bk_handle_lead( WP_REST_Request $request ) {
    // 1. Honeypot - if anything is in `webbplats`, it's a bot.
    if ( ! empty( $request->get_param( 'webbplats' ) ) ) {
        // Pretend success so bots don't probe.
        return new WP_REST_Response( array( 'ok' => true ), 200 );
    }

    // 1b. Lightweight per-IP rate limit so the public endpoint can't be used to
    // mail-bomb the admin. NOTE: behind a CDN/proxy (e.g. Cloudflare) REMOTE_ADDR
    // is the edge IP - prefer enforcing this at the edge/WAF and/or reading the
    // real client IP from a trusted forwarded header. Generous threshold to avoid
    // false positives on a shared edge IP.
    $ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
    $rk  = 'ampy_bk_rl_' . md5( $ip );
    $hits = (int) get_transient( $rk );
    if ( $hits >= 15 ) {
        return new WP_Error( 'ampy_bk_rate', 'För många förfrågningar. Försök igen om en stund.', array( 'status' => 429 ) );
    }
    set_transient( $rk, $hits + 1, 10 * MINUTE_IN_SECONDS );

    // 2. Sanitize + validate.
    $job_id     = sanitize_key( $request->get_param( 'job_id' ) );
    $verdict    = sanitize_key( $request->get_param( 'verdict' ) );
    $namn       = sanitize_text_field( $request->get_param( 'namn' ) );
    $kontakt    = sanitize_text_field( $request->get_param( 'kontakt' ) );
    $telefon    = sanitize_text_field( (string) $request->get_param( 'telefon' ) );
    $postnummer = preg_replace( '/[^0-9]/', '', (string) $request->get_param( 'postnummer' ) );
    $meddelande = sanitize_textarea_field( $request->get_param( 'meddelande' ) );
    $samtycke   = (bool) $request->get_param( 'samtycke' );

    if ( ! $namn || ! $kontakt || ! $telefon || ! $postnummer ) {
        return new WP_Error( 'ampy_bk_missing', 'Namn, e-post, telefon och postnummer krävs.', array( 'status' => 400 ) );
    }
    if ( ! $samtycke ) {
        return new WP_Error( 'ampy_bk_consent', 'Vi behöver ditt samtycke för att höra av oss.', array( 'status' => 400 ) );
    }
    if ( ! is_email( $kontakt ) ) {
        return new WP_Error( 'ampy_bk_epost', 'Ange en giltig e-postadress.', array( 'status' => 400 ) );
    }
    if ( ! preg_match( '/^[\d\s\+\-\(\)]{6,}$/', $telefon ) ) {
        return new WP_Error( 'ampy_bk_telefon', 'Ange ett giltigt telefonnummer.', array( 'status' => 400 ) );
    }
    if ( ! preg_match( '/^\d{5}$/', $postnummer ) ) {
        return new WP_Error( 'ampy_bk_postnummer', 'Ange ett giltigt postnummer (5 siffror).', array( 'status' => 400 ) );
    }
    if ( ! in_array( $verdict, array( 'green', 'yellow', 'red' ), true ) ) {
        return new WP_Error( 'ampy_bk_verdict', 'Okänt verdict.', array( 'status' => 400 ) );
    }

    // Verify the job_id exists in data - never trust client.
    $data = ampy_bk_get_data();
    if ( ! $data ) {
        return new WP_Error( 'ampy_bk_data', 'Internt fel - datafil saknas.', array( 'status' => 500 ) );
    }
    $job_label = '';
    foreach ( $data['jobs'] as $j ) {
        if ( $j['id'] === $job_id ) { $job_label = $j['label']; break; }
    }
    if ( ! $job_label ) {
        return new WP_Error( 'ampy_bk_job', 'Okänt jobb.', array( 'status' => 400 ) );
    }

    // ── Structured lead payload (shared by webhook + email). ────────────────
    $payload = array(
        'tool'       => 'elkollen',
        'job_id'     => $job_id,
        'job_label'  => $job_label,
        'verdict'    => $verdict,
        'namn'       => $namn,
        'kontakt'    => $kontakt,
        'telefon'    => $telefon,
        'postnummer' => $postnummer,
        'meddelande' => $meddelande,
        'samtycke'   => true,
        'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
        'tid'        => current_time( 'mysql' ),
    );

    // 3. DURABLE DELIVERY - n8n webhook is primary, email is the fallback.
    //    Both configured under Settings → Elkollen. The browser is told success
    //    ONLY when a durable path delivered (webhook 2xx, or fallback email
    //    accepted), mirroring the EV/battery calculators.
    $webhook_url  = (string) get_post_meta( AMPY_BK_POST_ID, '_ampy_bk_webhook_url', true );
    $notify_email = sanitize_email( (string) get_post_meta( AMPY_BK_POST_ID, '_ampy_bk_notify_email', true ) );
    if ( ! $notify_email || ! is_email( $notify_email ) ) {
        $notify_email = get_option( 'admin_email' );
    }

    $delivered     = false;
    $delivery_via  = 'none';
    $delivery_note = '';

    if ( $webhook_url ) {
        $resp = wp_remote_post( $webhook_url, array(
            'headers'     => array( 'Content-Type' => 'application/json' ),
            'body'        => wp_json_encode( $payload ),
            'timeout'     => 8,       // short, but blocking so we learn the outcome
            'blocking'    => true,
            'data_format' => 'body',
        ) );
        if ( is_wp_error( $resp ) ) {
            $delivery_note = 'webhook WP_Error: ' . $resp->get_error_message();
        } else {
            $code = (int) wp_remote_retrieve_response_code( $resp );
            if ( $code >= 200 && $code < 300 ) {
                $delivered    = true;
                $delivery_via = 'webhook';
            } else {
                $delivery_note = 'webhook HTTP ' . $code;
            }
        }
    }

    // Fall through to email if the webhook did not durably deliver (or none set).
    if ( ! $delivered ) {
        $subject = sprintf( '[Behörighetskollen] Lead: %s (%s)', $job_label, strtoupper( $verdict ) );
        $body = sprintf(
            "Ny offertförfrågan via Behörighetskollen\n\n" .
            "Jobb: %s (%s)\nBesked: %s\n\n" .
            "Namn: %s\nE-post: %s\nTelefon: %s\nPostnummer: %s\n\nMeddelande:\n%s\n\n" .
            "IP: %s\nTid: %s",
            $job_label, $job_id, strtoupper( $verdict ),
            $namn, $kontakt, $telefon ?: '-', $postnummer ?: '-',
            $meddelande ?: '-',
            $payload['ip'] ?: '-',
            $payload['tid']
        );
        if ( wp_mail( $notify_email, $subject, $body ) ) {
            $delivered    = true;
            $delivery_via = ( $delivery_note !== '' ) ? 'email_fallback' : 'email';
        } else {
            $delivery_note = trim( $delivery_note . ' | wp_mail failed' );
        }
    }

    // 4. Persist hook (CRM/CPT listeners). Fires regardless of channel outcome.
    do_action( 'ampy_bk_lead_received', $payload + array(
        'delivery' => array( 'ok' => $delivered, 'via' => $delivery_via ),
    ) );

    if ( ! $delivered ) {
        // Safety net so a lead is never silently lost.
        error_log( sprintf(
            'AMPY_BK LEAD (undelivered: %s) | jobb=%s verdict=%s | namn=%s | epost=%s | tel=%s | postnr=%s | tid=%s',
            $delivery_note ?: 'no channel configured',
            $job_id, $verdict, $namn, $kontakt, $telefon, $postnummer, current_time( 'mysql' )
        ) );
        return new WP_Error( 'ampy_bk_deliver', 'Kunde inte skicka just nu. Ring oss på 010-265 79 79 så hjälper vi dig.', array( 'status' => 502 ) );
    }
    return new WP_REST_Response( array( 'ok' => true, 'message' => 'Tack! Vi hör av oss inom kort.' ), 200 );
}


/* ==========================================================================
   6. EXCEL EDITOR PARITY  (Settings -> Elkollen)
   Mirrors the battery/LED ZipArchive + SimpleXML parser under ampy_bk_* names
   so the snippets never collide on redeclare. Elkollen bakes its data globally
   (no per-post lead-magnet binding), so the chosen .xlsx is stored as a site
   option and parsed on change; the result is merged over the baked default in
   ampy_bk_get_data(). The sheet drives the review/legal content (verdicts,
   citations, summaries, tips) and the sign-off metadata. Structure (rooms,
   service zones, conditional branching shape, quick picks) stays baked.

   Convention (mirrors battery/LED): header = row 1, row 2 is a hint row and is
   skipped, data starts on row 3. Sheets: Granskning, Jobb, Alternativ, Tips,
   Utlatanden. Any omitted sheet/row/cell keeps the baked default, so an
   unedited template is a no-op.

   SIGN-OFF: the Granskning sheet is the launch gate. Its "pending" cell maps to
   meta._pending_verification: leave it blank to keep Elkollen public; put text
   in it (e.g. a reason) to re-gate so only logged-in editors see the tool.
   ========================================================================== */

add_action( 'add_meta_boxes', function ( $post_type, $post ) {
    if ( $post_type !== 'lead-magnet' ) return;
    if ( AMPY_BK_POST_ID > 0 && (int) $post->ID !== (int) AMPY_BK_POST_ID ) return;
    add_meta_box( 'ampy_bk_settings', 'Elkollen - Settings', 'ampy_bk_render_metabox', 'lead-magnet', 'normal', 'high' );
}, 10, 2 );

// Media-library picker for the .xlsx, only on the bound lead-magnet editor.
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) return;
    $pid = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
    if ( AMPY_BK_POST_ID > 0 && $pid !== (int) AMPY_BK_POST_ID ) return;
    wp_enqueue_media();
} );

function ampy_bk_render_metabox( WP_Post $post ): void {
    wp_nonce_field( 'ampy_bk_save_' . $post->ID, '_ampy_bk_nonce' );

    $sheet_id     = (int) get_post_meta( $post->ID, '_ampy_bk_sheet_id', true );
    $sheet_name   = $sheet_id ? basename( (string) get_attached_file( $sheet_id ) ) : '';
    $sheet_url    = $sheet_id ? wp_get_attachment_url( $sheet_id ) : '';
    $status       = (string) get_post_meta( $post->ID, '_ampy_bk_import_status', true );
    $last         = (string) get_post_meta( $post->ID, '_ampy_bk_last_import',   true );
    $webhook_url  = (string) get_post_meta( $post->ID, '_ampy_bk_webhook_url',   true );
    $notify_email = (string) get_post_meta( $post->ID, '_ampy_bk_notify_email',  true );
    $data         = ampy_bk_get_data();
    $has_sheet    = ! empty( get_post_meta( $post->ID, '_ampy_bk_sheet_data', true ) );
    $src          = $has_sheet ? 'uploaded Excel sheet' : 'built-in default';
    $pending      = ampy_bk_is_pending( $data );
    $njobs        = count( $data['jobs'] ?? [] );

    echo '<p style="color:#444;margin:0 0 12px;">Status: <strong>' . esc_html( $pending ? 'Under granskning (dold för publik)' : 'Publik' ) . '</strong>'
       . ' &middot; Datakälla: <strong>' . esc_html( $src ) . '</strong> &middot; ' . (int) $njobs . ' jobb</p>';

    echo '<p style="font-weight:600;margin-bottom:4px;">Datakälla - Excel (.xlsx)</p>';
    echo '<p style="color:#666;margin:0 0 8px;max-width:46em;">Ladda upp Elkollen-arket för att uppdatera utlåtanden, källor, summeringar och tips, samt registrera signatur (fliken Granskning). Allt som inte fylls i behåller standardvärdet. Töm för att gå tillbaka till standard.</p>';
    echo '<input type="hidden" id="ampy_bk_sheet_id" name="ampy_bk_sheet_id" value="' . esc_attr( $sheet_id ?: '' ) . '">';
    echo '<p><button type="button" class="button" id="ampy_bk_upload_btn">' . ( $sheet_id ? 'Byt Excel-fil' : 'Välj Excel-fil' ) . '</button> ';
    if ( $sheet_name ) {
        echo '<code id="ampy_bk_sheet_name">' . esc_html( $sheet_name ) . '</code> ';
        if ( $sheet_url ) echo '<a href="' . esc_url( $sheet_url ) . '" target="_blank" rel="noopener">Öppna</a> ';
        echo '<button type="button" class="button-link" id="ampy_bk_clear_btn" style="color:#b32d2e;">Ta bort</button>';
    } else {
        echo '<span id="ampy_bk_sheet_name" style="color:#888;">Ingen fil vald</span>';
    }
    echo '</p>';
    if ( $last ) {
        $ok = ( strpos( $status, 'OK' ) !== false );
        echo '<p>Senaste import: ' . esc_html( $last )
           . ' <span style="padding:2px 8px;border-radius:4px;font-size:11px;background:' . ( $ok ? '#d4f7e7' : '#fce8e8' ) . ';color:' . ( $ok ? '#1a6b3c' : '#b91c1c' ) . ';">' . esc_html( $status ) . '</span></p>';
    }

    echo '<hr><p style="font-weight:600;margin-bottom:4px;">Lead-leverans</p>';
    echo '<p style="color:#666;margin:0 0 8px;max-width:46em;">Offertförfrågningar skickas i första hand till n8n-webhooken (hela leaden som JSON). Misslyckas den, eller är den tom, används reserv-e-posten.</p>';
    echo '<p><label style="display:block;font-weight:600;margin-bottom:4px;">n8n Webhook-URL</label>';
    echo '<input type="url" name="ampy_bk_webhook_url" value="' . esc_attr( $webhook_url ) . '" placeholder="https://your-n8n.com/webhook/..." style="width:100%;font-family:monospace;"></p>';
    echo '<p><label style="display:block;font-weight:600;margin-bottom:4px;">Reserv-e-post</label>';
    echo '<input type="email" name="ampy_bk_notify_email" value="' . esc_attr( $notify_email ) . '" placeholder="' . esc_attr( get_option( 'admin_email' ) ) . '" style="width:100%;"></p>';
    echo '<p style="color:#888;font-size:12px;">Tom webhook = endast e-post. Tom e-post = webbplatsens admin-e-post.</p>';
    ?>
    <script>
    (function($){
        var frame;
        $('#ampy_bk_upload_btn').on('click', function(e){
            e.preventDefault();
            if ( frame ) { frame.open(); return; }
            frame = wp.media({ title:'Välj Elkollen .xlsx', button:{ text:'Använd filen' }, multiple:false });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                $('#ampy_bk_sheet_id').val(att.id);
                $('#ampy_bk_sheet_name').replaceWith('<code id="ampy_bk_sheet_name">'+att.filename+'</code>');
                $('#ampy_bk_upload_btn').text('Byt Excel-fil');
            });
            frame.open();
        });
        $('#ampy_bk_clear_btn').on('click', function(e){
            e.preventDefault();
            $('#ampy_bk_sheet_id').val('');
            $('#ampy_bk_sheet_name').replaceWith('<span id="ampy_bk_sheet_name" style="color:#888;">Ingen fil vald</span>');
        });
    }(jQuery));
    </script>
    <?php
}

add_action( 'save_post_lead-magnet', function ( int $post_id, WP_Post $post, bool $update ): void {
    if ( AMPY_BK_POST_ID > 0 && $post_id !== (int) AMPY_BK_POST_ID ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['_ampy_bk_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_ampy_bk_nonce'] ), 'ampy_bk_save_' . $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    update_post_meta( $post_id, '_ampy_bk_webhook_url',  esc_url_raw( wp_unslash( $_POST['ampy_bk_webhook_url'] ?? '' ) ) );
    update_post_meta( $post_id, '_ampy_bk_notify_email', sanitize_email( wp_unslash( $_POST['ampy_bk_notify_email'] ?? '' ) ) );

    $new = absint( $_POST['ampy_bk_sheet_id'] ?? 0 );
    $old = (int) get_post_meta( $post_id, '_ampy_bk_sheet_id', true );
    update_post_meta( $post_id, '_ampy_bk_sheet_id', $new );
    if ( $new === 0 ) {
        delete_post_meta( $post_id, '_ampy_bk_sheet_data' );
        ampy_bk_set_import_status( $post_id, 'Cleared - using built-in default.' );
    } elseif ( $new !== $old ) {
        ampy_bk_parse_sheet( $post_id, $new );
    }
}, 10, 3 );

/* ---- xlsx primitives (verbatim battery/LED approach, ampy_bk_* names) ---- */
function ampy_bk_col_idx( string $col ): int {
    $col = strtoupper( $col ); $idx = 0;
    for ( $i = 0, $len = strlen( $col ); $i < $len; $i++ ) $idx = $idx * 26 + ( ord( $col[ $i ] ) - 64 );
    return $idx - 1;
}

function ampy_bk_xlsx_shared_strings( ZipArchive $zip ): array {
    $xml = $zip->getFromName( 'xl/sharedStrings.xml' );
    if ( ! $xml ) return [];
    $ss = new SimpleXMLElement( $xml ); $out = [];
    foreach ( $ss->si as $si ) {
        if ( isset( $si->t ) ) { $out[] = (string) $si->t; }
        elseif ( isset( $si->r ) ) { $t = ''; foreach ( $si->r as $r ) $t .= (string) $r->t; $out[] = $t; }
        else { $out[] = ''; }
    }
    return $out;
}

function ampy_bk_xlsx_sheet_map( ZipArchive $zip ): array {
    $wb   = $zip->getFromName( 'xl/workbook.xml' );
    $rels = $zip->getFromName( 'xl/_rels/workbook.xml.rels' );
    if ( ! $wb || ! $rels ) return [];
    $wbx = new SimpleXMLElement( $wb ); $relsx = new SimpleXMLElement( $rels );
    $rid = [];
    foreach ( $relsx->Relationship as $rel ) $rid[ (string) $rel['Id'] ] = 'xl/' . ltrim( (string) $rel['Target'], '/' );
    $map = [];
    foreach ( $wbx->sheets->sheet as $sheet ) {
        $id = (string) $sheet->attributes( 'r', true )->id;
        $nm = (string) $sheet['name'];
        if ( isset( $rid[ $id ] ) ) $map[ $nm ] = $rid[ $id ];
    }
    return $map;
}

function ampy_bk_xlsx_read_sheet( ZipArchive $zip, string $path, array $shared ): array {
    $xml = $zip->getFromName( $path );
    if ( ! $xml ) return [];
    $ws = new SimpleXMLElement( $xml ); $rows = [];
    foreach ( $ws->sheetData->row as $row ) {
        $ri = (int) $row['r'] - 1; $rd = [];
        foreach ( $row->c as $cell ) {
            $c = ampy_bk_col_idx( preg_replace( '/\d/', '', (string) $cell['r'] ) );
            $t = (string) $cell['t']; $v = (string) $cell->v;
            if ( $t === 's' ) $v = $shared[ (int) $v ] ?? '';
            elseif ( $t === 'b' ) $v = $v === '1' ? 'TRUE' : 'FALSE';
            elseif ( $t === 'inlineStr' ) $v = (string) $cell->is->t;
            while ( count( $rd ) <= $c ) $rd[] = '';
            $rd[ $c ] = $v;
        }
        $rows[ $ri ] = $rd;
    }
    ksort( $rows );
    return array_values( $rows );
}

function ampy_bk_xlsx_header_map( array $rows ): array { return array_flip( $rows[0] ?? [] ); }

function ampy_bk_set_import_status( int $post_id, string $msg ): void {
    update_post_meta( $post_id, '_ampy_bk_import_status', $msg );
    update_post_meta( $post_id, '_ampy_bk_last_import', current_time( 'mysql' ) );
}

function ampy_bk_truthy( $v ): bool {
    $v = strtolower( trim( (string) $v ) );
    return in_array( $v, [ 'ja', 'yes', 'true', '1', 'x' ], true );
}

/* ---- the parser: merge the sheet's review content over the baked default --- */
function ampy_bk_parse_sheet( int $post_id, int $attachment_id ): void {
    $file = get_attached_file( $attachment_id );
    if ( ! $file || ! file_exists( $file ) ) { ampy_bk_set_import_status( $post_id, 'Error: file not found on server.' ); return; }
    if ( strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) !== 'xlsx' ) { ampy_bk_set_import_status( $post_id, 'Error: only .xlsx files are supported.' ); return; }
    if ( ! class_exists( 'ZipArchive' ) ) { ampy_bk_set_import_status( $post_id, 'Error: PHP ZipArchive extension not available.' ); return; }

    try {
        $zip = new ZipArchive();
        if ( true !== $zip->open( $file ) ) throw new RuntimeException( 'Could not open .xlsx (invalid zip archive).' );
        $shared = ampy_bk_xlsx_shared_strings( $zip );
        $map    = ampy_bk_xlsx_sheet_map( $zip );
        $read = function ( string $name ) use ( $zip, $map, $shared ): array {
            return isset( $map[ $name ] ) ? ampy_bk_xlsx_read_sheet( $zip, $map[ $name ], $shared ) : [];
        };

        $data    = ampy_bk_baked_data();
        if ( empty( $data['jobs'] ) ) throw new RuntimeException( 'Baked default unreadable.' );
        $changed = [];

        // -- Granskning: key/value sign-off + launch gate ---------------------
        $rows = $read( 'Granskning' );
        if ( $rows ) {
            $col = ampy_bk_xlsx_header_map( $rows );
            for ( $i = 2; $i < count( $rows ); $i++ ) {
                $r = $rows[ $i ];
                $k = trim( $r[ $col['nyckel'] ?? -1 ] ?? '' );
                $v = isset( $col['varde'] ) ? (string) ( $r[ $col['varde'] ] ?? '' ) : '';
                if ( $k === 'reviewed_by' )           { $data['meta']['reviewed_by'] = trim( $v ); $changed['granskning'] = true; }
                elseif ( $k === 'last_reviewed' )     { $data['meta']['last_reviewed'] = trim( $v ); $changed['granskning'] = true; }
                elseif ( $k === 'pending' )           { $data['meta']['_pending_verification'] = trim( $v ); $changed['granskning'] = true; }
            }
        }

        // -- Jobb: per-job review fields, keyed by id -------------------------
        $rows = $read( 'Jobb' );
        if ( $rows ) {
            $col = ampy_bk_xlsx_header_map( $rows );
            $by  = [];
            for ( $i = 2; $i < count( $rows ); $i++ ) {
                $r  = $rows[ $i ];
                $id = trim( $r[ $col['id'] ?? -1 ] ?? '' );
                if ( $id !== '' ) $by[ $id ] = $r;
            }
            foreach ( $data['jobs'] as $ji => $job ) {
                $id = $job['id'] ?? '';
                if ( ! isset( $by[ $id ] ) ) continue;
                $r = $by[ $id ];
                foreach ( [ 'default_verdict', 'question', 'summary', 'do', 'dont', 'rule_citation', 'why_text' ] as $f ) {
                    // Only override keys the baked job actually has (keeps shape correct
                    // per type: fixed has do/dont/default_verdict, conditional has question).
                    if ( array_key_exists( $f, $job ) && isset( $col[ $f ] ) ) {
                        $cell = (string) ( $r[ $col[ $f ] ] ?? '' );
                        if ( trim( $cell ) !== '' ) { $data['jobs'][ $ji ][ $f ] = $cell; $changed['jobb'] = true; }
                    }
                }
            }
        }

        // -- Alternativ: conditional-job options, keyed by job_id + index ------
        $rows = $read( 'Alternativ' );
        if ( $rows ) {
            $col = ampy_bk_xlsx_header_map( $rows );
            for ( $i = 2; $i < count( $rows ); $i++ ) {
                $r   = $rows[ $i ];
                $jid = trim( $r[ $col['job_id'] ?? -1 ] ?? '' );
                $nr  = (int) ( $r[ $col['alternativ_nr'] ?? -1 ] ?? 0 );
                if ( $jid === '' || $nr < 1 ) continue;
                foreach ( $data['jobs'] as $ji => $job ) {
                    if ( ( $job['id'] ?? '' ) !== $jid || empty( $job['options'] ) ) continue;
                    $oi = $nr - 1;
                    if ( ! isset( $data['jobs'][ $ji ]['options'][ $oi ] ) ) break;
                    foreach ( [ 'verdict', 'summary', 'do', 'dont' ] as $f ) {
                        if ( isset( $col[ $f ] ) ) {
                            $cell = (string) ( $r[ $col[ $f ] ] ?? '' );
                            if ( trim( $cell ) !== '' ) { $data['jobs'][ $ji ]['options'][ $oi ][ $f ] = $cell; $changed['alternativ'] = true; }
                        }
                    }
                    break;
                }
            }
        }

        // -- Tips: replace a job's tips when that job has rows on the sheet ----
        $rows = $read( 'Tips' );
        if ( $rows ) {
            $col = ampy_bk_xlsx_header_map( $rows );
            $buckets = []; // job_id => list of [ordning, allowed, text]
            for ( $i = 2; $i < count( $rows ); $i++ ) {
                $r   = $rows[ $i ];
                $jid = trim( $r[ $col['job_id'] ?? -1 ] ?? '' );
                $txt = (string) ( $r[ $col['text'] ?? -1 ] ?? '' );
                if ( $jid === '' || trim( $txt ) === '' ) continue;
                $ord = (int) ( $r[ $col['ordning'] ?? -1 ] ?? ( count( $buckets[ $jid ] ?? [] ) + 1 ) );
                $allowed = isset( $col['tillaten'] ) ? ampy_bk_truthy( $r[ $col['tillaten'] ] ?? '' ) : true;
                $buckets[ $jid ][] = [ 'ord' => $ord, 'allowed' => $allowed, 'text' => $txt ];
            }
            foreach ( $buckets as $jid => $list ) {
                usort( $list, fn( $a, $b ) => $a['ord'] <=> $b['ord'] );
                $tips = array_map( fn( $t ) => [ 'text' => $t['text'], 'allowed' => $t['allowed'] ], $list );
                foreach ( $data['jobs'] as $ji => $job ) {
                    if ( ( $job['id'] ?? '' ) === $jid ) { $data['jobs'][ $ji ]['tips'] = $tips; $changed['tips'] = true; break; }
                }
            }
        }

        // -- Utlatanden: the three verdict definitions ------------------------
        $rows = $read( 'Utlatanden' );
        if ( $rows ) {
            $col = ampy_bk_xlsx_header_map( $rows );
            for ( $i = 2; $i < count( $rows ); $i++ ) {
                $r = $rows[ $i ];
                $k = trim( $r[ $col['nyckel'] ?? -1 ] ?? '' );
                if ( ! isset( $data['verdicts'][ $k ] ) ) continue;
                foreach ( [ 'label', 'caveat', 'consequence' ] as $f ) {
                    if ( array_key_exists( $f, $data['verdicts'][ $k ] ) && isset( $col[ $f ] ) ) {
                        $cell = (string) ( $r[ $col[ $f ] ] ?? '' );
                        if ( trim( $cell ) !== '' ) { $data['verdicts'][ $k ][ $f ] = $cell; $changed['utlatanden'] = true; }
                    }
                }
                // source is an object {text,url}; two columns drive it.
                if ( isset( $data['verdicts'][ $k ]['source'] ) && is_array( $data['verdicts'][ $k ]['source'] ) ) {
                    $st = isset( $col['source_text'] ) ? trim( (string) ( $r[ $col['source_text'] ] ?? '' ) ) : '';
                    $su = isset( $col['source_url'] )  ? trim( (string) ( $r[ $col['source_url'] ] ?? '' ) )  : '';
                    if ( $st !== '' ) { $data['verdicts'][ $k ]['source'] = [ 'text' => $st, 'url' => $su ]; $changed['utlatanden'] = true; }
                }
            }
        }

        $zip->close();

        // wp_slash: update_metadata() runs wp_unslash() on the value, which would
        // strip the JSON's backslash escapes (breaking any text containing a quote).
        // Slash first so the stored value is the exact wp_json_encode() output.
        update_post_meta( $post_id, '_ampy_bk_sheet_data',
            wp_slash( wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) );

        $parts = array_keys( $changed );
        $gate  = ampy_bk_is_pending( $data ) ? ' [gate: under granskning]' : ' [gate: publik]';
        ampy_bk_set_import_status( $post_id, 'OK - updated: ' . ( $parts ? implode( ', ', $parts ) : 'no recognised sheets (kept defaults)' ) . '.' . $gate );

    } catch ( Throwable $e ) {
        ampy_bk_set_import_status( $post_id, 'Error: ' . $e->getMessage() );
    }
}


/* ==========================================================================
   SHARED LEAD-MAGNET HERO HELPERS
   --------------------------------------------------------------------------
   Defined once and reused by Elkollen and Elcentral-kollen. Guarded, so it does
   not matter which snippet FluentSnippets loads first.

   Content model (nothing existing was re-keyed, nothing is lost):
     hero_heading   text      - already live, SCF group "Hero"
     hero_text      wysiwyg   - already live, SCF group "Hero"
     list_items     repeater  - already live, SCF group "Lead Magnet List Items"
                                 sub-fields: icon (image), text (wysiwyg)
     lm_cta_*       NEW       - registered below, so the two buttons stop being
                                 hard-coded in Bricks
   Every read falls back to the prototype's own string, so an empty field renders
   the prototype rather than a blank.
   ========================================================================== */

if ( ! function_exists( 'ampy_lm_source_id' ) ) {
/** Prefer the post being viewed when it carries hero copy, else the tool's own
 *  lead-magnet post. Keeps working when the tool is embedded on a service page. */
function ampy_lm_source_id( int $fallback ): int {
    /* Only a lead-magnet post may act as the source. `hero_heading` also exists on
       service / elektriker-i / eljour-i / elinstallation-i / laddbox-i /
       elektriker-for-x, so without this guard a tool embedded on a service page
       would take THAT page's hero heading, while its bullets and buttons (which
       only exist on lead-magnet) fell back to the prototype defaults. Mixed copy.
       The tool's own lead-magnet post is always the fallback. */
    $here = (int) get_the_ID();
    if ( $here && get_post_type( $here ) === 'lead-magnet' && function_exists( 'get_field' ) ) {
        $v = get_field( 'hero_heading', $here );
        if ( is_string( $v ) && trim( $v ) !== '' ) return $here;
    }
    return $fallback;
}}

if ( ! function_exists( 'ampy_lm_text' ) ) {
/** Plain-text field with a hard default. */
function ampy_lm_text( string $name, int $post_id, string $default = '' ): string {
    $v = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : '';
    if ( ! is_scalar( $v ) || trim( (string) $v ) === '' ) {
        $v = get_post_meta( $post_id, $name, true );
    }
    if ( ! is_scalar( $v ) || trim( (string) $v ) === '' ) return $default;
    return trim( (string) $v );
}}

if ( ! function_exists( 'ampy_lm_rich' ) ) {
/** Rich-text field, sanitised, with a single wrapping <p> removed so the value can
 *  sit inside the prototype's own <p>. Returns plain text when $strip is true. */
function ampy_lm_rich( string $name, int $post_id, string $default = '', bool $strip = false ): string {
    $v = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : '';
    if ( ! is_scalar( $v ) || trim( (string) $v ) === '' ) $v = get_post_meta( $post_id, $name, true );
    $html = is_scalar( $v ) ? trim( (string) $v ) : '';
    if ( $html === '' ) $html = $default;
    if ( $html === '' ) return '';
    if ( $strip ) return trim( wp_strip_all_tags( $html ) );
    $html = wp_kses_post( $html );
    if ( preg_match( '#^<p[^>]*>(.*)</p>$#is', $html, $m ) && stripos( $m[1], '<p' ) === false ) {
        $html = $m[1];
    }
    return $html;
}}

if ( ! function_exists( 'ampy_lm_icon' ) ) {
/** Icon for a hero bullet. An SVG chosen in the media library is inlined so it
 *  inherits currentColor exactly like the prototype's own icons; any other image
 *  becomes a 20x20 <img>. With no icon set you get the prototype's icon back. */
function ampy_lm_icon( $icon, string $fallback_svg ): string {
    $id = 0;
    if ( is_array( $icon ) )      $id = (int) ( $icon['ID'] ?? $icon['id'] ?? 0 );
    elseif ( is_numeric( $icon ) ) $id = (int) $icon;
    if ( ! $id ) return $fallback_svg;

    if ( get_post_mime_type( $id ) === 'image/svg+xml' ) {
        $file = get_attached_file( $id );
        if ( $file && file_exists( $file ) && filesize( $file ) < 51200 ) {
            $svg = (string) file_get_contents( $file );
            $svg = preg_replace( '#<\?xml.*?\?>#is', '', $svg );
            $svg = preg_replace( '#<!DOCTYPE.*?>#is', '', $svg );
            $svg = preg_replace( '#<!--.*?-->#s', '', $svg );
            $svg = preg_replace( '#<script.*?</script>#is', '', $svg );          // never inline script
            $svg = preg_replace( '#\son\w+\s*=\s*("[^"]*"|\x27[^\x27]*\x27)#i', '', $svg ); // strip handlers
            $svg = trim( (string) $svg );
            if ( stripos( $svg, '<svg' ) === 0 ) return $svg;
        }
    }
    $url = wp_get_attachment_image_url( $id, 'thumbnail' );
    if ( ! $url ) $url = wp_get_attachment_url( $id );
    if ( $url ) return '<img src="' . esc_url( $url ) . '" alt="" width="20" height="20" loading="lazy" decoding="async">';
    return $fallback_svg;
}}

if ( ! function_exists( 'ampy_lm_bullets' ) ) {
/** The `list_items` repeater, merged over the prototype's defaults.
 *  $defaults = [ [ 'svg' => '<svg…>', 'text' => '…' ], … ] */
function ampy_lm_bullets( int $post_id, array $defaults ): array {
    $rows = function_exists( 'get_field' ) ? get_field( 'list_items', $post_id ) : null;
    if ( ! is_array( $rows ) || ! $rows ) return $defaults;

    $out = array();
    foreach ( $rows as $i => $row ) {
        $text = isset( $row['text'] ) ? trim( wp_strip_all_tags( (string) $row['text'] ) ) : '';
        if ( $text === '' ) continue;
        $fallback = $defaults[ $i ]['svg'] ?? ( $defaults[0]['svg'] ?? '' );
        $out[] = array(
            'svg'  => ampy_lm_icon( $row['icon'] ?? 0, $fallback ),
            'text' => $text,
        );
    }
    return $out ? $out : $defaults;
}}

if ( ! function_exists( 'ampy_lm_cta' ) ) {
/** The two hero buttons. Text and links are fields; the SVGs are the design's. */
function ampy_lm_cta( int $post_id ): array {
    $phone_opt = function_exists( 'get_field' ) ? get_field( 'site_phone_number_local', 'option' ) : '';
    $phone_lbl = ( is_string( $phone_opt ) && trim( $phone_opt ) !== '' ) ? trim( $phone_opt ) : '010-265 79 79';
    return array(
        'primary_text' => ampy_lm_text( 'lm_cta_primary_text', $post_id, 'Kontakta oss' ),
        'primary_url'  => ampy_lm_text( 'lm_cta_primary_url',  $post_id, 'https://ampy.se/offert/' ),
        'phone_text'   => ampy_lm_text( 'lm_cta_phone_text',   $post_id, $phone_lbl ),
        'phone_url'    => ampy_lm_text( 'lm_cta_phone_url',    $post_id, 'tel:+46102657979' ),
    );
}}

if ( ! function_exists( 'ampy_lm_register_cta_fields' ) ) {
/** The two CTA buttons were hard-coded in Bricks. Register them as real fields so
 *  they can be edited in wp-admin. Existing groups (Hero, Lead Magnet List Items)
 *  are NOT touched: this is an additional local group, so no saved value is lost. */
function ampy_lm_register_cta_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;
    $mk = function ( $name, $label, $default, $ph = '' ) {
        return array(
            'key'           => 'field_' . $name,
            'label'         => $label,
            'name'          => $name,
            'type'          => 'text',
            'default_value' => $default,
            'placeholder'   => $ph ?: $default,
            'allow_in_bindings' => 1,
        );
    };
    acf_add_local_field_group( array(
        'key'    => 'group_ampy_lm_cta',
        'title'  => 'Lead magnet - hero buttons',
        'fields' => array(
            $mk( 'lm_cta_primary_text', 'Primary button label', 'Kontakta oss' ),
            $mk( 'lm_cta_primary_url',  'Primary button link',  'https://ampy.se/offert/' ),
            $mk( 'lm_cta_phone_text',   'Phone button label',   '010-265 79 79' ),
            $mk( 'lm_cta_phone_url',    'Phone button link',    'tel:+46102657979' ),
        ),
        'location' => array( array(
            array( 'param' => 'post_type', 'operator' => '==', 'value' => 'lead-magnet' ),
            array( 'param' => 'post',      'operator' => '!=', 'value' => '59306' ), // Eljour has its own copy group
        ) ),
        'menu_order'      => 5,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
        'active'          => true,
    ) );
}
add_action( 'acf/init', 'ampy_lm_register_cta_fields' );
}


/* ==========================================================================
   ELKOLLEN HERO  -  the prototype's split-hero, server-rendered from fields
   ========================================================================== */
if ( ! function_exists( 'ampy_bk_hero' ) ) {
function ampy_bk_hero( string $tool_html ): string {
    // Copy is data-driven from meta.hero so an owner swap is a data-file edit only.
    // Fallbacks below = the prototype's literal strings (preview/hero.html meta.hero).
    $hero = ampy_bk_get_data()["meta"]["hero"] ?? array();
    $pid  = ampy_lm_source_id( AMPY_BK_POST_ID );

    // Left-rail copy is EDITABLE in wp-admin (shared lead-magnet Hero + List Items +
    // CTA fields on the post); meta.hero from the data file is only the fallback.
    $h1_main   = ampy_lm_text( "hero_heading",    $pid, "Får du göra eljobbet själv?" );
    $h1_key    = ampy_lm_text( "hero_subheading", $pid, (string) ( $hero["h1_key"] ?? "Kolla innan du kopplar." ) );
    $sub       = ampy_lm_rich( "hero_text",       $pid, (string) ( $hero["sub"] ?? "Välj installationen du funderar på och få ett tydligt besked direkt!" ), true );
    $cta       = ampy_lm_cta( $pid );
    $btn_p     = $cta["primary_text"];
    $btn_p_url = $cta["primary_url"];
    $btn_s     = $cta["phone_text"];
    $btn_s_url = $cta["phone_url"];
    $ask       = (string) ( $hero["mobile_ask"] ?? "Hellre prata med en elektriker direkt?" );
    $trust_3   = (string) ( $hero["trust_3"]    ?? "Få ett tydligt besked på några sekunder" );

    // H1 = editable main line + optional teal key line (hero_subheading).
    $h1_html = esc_html( $h1_main );
    if ( trim( $h1_key ) !== "" ) {
        $h1_html .= ' <span class="hero__h1-key">' . esc_html( $h1_key ) . '</span>';
    }

    // Inline SVGs — VERBATIM from preview/hero.html (do not re-encode).
    $svg_check   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
    $svg_shield  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>';
    $svg_circle  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>';
    $svg_arrow   = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M4.66406 11.3333L11.3307 4.66663M11.3307 4.66663H4.66406M11.3307 4.66663V11.3333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    $svg_phone   = '<svg viewBox="0 0 19 20" fill="none" aria-hidden="true"><path d="M11.9146 4.16668C12.6878 4.32548 13.3984 4.72356 13.9555 5.30996C14.5126 5.89635 14.8908 6.6444 15.0416 7.45834M11.9146 0.833344C13.5211 1.02121 15.0191 1.77849 16.1628 2.98085C17.3065 4.18321 18.0278 5.75918 18.2083 7.45001M17.4166 14.1V16.6C17.4175 16.8321 17.3724 17.0618 17.284 17.2745C17.1957 17.4871 17.0662 17.678 16.9037 17.8349C16.7412 17.9918 16.5494 18.1112 16.3406 18.1856C16.1317 18.26 15.9104 18.2876 15.6908 18.2667C13.2547 17.988 10.9147 17.1118 8.85872 15.7083C6.94591 14.4289 5.32419 12.7218 4.10872 10.7083C2.77078 8.53435 1.93816 6.05917 1.6783 3.48334C1.65852 3.2529 1.68454 3.02064 1.7547 2.80136C1.82486 2.58208 1.93763 2.38058 2.08582 2.20969C2.23402 2.0388 2.4144 1.90227 2.61547 1.80878C2.81654 1.71529 3.0339 1.66689 3.25372 1.66668H5.62872C6.01292 1.6627 6.38539 1.80591 6.6767 2.06962C6.968 2.33333 7.15828 2.69955 7.21205 3.10001C7.31229 3.90007 7.4982 4.68562 7.76622 5.44168C7.87273 5.73995 7.89578 6.06411 7.83264 6.37574C7.7695 6.68738 7.62282 6.97344 7.40997 7.20001L6.40455 8.25834C7.53153 10.3446 9.17258 12.072 11.1546 13.2583L12.16 12.2C12.3752 11.976 12.647 11.8216 12.943 11.7551C13.2391 11.6886 13.547 11.7129 13.8304 11.825C14.5486 12.1071 15.2949 12.3028 16.055 12.4083C16.4395 12.4655 16.7907 12.6693 17.0418 12.9813C17.2929 13.2932 17.4263 13.6913 17.4166 14.1Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>';

    $html  = '<div class="elkollen-root" data-hero-theme="light">';
    $html .=   '<header class="hero">';
    $html .=     '<div class="wrap hero__grid">';

    // HEAD: H1 + sub
    $html .=       '<div class="hero__head">';
    $html .=         '<h1 class="hero__h1">' . $h1_html . '</h1>';
    $html .=         '<p class="hero__sub">' . esc_html( $sub ) . '</p>';
    $html .=       '</div>';

    // TOOL: the plugin-owned tool mount (hero layout)
    $html .=       '<div class="hero__tool">' . $tool_html . '</div>';

    // FOOT: 3 trust bullets + ask line + two CTAs
    $html .=       '<div class="hero__foot">';
    $default_bullets = array(
        array( "svg" => $svg_check,  "text" => "Byggt på Elsäkerhetslagen och Elsäkerhetsverket" ),
        array( "svg" => $svg_shield, "text" => "Registrerat elinstallationsföretag" ),
        array( "svg" => $svg_circle, "text" => $trust_3 ),
    );
    $bullets   = ampy_lm_bullets( $pid, $default_bullets ); // text = ACF list_items (editable)
    $fixed_svg = array( $svg_check, $svg_shield, $svg_circle );
    $bn        = count( $bullets );
    $html .=         '<ul class="hero__trust">';
    foreach ( $bullets as $bi => $brow ) {
        $bsvg  = $fixed_svg[ $bi ] ?? $svg_circle;
        $btext = ( $bi === $bn - 1 ) ? '<span>' . esc_html( $brow["text"] ) . '</span>' : esc_html( $brow["text"] );
        $html .= '<li>' . $bsvg . ' ' . $btext . '</li>';
    }
    $html .=         '</ul>';
    $html .=         '<p class="hero__foot-ask">' . esc_html( $ask ) . '</p>';
    $html .=         '<div class="hero__actions">';
    $html .=           '<a class="hero__btn hero__btn--primary" href="' . esc_url( $btn_p_url ) . '"><span>' . esc_html( $btn_p ) . '</span>' . $svg_arrow . '</a>';
    $html .=           '<a class="hero__btn hero__btn--secondary" href="' . esc_attr( $btn_s_url ) . '"><span>' . esc_html( $btn_s ) . '</span>' . $svg_phone . '</a>';
    $html .=         '</div>';
    $html .=       '</div>';

    $html .=     '</div>';
    $html .=   '</header>';
    $html .= '</div>';

    return $html;
}}