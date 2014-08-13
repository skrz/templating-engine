<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>

    {foreach $stylesheets as $stylesheet}
        <link rel="stylesheet" href="{$stylesheet}">
    {/foreach}

    {foreach $javascripts as $javascript}
        <script src="{$javascript}"></script>
    {/foreach}
</head>
<body>

{include "Table.tpl"}

</body>
</html>
