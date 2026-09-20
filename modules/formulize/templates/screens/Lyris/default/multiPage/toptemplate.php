<?php

// The page-nav strip is also the form screen's header bar: the print and
// "Office Use Only" icon buttons ($headerActions, issue #151) sit at its right
// hand end, across from the page tabs, and inherit the strip's sticky
// behaviour. The strip is therefore emitted whenever there is something to put
// in it, not only when the screen is configured to show tabs.
if($showTabs OR $headerActions) {
    print "
    <div id='pageNavTable' class='pill-tabs'>";
    if($showTabs) {
        if($saveAndLeave) {
            print "
            <a href='#' this-page='".($totalPages+1)."' class='icon-arrow-backward pill-tabs__item navtab'> $saveAndLeave</a>";
        }
        foreach($pageTitles as $i=>$title) {
            $activeClass = $i == $currentPage ? "pill-tabs__item--active" : "";
            $thisPage = $i != $currentPage ? "this-page='$i'" : "";
            print "
            <a href='' class='pill-tabs__item navtab $activeClass' $thisPage>$title</a>";
        }
    }
    print $headerActions;
    print"
    </div>";
}

// `.fz-form-screen` carries the form-screen density tokens; the inner
// container carries the design-system label-mode modifier. Density stays at
// the design system's default (`.fz-form` = 38px controls) - issue #113
// removed the `.fz-form--compact` modifier from form screens. The modifier
// is still defined in the stylesheet, for a future per-screen setting.
print "
    <div class='card fz-form-screen'>";

        if($formTitle) {
            print "
            <div class='card__header'>
                <h3 class='card__title'>".$formTitle."</h3>
            </div>";
        }

        print "
        <div class='card__body'>
            <div class='fz-form fz-form--label-top form-container'>
";
