<?php
// Pagination controls for the Lyris list footer. Receives the page state from
// formulize_LOEbuildPageNav: currentPage, totalPages, numberPerPage, totalEntries,
// pageStart, firstEntry, lastEntry, pageStarts (page=>record offset), jsFunction
// ('pageJump', called with a record-start offset), and entriesPerPageSelector (the
// core <select name="formulize_entriesPerPage">, reused as-is so its sync/reload
// behaviour is preserved — we only restyle it via CSS).

$prevStart = $pageStart - $numberPerPage;
$nextStart = $pageStart + $numberPerPage;
$hasPrev   = $currentPage > 1;
$hasNext   = $currentPage < $totalPages;

// an empty result set reports 0 pages; show it as a single page so the indicator
// reads "Page 1 of 1" rather than "Page 1 of 0".
$displayCurrentPage = max(1, $currentPage);
$displayTotalPages  = max(1, $totalPages);

$chevLeft  = "<svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'><path d='m15 18-6-6 6-6'/></svg>";
$chevRight = "<svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'><path d='m9 18 6-6-6-6'/></svg>";

print "<div class='fz-pagination'>";

// rows-per-page selector (core markup, restyled)
if($entriesPerPageSelector) {
    print "<span class='fz-pagination__perpage'>$entriesPerPageSelector</span>";
}

print "<div class='fz-pagination__nav'>";

if($hasPrev) {
    print "<button type='button' class='fz-btn fz-btn--ghost fz-btn--icon fz-btn--sm' aria-label='"._AM_FORMULIZE_LOE_PREVIOUS."' title='"._AM_FORMULIZE_LOE_PREVIOUS."' onclick=\"$jsFunction('$prevStart');return false;\">$chevLeft</button>";
} else {
    print "<button type='button' class='fz-btn fz-btn--ghost fz-btn--icon fz-btn--sm' disabled aria-disabled='true'>$chevLeft</button>";
}

// Split the leading label ("Page ") into its own span so it can be hidden on
// small screens, leaving just "X of Y". The label is whatever precedes the first
// placeholder in the language string.
$pageStatusFormat = defined('_AM_FORMULIZE_LOE_PAGE_X_OF_Y') ? _AM_FORMULIZE_LOE_PAGE_X_OF_Y : 'Page %s of %s';
$statusFirstPlaceholder = strpos($pageStatusFormat, '%s');
$statusLabel = $statusFirstPlaceholder !== false ? substr($pageStatusFormat, 0, $statusFirstPlaceholder) : '';
$statusNumbersFormat = $statusFirstPlaceholder !== false ? substr($pageStatusFormat, $statusFirstPlaceholder) : $pageStatusFormat;
$statusNumbers = sprintf($statusNumbersFormat, $displayCurrentPage, $displayTotalPages);
print "<span class='fz-pagination__status'><span class='fz-pagination__status-label'>$statusLabel</span>$statusNumbers</span>";

if($hasNext) {
    print "<button type='button' class='fz-btn fz-btn--ghost fz-btn--icon fz-btn--sm' aria-label='"._AM_FORMULIZE_LOE_NEXT."' title='"._AM_FORMULIZE_LOE_NEXT."' onclick=\"$jsFunction('$nextStart');return false;\">$chevRight</button>";
} else {
    print "<button type='button' class='fz-btn fz-btn--ghost fz-btn--icon fz-btn--sm' disabled aria-disabled='true'>$chevRight</button>";
}

print "</div>"; // .fz-pagination__nav

// Desktop-only "go to page" control. Hidden on mobile via CSS (.fz-pagination__goto
// is display:none below 769px). Uses the pageStarts map (page=>record offset) that
// the backend already provides so no backend changes are needed.
if($totalPages > 1) {
    // Encode the pageStarts map as a JS object literal: {1:0, 2:10, 3:20, ...}
    $jsPageStarts = '{';
    $pairs = array();
    foreach($pageStarts as $pg => $offset) {
        $pairs[] = intval($pg) . ':' . intval($offset);
    }
    $jsPageStarts .= implode(',', $pairs) . '}';

    $gotoId = 'fz-goto-' . rand(1000, 9999); // unique id in case multiple paginators exist
    $gotoLabel = defined('_AM_FORMULIZE_LOE_GOTO_PAGE') ? _AM_FORMULIZE_LOE_GOTO_PAGE : 'Go to page';

    print "<span class='fz-pagination__goto'>";
    print "<label for='$gotoId' class='fz-pagination__goto-label'>$gotoLabel</label>";
    print "<input id='$gotoId' type='number' min='1' max='$displayTotalPages' class='fz-pagination__goto-input' aria-label='$gotoLabel' placeholder='#'>";
    print "<button type='button' class='fz-btn fz-btn--ghost fz-btn--sm fz-pagination__goto-btn' aria-label='$gotoLabel'>";
    print defined('_AM_FORMULIZE_LOE_GO') ? _AM_FORMULIZE_LOE_GO : 'Go';
    print "</button>";
    print "<script>(function(){";
    print "var input=document.getElementById('$gotoId');";
    print "var pageStarts=$jsPageStarts;";
    print "var jsFn='$jsFunction';";
    print "var total=$displayTotalPages;";
    print "function go(){";
    print "  var p=parseInt(input.value,10);";
    print "  if(isNaN(p))return;";
    print "  p=Math.max(1,Math.min(p,total));";
    print "  var offset=pageStarts[p];";
    print "  if(typeof offset==='undefined')return;";
    print "  window[jsFn](offset);";
    print "}";
    print "input.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();go();}});";
    print "input.nextElementSibling.addEventListener('click',go);";
    print "}());</script>";
    print "</span>";
}

print "</div>"; // .fz-pagination
