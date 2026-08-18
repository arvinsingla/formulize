<{$settingsBlock}>
<{if $fontUrl}>@import url("<{$fontUrl}>");
<{/if}>
<{if $overrides}>:root {
<{foreach from=$overrides key=token item=value}>  <{$token}>: <{$value}>;
<{/foreach}>}
<{/if}>
