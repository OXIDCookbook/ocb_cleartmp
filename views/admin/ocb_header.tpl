<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html id="top">
<head>
    <title>[{ oxmultilang ident="NAVIGATION_TITLE" }]</title>
    <link rel="stylesheet" href="[{$oViewConf->getResourceUrl()}]nav.css">
    <link rel="stylesheet" href="[{$oViewConf->getResourceUrl()}]colors.css">
    <link rel="stylesheet" href="[{$oViewConf->getModuleUrl('ocb_cleartmp','out/admin/css/ocb_cleartmp.css')}]">
    <script type="text/javascript" src="[{$oViewConf->getResourceUrl()}]js/libs/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        $('select').change(function(){
            $('#cleartmp').submit();
        });
        $('input').change(function(){
            $('#cleartmp').submit();
        });
    });
    </script>
    <meta http-equiv="Content-Type" content="text/html; charset=[{$charset}]">
</head>
<body>
    [{assign var="oConfig" value=$oViewConf->getConfig()}]
    <ul>
      <li class="act">
          <a href="[{$oViewConf->getSelfLink()}]&cl=navigation&amp;item=home.tpl" id="homelink" target="basefrm" class="rc"><b>Home</b></a>
      </li>
      <li class="sep">
          <a href="[{$oViewConf->getSelfLink()}]&cl=navigation&amp;fnc=logout" id="logoutlink" target="_parent" class="rc"><b>Abmelden</b></a>
      </li>
      <li class="sep">
          <form method="post" action="[{$oViewConf->getSelfLink()}]" id="cleartmp">
              <div>
                  <input type="hidden" name="cl" value="navigation" />
                  <input type="hidden" name="item" value="header.tpl" />
                  <input type="hidden" name="fnc" value="cleartmp" />
                  <input type="hidden" name="editlanguage" value="[{ $editlanguage }]" />
                  [{$oViewConf->getHiddenSid()}]
              </div>
              <span class="rc">Cache leeren:</span>
              <select name="clearoption">
                  <option value="none">- bitte wählen -</option>
                  <option value="smarty">Template Cache</option>
                  <option value="staticcache">Static Cache</option>
                  <option value="language">Sprachecache</option>
                  <option value="database">Datenbankcache</option>
                  <option value="seo">SEO-Cache</option>
                  <option value="complete">kompletter Cache</option>
              </select>
              <input type="checkbox" value="1" [{if $prodmode}]disabled="disabled"[{/if}] id="devmode" name="devmode" [{if $oView->isDevMode()}]checked="checked"[{/if}] />
              <label for="devmode" class="rc[{if $prodmode}] disabled[{/if}]">Entwickler-Modus</label>
          </form>
      </li>
    </ul>

    <div class="version">
        <b>
            [{$oView->getShopFullEdition()}]
            [{$oView->getShopVersion()}]_[{$oView->getRevision()}]
        </b>
    </div>

</body>
</html>